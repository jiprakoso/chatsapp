<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Pivot table user_roles (composite primary key: user_id + role_id).
 * Operasi dilakukan lewat method custom, bukan lewat find()/update() berbasis primaryKey.
 */
class UserRoleModel extends Model
{
    protected $table         = 'user_roles';
    protected $primaryKey    = 'user_id';
    protected $returnType    = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['user_id', 'role_id', 'assigned_at'];

    protected $useTimestamps = false;

    public function roleIdsForUser(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->findColumn('role_id') ?? [];
    }

    public function assign(int $userId, int $roleId): bool
    {
        if ($this->where(['user_id' => $userId, 'role_id' => $roleId])->countAllResults() > 0) {
            return true;
        }

        return (bool) $this->insert([
            'user_id'     => $userId,
            'role_id'     => $roleId,
            'assigned_at' => date('Y-m-d H:i:s'),
        ], false);
    }

    public function revoke(int $userId, int $roleId): bool
    {
        return (bool) $this->where([
            'user_id' => $userId,
            'role_id' => $roleId,
        ])->delete();
    }

    /**
     * Ganti seluruh role user dengan satu role (aturan default: satu user = satu role aktif).
     */
    public function setSingleRole(int $userId, int $roleId): void
    {
        $this->where('user_id', $userId)->delete();
        $this->assign($userId, $roleId);
    }
}
