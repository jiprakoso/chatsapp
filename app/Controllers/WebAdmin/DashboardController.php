<?php

namespace App\Controllers\WebAdmin;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        return view('webadmin/dashboard');
    }
}