<?php

namespace App\Services;

use Config\Database;

/**
 * Menggabungkan permission user dari seluruh role yang dimiliki (Section 28 references.md).
 * Hasil di-cache per user untuk menghindari join berulang pada setiap request.
 */
class AuthorizationService
{
    private const CACHE_PREFIX = 'user_permissions_';
    private const CACHE_TTL    = 300; // fallback TTL, lihat Section 28.5

    public function userHasPermission(int $userId, string $permission): bool
    {
        return in_array($permission, $this->getPermissionNamesForUser($userId), true);
    }

    public function getPermissionNamesForUser(int $userId): array
    {
        $cache    = service('cache');
        $cacheKey = self::CACHE_PREFIX . $userId;

        $cached = $cache->get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        $permissions = $this->fetchPermissionNamesForUser($userId);

        $cache->save($cacheKey, $permissions, self::CACHE_TTL);

        return $permissions;
    }

    /**
     * Panggil ketika role yang dimiliki seorang user berubah (user_roles insert/delete).
     */
    public function invalidateUserCache(int $userId): void
    {
        service('cache')->delete(self::CACHE_PREFIX . $userId);
    }

    /**
     * Panggil ketika permission suatu role berubah (role_permissions insert/delete),
     * karena seluruh user pemegang role tersebut ikut terdampak.
     */
    public function invalidateRoleCache(int $roleId): void
    {
        $rows = Database::connect()
            ->table('user_roles')
            ->select('user_id')
            ->where('role_id', $roleId)
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $this->invalidateUserCache((int) $row['user_id']);
        }
    }

    private function fetchPermissionNamesForUser(int $userId): array
    {
        $rows = Database::connect()
            ->table('user_roles ur')
            ->select('DISTINCT p.name')
            ->join('role_permissions rp', 'rp.role_id = ur.role_id')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->where('ur.user_id', $userId)
            ->get()
            ->getResultArray();

        return array_column($rows, 'name');
    }
}
