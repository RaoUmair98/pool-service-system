<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = EmailLog::query()
            ->forCompany($request->user()->company_id)
            ->with(['customer'])
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->search, function ($query, $search) {
                $query->where(fn ($q) => $q->where('to_email', 'like', "%$search%")
                    ->orWhere('subject', 'like', "%$search%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%$search%")));
            })
            ->latest()
            ->latest('id')
            ->paginate($this->perPage($request, 25))
            ->withQueryString();

        return $this->infiniteOrView($request, 'email-logs.index', 'email-logs.rows', compact('logs'), 'logs');
    }
}
