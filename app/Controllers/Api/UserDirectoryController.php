<?php

namespace App\Controllers\Api;

use App\Models\UserModel;

/**
 * Direktori user untuk keperluan chat (New Private / New Group).
 * Tidak butuh permission admin — hanya jwtauth. Mengembalikan field
 * minimal (id, name, username, photo) supaya role user/moderator
 * yang hanya punya Live Chat tetap bisa memilih lawan chat.
 */
class UserDirectoryController extends ApiBaseController
{
    public function index()
    {
        $search  = trim((string) ($this->request->getGet('search') ?? ''));
        $perPage = (int) ($this->request->getGet('per_page') ?? 100);
        $perPage = max(1, min($perPage, 500));

        $userModel = new UserModel();
        $userModel->where('is_banned', 0);

        if ($search !== '') {
            $userModel->groupStart()
                ->like('name', $search)
                ->orLike('username', $search)
                ->groupEnd();
        }

        $users = $userModel
            ->select('id, name, username, photo, created_at')
            ->orderBy('name', 'ASC')
            ->paginate($perPage);

        return $this->success([
            'users'      => $users,
            'pagination' => $userModel->pager->getDetails(),
        ]);
    }
}
