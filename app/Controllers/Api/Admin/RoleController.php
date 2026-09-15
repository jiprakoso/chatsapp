<?php

namespace App\Controllers\Api\Admin;

use App\Models\PermissionModel;
use App\Models\RoleModel;
use App\Models\RolePermissionModel;
use App\Models\UserRoleModel;
use App\Services\AuthorizationService;

class RoleController extends AdminBaseController
{
    public function index()
    {
        $roleModel = new RoleModel();

        $search = trim((string) ($this->request->getGet('search') ?? ''));

        if ($search !== '') {
            $roleModel->groupStart()
                ->like('name', $search)
                ->orLike('description', $search)
                ->groupEnd();
        }

        $perPage = (int) ($this->request->getGet('per_page') ?? 20);
        $perPage = max(1, min($perPage, 500));

        $roles = $roleModel->orderBy('id', 'ASC')->paginate($perPage);

        // Hitung permission per role supaya tabel admin bisa menampilkannya.
        $rolePermissionModel = new RolePermissionModel();

        foreach ($roles as &$role) {
            $role['permission_count'] = count($rolePermissionModel->permissionIdsForRole((int) $role['id']));
        }
        unset($role);

        return $this->success([
            'roles'      => $roles,
            'pagination' => $roleModel->pager->getDetails(),
        ]);
    }

    public function show($id = null)
    {
        $roleModel = new RoleModel();
        $role      = $roleModel->find((int) $id);

        if ($role === null) {
            return $this->error('Role tidak ditemukan.', 404);
        }

        $permissionIds = (new RolePermissionModel())->permissionIdsForRole((int) $id);

        $role['permissions'] = $permissionIds === []
            ? []
            : (new PermissionModel())->whereIn('id', $permissionIds)->findAll();

        return $this->success($role);
    }

    public function create()
    {
        $input = $this->input();

        $rules = [
            'name'        => 'required|max_length[50]|is_unique[roles.name]',
            'description' => 'permit_empty|max_length[255]',
        ];

        if (! $this->validateData($input, $rules)) {
            return $this->error('Validasi gagal.', 422, $this->validator->getErrors());
        }

        $roleModel = new RoleModel();

        $roleId = $roleModel->insert([
            'name'        => $input['name'],
            'description' => $input['description'] ?? null,
            'is_system'   => 0,
        ]);

        if ($roleId === false) {
            return $this->error('Validasi gagal.', 422, $roleModel->errors());
        }

        return $this->success($roleModel->find($roleId), 201);
    }

    public function update($id = null)
    {
        $roleModel = new RoleModel();
        $role      = $roleModel->find((int) $id);

        if ($role === null) {
            return $this->error('Role tidak ditemukan.', 404);
        }

        $input = $this->input();

        $rules = [
            'name'        => "permit_empty|max_length[50]|is_unique[roles.name,id,{$id}]",
            'description' => 'permit_empty|max_length[255]',
        ];

        if (! $this->validateData($input, $rules)) {
            return $this->error('Validasi gagal.', 422, $this->validator->getErrors());
        }

        $data = array_intersect_key($input, array_flip(['name', 'description']));

        if ($data !== [] && ! $roleModel->update((int) $id, $data)) {
            return $this->error('Validasi gagal.', 422, $roleModel->errors());
        }

        return $this->success($roleModel->find($id));
    }

    public function delete($id = null)
    {
        $roleModel = new RoleModel();
        $role      = $roleModel->find((int) $id);

        if ($role === null) {
            return $this->error('Role tidak ditemukan.', 404);
        }

        if ((bool) $role['is_system']) {
            return $this->error('Role sistem tidak dapat dihapus.', 409);
        }

        $stillUsed = (new UserRoleModel())->where('role_id', (int) $id)->countAllResults() > 0;

        if ($stillUsed) {
            return $this->error('Role masih dipakai oleh user, pindahkan user tersebut ke role lain dahulu.', 409);
        }

        $roleModel->delete((int) $id);

        return $this->success(['deleted' => true]);
    }

    /**
     * Samakan permission suatu role persis dengan daftar permission_id yang dikirim.
     * Body: { "permission_ids": [1, 2, 3] }
     */
    public function syncPermissions($id = null)
    {
        $roleModel = new RoleModel();
        $role      = $roleModel->find((int) $id);

        if ($role === null) {
            return $this->error('Role tidak ditemukan.', 404);
        }

        $input = $this->input();

        if (! isset($input['permission_ids']) || ! is_array($input['permission_ids'])) {
            return $this->error('permission_ids wajib berupa array.', 422);
        }

        $permissionIds = array_map('intval', $input['permission_ids']);

        $validIds = $permissionIds === []
            ? []
            : ((new PermissionModel())->whereIn('id', $permissionIds)->findColumn('id') ?? []);

        (new RolePermissionModel())->syncForRole((int) $id, $validIds);
        (new AuthorizationService())->invalidateRoleCache((int) $id);

        return $this->success([
            'role_id'        => (int) $id,
            'permission_ids' => $validIds,
        ]);
    }
}
