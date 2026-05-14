<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperationLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->input('type', 'operation'); // 'operation' or 'activity'
        $search = $request->input('search');

        if ($type === 'activity') {
            $logs = \App\Models\UserActivityLog::with('user')
                ->when($search, function($q, $search) {
                    $q->whereHas('user', fn($query) => $query->where('name', 'like', "%{$search}%"))
                      ->orWhere('action', 'like', "%{$search}%")
                      ->orWhere('url', 'like', "%{$search}%");
                })
                ->latest()
                ->paginate(50);
        } else {
            $logs = OperationLog::with(['user', 'student.subject'])
                ->when($search, function($q, $search) {
                    $q->whereHas('student', fn($query) => $query->where('name', 'like', "%{$search}%"))
                      ->orWhereHas('user', fn($query) => $query->where('name', 'like', "%{$search}%"));
                })
                ->latest()
                ->paginate(50);
        }

        return Inertia::render('Admin/Logs', [
            'logs' => $logs,
            'filters' => [
                'search' => $search,
                'type' => $type
            ]
        ]);
    }
}
