<?php

namespace App\Models;

use CodeIgniter\Model;

class MessageModel extends Model
{
    private const MAX_LIMIT     = 100;
    private const DEFAULT_LIMIT = 50;

    protected $table            = 'messages';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['conversation_id', 'sender_id', 'message', 'type', 'reply_message_id', 'edited_at'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts        = [];
    protected array $castHandlers = [];

    // Dates (messages hanya punya created_at, tidak ada updated_at, lihat Section 7)
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'conversation_id' => 'required|is_natural_no_zero',
        'sender_id'       => 'required|is_natural_no_zero',
        'type'            => 'required|in_list[text,image,video,audio,file,location,contact]',
        'message'         => 'permit_empty|max_length[10000]',
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
     * History dengan cursor pagination (Section 17 & 20 references.md) — bukan OFFSET.
     * Tanpa cursor: N pesan terbaru. Dengan before_id: N pesan sebelum id tsb (scroll ke atas).
     * Dengan after_id: pesan setelah id tsb, urut naik (reconnect sync).
     */
    public function historyFor(int $conversationId, ?int $beforeId = null, ?int $afterId = null, int $limit = self::DEFAULT_LIMIT): array
    {
        $limit = max(1, min($limit, self::MAX_LIMIT));

        $builder = $this->where('conversation_id', $conversationId);

        if ($afterId !== null) {
            return $builder->where('id >', $afterId)
                ->orderBy('id', 'ASC')
                ->findAll($limit);
        }

        if ($beforeId !== null) {
            $builder->where('id <', $beforeId);
        }

        $rows = $builder->orderBy('id', 'DESC')->findAll($limit);

        return array_reverse($rows);
    }

    public function markEdited(int $messageId, string $newMessage): bool
    {
        return $this->update($messageId, [
            'message'   => $newMessage,
            'edited_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
