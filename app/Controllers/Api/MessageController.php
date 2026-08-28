<?php

namespace App\Controllers\Api;

use App\Models\ConversationMemberModel;
use App\Models\ConversationModel;
use App\Models\MessageAttachmentModel;
use App\Models\MessageModel;
use App\Models\MessageReadModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class MessageController extends ApiBaseController
{
    private const VALID_TYPES = ['text', 'image', 'video', 'audio', 'file', 'location', 'contact'];

    /**
     * mime => type message (Section 11 flow: file diupload via HTTP, bukan Socket.IO).
     */
    private const ALLOWED_MIMES = [
        'image/jpeg' => 'image',
        'image/png'  => 'image',
        'image/gif'  => 'image',
        'image/webp' => 'image',

        'video/mp4'       => 'video',
        'video/quicktime' => 'video',
        'video/webm'      => 'video',

        'audio/mpeg' => 'audio',
        'audio/mp4'  => 'audio',
        'audio/wav'  => 'audio',
        'audio/ogg'  => 'audio',

        'application/pdf'                                                        => 'file',
        'application/msword'                                                     => 'file',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'file',
        'application/vnd.ms-excel'                                               => 'file',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'       => 'file',
        'application/zip'                                                        => 'file',
        'text/plain'                                                             => 'file',
    ];

    private const MAX_FILE_SIZE_KB = 20480; // 20 MB, lihat Section 27 (file validation)

    /**
     * GET /api/conversations/(:num)/messages?before_id=&after_id=&limit=
     * Cursor pagination, bukan OFFSET (Section 17, 20, 36).
     */
    public function index($conversationId = null)
    {
        $conversationId = (int) $conversationId;
        $userId         = $this->currentUserId();

        if (! $this->assertMember($conversationId, $userId)) {
            return $this->error('Anda bukan anggota conversation ini.', 403);
        }

        $get      = $this->request->getGet();
        $beforeId = isset($get['before_id']) ? (int) $get['before_id'] : null;
        $afterId  = isset($get['after_id']) ? (int) $get['after_id'] : null;
        $limit    = isset($get['limit']) ? (int) $get['limit'] : 50;

        $messages = (new MessageModel())->historyFor($conversationId, $beforeId, $afterId, $limit);

        return $this->success(['messages' => $this->withAttachmentsList($messages)]);
    }

    /**
     * POST /api/conversations/(:num)/messages
     * Body: { "type": "text", "message": "Halo", "reply_message_id": null }
     *
     * Jalur REST ini opsional (Section 30-31); jalur utama pengiriman pesan tetap
     * Socket.IO. Endpoint ini berguna untuk klien yang belum terhubung realtime
     * dan untuk pengujian tanpa server Socket.IO.
     */
    public function store($conversationId = null)
    {
        $conversationId = (int) $conversationId;
        $userId         = $this->currentUserId();

        if (! $this->assertMember($conversationId, $userId)) {
            return $this->error('Anda bukan anggota conversation ini.', 403);
        }

        $input = $this->input();
        $type  = $input['type'] ?? 'text';

        if (! in_array($type, self::VALID_TYPES, true)) {
            return $this->error('type tidak valid.', 422);
        }

        $message = isset($input['message']) ? trim((string) $input['message']) : '';

        if ($type === 'text' && $message === '') {
            return $this->error('message wajib diisi untuk type text.', 422);
        }

        $replyMessageId = ! empty($input['reply_message_id']) ? (int) $input['reply_message_id'] : null;

        if ($replyMessageId !== null) {
            $replyTarget = (new MessageModel())->where('conversation_id', $conversationId)->find($replyMessageId);

            if ($replyTarget === null) {
                return $this->error('reply_message_id tidak ditemukan pada conversation ini.', 422);
            }
        }

        $messageModel = new MessageModel();

        $messageId = $messageModel->insert([
            'conversation_id'  => $conversationId,
            'sender_id'        => $userId,
            'message'          => $message !== '' ? $message : null,
            'type'             => $type,
            'reply_message_id' => $replyMessageId,
        ]);

        if ($messageId === false) {
            return $this->error('Validasi gagal.', 422, $messageModel->errors());
        }

        $saved = $messageModel->find($messageId);

        (new ConversationModel())->touchLastMessage(
            $conversationId,
            (int) $messageId,
            $userId,
            $message !== '' ? $message : '[' . $type . ']',
            $saved['created_at'],
        );

        return $this->success($this->withAttachments($saved), 201);
    }

    /**
     * POST /api/conversations/(:num)/attachments — upload file + buat message dalam satu
     * request (Section 11). File disimpan di writable/uploads (di luar document root),
     * hanya bisa diakses lewat GET /api/attachments/(:num) yang ber-otorisasi.
     * Body multipart: file (wajib), caption (opsional), reply_message_id (opsional).
     */
    public function storeAttachment($conversationId = null)
    {
        $conversationId = (int) $conversationId;
        $userId         = $this->currentUserId();

        if (! $this->assertMember($conversationId, $userId)) {
            return $this->error('Anda bukan anggota conversation ini.', 403);
        }

        $file = $this->request->getFile('file');

        if ($file === null || ! $file->isValid()) {
            return $this->error('file wajib diunggah dan valid.', 422);
        }

        $mime = $file->getMimeType();

        if (! isset(self::ALLOWED_MIMES[$mime])) {
            return $this->error('Tipe file tidak didukung.', 422);
        }

        if ($file->getSizeByUnit('kb') > self::MAX_FILE_SIZE_KB) {
            return $this->error('Ukuran file melebihi batas maksimum (20MB).', 422);
        }

        $input = $this->input();
        $type  = self::ALLOWED_MIMES[$mime];
        $caption = isset($input['caption']) ? trim((string) $input['caption']) : '';

        $replyMessageId = ! empty($input['reply_message_id']) ? (int) $input['reply_message_id'] : null;

        if ($replyMessageId !== null) {
            $replyTarget = (new MessageModel())->where('conversation_id', $conversationId)->find($replyMessageId);

            if ($replyTarget === null) {
                return $this->error('reply_message_id tidak ditemukan pada conversation ini.', 422);
            }
        }

        $targetDir = WRITEPATH . 'uploads/attachments/' . $conversationId . '/';

        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $newName  = $file->getRandomName();
        $fileSize = $file->getSize();
        $original = $file->getClientName();

        if (! $file->move($targetDir, $newName)) {
            return $this->error('Gagal menyimpan file.', 500);
        }

        $storedPath = $targetDir . $newName;
        $storageKey = 'attachments/' . $conversationId . '/' . $newName;

        $width = $height = null;

        if ($type === 'image') {
            $dimensions = @getimagesize($storedPath);
            $width      = $dimensions[0] ?? null;
            $height     = $dimensions[1] ?? null;
        }

        $messageModel = new MessageModel();

        $messageId = $messageModel->insert([
            'conversation_id'  => $conversationId,
            'sender_id'        => $userId,
            'message'          => $caption !== '' ? $caption : null,
            'type'             => $type,
            'reply_message_id' => $replyMessageId,
        ]);

        if ($messageId === false) {
            unlink($storedPath);

            return $this->error('Validasi gagal.', 422, $messageModel->errors());
        }

        (new MessageAttachmentModel())->insert([
            'message_id' => $messageId,
            'file_name'  => $original,
            'file_url'   => $storageKey,
            'mime_type'  => $mime,
            'file_size'  => $fileSize,
            'width'      => $width,
            'height'     => $height,
        ]);

        $saved = $messageModel->find($messageId);

        (new ConversationModel())->touchLastMessage(
            $conversationId,
            (int) $messageId,
            $userId,
            $caption !== '' ? $caption : '[' . $type . ']',
            $saved['created_at'],
        );

        return $this->success($this->withAttachments($saved), 201);
    }

    /**
     * PUT /api/messages/(:num) — hanya sender yang boleh mengedit pesannya sendiri.
     */
    public function update($id = null)
    {
        $messageId = (int) $id;
        $userId    = $this->currentUserId();

        $messageModel = new MessageModel();
        $message      = $messageModel->find($messageId);

        if ($message === null) {
            return $this->error('Pesan tidak ditemukan.', 404);
        }

        if ((int) $message['sender_id'] !== $userId) {
            return $this->error('Anda hanya dapat mengedit pesan sendiri.', 403);
        }

        $input      = $this->input();
        $newMessage = trim((string) ($input['message'] ?? ''));

        if ($newMessage === '') {
            return $this->error('message wajib diisi.', 422);
        }

        $messageModel->markEdited($messageId, $newMessage);

        return $this->success($this->withAttachments($messageModel->find($messageId)));
    }

    /**
     * DELETE /api/messages/(:num) — sender sendiri, atau owner/admin conversation
     * (moderasi tingkat conversation, lihat Section 5 — terpisah dari RBAC sistem).
     */
    public function delete($id = null)
    {
        $messageId = (int) $id;
        $userId    = $this->currentUserId();

        $messageModel = new MessageModel();
        $message      = $messageModel->find($messageId);

        if ($message === null) {
            return $this->error('Pesan tidak ditemukan.', 404);
        }

        $isOwnMessage = (int) $message['sender_id'] === $userId;

        if (! $isOwnMessage) {
            $role = (new ConversationMemberModel())->roleOf((int) $message['conversation_id'], $userId);

            if (! in_array($role, ['owner', 'admin'], true)) {
                return $this->error('Anda tidak memiliki izin menghapus pesan ini.', 403);
            }
        }

        $messageModel->delete($messageId);

        return $this->success(['deleted' => true]);
    }

    /**
     * POST /api/messages/(:num)/delivered — jalur REST opsional (Section 31: delivered
     * normalnya lewat Socket.IO). No-op untuk pesan milik sendiri.
     */
    public function markDelivered($id = null)
    {
        $message = $this->loadMessageForReadStatus((int) $id);

        if ($message instanceof ResponseInterface) {
            return $message;
        }

        if ((int) $message['sender_id'] !== $this->currentUserId()) {
            (new MessageReadModel())->markDelivered((int) $message['id'], $this->currentUserId());
        }

        return $this->success(['message_id' => (int) $message['id'], 'delivered' => true]);
    }

    /**
     * POST /api/messages/(:num)/read — tandai read (menyiratkan delivered) + naikkan
     * conversation_members.last_read_message_id. No-op untuk pesan milik sendiri.
     */
    public function markRead($id = null)
    {
        $message = $this->loadMessageForReadStatus((int) $id);

        if ($message instanceof ResponseInterface) {
            return $message;
        }

        $userId = $this->currentUserId();

        if ((int) $message['sender_id'] !== $userId) {
            (new MessageReadModel())->markRead((int) $message['id'], $userId);
        }

        (new ConversationMemberModel())->bumpLastRead((int) $message['conversation_id'], $userId, (int) $message['id']);

        return $this->success(['message_id' => (int) $message['id'], 'read' => true]);
    }

    /**
     * GET /api/messages/(:num)/status — read receipt per member (siapa sudah delivered/read).
     */
    public function status($id = null)
    {
        $message = $this->loadMessageForReadStatus((int) $id);

        if ($message instanceof ResponseInterface) {
            return $message;
        }

        $rows = (new MessageReadModel())->statusFor((int) $message['id']);

        $userIds = array_column($rows, 'user_id');
        $users   = $userIds === []
            ? []
            : (new UserModel())->select('id, name, username')->whereIn('id', $userIds)->findAll();
        $usersById = array_column($users, null, 'id');

        $receipts = array_map(static function (array $row) use ($usersById) {
            $row['user'] = $usersById[$row['user_id']] ?? null;

            return $row;
        }, $rows);

        return $this->success(['message_id' => (int) $message['id'], 'receipts' => $receipts]);
    }

    /**
     * Ambil pesan + validasi membership untuk endpoint read-status. Mengembalikan
     * array pesan jika valid, atau ResponseInterface error jika gagal.
     */
    private function loadMessageForReadStatus(int $messageId)
    {
        $message = (new MessageModel())->find($messageId);

        if ($message === null) {
            return $this->error('Pesan tidak ditemukan.', 404);
        }

        if (! $this->assertMember((int) $message['conversation_id'], $this->currentUserId())) {
            return $this->error('Anda bukan anggota conversation ini.', 403);
        }

        return $message;
    }

    private function assertMember(int $conversationId, int $userId): bool
    {
        return (new ConversationMemberModel())->isActiveMember($conversationId, $userId);
    }

    private function withAttachments(array $message): array
    {
        $message['attachments'] = (new MessageAttachmentModel())->forMessageId((int) $message['id']);

        return $message;
    }

    private function withAttachmentsList(array $messages): array
    {
        if ($messages === []) {
            return [];
        }

        $ids     = array_column($messages, 'id');
        $grouped = (new MessageAttachmentModel())->forMessageIds($ids);

        return array_map(static function (array $message) use ($grouped) {
            $message['attachments'] = $grouped[$message['id']] ?? [];

            return $message;
        }, $messages);
    }
}
