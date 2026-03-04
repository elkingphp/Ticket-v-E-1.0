<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Domain\Models\AuditLog;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AuditController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:audit.view'),
        ];
    }

    public function export(Request $request)
    {
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $logs = $query->get();
        $filename = "audit_logs_" . now()->format('Y-m-d_H-i-s') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['ID', 'User', 'Event', 'Module', 'Auditable Type', 'Auditable ID', 'IP Address', 'Date'];

        $callback = function () use ($logs, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user ? $log->user->full_name : 'System',
                    $log->event,
                    $log->category,
                    $log->auditable_type,
                    $log->auditable_id,
                    $log->ip_address,
                    $log->created_at,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function index(Request $request)
    {
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        // Search/Filters
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('log_level')) {
            $query->where('log_level', $request->log_level);
        }

        $logs = $query->paginate(20)->withQueryString();

        $categories = AuditLog::select('category')->distinct()->pluck('category');
        $events = AuditLog::select('event')->distinct()->pluck('event');

        return view('core::audit.index', compact('logs', 'categories', 'events'));
    }

    public function show($id)
    {
        $log = AuditLog::with('user')->findOrFail($id);

        // This would be used for the AJAX lazy loading of Diff
        return view('core::audit.show', compact('log'))->render();
    }
}