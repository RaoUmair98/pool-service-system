<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        $team = User::where('company_id', $companyId)->active();

        $stats = [
            'admins' => (clone $team)->role(User::ROLE_ADMIN)->count(),
            'technicians' => (clone $team)->role(User::ROLE_TECHNICIAN)->count(),
            'activity_today' => ActivityLog::forCompany($companyId)->whereDate('created_at', today())->count(),
        ];

        $recentActivity = ActivityLog::query()
            ->forCompany($companyId)
            ->with('user')
            ->latest()
            ->latest('id')
            ->limit(10)
            ->get();

        return view('dashboard', compact('stats', 'recentActivity'));
    }
}
