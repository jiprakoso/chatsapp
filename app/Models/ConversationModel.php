<?php

namespace App\Models;

use CodeIgniter\Model;

class ConversationModel extends Model
{
    protected $table            = 'conversations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'type', 'name', 'photo', 'created_by', 'private_key',
        'last_message_id', 'last_message_at', 'last_sender_id', 'last_message_preview',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts        = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'type' => 'required|in_list[private,group]',
        'name' => 'permit_empty|max_length[150]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Key deterministik untuk pasangan user pada private conversation (Section 6),
     * mencegah dua private conversation dibuat untuk pasangan user yang sama.
     */
    public static function makePrivateKey(int $userIdA, int $userIdB): string
    {
        $pair = [$userIdA, $userIdB];
        sort($pair);

        return implode(':', $pair);
    }

    public function findPrivateConversation(int $userIdA, int $userIdB): ?array
    {
        return $this->where('private_key', self::makePrivateKey($userIdA, $userIdB))->first();
    }

    /**
     * Perbarui ringkasan conversation setelah ada message baru (Section 4 & 18),
     * supaya chat list tidak perlu query MAX(created_at) ke tabel messages.
     */
    public function touchLastMessage(int $conversationId, int $messageId, int $senderId, string $preview, string $sentAt): void
    {
        $this->update($conversationId, [
            'last_message_id'      => $messageId,
            'last_message_at'      => $sentAt,
            'last_sender_id'       => $senderId,
            'last_message_preview' => mb_substr($preview, 0, 255),
        ]);
    }
}
