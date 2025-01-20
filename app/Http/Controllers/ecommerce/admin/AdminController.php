<?php

namespace App\Http\Controllers\ecommerce\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        return view('ecommerce.admin.dashboard');
    }
}
