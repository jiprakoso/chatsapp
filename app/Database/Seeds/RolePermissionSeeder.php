<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed role & permission default sesuai references.md Section 28.2.
 * Idempotent: aman dijalankan berulang kali.
 */
class RolePermissionSeeder extends Seeder
{
    private const PERMISSIONS = [
        'users.view'             => 'Melihat daftar user',
        'users.manage'           => 'Mengelola data user',
        'users.ban'              => 'Ban/unban user',
        'conversations.view_all' => 'Melihat seluruh conversation',
        'conversations.manage'   => 'Mengelola conversation (hapus/arsip)',
        'messages.delete_any'    => 'Menghapus pesan siapapun',
        'messages.view_all'      => 'Melihat seluruh pesan lintas conversation',
        'reports.view'           => 'Melihat laporan/report',
        'reports.resolve'        => 'Menyelesaikan laporan/report',
        'roles.manage'           => 'Mengelola role & permission',
        'permissions.manage'     => 'Mengelola permission',
        'system.settings'        => 'Mengubah pengaturan sistem',
    ];

    private const ROLES = [
        'super_admin' => [
            'description' => 'Akses penuh ke seluruh sistem',
            'permissions' => ['*'],
        ],
        'admin' => [
            'description' => 'Mengelola user, conversation, dan report',
            'permissions' => ['users.*', 'conversations.*', 'messages.delete_any', 'reports.*'],
        ],
        'moderator' => [
            'description' => 'Moderasi konten dan report',
            'permissions' => ['reports.view', 'reports.resolve', 'messages.delete_any'],
        ],
        'user' => [
            'description' => 'Role default untuk pengguna biasa',
            'permissions' => [],
        ],
    ];

    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $permissionIds = $this->seedPermissions($now);
        $roleIds       = $this->seedRoles($now);

        foreach (self::ROLES as $name => $role) {
            $roleId = $roleIds[$name];

            foreach ($this->resolvePermissionNames($role['permissions']) as $permissionName) {
                $permissionId = $permissionIds[$permissionName] ?? null;

                if ($permissionId === null) {
                    continue;
                }

                $this->grantIfMissing($roleId, $permissionId);
            }
        }
    }

    private function seedPermissions(string $now): array
    {
        $ids = [];

        foreach (self::PERMISSIONS as $name => $description) {
            $existing = $this->db->table('permissions')->where('name', $name)->get()->getRowArray();

            if ($existing) {
                $ids[$name] = (int) $existing['id'];
                continue;
            }

            $this->db->table('permissions')->insert([
                'name'        => $name,
                'description' => $description,
                'created_at'  => $now,
            ]);

            $ids[$name] = (int) $this->db->insertID();
        }

        return $ids;
    }

    private function seedRoles(string $now): array
    {
        $ids = [];

        foreach (self::ROLES as $name => $role) {
            $existing = $this->db->table('roles')->where('name', $name)->get()->getRowArray();

            if ($existing) {
                $ids[$name] = (int) $existing['id'];
                continue;
            }

            $this->db->table('roles')->insert([
                'name'        => $name,
                'description' => $role['description'],
                'is_system'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            $ids[$name] = (int) $this->db->insertID();
        }

        return $ids;
    }

    private function grantIfMissing(int $roleId, int $permissionId): void
    {
        $exists = $this->db->table('role_permissions')
            ->where(['role_id' => $roleId, 'permission_id' => $permissionId])
            ->get()
            ->getRowArray();

        if (! $exists) {
            $this->db->table('role_permissions')->insert([
                'role_id'       => $roleId,
                'permission_id' => $permissionId,
            ]);
        }
    }

    /**
     * Resolve wildcard seperti 'users.*' atau '*' menjadi daftar nama permission literal.
     */
    private function resolvePermissionNames(array $patterns): array
    {
        $all = array_keys(self::PERMISSIONS);

        if (in_array('*', $patterns, true)) {
            return $all;
        }

        $resolved = [];

        foreach ($patterns as $pattern) {
            if (str_ends_with($pattern, '.*')) {
                $prefix = substr($pattern, 0, -1);

                foreach ($all as $name) {
                    if (str_starts_with($name, $prefix)) {
                        $resolved[] = $name;
                    }
                }

                continue;
            }

            $resolved[] = $pattern;
        }

        return array_unique($resolved);
    }
}
