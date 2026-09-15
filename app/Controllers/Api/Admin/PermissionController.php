<?php

namespace App\Controllers\Api\Admin;

use App\Models\PermissionModel;
use App\Models\RolePermissionModel;
use App\Services\AuthorizationService;

class PermissionController extends AdminBaseController
{
    public function index()
    {
        $permissionModel = new PermissionModel();

        $search = trim((string) ($this->request->getGet('search') ?? ''));

        if ($search !== '') {
            $permissionModel->groupStart()
                ->like('name', $search)
                ->orLike('description', $search)
                ->groupEnd();
        }

        return $this->success([
            'permissions' => $permissionModel->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    public function show($id = null)
    {
        $permission = (new PermissionModel())->find((int) $id);

        if ($permission === null) {
            return $this->error('Permission tidak ditemukan.', 404);
        }

        return $this->success($permission);
    }

    public function create()
    {
        $input = $this->input();

        $rules = [
            'name'        => 'required|max_length[100]|is_unique[permissions.name]',
            'description' => 'permit_empty|max_length[255]',
        ];

        if (! $this->validateData($input, $rules)) {
            return $this->error('Validasi gagal.', 422, $this->validator->getErrors());
        }

        $permissionModel = new PermissionModel();

        $permissionId = $permissionModel->insert([
            'name'        => $input['name'],
            'description' => $input['description'] ?? null,
        ]);

        if ($permissionId === false) {
            return $this->error('Validasi gagal.', 422, $permissionModel->errors());
        }

        return $this->success($permissionModel->find($permissionId), 201);
    }

    public function update($id = null)
    {
        $permissionModel = new PermissionModel();
        $permission      = $permissionModel->find((int) $id);

        if ($permission === null) {
            return $this->error('Permission tidak ditemukan.', 404);
        }

        $input = $this->input();

        $rules = [
            'name'        => "permit_empty|max_length[100]|is_unique[permissions.name,id,{$id}]",
            'description' => 'permit_empty|max_length[255]',
        ];

        if (! $this->validateData($input, $rules)) {
            return $this->error('Validasi gagal.', 422, $this->validator->getErrors());
        }

        $data = array_intersect_key($input, array_flip(['name', 'description']));

        if ($data !== [] && ! $permissionModel->update((int) $id, $data)) {
            return $this->error('Validasi gagal.', 422, $permissionModel->errors());
        }

        return $this->success($permissionModel->find($id));
    }

    public function delete($id = null)
    {
        $permissionModel = new PermissionModel();
        $permission      = $permissionModel->find((int) $id);

        if ($permission === null) {
            return $this->error('Permission tidak ditemukan.', 404);
        }

        $rolePermissionModel = new RolePermissionModel();
        $authorizationService = new AuthorizationService();

        $affectedRoleIds = $rolePermissionModel
            ->where('permission_id', (int) $id)
            ->findColumn('role_id') ?? [];

        $permissionModel->delete((int) $id);

        foreach ($affectedRoleIds as $roleId) {
            $authorizationService->invalidateRoleCache((int) $roleId);
        }

        return $this->success(['deleted' => true]);
    }
}
