<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Pivot table message_reads (composite primary key: message_id + user_id).
 * Status delivered/read per pesan per user (Section 9 references.md) — dipakai
 * untuk detail granular (mis. centang biru 1:1 chat). Untuk optimasi group besar,
 * lihat ConversationMemberModel::updateLastRead() (Section 10).
 */
class MessageReadModel extends Model
{
    protected $table         = 'message_reads';
    protected $primaryKey    = 'message_id';
    protected $returnType    = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['message_id', 'user_id', 'delivered_at', 'read_at'];

    protected $useTimestamps = false;

    /**
     * Upsert delivered_at. Timestamp delivered pertama tidak ditimpa oleh panggilan berikutnya.
     */
    public function markDelivered(int $messageId, int $userId): void
    {
        $now = date('Y-m-d H:i:s');

        $this->db->query(
            'INSERT INTO message_reads (message_id, user_id, delivered_at)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE delivered_at = COALESCE(delivered_at, VALUES(delivered_at))',
            [$messageId, $userId, $now],
        );
    }

    /**
     * Upsert read_at (dan delivered_at jika belum ada, karena read menyiratkan delivered).
     * Timestamp pertama tidak ditimpa oleh panggilan berikutnya.
     */
    public function markRead(int $messageId, int $userId): void
    {
        $now = date('Y-m-d H:i:s');

        $this->db->query(
            'INSERT INTO message_reads (message_id, user_id, delivered_at, read_at)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                delivered_at = COALESCE(delivered_at, VALUES(delivered_at)),
                read_at = COALESCE(read_at, VALUES(read_at))',
            [$messageId, $userId, $now, $now],
        );
    }

    /**
     * Status delivered/read per user untuk satu pesan (dipakai sender melihat receipts).
     */
    public function statusFor(int $messageId): array
    {
        return $this->where('message_id', $messageId)->findAll();
    }
}
