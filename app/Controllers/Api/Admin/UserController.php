<?php

namespace App\Controllers\Api\Admin;

use App\Models\RoleModel;
use App\Models\UserModel;
use App\Models\UserRoleModel;
use App\Services\AuthorizationService;

class UserController extends AdminBaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $users     = $userModel->select('id, name, username, email, is_banned, created_at')->paginate(20);

        return $this->success([
            'users'      => $users,
            'pagination' => $userModel->pager->getDetails(),
        ]);
    }

    public function show($id = null)
    {
        $userModel = new UserModel();
        $user      = $userModel->find((int) $id);

        if ($user === null) {
            return $this->error('User tidak ditemukan.', 404);
        }

        unset($user['password_hash']);

        $roleIds = (new UserRoleModel())->roleIdsForUser((int) $id);

        $user['roles'] = $roleIds === []
            ? []
            : (new RoleModel())->whereIn('id', $roleIds)->findAll();

        return $this->success($user);
    }

    /**
     * Ganti role aktif user (aturan: satu user = satu role aktif, lihat Section 28.1).
     * Body: { "role_id": 3 }
     */
    public function assignRole($id = null)
    {
        $userModel = new UserModel();
        $user      = $userModel->find((int) $id);

        if ($user === null) {
            return $this->error('User tidak ditemukan.', 404);
        }

        $input = $this->input();

        if (empty($input['role_id'])) {
            return $this->error('role_id wajib diisi.', 422);
        }

        $role = (new RoleModel())->find((int) $input['role_id']);

        if ($role === null) {
            return $this->error('Role tidak ditemukan.', 404);
        }

        (new UserRoleModel())->setSingleRole((int) $id, (int) $role['id']);
        (new AuthorizationService())->invalidateUserCache((int) $id);

        return $this->success(['user_id' => (int) $id, 'role' => $role['name']]);
    }

    public function ban($id = null)
    {
        $userModel = new UserModel();
        $user      = $userModel->find((int) $id);

        if ($user === null) {
            return $this->error('User tidak ditemukan.', 404);
        }

        $input = $this->input();

        $userModel->ban((int) $id, $input['reason'] ?? null);

        return $this->success(['user_id' => (int) $id, 'is_banned' => true]);
    }

    public function unban($id = null)
    {
        $userModel = new UserModel();
        $user      = $userModel->find((int) $id);

        if ($user === null) {
            return $this->error('User tidak ditemukan.', 404);
        }

        $userModel->unban((int) $id);

        return $this->success(['user_id' => (int) $id, 'is_banned' => false]);
    }
}
