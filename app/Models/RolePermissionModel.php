<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Pivot table role_permissions (composite primary key: role_id + permission_id).
 * Operasi dilakukan lewat method custom, bukan lewat find()/update() berbasis primaryKey.
 */
class RolePermissionModel extends Model
{
    protected $table         = 'role_permissions';
    protected $primaryKey    = 'role_id';
    protected $returnType    = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['role_id', 'permission_id'];

    protected $useTimestamps = false;

    public function permissionIdsForRole(int $roleId): array
    {
        return $this->where('role_id', $roleId)
            ->findColumn('permission_id') ?? [];
    }

    public function grant(int $roleId, int $permissionId): bool
    {
        if ($this->where(['role_id' => $roleId, 'permission_id' => $permissionId])->countAllResults() > 0) {
            return true;
        }

        return (bool) $this->insert([
            'role_id'       => $roleId,
            'permission_id' => $permissionId,
        ], false);
    }

    public function revoke(int $roleId, int $permissionId): bool
    {
        return (bool) $this->where([
            'role_id'       => $roleId,
            'permission_id' => $permissionId,
        ])->delete();
    }

    /**
     * Samakan permission suatu role persis dengan daftar permission_id yang diberikan.
     */
    public function syncForRole(int $roleId, array $permissionIds): void
    {
        $this->where('role_id', $roleId)->delete();

        foreach (array_unique($permissionIds) as $permissionId) {
            $this->insert([
                'role_id'       => $roleId,
                'permission_id' => (int) $permissionId,
            ], false);
        }
    }
}
