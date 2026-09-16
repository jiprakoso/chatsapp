<?php

namespace App\Controllers\Api;

use App\Models\ConversationMemberModel;
use App\Models\ConversationModel;
use App\Models\UserModel;

class ConversationController extends ApiBaseController
{
    /**
     * GET /api/conversations — chat list milik user yang sedang login (Section 37).
     */
    public function index()
    {
        $userId = $this->currentUserId();

        $memberModel     = new ConversationMemberModel();
        $conversationIds = $memberModel->activeConversationIdsForUser($userId);

        if ($conversationIds === []) {
            return $this->success(['conversations' => [], 'pagination' => null]);
        }

        $conversationModel = new ConversationModel();
        $conversations     = $conversationModel
            ->whereIn('id', $conversationIds)
            ->orderBy('last_message_at', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->paginate(20);

        // Untuk private: tampilkan user tujuan (bukan "(Private)").
        // Group tetap pakai name.
        $memberModel = new ConversationMemberModel();
        $userModel   = new UserModel();
        foreach ($conversations as &$conv) {
            $conv['display_name'] = $conv['name'];
            if ($conv['type'] === 'private') {
                $memberIds = $memberModel->activeMemberUserIds((int) $conv['id']);
                if ($memberIds !== []) {
                    $members = $userModel->select('id, name')->whereIn('id', $memberIds)->findAll();
                    $map = array_column($members, 'name', 'id');
                    // member -> tampilkan lawan bicara
                    $otherIds = array_values(array_filter($memberIds, static fn($id) => (int) $id !== $userId));
                    if ($otherIds !== []) {
                        $conv['display_name'] = $map[$otherIds[0]] ?? $map[$otherIds[0]] ?? '(Private)';
                    } else {
                        // fallback: kalau hanya 1 member atau data anomali, tampilkan nama sendiri
                        $conv['display_name'] = $map[$userId] ?? '(Private)';
                    }
                    $conv['private_members'] = $members;
                }
            }
        }
        unset($conv);

        return $this->success([
            'conversations' => $conversations,
            'pagination'    => $conversationModel->pager->getDetails(),
        ]);
    }

    /**
     * GET /api/conversations/(:num) — detail + daftar member aktif.
     */
    public function show($id = null)
    {
        $userId         = $this->currentUserId();
        $conversationId = (int) $id;

        $conversationModel = new ConversationModel();
        $conversation       = $conversationModel->find($conversationId);

        if ($conversation === null) {
            return $this->error('Conversation tidak ditemukan.', 404);
        }

        $memberModel = new ConversationMemberModel();

        if (! $memberModel->isActiveMember($conversationId, $userId)) {
            return $this->error('Anda bukan anggota conversation ini.', 403);
        }

        $memberUserIds = $memberModel->activeMemberUserIds($conversationId);

        $members = $memberUserIds === []
            ? []
            : (new UserModel())->select('id, name, username, photo')->whereIn('id', $memberUserIds)->findAll();

        $conversation['members'] = $members;

        return $this->success($conversation);
    }

    /**
     * POST /api/conversations/(:num)/read — tandai seluruh pesan sampai message_id (atau
     * pesan terakhir jika tidak diisi) sebagai sudah dibaca. Hanya menaikkan
     * last_read_message_id (Section 10) — TIDAK insert per-message ke message_reads,
     * supaya tetap murah untuk group besar. Untuk receipt granular per pesan, pakai
     * MessageController::markRead() satu per satu.
     */
    public function markRead($id = null)
    {
        $userId         = $this->currentUserId();
        $conversationId = (int) $id;

        $conversationModel = new ConversationModel();
        $conversation       = $conversationModel->find($conversationId);

        if ($conversation === null) {
            return $this->error('Conversation tidak ditemukan.', 404);
        }

        $memberModel = new ConversationMemberModel();

        if (! $memberModel->isActiveMember($conversationId, $userId)) {
            return $this->error('Anda bukan anggota conversation ini.', 403);
        }

        $input     = $this->input();
        $messageId = ! empty($input['message_id']) ? (int) $input['message_id'] : ($conversation['last_message_id'] !== null ? (int) $conversation['last_message_id'] : null);

        if ($messageId === null) {
            return $this->success(['conversation_id' => $conversationId, 'last_read_message_id' => null]);
        }

        $memberModel->bumpLastRead($conversationId, $userId, $messageId);

        return $this->success(['conversation_id' => $conversationId, 'last_read_message_id' => $messageId]);
    }

    /**
     * POST /api/conversations — buat private atau group conversation.
     *
     * Private: { "type": "private", "user_id": 8 }
     * Group:   { "type": "group", "name": "Tim IT", "member_ids": [8, 20, 35] }
     */
    public function create()
    {
        $input = $this->input();

        if (! isset($input['type']) || ! in_array($input['type'], ['private', 'group'], true)) {
            return $this->error('type wajib diisi (private atau group).', 422);
        }

        return $input['type'] === 'private'
            ? $this->createPrivate($input)
            : $this->createGroup($input);
    }

    private function createPrivate(array $input)
    {
        $userId = $this->currentUserId();

        $targetUserId = (int) ($input['user_id'] ?? 0);

        if ($targetUserId <= 0) {
            return $this->error('user_id wajib diisi.', 422);
        }

        if ($targetUserId === $userId) {
            return $this->error('Tidak dapat membuat private conversation dengan diri sendiri.', 422);
        }

        $userModel = new UserModel();

        if ($userModel->find($targetUserId) === null) {
            return $this->error('User tujuan tidak ditemukan.', 404);
        }

        $conversationModel = new ConversationModel();
        $existing          = $conversationModel->findPrivateConversation($userId, $targetUserId);

        if ($existing !== null) {
            return $this->success($existing, 200);
        }

        $conversationId = $conversationModel->insert([
            'type'        => 'private',
            'created_by'  => $userId,
            'private_key' => ConversationModel::makePrivateKey($userId, $targetUserId),
        ]);

        if ($conversationId === false) {
            return $this->error('Validasi gagal.', 422, $conversationModel->errors());
        }

        $memberModel = new ConversationMemberModel();
        $memberModel->addMember((int) $conversationId, $userId, 'member');
        $memberModel->addMember((int) $conversationId, $targetUserId, 'member');

        return $this->success($conversationModel->find($conversationId), 201);
    }

    private function createGroup(array $input)
    {
        $userId = $this->currentUserId();

        $rules = ['name' => 'required|min_length[2]|max_length[150]'];

        if (! $this->validateData($input, $rules)) {
            return $this->error('Validasi gagal.', 422, $this->validator->getErrors());
        }

        $memberIds = array_unique(array_map('intval', (array) ($input['member_ids'] ?? [])));
        $memberIds = array_values(array_filter($memberIds, static fn ($id) => $id > 0 && $id !== $userId));

        if ($memberIds === []) {
            return $this->error('Group membutuhkan minimal 1 member selain pembuat.', 422);
        }

        $validMemberIds = (new UserModel())->whereIn('id', $memberIds)->findColumn('id') ?? [];

        if (count($validMemberIds) !== count($memberIds)) {
            return $this->error('Salah satu member_ids tidak ditemukan.', 422);
        }

        $conversationModel = new ConversationModel();

        $conversationId = $conversationModel->insert([
            'type'       => 'group',
            'name'       => $input['name'],
            'created_by' => $userId,
        ]);

        if ($conversationId === false) {
            return $this->error('Validasi gagal.', 422, $conversationModel->errors());
        }

        $memberModel = new ConversationMemberModel();
        $memberModel->addMember((int) $conversationId, $userId, 'owner');

        foreach ($validMemberIds as $memberId) {
            $memberModel->addMember((int) $conversationId, (int) $memberId, 'member');
        }

        return $this->success($conversationModel->find($conversationId), 201);
    }
}
