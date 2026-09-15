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

        // Filter untuk Web Admin (opsional, diabaikan bila kosong).
        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $role   = trim((string) ($this->request->getGet('role') ?? ''));
        $status = trim((string) ($this->request->getGet('status') ?? ''));

        if ($search !== '') {
            $userModel->groupStart()
                ->like('name', $search)
                ->orLike('username', $search)
                ->orLike('email', $search)
                ->groupEnd();
        }

        if ($status === 'banned') {
            $userModel->where('is_banned', 1);
        } elseif ($status === 'active') {
            $userModel->where('is_banned', 0);
        }

        $perPage = (int) ($this->request->getGet('per_page') ?? 20);
        $perPage = max(1, min($perPage, 500));

        $users = $userModel->select('id, name, username, email, is_banned, created_at')
            ->orderBy('id', 'DESC')
            ->paginate($perPage);

        // Sertakan nama role tiap user supaya tabel admin tidak perlu N request.
        $userRoleModel = new UserRoleModel();
        $roleModel     = new RoleModel();

        foreach ($users as &$user) {
            $roleIds       = $userRoleModel->roleIdsForUser((int) $user['id']);
            $user['roles'] = $roleIds === []
                ? []
                : array_column($roleModel->whereIn('id', $roleIds)->findAll(), 'name');
        }
        unset($user);

        if ($role !== '') {
            $users = array_values(array_filter(
                $users,
                static fn (array $user): bool => in_array($role, $user['roles'], true)
            ));
        }

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
    
    public function me()
    {
        $userId = $this->currentUserId();
        $userModel = new UserModel();
        $user = $userModel->find($userId);
        
        if (!$user) {
            return $this->error('User not found', 404);
        }
        
        unset($user['password_hash']);
        
        $roleIds = (new UserRoleModel())->roleIdsForUser($userId);
        $user['roles'] = $roleIds === []
            ? []
            : (new RoleModel())->whereIn('id', $roleIds)->findAll();
        
        return $this->success($user);
    }
}
