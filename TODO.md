# TODO List - Chatsapp

## ✅ Completed

### Backend (CodeIgniter 4)
- [x] **Database Schema**
  - [x] `users` table (id, name, username, email, password_hash, photo, is_banned, banned_at, banned_reason, timestamps, soft deletes)
  - [x] `roles` table (id, name, description, is_system, timestamps)
  - [x] `permissions` table (id, name, description, created_at)
  - [x] `role_permissions` pivot (composite PK, FK cascade)
  - [x] `user_roles` pivot (composite PK, FK cascade, assigned_at)
  - [x] `conversations` table (id, type, name, photo, created_by, private_key, last_message_*, timestamps, soft deletes)
  - [x] `conversation_members` (conversation_id, user_id, role[owner/admin/member], joined_at, left_at, timestamps)
  - [x] `messages` (id, conversation_id, sender_id, type, content, reply_message_id, edited_at, deleted_at, timestamps)
  - [x] `message_reads` (message_id, user_id, delivered_at, read_at, composite PK, FK cascade)
  - [x] `message_attachments` (id, message_id, file_name, mime_type, file_size, file_url, width, height, duration, timestamps)
  - [x] `user_devices` (id, user_id, platform, fcm_token, is_active, last_seen_at, timestamps)

- [x] **Migrations** - All tables created with proper FK order

- [x] **Models**
  - [x] UserModel (validation, soft deletes, findByLogin)
  - [x] RoleModel (is_system protection)
  - [x] PermissionModel
  - [x] RolePermissionModel (grant/revoke/syncForRole/permissionIdsForRole)
  - [x] UserRoleModel (assign/revoke/setRole/roleIdsForUser)
  - [x] ConversationModel (makePrivateKey, findPrivateConversation)
  - [x] ConversationMemberModel (updateLastRead, etc.)
  - [x] MessageModel (with attachments, forConversationId)
  - [x] MessageReadModel (markDelivered/markRead upsert, statusFor)
  - [x] MessageAttachmentModel (forMessageId/forMessageIds, present)
  - [x] UserDeviceModel (registerDevice upsert, activeTokensForUsers, deactivateByTokens)

- [x] **RBAC Seeder** - 4 roles (super_admin, admin, moderator, user) + 12 permissions + mappings (idempotent)

- [x] **JWT Auth** - Custom HS256 (App\Libraries\Jwt), Config\Jwt, .env secret

- [x] **AuthController**
  - [x] POST /api/auth/register (validation, hash, auto-assign user role, return JWT)
  - [x] POST /api/auth/login (identifier = email|username, password_verify, return JWT)

- [x] **Authorization Layer**
  - [x] AuthorizationService (permission cache + invalidateUserCache/invalidateRoleCache)
  - [x] JwtAuthFilter (Bearer token → CurrentUser)
  - [x] PermissionFilter (permission:resource.action, 401/403)
  - [x] InternalAuthFilter (X-Internal-Key for service-to-service)

- [x] **Admin Endpoints** (jwtauth + permission filters)
  - [x] RoleController (CRUD, syncPermissions, protect is_system, protect in-use)
  - [x] PermissionController (CRUD, auto-invalidate cache)
  - [x] UserController (index, show, assignRole, ban, unban)

- [x] **Conversation/Message Endpoints** (jwtauth)
  - [x] ConversationController (index, create, show, markRead)
  - [x] MessageController (index, store, update, delete, markDelivered, markRead, status)
  - [x] AttachmentController (show - stream with auth)

- [x] **Device/FCM Endpoints**
  - [x] DeviceController: POST /api/devices, DELETE /api/devices/{id} (user JWT)
  - [x] Internal\DeviceController: GET /api/internal/devices, POST /api/internal/devices/deactivate (X-Internal-Key)

### Realtime (Node.js + Socket.IO)
- [x] **Structure** (socket-server/)
  - [x] config/env.js (validated env vars)
  - [x] socket/auth.js (verifyToken HS256, cross-lang verified)
  - [x] socket/connection.js (authenticate → autoJoin → register handlers)
  - [x] socket/conversation.js (autoJoinConversations, join/leave with membership verify)
  - [x] socket/message.js (send_message, edit, delete, delivered, read)
  - [x] socket/typing.js (typing_start/stop ephemeral)
  - [x] socket/presence.js (Map<userId, Set<socketId>> multi-device)
  - [x] socket/rooms.js (conversationRoom helper)
  - [x] socket/errors.js (ApiError → ack/emit error + token_expired)

- [x] **Services**
  - [x] httpClient.js (ci4Request with Bearer + extraHeaders)
  - [x] conversationService.js (fetchConversationIds, verifyMembership)
  - [x] messageService.js (sendMessage, editMessage, deleteMessage, markDelivered, markRead)
  - [x] notificationService.js (FCM via firebase-admin, multicast to offline members)

- [x] **FCM Integration**
  - [x] firebase-admin dependency
  - [x] Offline member detection via presence
  - [x] FCM token fetch via internal CI4 API (X-Internal-Key)
  - [x] sendEachForMulticast with notification + data payload
  - [x] Invalid token handling → auto deactivate via internal API

- [x] **Docker Compose** - node service with env_file

### Documentation
- [x] references.md fully synced (Sections 1-34, 28 RBAC, 23.1 FCM impl, 30 Node structure)

---

## 🔄 In Progress / Next Up

### Flutter Client (outside this repo)
- [ ] Local database schema (Drift/SQLite) mirroring server
- [ ] REST API client (Dio/Retrofit)
- [ ] Socket.IO client connection + event handlers
- [ ] FCM client setup (firebase_messaging)
- [ ] Auth flow (register/login, token storage, refresh)
- [ ] Conversation list + pagination + sync
- [ ] Message list + cursor pagination + local insert
- [ ] Send message (optimistic UI + ACK)
- [ ] Attachments (image picker, upload, preview)
- [ ] Read/delivered status UI
- [ ] Typing indicator
- [ ] Presence (online/offline)
- [ ] Push notification handling (background/terminated)

### Web Admin UI (CI4 + Metronic)
- [ ] Add Metronic HTML 8.2.9 assets to `public/assets/`
- [ ] Create CI4 view layout (header, sidebar, footer) using Metronic partials
- [ ] Auth pages: login, register, forgot password (Metronic auth templates)
- [ ] Dashboard: conversation stats, user stats, message volume
- [ ] User management: list, search, view, ban/unban, assign role
- [ ] Role/Permission management: CRUD roles, sync permissions matrix
- [ ] Conversation management: list, view messages, moderate (delete)
- [ ] Message moderation: view reported/flagged messages
- [ ] System settings: JWT secret rotation, FCM config, upload limits
- [ ] AJAX/API integration with existing CI4 admin endpoints
- [ ] CSRF protection for forms
- [ ] Responsive layout (desktop + mobile)
- [ ] Dark/light theme toggle (Metronic supports this)

### Scalability / Production Hardening
- [ ] Redis for Socket.IO multi-instance (presence, room adapter)
- [ ] CI4 rate limiting (login, register, send_message)
- [ ] CI4 request validation middleware (global)
- [ ] Structured logging (Monolog + Loki/ELK)
- [ ] Metrics (Prometheus + Grafana)
- [ ] CI/CD pipeline (GitHub Actions)
- [ ] Automated tests (PHPUnit + Pest for CI4, Jest for Node)
- [ ] Load testing (k6/Artillery)

### Features
- [ ] Group management (add/remove member, promote/demote, update info)
- [ ] Message search (full-text or Meilisearch)
- [ ] Message reactions (emoji)
- [ ] Message forwarding
- [ ] Pinned messages
- [ ] Disappearing messages (TTL)
- [ ] User blocking
- [ ] Report/abuse system
- [ ] End-to-end encryption (optional)

### DevOps
- [ ] Kubernetes manifests (if moving from docker-compose)
- [ ] Secrets management (Vault/Sealed Secrets)
- [ ] Backup/restore strategy for MariaDB
- [ ] CDN for attachments (Cloudflare R2 / S3)

---

## 🐛 Known Issues / Tech Debt
- [ ] CI4 `forcehttps` filter requires HTTPS in production (configure properly)
- [ ] PHP `upload_max_filesize` / `post_max_size` must be ≥20MB in production php.ini
- [ ] Socket.IO presence is single-instance in-memory (needs Redis for horizontal scaling)
- [ ] No automated test suite yet (manual e2e only)
- [ ] FCM service account JSON not in repo (correct, but needs deployment docs)
- [ ] No database migration rollback testing in CI

---

## 📝 Notes
- All core backend + realtime + FCM pipeline complete and tested end-to-end
- Architecture: CI4 = source of truth (DB, business logic, auth), Node = realtime transport + presence + FCM sender
- Flutter will use same REST API for initial load/sync, Socket.IO for realtime, FCM for push
- Internal API key separates service-to-service from user auth