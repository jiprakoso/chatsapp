# Chatsapp — Backend Chat Realtime (CodeIgniter 4 + Node Socket.IO)

Aplikasi chat realtime dengan **CodeIgniter 4 sebagai REST API** (source of truth), **Node.js + Socket.IO sebagai transport realtime**, **MariaDB/MySQL** sebagai database, **Valkey** untuk cache/session, **FCM** untuk push notification, dan **Web Admin (Metronic)** untuk operasional. Client mobile (Flutter) berada di luar repo ini.

Dokumen desain rinci: `references.md`. Status pengerjaan: `TODO.md`.

## Arsitektur

```text
                    Flutter App (di luar repo)
                              |
              +-------------------------------+
              |                               |
            HTTPS                           WSS
              |                               |
              v                               v
  +-------------------+           +----------------------+
  |  CodeIgniter 4    |           |  Node.js + Socket.IO |
  |  REST API (:8080) |           |  Realtime (:4000)    |
  +---------+---------+           +----------+-----------+
            |                                |
            +----------------+---------------+
                             |
                             v
                    +------------------+
                    | MariaDB / MySQL  |  <- source of truth
                    +------------------+
                             |
                    +------------------+
                    | Valkey (cache &  |
                    | session)         |
                    +------------------+
                             |
                    +------------------+
                    | FCM (hanya untuk|
                    | member offline)  |
                    +------------------+
```

Prinsip utama:

1. Pesan selalu disimpan ke database. Socket.IO bukan database.
2. Socket.IO hanya untuk delta realtime (new/edit/delete, delivered/read, typing, presence).
3. History, pagination, upload, dan sinkronisasi lewat REST API + cursor (`before_id`/`after_id`/`limit`), bukan reload penuh.
4. FCM hanya notifikasi untuk member yang offline. Data tetap diverifikasi via API saat app aktif/reconnect.
5. `sender_id` selalu dari JWT, tidak pernah dari payload client. Setiap aksi socket memverifikasi membership conversation ke CI4.
6. Satu user boleh multi-device (presence `Map<userId, Set<socketId>>`, FCM token per-device di `user_devices`).

## Fitur yang sudah ada

Backend (CI4):

- Auth JWT HS256 custom (`App\Libraries\Jwt`): `POST /api/auth/register`, `POST /api/auth/login` (identifier = email/username, tolak user banned).
- RBAC dinamis: tabel `roles`, `permissions`, `role_permissions`, `user_roles`; 4 role bawaan (`super_admin, admin, moderator, user`); `JwtAuthFilter` + `PermissionFilter(permission:resource.action)` + cache permission dengan invalidasi eksplisit.
- Conversation private (idempotent via `private_key` `min:max`) dan group, chat list terurut `last_message_at`, detail + member.
- Message: kirim, history cursor-pagination, reply, edit (sender saja), hapus (sender atau owner/admin conversation, soft delete), ringkasan `last_message_*` otomatis.
- Read status ganda: granular per-pesan (`message_reads`, upsert idempotent) + hemat untuk group besar (`conversation_members.last_read_message_id`, bulk mark-read tanpa membengkakkan tabel).
- Attachment: upload multipart (`POST /api/conversations/{id}/attachments`, maks 20 MB app-level, mime whitelist), file disimpan di `writable/uploads/` di luar `public/`, diakses via `GET /api/attachments/{id}` ber-otorisasi.
- Device/FCM: `POST /api/devices`, `DELETE /api/devices/{id}`, endpoint internal service-to-service (`X-Internal-Key`) untuk ambil/nonaktifkan token.
- Web Admin session-based (langsung di root: `/login`, `/`, `/users`, `/roles`, `/permissions`, `/conversations`, `/settings`): login, dashboard statistik, kelola users/roles/permissions/conversations, settings sistem.

Realtime (Node):

- `authenticate` (verifikasi JWT HS256 lokal, secret harus sama dengan CI4) → auto-join semua room `conversation:{id}` → handler baru aktif → presence online.
- Event: `join_conversation`, `leave_conversation`, `send_message`, `message_edit`, `message_delete`, `message_delivered`, `message_read`, `typing_start/stop`; broadcast: `new_message`, `message_updated/deleted/delivered/read`, `user_typing`, `user_online/offline`; operasional: `error`, `token_expired`. Semua mendukung ACK.
- Node tidak akses DB langsung — setiap tulis diteruskan ke REST CI4 dengan Bearer token socket yang sama.
- FCM multicast via `firebase-admin` ke member offline saja; token invalid otomatis dinonaktifkan via internal API.

## Tech stack

| Lapisan | Teknologi |
|---|---|
| API | PHP 8.2+, CodeIgniter 4.7, JWT HS256 self-contained |
| Realtime | Node.js 20+, `socket.io@4`, `jsonwebtoken`, `firebase-admin@12` |
| Database | MariaDB/MySQL (13 migrasi), Valkey 8 (cache db0 + session db1, ekstensi php-redis) |
| Web Admin | CI4 Views + Metronic 8.2.9 (`public/assets/`), jQuery AJAX + DataTables |
| Infra | Docker Compose: `valkey`, `php` (PHP-FPM), `nginx` (:8080), `node` (:4000) |

## Struktur repo

```text
app/
  Controllers/Api/{Auth,Conversation,Message,Attachment,Device}Controller.php
  Controllers/Api/Admin/{Role,Permission,User,Stats,ConversationAdmin,System}Controller.php
  Controllers/WebAdmin/{Dashboard,Users,Roles,Permissions,Conversations,Settings,Auth}Controller.php
  Models/: User, Role, Permission, RolePermission, UserRole,
           Conversation, ConversationMember, Message, MessageRead,
           MessageAttachment, UserDevice
  Filters/: JwtAuthFilter, PermissionFilter, InternalAuthFilter, WebAdminAuthFilter
  Libraries/: Jwt, CurrentUser
  Services/: AuthorizationService
  Database/Migrations/ (13) + Seeds/RolePermissionSeeder.php
  Views/webadmin/
socket-server/
  src/server.js (+ /health)
  src/config/env.js
  src/socket/{auth,connection,conversation,message,typing,presence,rooms,errors}.js
  src/services/{httpClient,conversationService,messageService,notificationService}.js
docker/{Dockerfile.php, nginx.conf, php-fpm-pool.conf}
docker-compose.yml
references.md  # baseline arsitektur (40 section)
TODO.md        # status fitur & tech debt
```

## Prasyarat

- PHP 8.2+ dengan ekstensi `intl`, `mbstring`, `json`, `mysqlnd`, `curl`, `redis`
- Composer, Node.js 18+ (20 disarankan), Docker + Docker Compose
- MariaDB/MySQL berjalan dan database sudah dibuat (default `db_chatapp`)
- Firebase service-account JSON (untuk push; boleh kosong saat dev tanpa FCM)

## Cara jalan cepat (Docker)

```bash
cp .env.example .env 2>/dev/null || true
# edit .env: app.baseURL, database.default.*, JWT_SECRET, INTERNAL_API_KEY,
# VALKEY_HOST/PORT, CACHE_HANDLER, SESSION_DRIVER

cp socket-server/.env.example socket-server/.env
# samakan JWT_SECRET & INTERNAL_API_KEY dengan .env root
# set CI4_API_BASE_URL=http://nginx/api untuk docker-compose

docker compose up --build -d
docker compose exec php php spark migrate
docker compose exec php php spark db:seed RolePermissionSeeder
```

- API: `http://127.0.0.1:8080/api` (via nginx → `public/`)
- Socket.IO: `http://localhost:4000` (`GET /health` untuk cek)
- Web Admin: `http://127.0.0.1:8080/login` (root `/` = dashboard, tanpa prefix `/admin`)

Tanpa Docker (dev lokal):

```bash
composer install
php spark migrate
php spark db:seed RolePermissionSeeder
php spark serve              # API di http://localhost:8080

cd socket-server && npm install && cp .env.example .env
# set CI4_API_BASE_URL=http://localhost:8080/api
npm run dev                  # socket di http://localhost:4000
```

## Konfigurasi penting

`.env` root (CI4): `app.baseURL`, `database.default.{hostname,database,username,password,DBDriver,port}`, `JWT_SECRET` (wajib diganti, harus sama dengan socket-server), `INTERNAL_API_KEY` (service-to-service, harus sama dengan socket-server), `VALKEY_HOST/PORT`, `CACHE_HANDLER=redis`, `SESSION_DRIVER=redis`.

`socket-server/.env`: `PORT=4000`, `JWT_SECRET`, `CI4_API_BASE_URL` (`http://nginx/api` di compose, `http://localhost:8080/api` lokal), `INTERNAL_API_KEY`, `FIREBASE_SERVICE_ACCOUNT_PATH` (tanpa file ini FCM gagal — siapkan JSON dari Firebase Console), `VALKEY_HOST/PORT`.

Catatan operasional: `php.ini` (`upload_max_filesize`/`post_max_size`) harus >= 20 MB agar limit aplikasi berlaku; `client_max_body_size 32m` sudah diset di `docker/nginx.conf`; `forcehttps` filter perlu konfigurasi HTTPS di production.

## Endpoint API (ringkas)

Auth publik: `POST /api/auth/register`, `POST /api/auth/login`.

Butuh `Authorization: Bearer <JWT>`:

| Method | Endpoint | Keterangan |
|---|---|---|
| GET/POST | `/api/conversations` | List milik user / buat private (idempotent) atau group |
| GET | `/api/conversations/{id}` | Detail + member aktif |
| GET | `/api/conversations/{id}/messages?before_id=&after_id=&limit=` | History cursor (default 50, maks 100) |
| POST | `/api/conversations/{id}/messages` | Kirim pesan (jalur REST; utama via socket) |
| POST | `/api/conversations/{id}/attachments` | Upload file + buat message (multipart `file`) |
| POST | `/api/conversations/{id}/read` | Bulk mark-read (`last_read_message_id`) |
| PUT/DELETE | `/api/messages/{id}` | Edit / hapus (otorisasi sender / owner-admin) |
| POST | `/api/messages/{id}/delivered`, `/api/messages/{id}/read` | Tandai granular per-pesan |
| GET | `/api/messages/{id}/status` | Read receipt |
| GET | `/api/attachments/{id}` | Stream file (anggota saja) |
| POST/DELETE | `/api/devices`, `/api/devices/{id}` | Registrasi / nonaktifkan FCM token |

Admin (`permission:*`): `GET/POST /api/admin/roles`, `GET/PUT/DELETE /api/admin/roles/{id}`, `PUT /api/admin/roles/{id}/permissions`, `GET/POST /api/admin/permissions`, `DELETE /api/admin/permissions/{id}`, `GET /api/admin/users`, `GET /api/admin/users/{id}`, `PUT /api/admin/users/{id}/role`, `POST /api/admin/users/{id}/ban|unban`, plus `GET /api/admin/stats|activity|conversations` untuk dashboard.

Internal (`X-Internal-Key`): `GET /api/internal/devices?user_ids=`, `POST /api/internal/devices/deactivate`.

## Event Socket.IO (ringkas)

Client → server: `authenticate {token}`, `join_conversation`, `leave_conversation`, `send_message {conversation_id, type, message}`, `message_edit`, `message_delete`, `message_delivered`, `message_read`, `typing_start`, `typing_stop`.

Server → client: `authenticated`, `new_message`, `message_updated`, `message_deleted`, `message_delivered`, `message_read`, `user_typing`, `user_stopped_typing`, `user_online`, `user_offline`, `error {event,status,message}`, `token_expired`.

## Batasan & roadmap

Batasan saat ini: presence masih in-memory single-instance (adapter Valkey/Redis untuk multi-instance belum dipasang); belum ada rate-limit, logging terstruktur, metrics, CI/CD, dan test otomatis untuk logika bisnis (hanya test bawaan CI4); `README` ini menggantikan starter bawaan.

Roadmap (`TODO.md`): Flutter client (local DB Drift/SQLite, REST+Dio, Socket.IO, FCM), group management, message search, reaction/forward/pin/TTL, block/report, E2E opsional, hardening produksi (rate-limit, logging, Prometheus/Grafana, k6), backup MariaDB, CDN attachment, manifest K8s.

## Lisensi

MIT (mengikuti lisensi CodeIgniter 4 starter).
