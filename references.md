# Chat Application Reference

Dokumen acuan untuk membangun aplikasi chat realtime dengan:

- **Backend API:** CodeIgniter 4
- **Realtime transport:** Node.js + Socket.IO
- **Client mobile:** Flutter
- **Database:** MariaDB/MySQL
- **Push notification:** Firebase Cloud Messaging (FCM)

Dokumen ini menjadi baseline arsitektur. Detail implementasi dapat dikembangkan tanpa mengubah prinsip utama di bawah.

---

## 1. Arsitektur Sistem

```text
                         +----------------------+
                         |      Flutter App     |
                         |       Mobile        |
                         +----------+-----------+
                                    |
                    +---------------+----------------+
                    |                                |
                  HTTPS                          WebSocket
                    |                                |
                    v                                v
          +-------------------+          +----------------------+
          |    CodeIgniter 4  |          | Node.js + Socket.IO |
          |      REST API     |          |  Realtime Server    |
          +---------+---------+          +----------+-----------+
                    |                               |
                    +---------------+---------------+
                                    |
                                    v
                           +------------------+
                           | MariaDB / MySQL |
                           +------------------+
                                    |
                           +------------------+
                           |      FCM         |
                           | Push Notification|
                           +------------------+
```

### Tanggung jawab

#### CodeIgniter 4

Menangani request/response HTTP dan business logic API:

- authentication
- user/profile
- conversation
- chat history
- pagination
- upload file
- pencarian
- group management
- sinkronisasi data
- validasi dan authorization

#### Node.js + Socket.IO

Menangani komunikasi realtime:

- koneksi socket
- autentikasi socket
- join/leave room
- pesan baru
- typing indicator
- delivered status
- read status
- event edit/delete
- presence/online status
- realtime synchronization

#### Flutter

Menangani:

- UI
- local state
- local database/cache
- REST API client
- Socket.IO client
- FCM client
- offline/online state
- rendering message

#### MariaDB/MySQL

Menjadi persistent source of truth untuk data chat.

#### FCM

Hanya digunakan sebagai push notification, terutama ketika recipient tidak sedang aktif/realtime-connected.

---

# 2. Prinsip Utama

## 2.1 Database adalah source of truth

Pesan harus disimpan ke database.

Socket.IO bukan database.

```text
Flutter
   |
   | send_message
   v
Socket.IO
   |
   | INSERT
   v
MariaDB
   |
   | success
   v
Socket.IO
   |
   +--> recipient realtime
   |
   +--> FCM jika diperlukan
```

Jangan menganggap pesan aman hanya karena sudah berhasil di-emit.

---

## 2.2 Socket.IO digunakan untuk delta/realtime event

Socket tidak digunakan untuk mengambil seluruh history chat.

Socket digunakan untuk perubahan terbaru:

- new message
- message edited
- message deleted
- delivered
- read
- typing
- presence

History diambil melalui REST API dan/atau local database.

---

## 2.3 Jangan reload seluruh history setiap ada pesan baru

Saat conversation dibuka:

```text
GET /api/conversations/{id}/messages
        |
        v
Flutter local state/database
        |
        v
UI
```

Jika pesan baru masuk melalui Socket.IO:

```text
Socket.IO
   |
   v
new_message
   |
   v
insert/update local database
   |
   v
UI otomatis berubah
```

Tidak perlu:

```text
new_message
   |
   v
GET seluruh history lagi
```

---

# 3. Struktur Data

Minimal struktur:

```text
users
    |
    +--- conversation_members
              |
              +--- conversations
                        |
                        +--- messages
                              |
                              +--- message_reads
                              |
                              +--- message_attachments

users
    |
    +--- user_devices
    |
    +--- user_roles          (lihat Section 28, RBAC)
```

## 3.1 Table users

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150) NOT NULL,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,

    photo VARCHAR(255) NULL,

    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    deleted_at DATETIME NULL,

    UNIQUE KEY (username),
    UNIQUE KEY (email)
);
```

`username` dan `email` sama-sama unik dan sama-sama bisa dipakai untuk login (lihat `UserModel::findByLogin()`).

---

# 4. Table conversations

Satu conversation digunakan untuk private maupun group chat.

```sql
CREATE TABLE conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    type ENUM('private', 'group') NOT NULL,

    name VARCHAR(150) NULL,
    photo VARCHAR(255) NULL,

    created_by BIGINT UNSIGNED NULL,

    last_message_id BIGINT UNSIGNED NULL,
    last_message_at DATETIME NULL,
    last_sender_id BIGINT UNSIGNED NULL,
    last_message_preview VARCHAR(255) NULL,

    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,

    deleted_at DATETIME NULL,

    INDEX idx_last_message_at (last_message_at),
    INDEX idx_created_by (created_by)
);
```

### Catatan

`last_message_*` digunakan untuk mempercepat halaman daftar conversation.

Jangan setiap membuka chat list melakukan:

```sql
SELECT MAX(created_at)
FROM messages
GROUP BY conversation_id
```

untuk seluruh conversation.

Sebaliknya, update summary conversation setiap ada message baru.

---

# 5. Table conversation_members

```sql
CREATE TABLE conversation_members (
    conversation_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,

    role ENUM('owner', 'admin', 'member') NOT NULL DEFAULT 'member',

    joined_at DATETIME NOT NULL,
    left_at DATETIME NULL,

    is_muted TINYINT(1) NOT NULL DEFAULT 0,
    is_pinned TINYINT(1) NOT NULL DEFAULT 0,

    last_read_message_id BIGINT UNSIGNED NULL,

    PRIMARY KEY (conversation_id, user_id),

    INDEX idx_user_id (user_id),
    INDEX idx_user_conversation (user_id, conversation_id)
);
```

Private chat:

```text
conversation 10
    user 5
    user 8
```

Group:

```text
conversation 20
    user 5
    user 8
    user 20
    user 35
```

---

# 6. Private Conversation

Private conversation tetap menggunakan `conversations`.

Tidak perlu membuat tabel khusus private chat.

Contoh:

```text
conversations

id = 100
type = private
```

Members:

```text
100 | 5
100 | 8
```

## private_key

Untuk mencegah dua private conversation antara pasangan user yang sama, disarankan menambahkan:

```sql
private_key VARCHAR(100) NULL UNIQUE
```

Nilai dibuat berdasarkan user ID terurut.

Contoh:

```text
user 5 + user 8
=> 5:8
```

Dengan demikian:

```text
5 chat 8
```

selalu menggunakan conversation yang sama.

Untuk group, `private_key` bernilai NULL.

---

# 7. Table messages

```sql
CREATE TABLE messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    conversation_id BIGINT UNSIGNED NOT NULL,
    sender_id BIGINT UNSIGNED NOT NULL,

    message TEXT NULL,

    type ENUM(
        'text',
        'image',
        'video',
        'audio',
        'file',
        'location',
        'contact'
    ) NOT NULL DEFAULT 'text',

    reply_message_id BIGINT UNSIGNED NULL,

    edited_at DATETIME NULL,
    deleted_at DATETIME NULL,

    created_at DATETIME NOT NULL,

    INDEX idx_conversation_id_id (conversation_id, id),
    INDEX idx_conversation_created (conversation_id, created_at),
    INDEX idx_sender_id (sender_id),
    INDEX idx_reply_message_id (reply_message_id)
);
```

`id` menjadi identifier utama message.

Client tidak membuat ID database sendiri.

---

# 8. Message ID

Server harus menghasilkan ID message.

Contoh:

```text
id = 1001
```

Payload Socket:

```json
{
    "id": 1001,
    "conversation_id": 10,
    "sender_id": 5,
    "type": "text",
    "message": "Halo",
    "created_at": "2026-08-24 09:00:00"
}
```

Message ID digunakan untuk:

- deduplication
- sync
- read status
- reply
- edit
- delete
- pagination
- reconciliation

---

# 9. Table message_reads

Untuk status delivered/read.

```sql
CREATE TABLE message_reads (
    message_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,

    delivered_at DATETIME NULL,
    read_at DATETIME NULL,

    PRIMARY KEY (message_id, user_id),

    INDEX idx_user_id (user_id)
);
```

Untuk private chat:

```text
message 100
    user 8
        delivered_at = ...
        read_at = ...
```

Untuk group:

```text
message 100

user 8
user 20
user 35
```

---

# 10. Read status alternatif

Untuk group besar, membuat satu row `message_reads` untuk setiap message x user dapat menjadi sangat besar.

Karena itu, untuk tahap scale-up dapat digunakan pendekatan:

```text
conversation_members.last_read_message_id
```

Misalnya:

```text
user 8
conversation 20
last_read_message_id = 5000
```

Artinya user sudah membaca sampai message 5000.

Pendekatan ini lebih hemat daripada membuat row read untuk setiap message.

Gunakan `message_reads` bila membutuhkan detail delivered/read per message. Gunakan `last_read_message_id` untuk optimasi group besar.

---

# 11. Table message_attachments

```sql
CREATE TABLE message_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    message_id BIGINT UNSIGNED NOT NULL,

    file_name VARCHAR(255) NULL,
    file_url VARCHAR(500) NOT NULL,
    mime_type VARCHAR(100) NULL,

    file_size BIGINT UNSIGNED NULL,

    width INT UNSIGNED NULL,
    height INT UNSIGNED NULL,

    duration INT UNSIGNED NULL,

    created_at DATETIME NOT NULL,

    INDEX idx_message_id (message_id)
);
```

File tidak dikirim melalui Socket.IO.

Flow:

```text
Flutter
   |
   | upload HTTP
   v
CodeIgniter 4
   |
   v
Storage
   |
   v
URL
   |
   v
Socket.IO
   |
   v
message metadata
```

Storage dapat berupa local filesystem, object storage, atau service lain.

---

# 12. Table user_devices

FCM token disimpan per device.

```sql
CREATE TABLE user_devices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id BIGINT UNSIGNED NOT NULL,

    platform ENUM('android', 'ios', 'web') NOT NULL,

    fcm_token TEXT NOT NULL,

    is_active TINYINT(1) NOT NULL DEFAULT 1,

    last_seen_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,

    INDEX idx_user_id (user_id)
);
```

Satu user dapat memiliki beberapa device:

```text
user 5
    |
    +-- Android
    +-- Android Tablet
    +-- iPhone
```

Jangan hanya menyimpan satu FCM token di tabel users.

---

# 13. Message Lifecycle

Lifecycle normal:

```text
CREATED
   |
   v
DATABASE INSERT
   |
   v
SENT
   |
   v
DELIVERED
   |
   v
READ
```

Contoh:

```text
User A
  |
  | send_message
  v
Socket Server
  |
  | INSERT
  v
Database
  |
  | message_id = 100
  v
Socket Server
  |
  +----> User B online
  |          |
  |          v
  |      new_message
  |
  +----> User B offline
             |
             v
            FCM
```

---

# 14. Socket.IO Event Convention

Gunakan nama event yang konsisten.

## Client -> Server

```text
authenticate
join_conversation
leave_conversation

send_message
typing_start
typing_stop

message_delivered
message_read

message_edit
message_delete
```

## Server -> Client

```text
authenticated
new_message

user_typing
user_stopped_typing

message_delivered
message_read

message_updated
message_deleted

user_online
user_offline

sync_required
```

Nama event dapat berubah, tetapi convention harus konsisten di seluruh project.

---

# 15. Send Message

Flutter:

```dart
socket.emit('send_message', {
  'conversation_id': 10,
  'type': 'text',
  'message': 'Halo'
});
```

Server:

```text
receive event
      |
      v
validate JWT
      |
      v
validate conversation membership
      |
      v
INSERT messages
      |
      v
UPDATE conversations summary
      |
      v
emit new_message
      |
      +----> online members
      |
      +----> FCM untuk offline member
```

Server harus menentukan:

- sender_id
- message_id
- created_at

Jangan mempercayai:

```json
{
    "sender_id": 5
}
```

dari client.

`sender_id` harus berasal dari session/JWT/socket authentication.

---

# 16. Conversation Room

Setiap conversation menggunakan Socket.IO room.

Contoh:

```text
conversation:10
conversation:20
conversation:30
```

Ketika user membuka conversation:

```dart
socket.emit('join_conversation', {
  'conversation_id': 10
});
```

Server:

```javascript
socket.join(`conversation:${conversationId}`);
```

Pesan baru:

```javascript
io.to(`conversation:${conversationId}`)
  .emit('new_message', message);
```

Server tetap harus memverifikasi bahwa user memang anggota conversation sebelum mengizinkan join.

---

# 17. History Chat

History menggunakan REST API.

Contoh:

```text
GET /api/conversations/10/messages
```

Untuk tahap awal:

```text
ambil message terbaru
```

Jangan mengambil seluruh history.

Contoh:

```text
50 message terakhir
```

Kemudian Flutter menyimpan hasilnya ke local database/state.

---

# 18. Realtime Message Tidak Reload API

Misalnya API mengembalikan:

```text
100
101
102
```

Kemudian Socket mengirim:

```text
103
```

Flutter:

```text
100
101
102
103
```

Tidak melakukan:

```text
GET seluruh history
```

---

# 19. Deduplication

Pesan tidak boleh duplicate.

Contoh:

```text
API
100
101
102

Socket
102
103
```

Hasil yang benar:

```text
100
101
102
103
```

Gunakan `message.id` sebagai unique identifier.

Jika menggunakan local database:

```text
messages.id = PRIMARY KEY
```

sehingga insert duplicate dapat di-ignore/upsert.

---

# 20. Reconnect dan Synchronization

Socket bisa terputus.

Contoh:

```text
message 100
message 101
message 102

disconnect

message 103
message 104

reconnect
```

Client harus melakukan sync.

Jangan mengambil seluruh history.

Gunakan cursor:

```text
GET /api/conversations/10/messages?after_id=102
```

Server:

```text
103
104
```

Flutter memasukkan message tersebut ke local database.

---

# 21. Local Database Flutter

Untuk aplikasi produksi, disarankan menggunakan local database.

Contoh pilihan:

- Drift
- Isar
- SQLite langsung
- Hive untuk kebutuhan tertentu

Arsitektur:

```text
                  REST API
                     |
                     v
              Local Database
                     ^
                     |
                 Socket.IO
                     |
                     v
                  Flutter
                     |
                     v
                     UI
```

UI sebaiknya membaca dari local database/state repository, bukan bergantung langsung pada Socket.IO.

---

# 22. Source of Truth

Server:

```text
MariaDB
```

Client:

```text
Local Database
```

Socket:

```text
Transport perubahan realtime
```

API:

```text
Synchronization / Query
```

FCM:

```text
Notification
```

---

# 23. FCM

FCM bukan transport utama message.

Jangan membuat:

```text
message
   |
   v
FCM
   |
   v
chat
```

Gunakan:

```text
message
   |
   v
Database
   |
   v
Socket.IO
```

FCM hanya membantu memberitahu aplikasi:

```text
Ada pesan baru
```

Payload notification dapat berisi:

```json
{
    "type": "chat_message",
    "conversation_id": "10",
    "message_id": "1001"
}
```

Ketika aplikasi aktif/reconnect, data tetap diverifikasi melalui API/local synchronization.

---

# 24. Typing Indicator

Typing tidak perlu disimpan ke database.

```text
Flutter A
   |
   | typing_start
   v
Socket.IO
   |
   v
Flutter B
```

Ketika berhenti:

```text
typing_stop
```

Gunakan debounce/throttle agar tidak mengirim event untuk setiap karakter.

---

# 25. Presence

Presence juga realtime.

Contoh:

```text
user_online
user_offline
```

Server menyimpan state sementara:

```text
onlineUsers
```

Jangan menjadikan state memory Node sebagai source of truth permanen.

Jika nantinya menggunakan beberapa instance Node.js, state presence perlu shared storage/pub-sub seperti Redis.

---

# 26. Multi-device

Satu user dapat login di banyak perangkat.

```text
User 5

Device A
Device B
Device C
```

Socket mapping harus memungkinkan:

```text
user_id
    |
    +-- socket A
    +-- socket B
    +-- socket C
```

Jangan menggunakan:

```text
user_id -> satu socket
```

karena device lain akan kehilangan event.

---

# 27. Security

Minimum:

```text
HTTPS
WSS
JWT
Socket authentication
API authorization
Conversation membership validation
Input validation
File validation
Rate limiting
```

Setiap event Socket harus memeriksa authorization.

Contoh:

```text
user 5

join conversation 999
```

Server harus mengecek:

```text
Apakah user 5 anggota conversation 999?
```

Jika tidak:

```text
reject
```

Jangan hanya percaya kepada client.

Untuk detail otorisasi berbasis role (siapa boleh mengakses endpoint apa), lihat Section 28 (Role-Based Access Control).

---

# 28. Role-Based Access Control (RBAC)

RBAC mengatur otorisasi pada level API CodeIgniter 4: menentukan endpoint/aksi apa yang boleh diakses oleh seorang user berdasarkan role sistem yang dimilikinya.

RBAC ini terpisah dari role per-conversation (`conversation_members.role`, lihat Section 5). Perbedaannya:

| | RBAC (sistem) | Role per-conversation |
|---|---|---|
| Scope | Seluruh platform | Satu conversation/group |
| Contoh role | super_admin, admin, moderator, user | owner, admin, member |
| Contoh kontrol | akses admin panel, ban user, moderasi lintas-conversation | kick member, ubah info group |
| Disimpan di | `user_roles` | `conversation_members` |

Kedua sistem ini independen dan tidak saling menggantikan.

## 28.1 Model Data

Menggunakan pendekatan dynamic roles + permissions agar role dan permission baru dapat ditambahkan tanpa mengubah kode.

```text
users
  |
  +--- user_roles
            |
            +--- roles
                    |
                    +--- role_permissions
                              |
                              +--- permissions
```

```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,

    is_system TINYINT(1) NOT NULL DEFAULT 0,

    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);
```

```sql
CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,

    created_at DATETIME NOT NULL
);
```

```sql
CREATE TABLE role_permissions (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,

    PRIMARY KEY (role_id, permission_id),
    INDEX idx_permission_id (permission_id)
);
```

```sql
CREATE TABLE user_roles (
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,

    assigned_at DATETIME NOT NULL,

    PRIMARY KEY (user_id, role_id),
    INDEX idx_role_id (role_id)
);
```

`is_system = 1` menandai role bawaan (super_admin, admin, moderator, user) yang tidak boleh dihapus dari admin panel.

### Catatan

Struktur `user_roles` bersifat many-to-many sehingga satu user secara teknis bisa memiliki lebih dari satu role. Untuk kebutuhan awal, cukup terapkan aturan **satu user = satu role aktif** di level aplikasi/service, tanpa perlu constraint tambahan di database. Ini menjaga fleksibilitas untuk kebutuhan multi-role di masa depan.

## 28.2 Permission Naming Convention

Gunakan format `resource.action`:

```text
users.view
users.manage
users.ban

conversations.view_all
conversations.manage

messages.delete_any
messages.view_all

reports.view
reports.resolve

roles.manage
permissions.manage

system.settings
```

Seed default (sudah diimplementasikan di `app/Database/Seeds/RolePermissionSeeder.php`, idempotent — aman dijalankan berulang):

```text
role: super_admin  -> seluruh permission
role: admin         -> users.*, conversations.*, messages.delete_any, reports.*
role: moderator     -> reports.view, reports.resolve, messages.delete_any
role: user          -> (tanpa permission khusus, hanya akses endpoint milik sendiri)
```

## 28.3 Alur Otorisasi

```text
Request
   |
   v
JwtAuthFilter          (authentication: siapa usernya)
   |
   v
PermissionFilter        (authorization: apakah boleh akses ini)
   |
   +--- load roles + permissions user (cache)
   |
   +--- permission tidak ada  ----> 403 Forbidden
   |
   v
Controller
```

Contoh route:

```php
$routes->group('admin', ['filter' => 'jwtauth'], static function ($routes) {
    $routes->get('users', 'Api\Admin\UserController::index', ['filter' => 'permission:users.view']);
    $routes->post('users/(:num)/ban', 'Api\Admin\UserController::ban/$1', ['filter' => 'permission:users.ban']);
    $routes->post('roles', 'Api\Admin\RoleController::create', ['filter' => 'permission:roles.manage']);
});
```

`PermissionFilter` menerima argument nama permission, mengambil role & permission user yang sedang login (dari JWT/session), lalu mencocokkan.

Route dengan filter ganda menggunakan bentuk array:

```php
$routes->get('api/admin/users/(:num)/ban', 'Api\Admin\UserController::ban/$1', [
    'filter' => ['jwtauth', 'permission:users.ban'],
]);
```

### Implementasi JWT

Tidak ada akses composer/internet saat implementasi, sehingga JWT (HS256) dibuat self-contained di `App\Libraries\Jwt` (encode/decode pakai `hash_hmac('sha256', ...)` + `hash_equals`), bukan lewat library seperti `firebase/php-jwt`. Jika suatu saat composer punya akses jaringan, ini bisa diganti tanpa mengubah kontrak `JwtAuthFilter`.

Secret JWT disimpan di `.env` (`JWT_SECRET`) dan dibaca lewat `Config\Jwt`.

Identitas user hasil decode token disimpan di service `currentUser` (`App\Libraries\CurrentUser`, didaftarkan di `Config\Services::currentUser()`) selama request berlangsung:

```php
// di JwtAuthFilter, setelah token valid
service('currentUser')->setId((int) $payload['sub']);

// dibaca di PermissionFilter / Controller
service('currentUser')->id();
service('currentUser')->isAuthenticated();
```

## 28.4 Struktur Tambahan CI4

```text
app/
├── Filters/
│   ├── JwtAuthFilter.php
│   └── PermissionFilter.php
│
├── Libraries/
│   ├── Jwt.php              (encode/decode HS256, self-contained)
│   └── CurrentUser.php      (holder user_id per request)
│
├── Config/
│   └── Jwt.php              (baca JWT_SECRET dari .env)
│
├── Models/
│   ├── RoleModel.php
│   ├── PermissionModel.php
│   ├── RolePermissionModel.php
│   └── UserRoleModel.php
│
├── Services/
│   └── AuthorizationService.php
│
└── Controllers/
    └── Api/
        └── Admin/
            ├── RoleController.php
            ├── PermissionController.php
            └── UserController.php
```

`RoleModel`/`PermissionModel` punya primary key biasa (`id`). `RolePermissionModel`/`UserRoleModel` adalah pivot table dengan composite primary key, sehingga tidak memakai `find()`/`update()` bawaan CI4 Model — operasinya lewat method custom (`grant()`, `revoke()`, `syncForRole()`, `assign()`, `roleIdsForUser()`, dst).

`AuthorizationService` bertanggung jawab mengambil dan menggabungkan seluruh permission milik seorang user (dari semua role yang dimiliki), lalu menyediakan helper seperti:

```php
$authorizationService->userHasPermission($userId, 'users.ban');
```

## 28.5 Caching Permission

Setiap request tidak seharusnya melakukan join beberapa tabel (`user_roles` → `roles` → `role_permissions` → `permissions`) berulang kali.

```text
Request pertama
      |
      v
Query permission user
      |
      v
Simpan ke cache (key: user_id)
      |
      v
Request berikutnya
      |
      v
Ambil dari cache
```

Cache di-invalidate ketika:

- role user berubah (`user_roles` insert/delete)
- permission suatu role berubah (`role_permissions` insert/delete)

Gunakan TTL pendek sebagai fallback (misal 5-10 menit) jika invalidation berbasis event belum diimplementasikan.

### Implementasi

`AuthorizationService` memakai CI4 cache service bawaan (`service('cache')`, handler default `file`, lihat `Config\Cache`), dengan key `user_permissions_{user_id}` dan TTL fallback 300 detik.

```php
$authorizationService->invalidateUserCache($userId);   // panggil setelah user_roles berubah
$authorizationService->invalidateRoleCache($roleId);   // panggil setelah role_permissions berubah, invalidate semua user pemegang role tsb
```

Sudah diverifikasi end-to-end: mengubah `role_permissions` langsung di database belum berefek ke user sampai `invalidateRoleCache()` dipanggil — jadi controller admin yang mengubah role/permission **wajib** memanggil salah satu method invalidate di atas setelah insert/delete, bukan hanya mengandalkan TTL.

## 28.6 Prinsip RBAC

- Role dan permission user divalidasi di server, bukan dari client/payload.
- Role default (`user`) diberikan otomatis saat registrasi; role admin/moderator hanya bisa diberikan oleh user dengan permission `roles.manage`.
- RBAC sistem dan role per-conversation (Section 5) adalah dua konsep terpisah dan tidak saling menggantikan.
- Permission baru ditambahkan lewat data (`permissions` table), bukan hardcode di banyak tempat.
- Setiap endpoint admin/sensitif wajib melewati `PermissionFilter`, tidak cukup hanya `JwtAuthFilter`.

---

# 29. CodeIgniter 4 API Structure

Rekomendasi awal:

```text
app/
├── Controllers/
│   └── Api/
│       ├── AuthController.php
│       ├── ConversationController.php
│       ├── MessageController.php
│       ├── UserController.php
│       └── UploadController.php
│
├── Models/
│   ├── UserModel.php
│   ├── ConversationModel.php
│   ├── ConversationMemberModel.php
│   ├── MessageModel.php
│   ├── MessageReadModel.php
│   ├── MessageAttachmentModel.php
│   └── UserDeviceModel.php
│
├── Services/
│   ├── ConversationService.php
│   ├── MessageService.php
│   ├── NotificationService.php
│   └── UploadService.php
│
└── Filters/
    └── JwtAuthFilter.php
```

Business logic sebaiknya berada di Service, bukan ditumpuk di Controller.

---

# 30. Node Socket.IO Structure

Contoh:

```text
socket-server/
├── src/
│   ├── server.js
│   │
│   ├── socket/
│   │   ├── auth.js
│   │   ├── connection.js
│   │   ├── conversation.js
│   │   ├── message.js
│   │   ├── typing.js
│   │   └── presence.js
│   │
│   ├── services/
│   │   ├── messageService.js
│   │   ├── conversationService.js
│   │   └── notificationService.js
│   │
│   └── database/
│       └── connection.js
│
└── package.json
```

Node Socket Server dapat mengakses database secara langsung untuk operasi realtime tertentu, tetapi business rule utama sebaiknya tetap konsisten dan tidak dibuat ganda antara Node dan CodeIgniter.

Alternatif yang lebih ketat adalah Node memanggil internal API/service CodeIgniter untuk operasi tertentu. Pilihan ini tergantung kebutuhan performa dan kompleksitas proyek.

---

# 31. API vs Socket

| Kebutuhan | REST API | Socket.IO |
|---|---:|---:|
| Login | ✓ | |
| Register | ✓ | |
| Load profile | ✓ | |
| Load chat history | ✓ | |
| Pagination history | ✓ | |
| Search message | ✓ | |
| Upload file | ✓ | |
| Send message | optional | ✓ |
| New message realtime | | ✓ |
| Typing | | ✓ |
| Delivered | | ✓ |
| Read | | ✓ |
| Online status | | ✓ |
| Edit message realtime | | ✓ |
| Delete message realtime | | ✓ |
| Initial synchronization | ✓ | |
| Reconnect synchronization | ✓ | |

Untuk operasi yang membutuhkan durability, server tetap menyimpan perubahan ke database.

---

# 32. Initial Open Conversation

Flow:

```text
User membuka conversation
        |
        v
Flutter cek local database
        |
        +---- ada data ----> tampilkan
        |
        +---- belum ada ---> API
                              |
                              v
                         save local DB
                              |
                              v
                             UI
        |
        v
join Socket.IO room
```

Jika menggunakan local database, UI dapat langsung menampilkan data lokal sambil melakukan sync di background.

---

# 33. New Message Flow

```text
Sender Flutter
      |
      | send_message
      v
Node Socket.IO
      |
      | validate
      v
MariaDB
      |
      | commit
      v
Node Socket.IO
      |
      +----> recipient socket
      |
      +----> sender ACK
      |
      +----> FCM jika perlu
```

Recipient:

```text
Socket.IO
    |
    v
local DB
    |
    v
UI
```

---

# 34. Send Message ACK

Disarankan menggunakan acknowledgement Socket.IO.

Contoh konsep:

```dart
socket.emitWithAck(
  'send_message',
  {
    'conversation_id': 10,
    'message': 'Halo'
  },
  ack: (response) {
    // server response
  },
);
```

Server mengembalikan:

```json
{
    "success": true,
    "message": {
        "id": 1001,
        "conversation_id": 10,
        "sender_id": 5,
        "message": "Halo"
    }
}
```

Ini membantu client mengetahui apakah message benar-benar diterima server.

---

# 35. Client Message State

Flutter dapat memiliki state:

```text
pending
sent
delivered
read
failed
```

Contoh:

```text
User mengetik
     |
     v
send
     |
     v
pending
     |
     v
server ACK
     |
     v
sent
     |
     v
recipient received
     |
     v
delivered
     |
     v
recipient read
     |
     v
read
```

Jika timeout:

```text
pending
   |
   v
failed
```

Client dapat menyediakan tombol retry.

---

# 36. Pagination

Pagination detail dibahas setelah struktur dasar selesai.

Prinsipnya:

```text
latest messages
       |
       v
scroll ke atas
       |
       v
load older messages
```

Gunakan cursor berdasarkan message ID atau timestamp.

Lebih disarankan cursor pagination daripada:

```text
OFFSET 100000
```

untuk tabel message yang besar.

Contoh:

```text
GET /messages?before_id=5000&limit=50
```

---

# 37. Chat List

Halaman utama:

```text
Conversations
```

harus membaca:

```text
conversations
+
conversation_members
+
last_message summary
```

Contoh:

```text
Aji
"Halo..."
10:32

Budi
"Besok jadi?"
09:20

Tim IT
"Deploy jam 8"
Yesterday
```

Tidak perlu mengambil seluruh messages untuk setiap conversation.

Gunakan:

```text
last_message_id
last_message_at
last_sender_id
last_message_preview
```

di `conversations`.

---

# 38. Future Scalability

Untuk satu server:

```text
Flutter
   |
   +-- CI4
   |
   +-- Node Socket.IO
   |
   +-- MariaDB
```

Jika berkembang:

```text
                 Load Balancer
                      |
          +-----------+-----------+
          |                       |
     Socket Server 1        Socket Server 2
          |                       |
          +-----------+-----------+
                      |
                    Redis
                      |
                  MariaDB
```

Redis dapat digunakan untuk:

- Socket.IO adapter
- pub/sub
- presence
- shared ephemeral state
- scaling multiple Socket.IO instances

Database tetap menjadi persistent source of truth.

---

# 39. Prinsip yang Harus Dipertahankan

1. **Message selalu disimpan ke database.**
2. **Socket.IO bukan database.**
3. **FCM bukan transport utama chat.**
4. **Jangan reload seluruh history setiap ada message baru.**
5. **Gunakan message ID sebagai unique identifier.**
6. **Gunakan Socket.IO room berdasarkan conversation.**
7. **Validasi membership pada server.**
8. **Sender ID berasal dari authentication, bukan payload client.**
9. **Gunakan local database Flutter untuk aplikasi produksi.**
10. **Gunakan cursor synchronization ketika reconnect.**
11. **Pisahkan API, realtime transport, dan notification.**
12. **Siapkan multi-device sejak desain awal.**
13. **Update conversation summary ketika ada message baru.**
14. **File dikirim melalui HTTP/storage, bukan Socket.IO.**
15. **Typing/presence adalah ephemeral event dan tidak perlu disimpan sebagai message.**
16. **Otorisasi API divalidasi lewat RBAC di server (role & permission), bukan dari client.**

---

# 40. Target Arsitektur Akhir

```text
                         ┌─────────────────────┐
                         │      Flutter        │
                         │                     │
                         │ UI                  │
                         │ Repository          │
                         │ Local DB            │
                         │ Socket Client       │
                         │ API Client          │
                         │ FCM Client          │
                         └──────────┬──────────┘
                                    │
                ┌───────────────────┴──────────────────┐
                │                                      │
               HTTPS                                WSS
                │                                      │
                v                                      v
       ┌─────────────────┐                   ┌─────────────────┐
       │   CodeIgniter 4 │                   │ Node.js         │
       │   REST API      │                   │ Socket.IO       │
       └────────┬────────┘                   └────────┬────────┘
                │                                      │
                └──────────────────┬───────────────────┘
                                   │
                                   v
                         ┌──────────────────┐
                         │ MariaDB / MySQL  │
                         │                  │
                         │ users            │
                         │ conversations    │
                         │ members          │
                         │ messages         │
                         │ reads            │
                         │ attachments      │
                         │ devices          │
                         └──────────────────┘
                                   │
                                   │
                                   v
                         ┌──────────────────┐
                         │       FCM        │
                         └──────────────────┘
```

Dokumen ini menjadi **baseline reference**. Implementasi berikutnya sebaiknya dilakukan bertahap: **database/schema → CI4 API → Socket.IO authentication → conversation/room → send message → local DB Flutter → synchronization → read/delivered → FCM → pagination → attachment → group → scalability**.
