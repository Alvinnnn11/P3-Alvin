<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardC extends Controller
{
    public function index(){
        // $user = User::count();
        return view('dashboard.index');
    }


}