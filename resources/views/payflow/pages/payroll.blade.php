{{--
    payroll.blade.php
    Variables: $payrolls (LengthAwarePaginator), $statusFilter, $periodFilter,
               $allowedStatuses, $sortBy, $sortDir, $perPage, $isSuperAdminViewing
--}}
@php
    $isSuperAdmin = $isSuperAdminViewing ?? false;
    $statusLabels = [
        'draft'            => ['label' => 'Draft',             'badge' => 'badge-amber',  'color' => '#d97706'],
        'needs_review'     => ['label' => 'Needs Review',      'badge' => 'badge-amber',  'color' => '#d97706'],
        'pending_approval' => ['label' => 'Pending Approval',  'badge' => 'badge-blue',   'color' => '#2563eb'],
        'approved'         => ['label' => 'Approved',          'badge' => 'badge-green',  'color' => '#16a34a'],
        'disbursed'        => ['label' => 'Disbursed',         'badge' => 'badge-green',  'color' => '#16a34a'],
    ];
    $activePayroll = isset($payrolls)
        ? ($payrolls->firstWhere(fn($p) => $p->status !== 'draft') ?? $payrolls->first())
        : null;

    // Progress step map
    $progressSteps = [
        ['key' => 'data_input',    'label' => 'Data Input',     'statuses' => ['draft']],
        ['key' => 'kalkulasi',     'label' => 'Kalkulasi',      'statuses' => ['draft']],
        ['key' => 'review',        'label' => 'Review Anomali', 'statuses' => ['needs_review']],
        ['key' => 'approval',      'label' => 'Approval',       'statuses' => ['pending_approval']],
        ['key' => 'finalisasi',    'label' => 'Finalisasi',     'statuses' => ['approved']],
        ['key' => 'disbursement',  'label' => 'Disbursement',   'statuses' => ['disbursed']],
    ];
    $statusOrder = ['draft' => 0, 'needs_review' => 2, 'pending_approval' => 3, 'approved' => 4, 'disbursed' => 5];
    $currentOrder = $statusOrder[$activePayroll?->status ?? 'draft'] ?? 0;

    function stepState(array $step, int $currentOrder, array $statusOrder): string {
        $stepOrder = max(array_map(fn($s) => $statusOrder[$s] ?? 0, $step['statuses']));
        if ($currentOrder > $stepOrder) return 'done';
        if ($currentOrder === $stepOrder) return 'active';
        return 'pending';
    }
@endphp

@if($isEmpty ?? false)
{{-- Empty state --}}
<div class="section-card">
    <div class="section-content" style="text-align:center; padding:64px 20px;">
        <svg width="56" height="56" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" style="color:var(--muted); display:block; margin:0 auto 16px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        <div style="font-size:20px; font-weight:800; color:var(--navy); margin-bottom:8px;">Belum ada payroll</div>
        <p class="muted" style="margin:0 0 20px; font-size:14px; max-width:400px; margin-left:auto; margin-right:auto;">Tambahkan karyawan terlebih dahulu, lengkapi komponen gaji, lalu mulai proses payroll pertama.</p>
        <a href="{{ route('payroll.create') }}" class="btn btn-primary">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Buat Payroll Baru
        </a>
    </div>
</div>
@else

{{-- ═══ KPI Strip ═══ --}}
@if($activePayroll)
<div class="grid grid-4" style="margin-bottom:20px;">
    <div class="kpi-modern kpi-blue">
        <div class="kpi-icon-wrap blue">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div class="kpi-label">Karyawan</div>
        <div class="kpi-value">{{ number_format($activePayroll->employee_count, 0, ',', '.') }}</div>
        <div class="kpi-footer"><span class="badge badge-blue">{{ $activePayroll->period_label }}</span></div>
    </div>
    <div class="kpi-modern kpi-amber">
        <div class="kpi-icon-wrap amber">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="kpi-label">Gross Income</div>
        <div class="kpi-value" style="font-size:20px;">Rp {{ number_format((float)$activePayroll->gross_total / 1_000_000, 2, ',', '.') }} Jt</div>
        <div class="kpi-footer"><span class="badge badge-amber">Sebelum potongan</span></div>
    </div>
    <div class="kpi-modern kpi-red">
        <div class="kpi-icon-wrap red">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div class="kpi-label">Total Deduction</div>
        <div class="kpi-value" style="font-size:20px;">Rp {{ number_format((float)$activePayroll->deduction_total / 1_000_000, 2, ',', '.') }} Jt</div>
        <div class="kpi-footer"><span class="badge badge-red">BPJS + PPh21</span></div>
    </div>
    <div class="kpi-modern kpi-green">
        <div class="kpi-icon-wrap green">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div class="kpi-label">Take-home Pay</div>
        <div class="kpi-value" style="font-size:20px;">Rp {{ number_format((float)$activePayroll->net_total / 1_000_000, 2, ',', '.') }} Jt</div>
        <div class="kpi-footer">
            <span class="badge {{ $activePayroll->anomaly_count > 0 ? 'badge-red' : 'badge-green' }}">
                {{ $activePayroll->anomaly_count > 0 ? $activePayroll->anomaly_count.' anomali' : '✓ Bersih' }}
            </span>
        </div>
    </div>
</div>
@endif

{{-- ═══ Active Payroll Workspace ═══ --}}
@if($activePayroll)
<div class="section-card" style="margin-bottom:20px;">

    {{-- Card header --}}
    <div class="section-header">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:44px; height:44px; border-radius:12px; background:var(--brand-soft); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="color:var(--brand);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div>
                <div style="font-size:17px; font-weight:800; color:var(--navy);">Payroll {{ $activePayroll->period_label }}</div>
                <div style="display:flex; align-items:center; gap:6px; margin-top:3px;">
                    <span class="badge {{ $statusLabels[$activePayroll->status]['badge'] ?? '' }}">
                        {{ $statusLabels[$activePayroll->status]['label'] ?? ucfirst($activePayroll->status) }}
                    </span>
                    @if($activePayroll->anomaly_count > 0)
                        <span class="badge badge-red">{{ $activePayroll->anomaly_count }} anomali</span>
                    @endif
                </div>
            </div>
        </div>
        <div style="display:flex; gap:8px; align-items:center;">
            <a href="{{ route('payroll.show', $activePayroll) }}" class="btn btn-secondary" style="font-size:13px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                Detail
            </a>
            @if(!$isSuperAdmin)
                <a href="{{ route('payroll.create') }}" class="btn btn-secondary" style="font-size:13px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Buat Baru
                </a>
            @endif
        </div>
    </div>

    {{-- Rejection note banner --}}
    @if($activePayroll->rejection_note && $activePayroll->status === 'needs_review')
    <div style="margin:0 20px 16px; padding:12px 16px; background:#fef3c7; border:1px solid #fde68a; border-radius:10px; font-size:13px; color:#92400e; display:flex; gap:10px; align-items:flex-start;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0; margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div><strong>Ditolak oleh Finance:</strong> {{ $activePayroll->rejection_note }}</div>
    </div>
    @endif

    {{-- Progress steps --}}
    <div style="padding:0 20px 16px;">
        <div style="display:flex; align-items:flex-start; gap:0; overflow-x:auto; padding-bottom:4px;">
            @foreach($progressSteps as $i => $step)
            @php $state = stepState($step, $currentOrder, $statusOrder); @endphp
            <div style="flex:1; min-width:80px; display:flex; flex-direction:column; align-items:center; position:relative;">
                {{-- Connector line --}}
                @if($i > 0)
                <div style="position:absolute; top:14px; right:50%; left:-50%; height:2px; background:{{ $state === 'pending' ? 'var(--line)' : 'var(--brand)' }}; z-index:0;"></div>
                @endif
                {{-- Dot --}}
                <div style="width:28px; height:28px; border-radius:50%; z-index:1; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:800;
                    @if($state === 'done') background:var(--brand); color:#fff;
                    @elseif($state === 'active') background:#fff; border:2px solid var(--brand); color:var(--brand);
                    @else background:#f1f5f9; border:2px solid var(--line); color:var(--muted);
                    @endif">
                    @if($state === 'done')
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    @else
                        {{ $i + 1 }}
                    @endif
                </div>
                {{-- Label --}}
                <div style="margin-top:6px; font-size:11px; font-weight:{{ $state === 'active' ? '700' : '500' }}; color:{{ $state === 'pending' ? 'var(--muted)' : ($state === 'active' ? 'var(--brand)' : 'var(--navy)') }}; text-align:center; white-space:nowrap;">
                    {{ $step['label'] }}
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Action buttons by status --}}
    @php $userRole = auth()->user()->role; @endphp
    <div style="padding:0 20px 20px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">

        @if($isSuperAdmin)
            <div style="padding:10px 14px; background:#f8fafc; border:1px solid var(--line); border-radius:10px; font-size:13px; color:var(--muted);">
                Mode Pantau — aksi tersedia untuk HR/Finance sesuai role masing-masing.
            </div>

        @elseif($userRole === 'hr_manager')
            @if($activePayroll->status === 'draft')
                <a href="{{ route('payroll.attendance.import', $activePayroll) }}" class="btn btn-secondary">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Import Kehadiran
                </a>
                <form method="POST" action="{{ route('payroll.calculate', $activePayroll) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="10" x2="16" y2="10"/></svg>
                        Hitung Payroll
                    </button>
                </form>
            @elseif($activePayroll->status === 'needs_review')
                <form method="POST" action="{{ route('payroll.recalculate', $activePayroll) }}">
                    @csrf @method('PUT')
                    <button type="submit" class="btn btn-secondary">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                        Recalculate
                    </button>
                </form>
                <form method="POST" action="{{ route('payroll.submit', $activePayroll) }}">
                    @csrf @method('PUT')
                    <button type="submit" class="btn btn-primary"
                        {{ $activePayroll->anomaly_count > 0 ? 'disabled title="Acknowledge semua anomali dahulu"' : '' }}>
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        Submit ke Finance
                    </button>
                </form>
                @if($activePayroll->anomaly_count > 0)
                    <a href="{{ route('payroll.show', $activePayroll) }}" class="btn btn-secondary" style="color:#d97706; border-color:#fde68a;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Review {{ $activePayroll->anomaly_count }} Anomali
                    </a>
                @endif
            @elseif($activePayroll->status === 'pending_approval')
                <div style="padding:10px 14px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; font-size:13px; color:#1d4ed8; display:flex; align-items:center; gap:8px;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Menunggu persetujuan Finance Manager
                </div>
            @elseif($activePayroll->status === 'approved')
                <div style="padding:10px 14px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; font-size:13px; color:#166534; display:flex; align-items:center; gap:8px;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Disetujui Finance — menunggu proses transfer oleh Finance
                </div>
            @elseif($activePayroll->status === 'disbursed')
                <div style="padding:10px 14px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; font-size:13px; color:#166534; display:flex; align-items:center; gap:8px;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Transfer selesai — gaji sudah disalurkan
                </div>
            @endif

        @elseif($userRole === 'finance_manager')
            @if($activePayroll->status === 'pending_approval')
                <a href="{{ url('/app/approval') }}" class="btn btn-primary">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Lihat Approval Queue
                </a>
            @elseif($activePayroll->status === 'approved')
                <a href="{{ url('/app/disbursement') }}" class="btn btn-primary" style="background:#16a34a; border-color:#16a34a;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    Proses Disbursement
                </a>
            @elseif($activePayroll->status === 'disbursed')
                <a href="{{ url('/app/reconciliation') }}" class="btn btn-secondary">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    Lihat Rekonsiliasi
                </a>
            @endif
        @endif

    </div>
</div>
@endif

{{-- ═══ Filter + Payroll History ═══ --}}
<div class="section-card">
    <div class="section-header">
        <div>
            <div style="font-size:16px; font-weight:700; color:var(--navy);">Riwayat Payroll</div>
            <div class="muted" style="font-size:13px; margin-top:2px;">
                {{ isset($payrolls) ? $payrolls->total() : 0 }} periode ditemukan
            </div>
        </div>
        <form method="GET" action="{{ route('payroll.index') }}" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
            @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
            @if(request('dir'))<input type="hidden" name="dir" value="{{ request('dir') }}">@endif
            <input class="input" style="max-width:190px; font-size:13px;" type="text" name="period"
                value="{{ $periodFilter ?? '' }}" placeholder="Cari periode (2026-07)"
                x-on:input.debounce.400ms="$el.form.submit()">
            <select class="input" style="max-width:170px; font-size:13px;" name="status" @change="$el.form.submit()">
                <option value="">Semua Status</option>
                @foreach($allowedStatuses ?? [] as $s)
                    <option value="{{ $s }}" {{ ($statusFilter ?? '') === $s ? 'selected' : '' }}>
                        {{ ucwords(str_replace('_', ' ', $s)) }}
                    </option>
                @endforeach
            </select>
            @if(($periodFilter ?? '') || ($statusFilter ?? ''))
                <a href="{{ route('payroll.index') }}" class="btn btn-secondary" style="font-size:13px;">Reset</a>
            @endif
        </form>
    </div>

    <div class="section-content" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        @include('payflow.partials.sort-header', ['column'=>'period',         'label'=>'Periode',   'currentSort'=>$sortBy??'period','currentDir'=>$sortDir??'desc'])
                        @include('payflow.partials.sort-header', ['column'=>'status',         'label'=>'Status',    'currentSort'=>$sortBy??'period','currentDir'=>$sortDir??'desc'])
                        @include('payflow.partials.sort-header', ['column'=>'employee_count', 'label'=>'Karyawan',  'currentSort'=>$sortBy??'period','currentDir'=>$sortDir??'desc'])
                        @include('payflow.partials.sort-header', ['column'=>'gross_total',    'label'=>'Gross',     'currentSort'=>$sortBy??'period','currentDir'=>$sortDir??'desc'])
                        <th>Deduction</th>
                        @include('payflow.partials.sort-header', ['column'=>'net_total',      'label'=>'Net Pay',   'currentSort'=>$sortBy??'period','currentDir'=>$sortDir??'desc'])
                        <th>Anomali</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls ?? [] as $payroll)
                    @php
                        $isRejected = $payroll->status === 'needs_review' && !empty($payroll->rejection_note);
                        $sl = $isRejected
                            ? ['label' => 'Rejected by Finance', 'badge' => 'badge-red']
                            : ($statusLabels[$payroll->status] ?? ['label' => ucfirst($payroll->status), 'badge' => '']);
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight:700; color:var(--navy);">{{ $payroll->period_label }}</div>
                            <div class="muted" style="font-size:11px; font-family:monospace;">{{ $payroll->period }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $sl['badge'] }}">{{ $sl['label'] }}</span>
                            @if($isRejected)
                                <div class="muted" style="font-size:11px; margin-top:2px; max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $payroll->rejection_note }}">
                                    {{ Str::limit($payroll->rejection_note, 40) }}
                                </div>
                            @endif
                        </td>
                        <td style="text-align:right; font-weight:600;">{{ number_format($payroll->employee_count, 0, ',', '.') }}</td>
                        <td style="text-align:right;">Rp {{ number_format((float)$payroll->gross_total / 1_000_000, 2, ',', '.') }} Jt</td>
                        <td style="text-align:right; color:var(--red);">Rp {{ number_format((float)$payroll->deduction_total / 1_000_000, 2, ',', '.') }} Jt</td>
                        <td style="text-align:right; font-weight:700; color:var(--navy);">Rp {{ number_format((float)$payroll->net_total / 1_000_000, 2, ',', '.') }} Jt</td>
                        <td style="text-align:center;">
                            @if($payroll->anomaly_count > 0)
                                <span class="badge badge-red">{{ $payroll->anomaly_count }}</span>
                            @else
                                <span style="color:var(--muted);">—</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex; gap:6px; align-items:center;">
                                <a href="{{ route('payroll.show', $payroll) }}"
                                   class="btn btn-secondary" style="padding:5px 10px; font-size:12px;">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    Detail
                                </a>
                                @if(!$isSuperAdmin && in_array($payroll->status, ['draft', 'needs_review']))
                                    <form method="POST" action="{{ route('payroll.recalculate', $payroll) }}" style="margin:0;">
                                        @csrf @method('PUT')
                                        <button type="submit" class="btn btn-secondary" style="padding:5px 10px; font-size:12px;">Recalc</button>
                                    </form>
                                @endif
                                @if(!$isSuperAdmin && $payroll->status === 'needs_review')
                                    <form method="POST" action="{{ route('payroll.submit', $payroll) }}" style="margin:0;">
                                        @csrf @method('PUT')
                                        <button type="submit" class="btn btn-primary" style="padding:5px 10px; font-size:12px;">Submit</button>
                                    </form>
                                @endif
                                @if(!$isSuperAdmin && $payroll->status === 'draft')
                                    <form method="POST" action="{{ route('payroll.calculate', $payroll) }}" style="margin:0;">
                                        @csrf
                                        <button type="submit" class="btn btn-primary" style="padding:5px 10px; font-size:12px;">Hitung</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center; padding:40px; color:var(--muted);">
                            @if(($periodFilter ?? '') || ($statusFilter ?? ''))
                                Tidak ada payroll sesuai filter.
                                <a href="{{ route('payroll.index') }}" style="color:var(--brand);">Reset filter</a>
                            @else
                                Belum ada data payroll.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('payflow.partials.pagination', ['paginator' => $payrolls ?? null])
</div>

@endif
