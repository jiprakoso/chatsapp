<?php

namespace App\Services;

use App\Models\RoleModel;
use App\Models\UserRoleModel;

/**
 * Hierarki role untuk penjagaan bertingkat.
 * super_admin (100) > admin (80) > moderator (60) > user (10) > custom/unknown (0)
 *
 * Aturan: actor dapat mengelola target jika rank actor >= rank target (selevel atau di bawah).
 * Termasuk mengelola diri sendiri (selevel).
 */
class RoleHierarchy
{
    public const RANKS = [
        'super_admin' => 100,
        'admin'       => 80,
        'moderator'   => 60,
        'user'        => 10,
    ];

    public static function rankOf(string $roleName): int
    {
        return self::RANKS[$roleName] ?? 0;
    }

    public static function highestRankForRoleNames(array $roleNames): int
    {
        $max = 0;
        foreach ($roleNames as $name) {
            $rank = self::rankOf($name);
            if ($rank > $max) {
                $max = $rank;
            }
        }
        return $max;
    }

    public static function highestRankForUser(int $userId): int
    {
        $roleIds = (new UserRoleModel())->roleIdsForUser($userId);
        if ($roleIds === []) {
            return 0;
        }
        $roles = (new RoleModel())->whereIn('id', $roleIds)->findAll();
        $names = array_column($roles, 'name');

        return self::highestRankForRoleNames($names);
    }

    /**
     * Apakah actor boleh mengelola target (edit, ban, assign role ke target)?
     * True jika rank actor >= rank tertinggi target.
     */
    public static function canManage(int $actorId, int $targetId): bool
    {
        // Self-edit selalu diizinkan untuk data profil sendiri (kecuali eskalasi role, dicek terpisah)
        // Tapi untuk konsistensi hierarki, selevel = boleh, jadi self = boleh.
        $actorRank  = self::highestRankForUser($actorId);
        $targetRank = self::highestRankForUser($targetId);

        return $actorRank >= $targetRank;
    }

    public static function canAssignRole(int $actorId, int $roleId): bool
    {
        $actorRank = self::highestRankForUser($actorId);
        $role      = (new RoleModel())->find($roleId);
        if ($role === null) {
            return false;
        }
        $targetRank = self::rankOf($role['name']);

        return $actorRank >= $targetRank;
    }
}
