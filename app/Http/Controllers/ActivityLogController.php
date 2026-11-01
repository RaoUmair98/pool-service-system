<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Read-only: the activity log can be searched and filtered, never edited or cleared.
 */
class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::query()
            ->with('user')
            ->filter($request->only(['search', 'user_id', 'action', 'group', 'date_from', 'date_to']))
            ->latest()
            ->latest('id')
            ->paginate($this->perPage($request, 50))
            ->withQueryString();

        return view('activity.index', [
            'logs' => $logs,
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
