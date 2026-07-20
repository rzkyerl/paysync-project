@php
    $reportPayrolls      = $reportPayrolls      ?? collect();
    $reportLatestPayroll = $reportLatestPayroll  ?? null;
    $reportPayrollItems  = $reportPayrollItems   ?? collect();
    $isSuperAdmin        = $isSuperAdminViewing  ?? false;

    // Selected period from query string (handled via Alpine, fallback to latest)
    $selectedPeriod      = request('report_period', $reportLatestPayroll?->period);
    $activePayroll       = $selectedPeriod
        ? $reportPayrolls->firstWhere('period', $selectedPeriod)
        : $reportLatestPayroll;
    $activeItems         = $activePayroll
        ? $activePayroll->payrollItems
        : collect();

    function fmtRpt(float $v): string {
        if ($v >= 1_000_000_000) return 'Rp ' . number_format($v / 1_000_000_000, 2, ',', '.') . ' M';
        if ($v >= 1_000_000)     return 'Rp ' . number_format($v / 1_000_000, 2, ',', '.') . ' Jt';
        return 'Rp ' . number_format($v, 0, ',', '.');
    }

    $totalGross     = $reportPayrolls->sum('gross_total');
    $totalNet       = $reportPayrolls->sum('net_total');
    $totalDeduction = $reportPayrolls->sum('deduction_total');
    $totalEmployees = $reportPayrolls->max('employee_count') ?? 0;
@endphp

{{-- ═══ Summary KPIs ═══ --}}
<div class="grid grid-4" style="margin-bottom:20px;">
    <div class="kpi-modern kpi-blue">
        <div class="kpi-icon-wrap blue">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div class="kpi-label">Total Periode</div>
        <div class="kpi-value">{{ $reportPayrolls->count() }}</div>
        <div class="kpi-footer"><span class="badge badge-blue">Semua payroll</span></div>
    </div>
    <div class="kpi-modern kpi-amber">
        <div class="kpi-icon-wrap amber">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="kpi-label">Total Gross Pay</div>
        <div class="kpi-value" style="font-size:20px;">{{ fmtRpt((float)$totalGross) }}</div>
        <div class="kpi-footer"><span class="badge badge-amber">Semua periode</span></div>
    </div>
    <div class="kpi-modern kpi-red">
        <div class="kpi-icon-wrap red">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div class="kpi-label">Total Deduction</div>
        <div class="kpi-value" style="font-size:20px;">{{ fmtRpt((float)$totalDeduction) }}</div>
        <div class="kpi-footer"><span class="badge badge-red">Semua periode</span></div>
    </div>
    <div class="kpi-modern kpi-green">
        <div class="kpi-icon-wrap green">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div class="kpi-label">Total Net Pay</div>
        <div class="kpi-value" style="font-size:20px;">{{ fmtRpt((float)$totalNet) }}</div>
        <div class="kpi-footer"><span class="badge badge-green">Semua periode</span></div>
    </div>
</div>

{{-- ═══ Report Cards ═══ --}}
<div class="grid grid-3" style="margin-bottom:20px;">
    @php
    $reportCards = [
        ['title' => 'Payroll Report',        'desc' => 'Rincian gross pay, deduction, dan net pay per karyawan per periode.',        'icon' => 'payroll',   'href' => route('app', 'reports') . '?tab=payroll'],
        ['title' => 'Attendance Report',     'desc' => 'Data kehadiran, absensi, dan lembur per karyawan.',                          'icon' => 'calendar',  'href' => route('app', 'attendance')],
        ['title' => 'Disbursement Report',   'desc' => 'Status transfer batch, jumlah berhasil dan gagal per periode.',              'icon' => 'bank',      'href' => route('app', 'disbursement')],
        ['title' => 'Reconciliation Report', 'desc' => 'Cocokkan payroll net pay dengan nilai transfer aktual.',                     'icon' => 'link',      'href' => route('app', 'reconciliation')],
        ['title' => 'Audit Report',          'desc' => 'Riwayat perubahan data karyawan, payroll, dan pengaturan.',                  'icon' => 'shield',    'href' => route('app', 'audit')],
    ];
    @endphp
    @foreach($reportCards as $card)
    <a href="{{ $card['href'] }}" class="section-card" style="text-decoration:none; display:block; transition:box-shadow .15s, transform .15s; cursor:pointer;"
       onmouseover="this.style.boxShadow='0 8px 32px rgba(15,23,42,.12)';this.style.transform='translateY(-2px)'"
       onmouseout="this.style.boxShadow='';this.style.transform=''">
        <div class="section-content" style="display:flex; gap:16px; align-items:flex-start;">
            <div style="width:44px; height:44px; border-radius:12px; background:var(--brand-soft); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                @include('payflow.partials.icon', ['name' => $card['icon'], 'class' => 'icon icon-sm', 'style' => 'color:var(--brand)'])
            </div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:15px; font-weight:700; color:var(--navy); margin-bottom:4px;">{{ $card['title'] }}</div>
                <p class="muted" style="font-size:13px; margin:0; line-height:1.5;">{{ $card['desc'] }}</p>
            </div>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--muted); flex-shrink:0; margin-top:2px;"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
    </a>
    @endforeach
</div>

{{-- ═══ Payroll Report Detail ═══ --}}
<div class="section-card">
    <div class="section-header">
        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <div>
                <div style="font-size:16px; font-weight:700; color:var(--navy);">Payroll Report</div>
                <div class="muted" style="font-size:13px; margin-top:2px;">
                    Rincian per karyawan
                    @if($activePayroll) · {{ $activePayroll->period_label }} @endif
                </div>
            </div>
            @if($reportPayrolls->isNotEmpty())
            <form method="GET" action="{{ route('app', 'reports') }}" style="margin:0;">
                <select name="report_period" class="input" style="max-width:180px; font-size:13px;" onchange="this.form.submit()">
                    @foreach($reportPayrolls as $p)
                        <option value="{{ $p->period }}" {{ $activePayroll?->period === $p->period ? 'selected' : '' }}>
                            {{ $p->period_label }}
                        </option>
                    @endforeach
                </select>
            </form>
            @endif
        </div>
        @if(!$isSuperAdmin && $activePayroll)
        <div style="display:flex; gap:8px;">
            <a href="{{ route('payroll.show', $activePayroll) }}" class="btn btn-secondary" style="font-size:13px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                Lihat Detail
            </a>
        </div>
        @endif
    </div>

    @if($reportPayrolls->isEmpty())
        <div class="section-content" style="text-align:center; padding:48px 20px;">
            <svg width="44" height="44" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" style="color:var(--muted); display:block; margin:0 auto 12px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <div style="font-size:16px; font-weight:700; color:var(--navy); margin-bottom:6px;">Belum ada data payroll</div>
            <p class="muted" style="margin:0; font-size:13px;">Laporan akan tersedia setelah payroll pertama diproses.</p>
        </div>
    @else
        <div class="section-content" style="padding:0;">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>NIP</th>
                            <th style="text-align:right;">Gaji Pokok</th>
                            <th style="text-align:right;">Earning</th>
                            <th style="text-align:right;">Deduction</th>
                            <th style="text-align:right;">Net Pay</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeItems as $item)
                        <tr style="{{ $item->has_anomaly ? 'background:#fffbeb;' : '' }}">
                            <td>
                                <div style="font-weight:600; color:var(--navy);">{{ $item->employee?->name ?? '-' }}</div>
                                <div class="muted" style="font-size:12px;">{{ $item->employee?->department ?? '' }}</div>
                            </td>
                            <td><span style="font-family:monospace; font-size:12px; color:var(--muted);">{{ $item->employee?->nip ?? '-' }}</span></td>
                            <td style="text-align:right;">Rp {{ number_format((float)$item->basic_salary_snapshot, 0, ',', '.') }}</td>
                            <td style="text-align:right;">Rp {{ number_format((float)$item->overtime_pay, 0, ',', '.') }}</td>
                            <td style="text-align:right; color:var(--red);">Rp {{ number_format((float)$item->total_deduction, 0, ',', '.') }}</td>
                            <td style="text-align:right; font-weight:700; color:var(--navy);">Rp {{ number_format((float)$item->net_pay, 0, ',', '.') }}</td>
                            <td>
                                @if($item->has_anomaly)
                                    <span class="badge badge-amber">⚠ Anomali</span>
                                @else
                                    <span class="badge badge-green">✓ OK</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:32px; color:var(--muted);">
                                Belum ada item payroll untuk periode ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($activeItems->isNotEmpty())
                    <tfoot>
                        <tr style="background:#f8fafc; font-weight:700; border-top:2px solid var(--line);">
                            <td colspan="2" style="padding:12px 16px; color:var(--muted); font-size:12px; text-transform:uppercase; letter-spacing:.05em;">
                                Total ({{ $activeItems->count() }} karyawan)
                            </td>
                            <td style="text-align:right; padding:12px 16px;">Rp {{ number_format($activeItems->sum('basic_salary_snapshot'), 0, ',', '.') }}</td>
                            <td style="text-align:right; padding:12px 16px;">Rp {{ number_format($activeItems->sum('overtime_pay'), 0, ',', '.') }}</td>
                            <td style="text-align:right; padding:12px 16px; color:var(--red);">Rp {{ number_format($activeItems->sum('total_deduction'), 0, ',', '.') }}</td>
                            <td style="text-align:right; padding:12px 16px; color:var(--brand);">Rp {{ number_format($activeItems->sum('net_pay'), 0, ',', '.') }}</td>
                            <td style="padding:12px 16px;"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- Payroll history summary --}}
        <div style="padding:16px 20px; border-top:1px solid var(--line);">
            <div style="font-size:13px; font-weight:700; color:var(--navy); margin-bottom:10px;">Riwayat Semua Periode</div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                @foreach($reportPayrolls as $p)
                @php
                    $statusBadge = match($p->status) {
                        'disbursed'        => 'badge-green',
                        'approved'         => 'badge-blue',
                        'pending_approval' => 'badge-amber',
                        'needs_review'     => 'badge-amber',
                        default            => '',
                    };
                    $statusLabel = match($p->status) {
                        'disbursed'        => 'Disbursed',
                        'approved'         => 'Approved',
                        'pending_approval' => 'Pending',
                        'needs_review'     => 'Review',
                        default            => ucfirst($p->status),
                    };
                @endphp
                <a href="{{ route('payroll.show', $p) }}"
                   style="display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:8px; border:1px solid var(--line); background:{{ $activePayroll?->id === $p->id ? 'var(--brand-soft)' : '#fff' }}; font-size:13px; font-weight:600; color:{{ $activePayroll?->id === $p->id ? 'var(--brand)' : 'var(--navy)' }}; text-decoration:none;">
                    {{ $p->period_label }}
                    <span class="badge {{ $statusBadge }}" style="font-size:10px; padding:2px 6px;">{{ $statusLabel }}</span>
                </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
