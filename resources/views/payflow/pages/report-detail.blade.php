@php
    $user       = auth()->user();
    $isHr       = $user->isHrManager();
    $isFinance  = $user->isFinanceManager();
    $isSuperAdmin = $isSuperAdminViewing ?? $user->isSuperAdmin();

    $statusMap = [
        'draft'            => ['label' => 'Draft',             'badge' => 'badge-amber',  'bg' => '#fffbeb', 'border' => '#fde68a', 'color' => '#92400e'],
        'needs_review'     => ['label' => 'Needs Review',      'badge' => 'badge-amber',  'bg' => '#fffbeb', 'border' => '#fde68a', 'color' => '#92400e'],
        'pending_approval' => ['label' => 'Pending Approval',  'badge' => 'badge-blue',   'bg' => '#eff6ff', 'border' => '#bfdbfe', 'color' => '#1d4ed8'],
        'approved'         => ['label' => 'Approved',          'badge' => 'badge-green',  'bg' => '#f0fdf4', 'border' => '#bbf7d0', 'color' => '#166534'],
        'disbursed'        => ['label' => 'Disbursed',         'badge' => 'badge-green',  'bg' => '#f0fdf4', 'border' => '#bbf7d0', 'color' => '#166534'],
    ];
    $si = $statusMap[$payroll->status] ?? ['label' => ucfirst($payroll->status), 'badge' => '', 'bg' => '#f8fafc', 'border' => 'var(--line)', 'color' => 'var(--muted)'];

    $anomalyLabels = [
        'no_bank_account'    => 'Rekening kosong',
        'unverified_bank'    => 'Rekening belum verified',
        'zero_net_pay'       => 'Net pay nol',
        'missing_attendance' => 'Kehadiran tidak ada',
    ];

    $grandDeduct = $breakdown['deduction'];

    function fmtDt(float $v): string {
        if ($v >= 1_000_000_000) return 'Rp '.number_format($v/1_000_000_000,2,',','.').' M';
        if ($v >= 1_000_000)     return 'Rp '.number_format($v/1_000_000,2,',','.').' Jt';
        return 'Rp '.number_format($v,0,',','.');
    }
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Report {{ $payroll->period_label }} — PaySync</title>
    @include('payflow.partials.styles')
    <style>
        .rpt-nav-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 12px; border-radius: 9px; font-size: 13px; font-weight: 700;
            border: 1px solid var(--line); background: #fff; color: var(--navy);
            text-decoration: none; transition: background .12s, border-color .12s;
        }
        .rpt-nav-btn:hover { background: var(--brand-soft); border-color: var(--brand-line); color: var(--brand); }
        .breakdown-bar { height: 8px; border-radius: 999px; overflow: hidden; background: #e2e8f0; margin-top: 5px; }
        .breakdown-bar span { display: block; height: 100%; border-radius: inherit;
            transform-origin: left; animation: progress-fill .9s cubic-bezier(.2,.8,.2,1) both; }
    </style>
</head>
<body>

{{-- ── App Shell (sidebar + topbar, same as app.blade.php) ── --}}
@php
    $role = $user->role;
    $canSeeHr      = in_array($role, ['super_admin', 'hr_manager'], true);
    $canSeeFinance = in_array($role, ['super_admin', 'finance_manager'], true);
    $nav = [];
    if ($canSeeHr || $canSeeFinance) {
        if ($canSeeHr)      $nav['People']  = [['employees', 'Karyawan']];
        if ($canSeeHr)      $nav['Payroll'] = [['payroll', 'Proses Payroll']];
        if ($canSeeFinance) $nav['Payroll'] = array_merge($nav['Payroll'] ?? [], [['approval','Persetujuan'],['payslips','Slip Gaji']]);
        if ($canSeeFinance) $nav['Disbursement'] = [['disbursement','Batch Transfer'],['reconciliation','Rekonsiliasi']];
        $nav['Reports'] = [['reports','Laporan']];
    }
    if ($role === 'super_admin') {
        $nav['System'] = [['settings','Pengaturan'],['audit','Audit Log']];
    }
    $navIcons = ['employees'=>'users','payroll'=>'payroll','approval'=>'approval','payslips'=>'file',
                 'disbursement'=>'bank','reconciliation'=>'link','reports'=>'report',
                 'settings'=>'settings','audit'=>'shield'];
@endphp

<div class="app-shell">
    {{-- Sidebar --}}
    <aside class="sidebar" x-data :class="{ 'collapsed': $store.sidebar.collapsed }">
        @include('payflow.partials.brand')
        <div class="workspace">
            <strong>{{ $companyName }}</strong>
            <div><span class="badge" style="margin-top:8px;background:rgba(255,255,255,.08);color:#d9e7ff;border-color:rgba(255,255,255,.12);">Company Workspace</span></div>
        </div>
        @foreach($nav as $group => $items)
        <div class="nav-group">
            <div class="nav-title">{{ $group }}</div>
            @foreach($items as [$slug, $label])
            <a class="nav-link {{ $slug === 'reports' ? 'active' : '' }}"
               href="{{ url('/app/'.$slug) }}" title="{{ $label }}">
                @include('payflow.partials.icon', ['name' => $navIcons[$slug] ?? 'dashboard', 'class' => 'icon icon-sm'])
                <span class="nav-label">{{ $label }}</span>
            </a>
            @endforeach
        </div>
        @endforeach
    </aside>

    {{-- Main --}}
    <main x-data>
        {{-- Topbar --}}
        <header class="topbar">
            <div class="topbar-left">
                <button class="topbar-menu-btn" @click="$store.sidebar.toggle()">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <div class="topbar-breadcrumb">
                    <span class="topbar-brand">PaySync</span>
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:#cbd5e1;"><polyline points="9 18 15 12 9 6"/></svg>
                    <a href="{{ url('/app/reports') }}" style="color:var(--muted);font-size:14px;font-weight:600;">Laporan</a>
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:#cbd5e1;"><polyline points="9 18 15 12 9 6"/></svg>
                    <span class="topbar-page">{{ $payroll->period_label }}</span>
                </div>
            </div>
            <div class="topbar-right">
                {{-- User dropdown --}}
                <div x-data="{ open: false }" @click.outside="open = false" class="topbar-user-wrap">
                    <button @click="open = !open" class="topbar-user-btn">
                        <span class="topbar-avatar">{{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}</span>
                        <span class="topbar-user-name">{{ $user->name }}</span>
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color:#94a3b8;flex-shrink:0;"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="topbar-dropdown topbar-dropdown--right" x-show="open" x-cloak x-transition>
                        <div class="topbar-dropdown-head">
                            <div style="font-weight:700;color:var(--navy);">{{ $user->name }}</div>
                            <div class="muted" style="font-size:12px;">{{ $user->email }}</div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="topbar-dropdown-item topbar-dropdown-item--link topbar-dropdown-item--danger" style="width:100%;border:none;background:none;cursor:pointer;text-align:left;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <section class="content">

            {{-- ── Page Head ── --}}
            <div class="page-head">
                <div>
                    <h1>Payroll Report — {{ $payroll->period_label }}</h1>
                    <div style="display:flex;align-items:center;gap:8px;margin-top:6px;flex-wrap:wrap;">
                        <span class="badge {{ $si['badge'] }}">{{ $si['label'] }}</span>
                        @if($payroll->submitter)
                            <span class="muted" style="font-size:12px;">Disubmit: <strong>{{ $payroll->submitter->name }}</strong></span>
                        @endif
                        @if($payroll->approver)
                            <span class="muted" style="font-size:12px;">· Disetujui: <strong>{{ $payroll->approver->name }}</strong>
                                @if($payroll->approved_at) ({{ $payroll->approved_at->format('d M Y') }}) @endif
                            </span>
                        @endif
                        @if($payroll->disbursed_at)
                            <span class="muted" style="font-size:12px;">· Disbursed: <strong>{{ $payroll->disbursed_at->format('d M Y') }}</strong></span>
                        @endif
                    </div>
                </div>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    {{-- Period Navigation --}}
                    @if($prevPayroll)
                    <a href="{{ url('/app/reports/'.$prevPayroll->id) }}" class="rpt-nav-btn" title="{{ $prevPayroll->period_label }}">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                        {{ $prevPayroll->period_label }}
                    </a>
                    @endif
                    @if($nextPayroll)
                    <a href="{{ url('/app/reports/'.$nextPayroll->id) }}" class="rpt-nav-btn" title="{{ $nextPayroll->period_label }}">
                        {{ $nextPayroll->period_label }}
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                    @endif
                    <a href="{{ url('/app/reports') }}" class="btn btn-secondary" style="font-size:13px;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                        Semua Laporan
                    </a>
                    <a href="{{ url('/app/reports/'.$payroll->id.'?export=csv') }}" class="btn btn-secondary" style="font-size:13px;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v12"/><path d="m7 11 5 5 5-5"/><path d="M5 20h14"/></svg>
                        Export CSV
                    </a>
                </div>
            </div>

            {{-- Rejection note --}}
            @if($payroll->rejection_note)
            <div style="padding:12px 16px;background:#fef3c7;border:1px solid #fde68a;border-radius:12px;color:#92400e;font-size:13px;margin-bottom:16px;display:flex;gap:10px;align-items:flex-start;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div><strong>Catatan Penolakan:</strong> {{ $payroll->rejection_note }}</div>
            </div>
            @endif

            {{-- ── KPI Strip ── --}}
            <div class="grid grid-4" style="margin-bottom:20px;">
                <div class="kpi-modern">
                    <div class="kpi-icon-wrap blue"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
                    <div class="kpi-label">Karyawan</div>
                    <div class="kpi-value">{{ number_format($payroll->employee_count,0,',','.') }}</div>
                    <div class="kpi-footer"><span class="badge badge-blue">{{ $payroll->period_label }}</span></div>
                </div>
                <div class="kpi-modern">
                    <div class="kpi-icon-wrap amber"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
                    <div class="kpi-label">Gross Pay</div>
                    <div class="kpi-value" style="font-size:20px;">{{ fmtDt((float)$payroll->gross_total) }}</div>
                    <div class="kpi-footer"><span class="badge badge-amber">Sebelum potongan</span></div>
                </div>
                <div class="kpi-modern">
                    <div class="kpi-icon-wrap red"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></div>
                    <div class="kpi-label">Total Deduction</div>
                    <div class="kpi-value" style="font-size:20px;">{{ fmtDt((float)$payroll->deduction_total) }}</div>
                    <div class="kpi-footer"><span class="badge badge-red">BPJS + PPh21</span></div>
                </div>
                <div class="kpi-modern">
                    <div class="kpi-icon-wrap green"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <div class="kpi-label">Net Pay</div>
                    <div class="kpi-value" style="font-size:20px;">{{ fmtDt((float)$payroll->net_total) }}</div>
                    <div class="kpi-footer">
                        <span class="badge {{ $payroll->anomaly_count > 0 ? 'badge-red' : 'badge-green' }}">
                            {{ $payroll->anomaly_count > 0 ? $payroll->anomaly_count.' anomali' : '✓ Bersih' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- ── Breakdown + Period List (2-col) ── --}}
            <div class="grid grid-2" style="margin-bottom:20px;">

                {{-- Deduction Breakdown --}}
                <div class="section-card">
                    <div class="section-header">
                        <div style="font-size:15px;font-weight:700;color:var(--navy);">Breakdown Potongan</div>
                        <span class="badge badge-red">{{ $payroll->period_label }}</span>
                    </div>
                    <div class="section-content">
                        @foreach([
                            ['BPJS Ketenagakerjaan (2%)', $breakdown['bpjs_tk'],  'linear-gradient(90deg,#0f3473,#4f86cf)'],
                            ['BPJS Kesehatan (1%)',        $breakdown['bpjs_kes'], 'linear-gradient(90deg,#0891b2,#38bdf8)'],
                            ['PPh 21 (5% > Rp 4,5 Jt)',   $breakdown['pph21'],    'linear-gradient(90deg,#dc2626,#f87171)'],
                        ] as [$label, $val, $grad])
                        <div style="margin-bottom:14px;">
                            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
                                <span style="font-weight:600;color:var(--navy);">{{ $label }}</span>
                                <span style="font-weight:700;color:var(--navy);">{{ fmtDt($val) }}</span>
                            </div>
                            <div class="breakdown-bar">
                                <span style="width:{{ $grandDeduct > 0 ? round($val/$grandDeduct*100,1) : 0 }}%;background:{{ $grad }};"></span>
                            </div>
                            <div class="muted" style="font-size:11px;margin-top:3px;">
                                {{ $grandDeduct > 0 ? number_format($val/$grandDeduct*100,1,',','.') : 0 }}% dari total potongan
                            </div>
                        </div>
                        @endforeach
                        @if($breakdown['overtime'] > 0)
                        <div style="padding:10px 12px;background:var(--brand-soft);border:1px solid var(--brand-line);border-radius:9px;display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                            <span style="font-size:13px;font-weight:600;color:var(--brand);">Total Lembur</span>
                            <span style="font-size:14px;font-weight:800;color:var(--brand);">+ {{ fmtDt($breakdown['overtime']) }}</span>
                        </div>
                        @endif
                        <div style="padding:12px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:9px;display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:13px;font-weight:700;color:#991b1b;">Total Potongan</span>
                            <span style="font-size:15px;font-weight:800;color:#dc2626;">{{ fmtDt($grandDeduct) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Period Navigator --}}
                <div class="section-card">
                    <div class="section-header">
                        <div style="font-size:15px;font-weight:700;color:var(--navy);">Semua Periode</div>
                        <span class="badge badge-blue">{{ $allPayrolls->count() }}</span>
                    </div>
                    <div style="padding:10px 14px;max-height:280px;overflow-y:auto;">
                        @foreach($allPayrolls as $p)
                        @php
                            $pBadge = match($p->status) { 'disbursed'=>'badge-green','approved'=>'badge-blue','pending_approval'=>'badge-amber','needs_review'=>'badge-amber',default=>'' };
                            $pLabel = match($p->status) { 'disbursed'=>'Disbursed','approved'=>'Approved','pending_approval'=>'Pending','needs_review'=>'Review',default=>ucfirst($p->status) };
                            $isActive = $p->id === $payroll->id;
                        @endphp
                        <a href="{{ url('/app/reports/'.$p->id) }}"
                           style="display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:9px;margin-bottom:4px;text-decoration:none;min-height:0;
                                  background:{{ $isActive ? 'var(--brand-soft)' : '#fff' }};
                                  border:1px solid {{ $isActive ? 'var(--brand-line)' : 'var(--line)' }};
                                  transition:background .12s,border-color .12s;"
                           onmouseover="if(!{{ $isActive?'true':'false' }}){this.style.background='var(--brand-soft)';this.style.borderColor='var(--brand-line)';}"
                           onmouseout="if(!{{ $isActive?'true':'false' }}){this.style.background='#fff';this.style.borderColor='var(--line)';}">
                            @if($isActive)
                            <div style="width:4px;height:28px;border-radius:999px;background:var(--brand);flex-shrink:0;"></div>
                            @else
                            <div style="width:4px;height:28px;flex-shrink:0;"></div>
                            @endif
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:13px;font-weight:700;color:{{ $isActive?'var(--brand)':'var(--navy)' }};">{{ $p->period_label }}</div>
                                <div class="muted" style="font-size:11px;">{{ $p->employee_count }} karyawan · {{ fmtDt((float)$p->net_total) }}</div>
                            </div>
                            <span class="badge {{ $pBadge }}" style="font-size:10px;">{{ $pLabel }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ── Payroll Items Table ── --}}
            <div class="section-card">
                <div class="section-header" style="flex-wrap:wrap;gap:12px;">
                    <div>
                        <div style="font-size:16px;font-weight:700;color:var(--navy);">Rincian per Karyawan</div>
                        <div class="muted" style="font-size:13px;margin-top:2px;">
                            {{ $payroll->payrollItems->count() }} karyawan ·
                            <span class="{{ $payroll->anomaly_count > 0 ? '' : '' }}" style="color:{{ $payroll->anomaly_count > 0 ? 'var(--red)' : 'var(--green)' }};font-weight:700;">
                                {{ $payroll->anomaly_count > 0 ? $payroll->anomaly_count.' anomali' : '✓ Semua bersih' }}
                            </span>
                        </div>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center;margin-left:auto;">
                        <span class="badge badge-blue">{{ $payroll->payrollItems->where('has_anomaly',false)->count() }} bersih</span>
                        @if($payroll->payrollItems->where('has_anomaly',true)->count() > 0)
                        <span class="badge badge-amber">{{ $payroll->payrollItems->where('has_anomaly',true)->count() }} anomali</span>
                        @endif
                    </div>
                </div>

                <div style="overflow-x:auto;">
                    <table style="min-width:1000px;">
                        <thead>
                            <tr>
                                <th style="width:200px;">Karyawan</th>
                                <th>NIP</th>
                                <th style="text-align:right;">Gaji Pokok</th>
                                <th style="text-align:right;">Lembur</th>
                                <th style="text-align:right;">Gross Pay</th>
                                <th style="text-align:right;color:#b45309;">BPJS TK</th>
                                <th style="text-align:right;color:#b45309;">BPJS Kes</th>
                                <th style="text-align:right;color:#b45309;">PPh21</th>
                                <th style="text-align:right;">Net Pay</th>
                                <th>Status</th>
                                @if(!$isSuperAdmin && in_array($payroll->status,['approved','disbursed']))<th>Payslip</th>@endif
                                @if($isHr && $payroll->status==='needs_review')<th>Aksi</th>@endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payroll->payrollItems as $item)
                            @php
                                $anomalyTypes = [];
                                if ($item->has_anomaly && $item->anomaly_type) {
                                    $anomalyTypes = is_array($item->anomaly_type)
                                        ? $item->anomaly_type
                                        : (json_decode($item->anomaly_type, true) ?? []);
                                }
                                $rowBg = $item->has_anomaly && !$item->anomaly_acknowledged ? 'background:#fffbeb;' : '';
                            @endphp
                            <tr style="{{ $rowBg }}">
                                <td>
                                    <div style="font-weight:600;color:var(--navy);">{{ $item->employee?->name ?? '-' }}</div>
                                    <div class="muted" style="font-size:12px;">{{ $item->employee?->department ?? '' }}</div>
                                    @if($anomalyTypes)
                                    <div style="margin-top:4px;display:flex;flex-wrap:wrap;gap:3px;">
                                        @foreach($anomalyTypes as $t)
                                        <span style="font-size:10px;font-weight:700;padding:2px 5px;border-radius:4px;background:#fef3c7;color:#92400e;border:1px solid #fde68a;">{{ $anomalyLabels[$t] ?? $t }}</span>
                                        @endforeach
                                        @if($item->anomaly_acknowledged)
                                        <span style="font-size:10px;font-weight:700;padding:2px 5px;border-radius:4px;background:#dcfce7;color:#166534;border:1px solid #bbf7d0;">✓ Ack</span>
                                        @endif
                                    </div>
                                    @endif
                                </td>
                                <td><span style="font-family:monospace;font-size:12px;color:var(--muted);">{{ $item->employee?->nip ?? '-' }}</span></td>
                                <td style="text-align:right;font-size:13px;">Rp {{ number_format((float)$item->basic_salary_snapshot,0,',','.') }}</td>
                                <td style="text-align:right;font-size:13px;">
                                    @if((float)$item->overtime_pay > 0)
                                        <span style="color:var(--brand);font-weight:600;">Rp {{ number_format((float)$item->overtime_pay,0,',','.') }}</span>
                                    @else <span class="muted">—</span> @endif
                                </td>
                                <td style="text-align:right;font-size:13px;font-weight:600;">Rp {{ number_format((float)$item->gross_pay,0,',','.') }}</td>
                                <td style="text-align:right;font-size:12px;color:#b45309;">Rp {{ number_format((float)$item->bpjs_tk_deduction,0,',','.') }}</td>
                                <td style="text-align:right;font-size:12px;color:#b45309;">Rp {{ number_format((float)$item->bpjs_kesehatan_deduction,0,',','.') }}</td>
                                <td style="text-align:right;font-size:12px;color:#b45309;">
                                    @if((float)$item->pph21_deduction > 0) Rp {{ number_format((float)$item->pph21_deduction,0,',','.') }}
                                    @else <span class="muted">—</span> @endif
                                </td>
                                <td style="text-align:right;font-weight:700;color:var(--navy);">Rp {{ number_format((float)$item->net_pay,0,',','.') }}</td>
                                <td>
                                    @if($item->has_anomaly && !$item->anomaly_acknowledged)
                                        <span class="badge badge-amber">⚠ Anomali</span>
                                    @elseif($item->has_anomaly)
                                        <span class="badge badge-blue">⚠ Ack</span>
                                    @else
                                        <span class="badge badge-green">✓ OK</span>
                                    @endif
                                </td>
                                @if(!$isSuperAdmin && in_array($payroll->status,['approved','disbursed']))
                                <td>
                                    @if($item->employee)
                                    <a href="{{ route('payroll.payslip',[$payroll,$item->employee]) }}"
                                       class="btn btn-secondary" style="padding:4px 10px;font-size:12px;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 3h7l4 4v14H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M14 3v5h5"/></svg>
                                        Payslip
                                    </a>
                                    @endif
                                </td>
                                @endif
                                @if($isHr && $payroll->status==='needs_review')
                                <td>
                                    @if($item->has_anomaly && !$item->anomaly_acknowledged)
                                    <form method="POST" action="{{ route('payroll.anomaly.acknowledge',[$payroll,$item]) }}">
                                        @csrf
                                        <button class="btn btn-secondary" style="padding:4px 10px;font-size:12px;">Acknowledge</button>
                                    </form>
                                    @endif
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" style="text-align:center;padding:40px;color:var(--muted);">
                                    Belum ada item payroll untuk periode ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($payroll->payrollItems->isNotEmpty())
                        <tfoot>
                            <tr style="background:#f8fafc;font-weight:700;border-top:2px solid var(--line);">
                                <td colspan="2" style="padding:12px 14px;font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;">
                                    Total ({{ $payroll->payrollItems->count() }} karyawan)
                                </td>
                                <td style="text-align:right;padding:12px 14px;font-size:13px;">Rp {{ number_format($payroll->payrollItems->sum('basic_salary_snapshot'),0,',','.') }}</td>
                                <td style="text-align:right;padding:12px 14px;font-size:13px;color:var(--brand);">Rp {{ number_format($payroll->payrollItems->sum('overtime_pay'),0,',','.') }}</td>
                                <td style="text-align:right;padding:12px 14px;font-size:13px;">Rp {{ number_format($payroll->payrollItems->sum('gross_pay'),0,',','.') }}</td>
                                <td style="text-align:right;padding:12px 14px;font-size:12px;color:#b45309;">Rp {{ number_format($payroll->payrollItems->sum('bpjs_tk_deduction'),0,',','.') }}</td>
                                <td style="text-align:right;padding:12px 14px;font-size:12px;color:#b45309;">Rp {{ number_format($payroll->payrollItems->sum('bpjs_kesehatan_deduction'),0,',','.') }}</td>
                                <td style="text-align:right;padding:12px 14px;font-size:12px;color:#b45309;">Rp {{ number_format($payroll->payrollItems->sum('pph21_deduction'),0,',','.') }}</td>
                                <td style="text-align:right;padding:12px 14px;font-size:14px;color:var(--brand);">Rp {{ number_format($payroll->payrollItems->sum('net_pay'),0,',','.') }}</td>
                                <td colspan="3" style="padding:12px 14px;"></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

        </section>
    </main>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('sidebar', {
            collapsed: localStorage.getItem('sidebar-collapsed') === 'true',
            toggle() { this.collapsed = !this.collapsed; localStorage.setItem('sidebar-collapsed', this.collapsed); }
        });
    });
</script>
<script defer src="{{ asset('vendor/alpinejs/cdn.min.js') }}"></script>
</body>
</html>
