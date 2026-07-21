@php
    $reportPayrolls      = $reportPayrolls      ?? collect();
    $reportLatestPayroll = $reportLatestPayroll  ?? null;
    $reportPayrollItems  = $reportPayrollItems   ?? collect();
    $isSuperAdmin        = $isSuperAdminViewing  ?? false;

    // Selected period from query string (fallback to latest)
    $selectedPeriod = request('report_period', $reportLatestPayroll?->period);
    $activePayroll  = $selectedPeriod
        ? $reportPayrolls->firstWhere('period', $selectedPeriod)
        : $reportLatestPayroll;

    // Filtered items based on active payroll + search params
    $allActiveItems = $activePayroll ? $activePayroll->payrollItems : collect();

    // Client-side search is handled by Alpine — pass full dataset
    $searchQuery    = request('rpt_search', '');
    $anomalyFilter  = request('rpt_anomaly', '');

    // Apply server-side filters
    $filteredItems = $allActiveItems
        ->when($searchQuery, fn($col) => $col->filter(fn($item) =>
            str_contains(strtolower($item->employee?->name ?? ''), strtolower($searchQuery)) ||
            str_contains(strtolower($item->employee?->department ?? ''), strtolower($searchQuery)) ||
            str_contains(strtolower($item->employee?->nip ?? ''), strtolower($searchQuery))
        ))
        ->when($anomalyFilter === 'anomaly', fn($col) => $col->filter(fn($item) => $item->has_anomaly))
        ->when($anomalyFilter === 'clean',   fn($col) => $col->filter(fn($item) => !$item->has_anomaly));

    // Summary KPIs across ALL periods
    $totalGross        = $reportPayrolls->sum('gross_total');
    $totalNet          = $reportPayrolls->sum('net_total');
    $totalDeduction    = $reportPayrolls->sum('deduction_total');
    $totalPeriods      = $reportPayrolls->count();

    // Trend data for chart (last 6 periods, ascending)
    $sorted            = $reportPayrolls->sortBy('period')->values();
    $trendPayrolls     = $sorted->slice(max(0, $sorted->count() - 6))->values();

    function fmtRpt(float $v): string {
        if ($v >= 1_000_000_000) return 'Rp ' . number_format($v / 1_000_000_000, 2, ',', '.') . ' M';
        if ($v >= 1_000_000)     return 'Rp ' . number_format($v / 1_000_000, 2, ',', '.') . ' Jt';
        return 'Rp ' . number_format($v, 0, ',', '.');
    }
@endphp

{{-- ═══════════════════════════════════════════════════════════════
     SECTION 1 — Summary KPI Strip
═══════════════════════════════════════════════════════════════ --}}
<div class="grid grid-4" style="margin-bottom:20px;">
    <div class="kpi-modern">
        <div class="kpi-icon-wrap blue">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div class="kpi-label">Total Periode</div>
        <div class="kpi-value">{{ $totalPeriods }}</div>
        <div class="kpi-footer"><span class="badge badge-blue">Semua payroll</span></div>
    </div>
    <div class="kpi-modern">
        <div class="kpi-icon-wrap amber">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="kpi-label">Total Gross Pay</div>
        <div class="kpi-value" style="font-size:20px;">{{ fmtRpt((float)$totalGross) }}</div>
        <div class="kpi-footer"><span class="badge badge-amber">Semua periode</span></div>
    </div>
    <div class="kpi-modern">
        <div class="kpi-icon-wrap red">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div class="kpi-label">Total Deduction</div>
        <div class="kpi-value" style="font-size:20px;">{{ fmtRpt((float)$totalDeduction) }}</div>
        <div class="kpi-footer"><span class="badge badge-red">BPJS + PPh21</span></div>
    </div>
    <div class="kpi-modern">
        <div class="kpi-icon-wrap green">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div class="kpi-label">Total Net Pay</div>
        <div class="kpi-value" style="font-size:20px;">{{ fmtRpt((float)$totalNet) }}</div>
        <div class="kpi-footer"><span class="badge badge-green">Take-home semua</span></div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     SECTION 2 — Trend Chart + Quick Links (2-col)
═══════════════════════════════════════════════════════════════ --}}
<div class="grid grid-2" style="margin-bottom:20px; grid-template-columns: 1.6fr 1fr;">

    {{-- Trend Chart --}}
    <div class="section-card">
        <div class="section-header">
            <div>
                <div style="font-size:16px; font-weight:700; color:var(--navy);">Tren Payroll</div>
                <div class="muted" style="font-size:13px; margin-top:2px;">Gross vs Net Pay per periode</div>
            </div>
            <span class="badge badge-blue">{{ $trendPayrolls->count() }} periode</span>
        </div>
        <div class="section-content">
            @if($trendPayrolls->isEmpty())
                <div style="text-align:center; padding:32px 0; color:var(--muted); font-size:14px;">Belum ada data untuk ditampilkan.</div>
            @else
                <div class="chart-wrap" style="height:200px;">
                    <canvas id="rpt-trend-chart" aria-label="Tren Payroll Chart"></canvas>
                </div>
                <script>
                (function() {
                    var _rptChartData = {
                        labels: {!! json_encode($trendPayrolls->pluck('period_label')->values()->toArray()) !!},
                        gross:  {!! json_encode($trendPayrolls->pluck('gross_total')->map(fn($v) => (float)$v)->values()->toArray()) !!},
                        net:    {!! json_encode($trendPayrolls->pluck('net_total')->map(fn($v) => (float)$v)->values()->toArray()) !!}
                    };

                    function buildChart() {
                        if (typeof Chart === 'undefined') { setTimeout(buildChart, 100); return; }
                        var canvas = document.getElementById('rpt-trend-chart');
                        if (!canvas) return;
                        new Chart(canvas.getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: _rptChartData.labels,
                                datasets: [
                                    {
                                        label: 'Gross Pay',
                                        data: _rptChartData.gross,
                                        backgroundColor: 'rgba(15,52,115,0.18)',
                                        borderColor: 'rgba(15,52,115,0.6)',
                                        borderWidth: 1.5,
                                        borderRadius: 6,
                                    },
                                    {
                                        label: 'Net Pay',
                                        data: _rptChartData.net,
                                        backgroundColor: 'rgba(22,163,74,0.20)',
                                        borderColor: 'rgba(22,163,74,0.65)',
                                        borderWidth: 1.5,
                                        borderRadius: 6,
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { position: 'top', labels: { font: { size: 12 }, boxWidth: 12 } }
                                },
                                scales: {
                                    y: {
                                        ticks: { callback: function(v) { return 'Rp ' + (v/1e6).toFixed(1) + ' Jt'; }, font: { size: 11 } },
                                        grid: { color: 'rgba(0,0,0,0.05)' }
                                    },
                                    x: { ticks: { font: { size: 11 } }, grid: { display: false } }
                                }
                            }
                        });
                    }

                    // Run after full page load so Chart.js script has time to execute
                    if (document.readyState === 'complete') {
                        buildChart();
                    } else {
                        window.addEventListener('load', buildChart);
                    }
                })();
                </script>
            @endif
        </div>
    </div>

    {{-- Quick Links to Other Reports --}}
    <div class="section-card">
        <div class="section-header">
            <div style="font-size:16px; font-weight:700; color:var(--navy);">Modul Laporan</div>
        </div>
        <div style="padding:12px 16px; display:grid; gap:8px;">
            @php
            $reportLinks = [
                ['label' => 'Attendance Report',     'sub' => 'Data kehadiran & lembur',           'icon' => 'calendar', 'href' => url('/app/attendance')],
                ['label' => 'Approval & Payslip',    'sub' => 'Slip gaji digital karyawan',         'icon' => 'file',     'href' => url('/app/payslips')],
                ['label' => 'Batch Transfer',        'sub' => 'Status disbursement & transfer',     'icon' => 'bank',     'href' => url('/app/disbursement')],
                ['label' => 'Rekonsiliasi',          'sub' => 'Cocokkan payroll vs transfer aktual','icon' => 'link',     'href' => url('/app/reconciliation')],
                ['label' => 'Audit Log',             'sub' => 'Riwayat perubahan sistem',           'icon' => 'shield',   'href' => url('/app/audit')],
            ];
            @endphp
            @foreach($reportLinks as $link)
            <a href="{{ $link['href'] }}"
               style="display:flex; align-items:center; gap:12px; padding:10px 12px; border-radius:10px; border:1px solid var(--line); background:#fff; text-decoration:none; transition:background .12s, border-color .12s;"
               onmouseover="this.style.background='var(--brand-soft)';this.style.borderColor='var(--brand-line)'"
               onmouseout="this.style.background='#fff';this.style.borderColor='var(--line)'">
                <div style="width:34px; height:34px; border-radius:9px; background:var(--brand-soft); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    @include('payflow.partials.icon', ['name' => $link['icon'], 'class' => 'icon icon-sm', 'style' => 'color:var(--brand)'])
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:13px; font-weight:700; color:var(--navy);">{{ $link['label'] }}</div>
                    <div class="muted" style="font-size:12px;">{{ $link['sub'] }}</div>
                </div>
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--muted); flex-shrink:0;"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            @endforeach
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     SECTION 3 — Payroll Report Detail Table
═══════════════════════════════════════════════════════════════ --}}
<div class="section-card">

    {{-- Card Header with period selector + filters + export --}}
    <div class="section-header" style="flex-wrap:wrap; gap:12px; align-items:flex-start;">
        <div>
            <div style="font-size:16px; font-weight:700; color:var(--navy);">Payroll Report</div>
            <div class="muted" style="font-size:13px; margin-top:2px;">
                Rincian per karyawan
                @if($activePayroll) · {{ $activePayroll->period_label }} @endif
                @if($filteredItems->count() !== $allActiveItems->count())
                    · <span style="color:var(--brand); font-weight:700;">{{ $filteredItems->count() }} dari {{ $allActiveItems->count() }}</span> karyawan
                @else
                    · {{ $allActiveItems->count() }} karyawan
                @endif
            </div>
        </div>

        <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center; margin-left:auto;">
            {{-- Period Selector --}}
            @if($reportPayrolls->isNotEmpty())
            <form method="GET" action="{{ route('app', 'reports') }}" id="period-form" style="margin:0;">
                @if($searchQuery)<input type="hidden" name="rpt_search" value="{{ $searchQuery }}">@endif
                @if($anomalyFilter)<input type="hidden" name="rpt_anomaly" value="{{ $anomalyFilter }}">@endif
                <select name="report_period" class="input" style="max-width:190px; font-size:13px;" onchange="this.form.submit()">
                    @foreach($reportPayrolls as $p)
                        <option value="{{ $p->period }}" {{ $activePayroll?->period === $p->period ? 'selected' : '' }}>
                            {{ $p->period_label }}
                        </option>
                    @endforeach
                </select>
            </form>
            @endif

            {{-- Export CSV Button --}}
            @if($activePayroll && $allActiveItems->isNotEmpty())
            <a href="{{ route('app', 'reports') }}?report_period={{ $activePayroll->period }}&export=csv"
               class="btn btn-secondary" style="font-size:13px;"
               title="Export ke CSV">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v12"/><path d="m7 11 5 5 5-5"/><path d="M5 20h14"/></svg>
                Export CSV
            </a>
            @endif

            {{-- Link to Payroll Detail --}}
            @if(!$isSuperAdmin && $activePayroll)
            <a href="{{ route('payroll.show', $activePayroll) }}" class="btn btn-secondary" style="font-size:13px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                Lihat Payroll
            </a>
            @endif
        </div>
    </div>

    {{-- Payroll Status Banner --}}
    @if($activePayroll)
    @php
        $statusBannerMap = [
            'draft'            => ['bg'=>'#f8fafc', 'border'=>'var(--line)',    'color'=>'var(--muted)',  'label'=>'Draft — belum dikalkulasi'],
            'needs_review'     => ['bg'=>'#fffbeb', 'border'=>'#fde68a',        'color'=>'#92400e',       'label'=>'Needs Review — menunggu acknowledgment anomali'],
            'pending_approval' => ['bg'=>'#eff6ff', 'border'=>'#bfdbfe',        'color'=>'#1d4ed8',       'label'=>'Pending Approval — menunggu persetujuan Finance'],
            'approved'         => ['bg'=>'#f0fdf4', 'border'=>'#bbf7d0',        'color'=>'#166534',       'label'=>'Approved — siap disbursement'],
            'disbursed'        => ['bg'=>'#f0fdf4', 'border'=>'#bbf7d0',        'color'=>'#166534',       'label'=>'Disbursed — gaji sudah disalurkan'],
        ];
        $banner = $statusBannerMap[$activePayroll->status] ?? null;
    @endphp
    @if($banner)
    <div style="margin:0 20px 0; padding:10px 14px; background:{{ $banner['bg'] }}; border-bottom:1px solid {{ $banner['border'] }}; font-size:13px; color:{{ $banner['color'] }}; display:flex; align-items:center; gap:8px;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <strong>Status:</strong> {{ $banner['label'] }}
        @if($activePayroll->anomaly_count > 0)
            · <span style="color:var(--red); font-weight:700;">{{ $activePayroll->anomaly_count }} anomali belum di-acknowledge</span>
        @endif
    </div>
    @endif
    @endif

    {{-- Filter Bar --}}
    <div style="padding:14px 20px; border-bottom:1px solid var(--line); display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
        <form method="GET" action="{{ route('app', 'reports') }}" id="filter-form" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center; flex:1;">
            <input type="hidden" name="report_period" value="{{ $activePayroll?->period ?? '' }}">

            {{-- Search --}}
            <div style="position:relative; flex:1; min-width:200px; max-width:300px;">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#94a3b8; pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input class="input" type="text" name="rpt_search"
                    value="{{ $searchQuery }}"
                    placeholder="Cari nama, NIP, departemen..."
                    style="padding-left:34px; font-size:13px;"
                    x-on:input.debounce.500ms="$el.form.submit()">
            </div>

            {{-- Anomaly Filter --}}
            <select class="input" name="rpt_anomaly" style="max-width:160px; font-size:13px;" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="clean"   {{ $anomalyFilter === 'clean'   ? 'selected' : '' }}>✓ Bersih</option>
                <option value="anomaly" {{ $anomalyFilter === 'anomaly' ? 'selected' : '' }}>⚠ Ada Anomali</option>
            </select>

            {{-- Reset --}}
            @if($searchQuery || $anomalyFilter)
            <a href="{{ route('app', 'reports') }}?report_period={{ $activePayroll?->period }}" class="btn btn-secondary" style="font-size:13px;">Reset</a>
            @endif
        </form>

        {{-- Item count summary --}}
        @if($activePayroll && $allActiveItems->isNotEmpty())
        <div style="display:flex; gap:10px; align-items:center; flex-shrink:0;">
            <span class="badge badge-blue">{{ $allActiveItems->where('has_anomaly', false)->count() }} bersih</span>
            @if($allActiveItems->where('has_anomaly', true)->count() > 0)
                <span class="badge badge-amber">{{ $allActiveItems->where('has_anomaly', true)->count() }} anomali</span>
            @endif
        </div>
        @endif
    </div>

    {{-- Table --}}
    @if($reportPayrolls->isEmpty())
        <div style="text-align:center; padding:56px 20px;">
            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" style="color:var(--muted); display:block; margin:0 auto 14px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            <div style="font-size:18px; font-weight:800; color:var(--navy); margin-bottom:8px;">Belum ada data payroll</div>
            <p class="muted" style="margin:0; font-size:14px; max-width:360px; margin-inline:auto;">Laporan akan tersedia setelah payroll pertama diproses.</p>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table style="min-width:960px;">
                <thead>
                    <tr>
                        <th style="width:220px;">Karyawan</th>
                        <th>NIP</th>
                        <th style="text-align:right;">Gaji Pokok</th>
                        <th style="text-align:right;">Lembur</th>
                        <th style="text-align:right;">Gross Pay</th>
                        <th style="text-align:right; color:#b45309;">BPJS TK</th>
                        <th style="text-align:right; color:#b45309;">BPJS Kes</th>
                        <th style="text-align:right; color:#b45309;">PPh21</th>
                        <th style="text-align:right;">Net Pay</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($filteredItems as $item)
                    @php
                        $anomalyTypes = [];
                        if ($item->has_anomaly && $item->anomaly_type) {
                            $anomalyTypes = is_array($item->anomaly_type)
                                ? $item->anomaly_type
                                : (json_decode($item->anomaly_type, true) ?? []);
                        }
                        $anomalyLabels = [
                            'no_bank_account'    => 'Rekening kosong',
                            'unverified_bank'    => 'Rekening belum verified',
                            'zero_net_pay'       => 'Net pay nol',
                            'missing_attendance' => 'Kehadiran tidak ditemukan',
                        ];
                    @endphp
                    <tr style="{{ $item->has_anomaly && !$item->anomaly_acknowledged ? 'background:#fffbeb;' : '' }}">
                        <td>
                            <div style="font-weight:600; color:var(--navy);">{{ $item->employee?->name ?? '-' }}</div>
                            <div class="muted" style="font-size:12px;">{{ $item->employee?->department ?? '' }}</div>
                            {{-- Anomaly detail --}}
                            @if($item->has_anomaly && $anomalyTypes)
                            <div style="margin-top:4px; display:flex; flex-wrap:wrap; gap:3px;">
                                @foreach($anomalyTypes as $type)
                                <span style="font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px; background:#fef3c7; color:#92400e; border:1px solid #fde68a;">
                                    {{ $anomalyLabels[$type] ?? $type }}
                                </span>
                                @endforeach
                                @if($item->anomaly_acknowledged)
                                <span style="font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px; background:#dcfce7; color:#166534; border:1px solid #bbf7d0;">✓ Acknowledged</span>
                                @endif
                            </div>
                            @endif
                        </td>
                        <td><span style="font-family:monospace; font-size:12px; color:var(--muted);">{{ $item->employee?->nip ?? '-' }}</span></td>
                        <td style="text-align:right; font-size:13px;">Rp {{ number_format((float)$item->basic_salary_snapshot, 0, ',', '.') }}</td>
                        <td style="text-align:right; font-size:13px;">
                            @if((float)$item->overtime_pay > 0)
                                <span style="color:var(--brand); font-weight:600;">Rp {{ number_format((float)$item->overtime_pay, 0, ',', '.') }}</span>
                            @else
                                <span class="muted">—</span>
                            @endif
                        </td>
                        <td style="text-align:right; font-size:13px; font-weight:600;">Rp {{ number_format((float)$item->gross_pay, 0, ',', '.') }}</td>
                        <td style="text-align:right; font-size:12px; color:#b45309;">Rp {{ number_format((float)$item->bpjs_tk_deduction, 0, ',', '.') }}</td>
                        <td style="text-align:right; font-size:12px; color:#b45309;">Rp {{ number_format((float)$item->bpjs_kesehatan_deduction, 0, ',', '.') }}</td>
                        <td style="text-align:right; font-size:12px; color:#b45309;">
                            @if((float)$item->pph21_deduction > 0)
                                Rp {{ number_format((float)$item->pph21_deduction, 0, ',', '.') }}
                            @else
                                <span class="muted">—</span>
                            @endif
                        </td>
                        <td style="text-align:right; font-weight:700; color:var(--navy);">Rp {{ number_format((float)$item->net_pay, 0, ',', '.') }}</td>
                        <td>
                            @if($item->has_anomaly && !$item->anomaly_acknowledged)
                                <span class="badge badge-amber">⚠ Anomali</span>
                            @elseif($item->has_anomaly && $item->anomaly_acknowledged)
                                <span class="badge badge-blue">⚠ Acknowledged</span>
                            @else
                                <span class="badge badge-green">✓ OK</span>
                            @endif
                        </td>
                        <td>
                            @if($activePayroll && $item->employee && in_array($activePayroll->status, ['approved', 'disbursed']))
                            <a href="{{ route('payroll.payslip', [$activePayroll, $item->employee]) }}"
                               class="btn btn-secondary" style="padding:4px 10px; font-size:12px;" title="Lihat payslip">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 3h7l4 4v14H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M14 3v5h5"/></svg>
                                Payslip
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" style="text-align:center; padding:40px 20px; color:var(--muted);">
                            @if($searchQuery || $anomalyFilter)
                                Tidak ada karyawan sesuai filter.
                                <a href="{{ route('app', 'reports') }}?report_period={{ $activePayroll?->period }}" style="color:var(--brand);">Reset filter</a>
                            @else
                                Belum ada item payroll untuk periode ini.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($filteredItems->isNotEmpty())
                <tfoot>
                    <tr style="background:#f8fafc; font-weight:700; border-top:2px solid var(--line);">
                        <td colspan="2" style="padding:12px 16px; color:var(--muted); font-size:12px; text-transform:uppercase; letter-spacing:.05em;">
                            Subtotal ({{ $filteredItems->count() }} karyawan)
                        </td>
                        <td style="text-align:right; padding:12px 14px; font-size:13px;">Rp {{ number_format($filteredItems->sum('basic_salary_snapshot'), 0, ',', '.') }}</td>
                        <td style="text-align:right; padding:12px 14px; font-size:13px; color:var(--brand);">Rp {{ number_format($filteredItems->sum('overtime_pay'), 0, ',', '.') }}</td>
                        <td style="text-align:right; padding:12px 14px; font-size:13px;">Rp {{ number_format($filteredItems->sum('gross_pay'), 0, ',', '.') }}</td>
                        <td style="text-align:right; padding:12px 14px; font-size:12px; color:#b45309;">Rp {{ number_format($filteredItems->sum('bpjs_tk_deduction'), 0, ',', '.') }}</td>
                        <td style="text-align:right; padding:12px 14px; font-size:12px; color:#b45309;">Rp {{ number_format($filteredItems->sum('bpjs_kesehatan_deduction'), 0, ',', '.') }}</td>
                        <td style="text-align:right; padding:12px 14px; font-size:12px; color:#b45309;">Rp {{ number_format($filteredItems->sum('pph21_deduction'), 0, ',', '.') }}</td>
                        <td style="text-align:right; padding:12px 14px; color:var(--brand); font-size:14px;">Rp {{ number_format($filteredItems->sum('net_pay'), 0, ',', '.') }}</td>
                        <td colspan="2" style="padding:12px 14px;"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════════
     SECTION 4 — Deduction Breakdown + Period History
═══════════════════════════════════════════════════════════════ --}}
@if($activePayroll && $allActiveItems->isNotEmpty())
<div class="grid grid-2" style="margin-top:20px;">

    {{-- Deduction Breakdown Card --}}
    <div class="section-card">
        <div class="section-header">
            <div style="font-size:15px; font-weight:700; color:var(--navy);">Breakdown Potongan</div>
            <span class="badge badge-red">{{ $activePayroll->period_label }}</span>
        </div>
        <div class="section-content">
            @php
                $bpjsTkTotal  = (float)$allActiveItems->sum('bpjs_tk_deduction');
                $bpjsKesTotal = (float)$allActiveItems->sum('bpjs_kesehatan_deduction');
                $pph21Total   = (float)$allActiveItems->sum('pph21_deduction');
                $grandDeduct  = $bpjsTkTotal + $bpjsKesTotal + $pph21Total;
            @endphp

            @foreach([
                ['label'=>'BPJS Ketenagakerjaan (2%)', 'value'=>$bpjsTkTotal,  'color'=>'var(--amber)', 'bar'=>'bar-blue'],
                ['label'=>'BPJS Kesehatan (1%)',        'value'=>$bpjsKesTotal, 'color'=>'#0891b2',      'bar'=>'bar-blue'],
                ['label'=>'PPh 21 (5% > 4,5 Jt)',       'value'=>$pph21Total,   'color'=>'var(--red)',    'bar'=>'bar-red'],
            ] as $row)
            <div style="margin-bottom:14px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:5px; font-size:13px;">
                    <span style="font-weight:600; color:var(--navy);">{{ $row['label'] }}</span>
                    <span style="font-weight:700; color:{{ $row['color'] }};">{{ fmtRpt($row['value']) }}</span>
                </div>
                <div class="progress-modern-bar">
                    <span class="{{ $row['bar'] }}" style="width:{{ $grandDeduct > 0 ? round($row['value'] / $grandDeduct * 100, 1) : 0 }}%;"></span>
                </div>
                <div class="muted" style="font-size:11px; margin-top:3px;">
                    {{ $grandDeduct > 0 ? round($row['value'] / $grandDeduct * 100, 1) : 0 }}% dari total potongan
                </div>
            </div>
            @endforeach

            <div style="padding:12px 14px; background:#fef2f2; border:1px solid #fecaca; border-radius:10px; display:flex; justify-content:space-between; align-items:center; margin-top:6px;">
                <span style="font-size:13px; font-weight:700; color:#991b1b;">Total Potongan</span>
                <span style="font-size:15px; font-weight:800; color:#dc2626;">{{ fmtRpt($grandDeduct) }}</span>
            </div>
        </div>
    </div>

    {{-- Period History --}}
    <div class="section-card">
        <div class="section-header">
            <div style="font-size:15px; font-weight:700; color:var(--navy);">Riwayat Periode</div>
            <span class="badge badge-blue">{{ $reportPayrolls->count() }} total</span>
        </div>
        <div style="padding:12px 16px; max-height:300px; overflow-y:auto;">
            @foreach($reportPayrolls as $p)
            @php
                $pBadge = match($p->status) {
                    'disbursed'        => 'badge-green',
                    'approved'         => 'badge-blue',
                    'pending_approval' => 'badge-amber',
                    'needs_review'     => 'badge-amber',
                    default            => '',
                };
                $pLabel = match($p->status) {
                    'disbursed'        => 'Disbursed',
                    'approved'         => 'Approved',
                    'pending_approval' => 'Pending',
                    'needs_review'     => 'Review',
                    default            => ucfirst($p->status),
                };
                $isActive = $activePayroll?->id === $p->id;
                $periodUrl = url('/app/reports/' . $p->id);
            @endphp
            <a href="{{ $periodUrl }}"
               style="display:flex; align-items:center; gap:10px; padding:9px 10px; border-radius:9px; margin-bottom:4px; text-decoration:none;
                      background:{{ $isActive ? 'var(--brand-soft)' : '#fff' }};
                      border:1px solid {{ $isActive ? 'var(--brand-line)' : 'var(--line)' }};
                      transition:background .12s, border-color .12s; min-height:0;"
               onmouseover="if(!{{ $isActive ? 'true' : 'false' }}) { this.style.background='var(--brand-soft)'; this.style.borderColor='var(--brand-line)'; }"
               onmouseout="if(!{{ $isActive ? 'true' : 'false' }}) { this.style.background='#fff'; this.style.borderColor='var(--line)'; }">
                <div style="flex:1; min-width:0;">
                    <div style="font-size:13px; font-weight:700; color:{{ $isActive ? 'var(--brand)' : 'var(--navy)' }};">{{ $p->period_label }}</div>
                    <div class="muted" style="font-size:12px;">{{ $p->employee_count }} karyawan · Net {{ fmtRpt((float)$p->net_total) }}</div>
                </div>
                <span class="badge {{ $pBadge }}" style="font-size:10px;">{{ $pLabel }}</span>
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:{{ $isActive ? 'var(--brand)' : 'var(--muted)' }}; flex-shrink:0;"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endif
