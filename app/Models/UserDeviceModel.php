<?php

namespace App\Models;

use CodeIgniter\Model;

class UserDeviceModel extends Model
{
    protected $table            = 'user_devices';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'platform', 'fcm_token', 'is_active', 'last_seen_at'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts        = [
        'is_active' => 'boolean',
    ];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'user_id'   => 'required|is_natural_no_zero',
        'platform'  => 'required|in_list[android,ios,web]',
        'fcm_token' => 'required',
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
     * Satu user dapat memiliki beberapa device (Section 12). Registrasi bersifat
     * upsert berdasarkan (user_id, fcm_token) — jika token yang sama sudah
     * terdaftar (reinstall app tanpa token baru, atau register ulang), cukup
     * refresh last_seen_at/platform/is_active alih-alih membuat baris duplikat.
     */
    public function registerDevice(int $userId, string $platform, string $fcmToken): array
    {
        $existing = $this->where(['user_id' => $userId, 'fcm_token' => $fcmToken])->first();

        $now = date('Y-m-d H:i:s');

        if ($existing !== null) {
            $this->update($existing['id'], [
                'platform'     => $platform,
                'is_active'    => 1,
                'last_seen_at' => $now,
            ]);

            return $this->find($existing['id']);
        }

        $id = $this->insert([
            'user_id'      => $userId,
            'platform'     => $platform,
            'fcm_token'    => $fcmToken,
            'is_active'    => 1,
            'last_seen_at' => $now,
        ]);

        return $this->find($id);
    }

    public function deactivateOwnDevice(int $deviceId, int $userId): bool
    {
        return $this->where(['id' => $deviceId, 'user_id' => $userId])
            ->set('is_active', 0)
            ->update();
    }

    /**
     * Token FCM aktif milik sekumpulan user (dipakai endpoint internal untuk Node
     * saat mengirim push ke member conversation yang sedang offline).
     */
    public function activeTokensForUsers(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        return $this->select('id, user_id, platform, fcm_token')
            ->whereIn('user_id', $userIds)
            ->where('is_active', 1)
            ->findAll();
    }

    /**
     * Dipanggil setelah FCM melaporkan token tidak valid/kedaluwarsa (unregistered),
     * supaya push berikutnya tidak percuma mencoba token yang sama lagi.
     */
    public function deactivateByTokens(array $tokens): int
    {
        if ($tokens === []) {
            return 0;
        }

        $this->whereIn('fcm_token', $tokens)->set('is_active', 0)->update();

        return $this->db->affectedRows();
    }
}
