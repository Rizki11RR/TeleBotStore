<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ActivityLog::with('admin');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('admin', function ($qa) use ($search) {
                      $qa->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->latest()->paginate(25)->withQueryString();

        return view('admin.activity-logs.index', compact('logs'));
    }
}
