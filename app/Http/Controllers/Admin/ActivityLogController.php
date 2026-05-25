<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AiEmployee;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ActivityLog::with(['user', 'client', 'aiEmployee'])->orderByDesc('created_at');

        $query = $this->applyFilters($query, $request);

        $logs    = $query->paginate(50)->withQueryString();
        $clients = Client::orderBy('name')->get();
        $users   = User::where('user_type', '!=', 'ai')->orderBy('name')->get();
        $employees = AiEmployee::orderBy('name')->get();

        return view('admin.activity-logs.index', compact('logs', 'clients', 'users', 'employees'));
    }

    public function export(Request $request): Response
    {
        $query = ActivityLog::with(['user', 'client', 'aiEmployee'])->orderByDesc('created_at');
        $query = $this->applyFilters($query, $request);

        $logs = $query->limit(5000)->get();

        $headers = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename=activity-logs-'.now()->format('Y-m-d-His').'.csv'];

        $rows   = [];
        $rows[] = ['Data', 'Nível', 'Ação', 'Módulo', 'Descrição', 'Cliente', 'Usuário', 'Funcionário IA', 'Subject Type', 'Subject ID', 'IP'];

        foreach ($logs as $log) {
            $rows[] = [
                $log->created_at->format('d/m/Y H:i:s'),
                $log->level ?? 'info',
                $log->action,
                $log->module ?? '',
                $log->description ?? '',
                $log->client?->name ?? '',
                $log->user?->name ?? '',
                $log->aiEmployee?->name ?? '',
                $log->subject_type ?? '',
                $log->subject_id ?? '',
                $log->ip_address ?? '',
            ];
        }

        $csv = '';
        foreach ($rows as $row) {
            $csv .= implode(';', array_map(fn ($v) => '"'.str_replace('"', '""', (string) $v).'"', $row))."\n";
        }

        return response("\xEF\xBB\xBF".$csv, 200, $headers);
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('ai_employee_id')) {
            $query->where('ai_employee_id', $request->ai_employee_id);
        }
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }
        if ($request->filled('action')) {
            $query->where('action', 'like', '%'.$request->action.'%');
        }
        if ($request->filled('subject_type')) {
            $query->where('subject_type', 'like', '%'.$request->subject_type.'%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('action', 'like', "%{$s}%")->orWhere('description', 'like', "%{$s}%"));
        }
        return $query;
    }
}
