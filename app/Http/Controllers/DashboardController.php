<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => User::count(),
            'activity_today' => ActivityLog::whereDate('created_at', today())->count(),
        ];

        $recentActivity = ActivityLog::query()
            ->with('user')
            ->latest()
            ->latest('id')
            ->limit(10)
            ->get();

        return view('dashboard', compact('stats', 'recentActivity'));
    }
}
