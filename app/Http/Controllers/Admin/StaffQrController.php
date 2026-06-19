<?php
// app/Http/Controllers/Admin/StaffQrController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StaffQrController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->get('role'); // optional filter: admin|teacher|finance
        
        $users = User::whereNotNull('code')->orderBy('name','desc')->get();

        return view('admin.users.qr', compact('users', 'role'));
    }
}
