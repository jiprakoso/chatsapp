<?php

namespace App\Models;

use CodeIgniter\Model;

class MessageAttachmentModel extends Model
{
    protected $table            = 'message_attachments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'message_id', 'file_name', 'file_url', 'mime_type', 'file_size', 'width', 'height', 'duration',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts        = [];
    protected array $castHandlers = [];

    // Dates (message_attachments hanya punya created_at, lihat Section 11)
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    // Validation
    protected $validationRules = [
        'message_id' => 'required|is_natural_no_zero',
        'file_url'   => 'required|max_length[500]',
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
     * Attachment untuk banyak message sekaligus (hindari N+1 saat render history),
     * dikelompokkan per message_id.
     */
    public function forMessageIds(array $messageIds): array
    {
        if ($messageIds === []) {
            return [];
        }

        $rows = $this->whereIn('message_id', $messageIds)->findAll();

        $grouped = [];

        foreach ($rows as $row) {
            $grouped[$row['message_id']][] = $this->present($row);
        }

        return $grouped;
    }

    public function forMessageId(int $messageId): array
    {
        return array_map(
            [$this, 'present'],
            $this->where('message_id', $messageId)->findAll(),
        );
    }

    /**
     * file_url di database menyimpan storage key relatif (bukan URL publik langsung),
     * karena file disimpan di writable/uploads (di luar document root) dan hanya bisa
     * diakses lewat endpoint ber-otorisasi GET /api/attachments/(:num). Method ini
     * mengganti file_url dengan link yang benar-benar bisa diakses klien.
     */
    private function present(array $row): array
    {
        helper('url');

        $row['file_url'] = site_url('api/attachments/' . $row['id']);

        return $row;
    }
}
