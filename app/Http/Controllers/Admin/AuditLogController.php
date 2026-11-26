<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\SpreadsheetSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->filter($request->all());

        // Summary stats
        $totalLogs = AuditLog::count();
        $todayLogs = AuditLog::whereDate('created_at', today())->count();
        $financialLogs = AuditLog::whereIn('module', ['Transaksi Warga', 'Persetujuan Tarik', 'Gudang & Penjualan'])->count();
        $activeUserToday = AuditLog::whereDate('created_at', today())
            ->whereNotNull('user_name')
            ->select('user_name', 'user_role', DB::raw('count(*) as total'))
            ->groupBy('user_name', 'user_role')
            ->orderByDesc('total')
            ->first();

        // Filters options
        $modules = AuditLog::select('module')->distinct()->orderBy('module')->pluck('module');
        $actions = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');
        $users = User::orderBy('name')->get(['id', 'name', 'role']);

        $logs = $query->orderByDesc('id')->paginate(25)->withQueryString();

        return view('admin.audit-log.index', compact(
            'logs',
            'totalLogs',
            'todayLogs',
            'financialLogs',
            'activeUserToday',
            'modules',
            'actions',
            'users'
        ));
    }

    public function show(AuditLog $auditLog)
    {
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'id' => $auditLog->id,
                'user_name' => $auditLog->user_name,
                'user_role' => $auditLog->user_role,
                'action' => $auditLog->action,
                'module' => $auditLog->module,
                'description' => $auditLog->description,
                'ip_address' => $auditLog->ip_address,
                'user_agent' => $auditLog->user_agent,
                'old_values' => $auditLog->old_values,
                'new_values' => $auditLog->new_values,
                'created_at' => $auditLog->created_at->format('d/m/Y H:i:s'),
                'created_at_human' => $auditLog->created_at->diffForHumans(),
            ]);
        }

        return redirect()->route('admin.audit-log.index');
    }

    public function export(Request $request): StreamedResponse
    {
        $query = AuditLog::filter($request->all())->orderByDesc('id');
        $filename = 'audit-log-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            fputcsv($handle, [
                'ID',
                'Waktu Kejadian',
                'Pengguna',
                'Role',
                'Modul',
                'Aksi',
                'Deskripsi Aktivitas',
                'Alamat IP',
                'Perangkat / User Agent',
            ]);

            foreach ($query->cursor() as $log) {
                fputcsv($handle, SpreadsheetSanitizer::sanitizeRow([
                    $log->id,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user_name ?? 'Sistem',
                    ucfirst($log->user_role ?? '-'),
                    $log->module,
                    ucfirst($log->action),
                    $log->description,
                    $log->ip_address ?? '-',
                    $log->user_agent ?? '-',
                ]));
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
