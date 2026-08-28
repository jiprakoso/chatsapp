<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Pivot table conversation_members (composite primary key: conversation_id + user_id).
 * Operasi dilakukan lewat method custom, bukan lewat find()/update() bawaan CI4 Model.
 */
class ConversationMemberModel extends Model
{
    protected $table         = 'conversation_members';
    protected $primaryKey    = 'conversation_id';
    protected $returnType    = 'array';
    protected $protectFields = true;
    protected $allowedFields = [
        'conversation_id', 'user_id', 'role', 'joined_at', 'left_at',
        'is_muted', 'is_pinned', 'last_read_message_id',
    ];

    protected $useTimestamps = false;

    /**
     * Aktif = belum leave (left_at IS NULL). Member yang sudah leave tetap
     * ada row-nya (histori), tapi tidak dianggap anggota untuk otorisasi.
     */
    public function isActiveMember(int $conversationId, int $userId): bool
    {
        return $this->where([
            'conversation_id' => $conversationId,
            'user_id'         => $userId,
            'left_at'         => null,
        ])->countAllResults() > 0;
    }

    public function roleOf(int $conversationId, int $userId): ?string
    {
        $row = $this->where([
            'conversation_id' => $conversationId,
            'user_id'         => $userId,
            'left_at'         => null,
        ])->first();

        return $row['role'] ?? null;
    }

    public function addMember(int $conversationId, int $userId, string $role = 'member'): bool
    {
        return (bool) $this->insert([
            'conversation_id' => $conversationId,
            'user_id'         => $userId,
            'role'            => $role,
            'joined_at'       => date('Y-m-d H:i:s'),
        ], false);
    }

    public function activeConversationIdsForUser(int $userId): array
    {
        return $this->where(['user_id' => $userId, 'left_at' => null])
            ->findColumn('conversation_id') ?? [];
    }

    public function activeMemberUserIds(int $conversationId): array
    {
        return $this->where(['conversation_id' => $conversationId, 'left_at' => null])
            ->findColumn('user_id') ?? [];
    }

    public function updateLastRead(int $conversationId, int $userId, int $messageId): bool
    {
        return $this->where([
            'conversation_id' => $conversationId,
            'user_id'         => $userId,
        ])->set('last_read_message_id', $messageId)->update();
    }

    /**
     * Naikkan last_read_message_id hanya jika messageId lebih besar dari nilai saat ini
     * (Section 10 — optimasi read status untuk group besar, tanpa insert per-message).
     */
    public function bumpLastRead(int $conversationId, int $userId, int $messageId): void
    {
        $this->db->query(
            'UPDATE conversation_members
             SET last_read_message_id = GREATEST(COALESCE(last_read_message_id, 0), ?)
             WHERE conversation_id = ? AND user_id = ?',
            [$messageId, $conversationId, $userId],
        );
    }

    public function leave(int $conversationId, int $userId): bool
    {
        return $this->where([
            'conversation_id' => $conversationId,
            'user_id'         => $userId,
        ])->set('left_at', date('Y-m-d H:i:s'))->update();
    }
}
