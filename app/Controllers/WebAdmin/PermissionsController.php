<?php

namespace App\Controllers\WebAdmin;

use App\Controllers\BaseController;

class PermissionsController extends BaseController
{
    private function ensure(): ?\CodeIgniter\HTTP\RedirectResponse
    {
        $uid  = (int) session()->get('admin_user_id');
        $auth = $uid ? new \App\Services\AuthorizationService() : null;
        if ($auth === null || ! $auth->userHasPermission($uid, 'permissions.manage')) {
            return redirect()->to(base_url('chat'));
        }
        return null;
    }

    public function index()
    {
        if ($r = $this->ensure()) return $r;
        return view('webadmin/permissions/index');
    }
    
    public function create()
    {
        if ($r = $this->ensure()) return $r;
        return view('webadmin/permissions/form');
    }
    
    public function edit($id = null)
    {
        if ($r = $this->ensure()) return $r;
        $permModel = new \App\Models\PermissionModel();
        $permission = $permModel->find($id);
        
        if (!$permission) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Permission not found');
        }
        
        return view('webadmin/permissions/form', ['permission' => $permission]);
    }
}