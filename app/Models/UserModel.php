<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'username', 'email', 'password_hash', 'photo'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts       = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'name'          => 'required|min_length[2]|max_length[150]',
        'username'      => 'required|min_length[3]|max_length[50]|regex_match[/^[a-zA-Z0-9._]+$/]|is_unique[users.username,id,{id}]',
        'email'         => 'required|valid_email|max_length[150]|is_unique[users.email,id,{id}]',
        'password_hash' => 'required',
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
     * Cari user berdasarkan email atau username, untuk keperluan login.
     *
     * Dikelompokkan dengan groupStart()/groupEnd() supaya filter soft-delete
     * yang ditambahkan otomatis oleh first() tidak salah presedensi
     * (tanpa ini: `email=X OR (username=X AND deleted_at IS NULL)`).
     */
    public function findByLogin(string $identifier): ?array
    {
        return $this->groupStart()
                ->where('email', $identifier)
                ->orWhere('username', $identifier)
            ->groupEnd()
            ->first();
    }

    /**
     * is_banned/banned_at/banned_reason sengaja tidak masuk $allowedFields
     * (tidak boleh mass-assignable lewat update() biasa), jadi diubah lewat
     * builder() langsung, bukan lewat method update() milik Model.
     */
    public function ban(int $userId, ?string $reason = null): bool
    {
        return $this->builder()->where('id', $userId)->update([
            'is_banned'     => 1,
            'banned_at'     => date('Y-m-d H:i:s'),
            'banned_reason' => $reason,
        ]);
    }

    public function unban(int $userId): bool
    {
        return $this->builder()->where('id', $userId)->update([
            'is_banned'     => 0,
            'banned_at'     => null,
            'banned_reason' => null,
        ]);
    }
}
