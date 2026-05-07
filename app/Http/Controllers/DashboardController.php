<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {        
        $projects = auth()->user()
            ->projects()
            ->with('chapters')
            ->get();

        return inertia('Dashboard', [
            'projects' => $projects
        ]);
    }
}
