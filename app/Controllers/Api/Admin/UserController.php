<?php

namespace App\Controllers\Api\Admin;

use App\Models\RoleModel;
use App\Models\UserModel;
use App\Models\UserRoleModel;
use App\Services\AuthorizationService;
use App\Services\RoleHierarchy;

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
     * Buat user baru + assign role awal (default: user).
     * Body: { "name", "username", "email", "password", "role_id"? }
     * Penjagaan: butuh permission users.manage (route filter) + actor hanya boleh
     * assign role selevel atau di bawahnya (RoleHierarchy::canAssignRole).
     */
    public function store()
    {
        $actorId = $this->currentUserId();
        $input   = $this->input();

        $rules = [
            'name'     => 'required|min_length[2]|max_length[150]',
            'username' => 'required|min_length[3]|max_length[50]|regex_match[/^[a-zA-Z0-9._]+$/]|is_unique[users.username]',
            'email'    => 'required|valid_email|max_length[150]|is_unique[users.email]',
            'password' => 'required|min_length[8]',
        ];

        if (! $this->validateData($input, $rules)) {
            return $this->error('Validasi gagal.', 422, $this->validator->getErrors());
        }

        $roleModel = new RoleModel();

        if (! empty($input['role_id'])) {
            $role = $roleModel->find((int) $input['role_id']);

            if ($role === null) {
                return $this->error('Role tidak ditemukan.', 404);
            }

            if (! RoleHierarchy::canAssignRole($actorId, (int) $role['id'])) {
                return $this->error('Anda tidak boleh memberikan role dengan level lebih tinggi dari milik Anda.', 403);
            }
        } else {
            $role = $roleModel->findByName('user');

            if ($role === null) {
                return $this->error('Role default (user) tidak ditemukan.', 500);
            }
        }

        $userModel = new UserModel();

        $userId = $userModel->insert([
            'name'          => $input['name'],
            'username'      => $input['username'],
            'email'         => $input['email'],
            'password_hash' => password_hash($input['password'], PASSWORD_DEFAULT),
        ]);

        if ($userId === false) {
            return $this->error('Validasi gagal.', 422, $userModel->errors());
        }

        (new UserRoleModel())->setSingleRole((int) $userId, (int) $role['id']);

        $created = $userModel->find($userId);
        unset($created['password_hash']);
        $created['roles'] = [$role];

        return $this->success($created, 201);
    }

    /**
     * Update data user (name, username, email) dengan penjagaan bertingkat.
     * Body: { "name": "...", "username": "...", "email": "..." }
     */
    public function update($id = null)
    {
        $actorId  = $this->currentUserId();
        $targetId = (int) $id;

        if ($actorId !== $targetId && ! RoleHierarchy::canManage($actorId, $targetId)) {
            return $this->error('Anda tidak memiliki hak untuk mengedit user dengan level lebih tinggi.', 403);
        }

        $userModel = new UserModel();
        $user      = $userModel->find($targetId);

        if ($user === null) {
            return $this->error('User tidak ditemukan.', 404);
        }

        $input = $this->input();

        // Filter hanya field yang boleh diupdate via endpoint ini
        $data = array_intersect_key($input, array_flip(['name', 'username', 'email']));

        if ($data === []) {
            return $this->error('Tidak ada data untuk diupdate.', 422);
        }

        // Validasi manual dengan is_unique mengecualikan diri sendiri
        $rules = [];
        if (array_key_exists('name', $data)) {
            $rules['name'] = 'required|min_length[2]|max_length[150]';
        }
        if (array_key_exists('username', $data)) {
            $rules['username'] = "required|min_length[3]|max_length[50]|regex_match[/^[a-zA-Z0-9._]+$/]|is_unique[users.username,id,{$targetId}]";
        }
        if (array_key_exists('email', $data)) {
            $rules['email'] = "required|valid_email|max_length[150]|is_unique[users.email,id,{$targetId}]";
        }

        if (! $this->validateData($data, $rules)) {
            return $this->error('Validasi gagal.', 422, $this->validator->getErrors());
        }

        if (! $userModel->update($targetId, $data)) {
            return $this->error('Validasi gagal.', 422, $userModel->errors());
        }

        return $this->success($userModel->find($targetId));
    }

    /**
     * Ganti role aktif user (aturan: satu user = satu role aktif, lihat Section 28.1).
     * Body: { "role_id": 3 }
     * Penjagaan: actor hanya boleh assign role selevel atau di bawahnya, dan hanya boleh
     * mengelola target selevel atau di bawahnya.
     */
    public function assignRole($id = null)
    {
        $actorId  = $this->currentUserId();
        $targetId = (int) $id;

        if ($actorId !== $targetId && ! RoleHierarchy::canManage($actorId, $targetId)) {
            return $this->error('Anda tidak memiliki hak untuk mengubah role user dengan level lebih tinggi.', 403);
        }

        $userModel = new UserModel();
        $user      = $userModel->find($targetId);

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

        if (! RoleHierarchy::canAssignRole($actorId, (int) $role['id'])) {
            return $this->error('Anda tidak boleh memberikan role dengan level lebih tinggi dari milik Anda.', 403);
        }

        (new UserRoleModel())->setSingleRole($targetId, (int) $role['id']);
        (new AuthorizationService())->invalidateUserCache($targetId);

        return $this->success(['user_id' => $targetId, 'role' => $role['name']]);
    }

    public function ban($id = null)
    {
        $actorId  = $this->currentUserId();
        $targetId = (int) $id;

        if (! RoleHierarchy::canManage($actorId, $targetId)) {
            return $this->error('Anda tidak memiliki hak untuk mem-ban user dengan level lebih tinggi.', 403);
        }

        $userModel = new UserModel();
        $user      = $userModel->find($targetId);

        if ($user === null) {
            return $this->error('User tidak ditemukan.', 404);
        }

        $input = $this->input();

        $userModel->ban($targetId, $input['reason'] ?? null);

        return $this->success(['user_id' => $targetId, 'is_banned' => true]);
    }

    public function unban($id = null)
    {
        $actorId  = $this->currentUserId();
        $targetId = (int) $id;

        if (! RoleHierarchy::canManage($actorId, $targetId)) {
            return $this->error('Anda tidak memiliki hak untuk meng-unban user dengan level lebih tinggi.', 403);
        }

        $userModel = new UserModel();
        $user      = $userModel->find($targetId);

        if ($user === null) {
            return $this->error('User tidak ditemukan.', 404);
        }

        $userModel->unban($targetId);

        return $this->success(['user_id' => $targetId, 'is_banned' => false]);
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
        
        $user['permissions'] = (new AuthorizationService())->getPermissionNamesForUser($userId);
        
        return $this->success($user);
    }
}
