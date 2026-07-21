<div x-data="{ loading: true }" x-init="$nextTick(() => loading = false)">

    {{-- Skeleton --}}
    <div x-show="loading">
        @include('payflow.partials.skeleton-kpi')
        <div class="grid grid-2" style="margin-top:16px;">
            <div class="card skeleton" style="height:280px;"></div>
            <div class="card skeleton" style="height:280px;"></div>
        </div>
        <div class="card skeleton" style="height:200px; margin-top:16px;"></div>
    </div>

    <div x-show="!loading" x-cloak>

@if($isDemoUser ?? false)
    <div class="card" style="margin-bottom:16px; padding:12px 16px; border-left:4px solid var(--amber); display:flex; align-items:center; gap:10px;">
        <span class="badge badge-amber">Mode Demo</span>
        <span class="muted" style="font-size:13px;">Data ini adalah contoh milik {{ $companyName }}.</span>
    </div>
@endif

@if($isEmpty ?? false)
    {{-- ── Empty State: belum ada karyawan ── --}}
    <div style="display:grid; place-items:center; min-height:420px;">
        <div style="text-align:center; max-width:480px; padding:20px;">
            <div style="width:72px; height:72px; border-radius:20px; background:var(--brand-soft); display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                <svg width="34" height="34" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--brand);"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
            </div>
            <h2 style="margin:0 0 10px; font-size:22px; font-weight:800; color:var(--navy); font-family:var(--font-display); letter-spacing:-0.02em;">Mulai dengan menambahkan karyawan</h2>
            <p class="muted" style="margin:0 0 24px; font-size:14px; line-height:1.7;">
                Tambahkan data karyawan terlebih dahulu — bisa satu per satu atau import sekaligus via CSV.
                Setelah itu, proses payroll pertama bisa dimulai.
            </p>
            <div style="display:flex; gap:10px; flex-wrap:wrap; justify-content:center;">
                @if(!($isSuperAdminViewing ?? false))
                <a href="{{ route('employees.create') }}" class="btn btn-primary">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Tambah Karyawan
                </a>
                <a href="{{ route('employees.import') }}" class="btn btn-secondary">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Import CSV
                </a>
                @else
                <p class="muted" style="font-size:13px; margin:0;">Mode Pantau — aksi tersedia untuk HR Manager.</p>
                @endif
            </div>
        </div>
    </div>
@else

@if ($error ?? null)
    <div class="card" style="border-left: 4px solid var(--red); padding: 16px; margin-bottom: 16px; display:flex; align-items:center; gap:10px;">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--red);flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div><strong style="color:var(--red);">Gagal memuat data</strong> <span class="muted" style="margin-left:6px;">{{ $error }}</span></div>
    </div>
@endif

{{-- ═══ KPI Cards ═══ --}}
<div class="grid grid-4">
    @php
        $kpiMeta = [
            ['color' => 'blue',  'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
            ['color' => 'amber', 'icon' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>'],
            ['color' => 'green', 'icon' => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>'],
            ['color' => 'blue',  'icon' => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>'],
        ];
    @endphp
    @forelse ($kpis as $i => $kpi)
        @php $meta = $kpiMeta[$i] ?? ['color'=>'blue','icon'=>'']; @endphp
        <div class="kpi-modern kpi-{{ $meta['color'] }}">
            <div class="kpi-icon-wrap {{ $meta['color'] }}">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $meta['icon'] !!}</svg>
            </div>
            <div class="kpi-label" style="padding-right:52px;">{{ $kpi['label'] }}</div>
            <div class="kpi-value">{{ $kpi['value'] }}</div>
            <div class="kpi-footer">
                <span class="badge {{ $kpi['badge'] }}">{{ $kpi['sub'] }}</span>
            </div>
        </div>
    @empty
        <div class="kpi-modern kpi-blue"><div class="kpi-label">Karyawan Aktif</div><div class="kpi-value">—</div></div>
        <div class="kpi-modern kpi-amber"><div class="kpi-label">Kehadiran Periode Ini</div><div class="kpi-value">—</div></div>
        <div class="kpi-modern kpi-green"><div class="kpi-label">Status Payroll</div><div class="kpi-value">—</div></div>
        <div class="kpi-modern kpi-blue"><div class="kpi-label">Estimasi Take-home Pay</div><div class="kpi-value">—</div></div>
    @endforelse
</div>

{{-- ═══ Row 2: Payroll Timeline + Action Center ═══ --}}
<div class="grid grid-2" style="margin-top:18px;">

    {{-- Payroll Timeline --}}
    <div class="section-card">
        <div class="section-header">
            <h2>Payroll Timeline</h2>
            <div style="display:flex; gap:8px; align-items:center;">
                <span class="badge badge-amber">{{ $activeStageLabel ?? 'Draft' }}</span>
                <a href="{{ route('payroll.index') }}" class="btn btn-secondary" style="padding:6px 12px; font-size:12px;">Buka Payroll →</a>
            </div>
        </div>
        <div class="section-content timeline-modern">
            @forelse ($payrollTimeline as $t)
                @php
                    $stepClass = match($t['status']) { 'done' => 'done', 'active' => 'active', default => 'wait' };
                @endphp
                <div class="timeline-item">
                    <div class="timeline-step {{ $stepClass }}">{{ $t['step'] }}</div>
                    <div>
                        <div style="font-weight:600; font-size:14px;">{{ $t['label'] }}</div>
                        @if($t['status'] === 'active')
                            <div class="muted" style="font-size:12px;">Sedang berjalan</div>
                        @endif
                    </div>
                    <span class="badge {{ $t['badgeClass'] }}">{{ $t['statusText'] }}</span>
                </div>
            @empty
                <div class="section-content"><p class="muted">Tidak ada data payroll aktif.</p></div>
            @endforelse
        </div>
    </div>

    {{-- Action Center --}}
    <div class="section-card">
        <div class="section-header">
            <h2>Action Center</h2>
            @if (($actionItemCount ?? 0) > 0)
                <span class="badge badge-red">{{ $actionItemCount }} perlu tindakan</span>
            @else
                <span class="badge badge-green">✓ Semua beres</span>
            @endif
        </div>
        <div class="section-content" style="display:grid; gap:8px;">
            @forelse ($actionItems as $item)
                @php
                    $dotColor = match($item['level']) { 'danger' => 'background:var(--red);', 'warning' => 'background:var(--amber);', default => 'background:var(--brand);' };
                    $bgStyle = match($item['level']) { 'danger' => 'background:#fef2f2; border-color:#fecaca;', 'warning' => 'background:#fffbeb; border-color:#fde68a;', default => 'background:var(--brand-soft); border-color:var(--brand-line);' };
                @endphp
                <div class="action-item" style="{{ $bgStyle }}">
                    <span class="action-dot" style="{{ $dotColor }}"></span>
                    <div>
                        <div style="font-weight:600; font-size:13px;">{{ $item['text'] }}</div>
                        @if($item['level'] === 'danger')
                            <div class="muted" style="font-size:12px; margin-top:2px;">Segera tindak lanjuti sebelum payroll dikirim.</div>
                        @endif
                    </div>
                </div>
            @empty
                <div style="text-align:center; padding:24px 0;">
                    <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--green); margin-bottom:8px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <p class="muted" style="margin:0; font-size:13px;">Tidak ada item yang perlu ditindaklanjuti.</p>
                </div>
            @endforelse
            @if(($actionItemCount ?? 0) > 0)
                <div class="quick-actions">
                    <a href="{{ route('employees.index') }}" class="quick-action-btn primary">Cek Rekening</a>
                    <a href="{{ route('payroll.index') }}" class="quick-action-btn">Review Payroll</a>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- ═══ Row 3: Chart + Recent Employees ═══ --}}
<div class="grid grid-2" style="margin-top:18px;">

    {{-- Payroll Overview Chart --}}
    <div class="section-card">
        <div class="section-header">
            <h2>Overview Payroll</h2>
            <span class="badge badge-blue">6 Bulan Terakhir</span>
        </div>
        <div class="section-content">
            <div class="chart-wrap">
                <canvas id="hrPayrollChart" height="200"></canvas>
            </div>
            <div style="display:flex; gap:16px; margin-top:14px; flex-wrap:wrap;">
                <div style="display:flex; align-items:center; gap:6px; font-size:12px;">
                    <span style="width:12px;height:12px;border-radius:3px;background:var(--brand);display:inline-block;"></span> Gross Pay
                </div>
                <div style="display:flex; align-items:center; gap:6px; font-size:12px;">
                    <span style="width:12px;height:12px;border-radius:3px;background:#22c55e;display:inline-block;"></span> Net Pay
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Employee Changes --}}
    <div class="section-card">
        <div class="section-header">
            <h2>Karyawan Terbaru</h2>
            <a class="btn btn-secondary" href="{{ route('employees.index') }}" style="padding:6px 12px; font-size:12px;">Lihat Semua →</a>
        </div>
        <div style="overflow-x:auto;">
            <table style="min-width:0;">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Status</th>
                        <th>Rekening</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentEmployees as $employee)
                        @php
                            $workBadge = match ($employee->work_status) { 'active' => 'badge-green', 'probation' => 'badge-amber', 'contract' => 'badge-blue', 'inactive' => 'badge-red', default => '' };
                            $workLabel = match ($employee->work_status) { 'active' => 'Aktif', 'probation' => 'Probation', 'contract' => 'Kontrak', 'inactive' => 'Nonaktif', default => $employee->work_status };
                            $bankBadge = match ($employee->bank_account_status) { 'verified' => 'badge-green', 'unverified' => 'badge-amber', 'rejected' => 'badge-red', default => '' };
                            $bankLabel = match ($employee->bank_account_status) { 'verified' => 'Verified', 'unverified' => 'Pending', 'rejected' => 'Ditolak', default => $employee->bank_account_status };
                        @endphp
                        <tr>
                            <td>
                                <div style="font-weight:600; font-size:13px;">{{ $employee->name }}</div>
                                <div class="muted" style="font-size:11px;">{{ $employee->department }} · {{ $employee->nip }}</div>
                            </td>
                            <td><span class="badge {{ $workBadge }}">{{ $workLabel }}</span></td>
                            <td><span class="badge {{ $bankBadge }}">{{ $bankLabel }}</span></td>
                            <td>
                                <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-secondary" style="padding:5px 10px; font-size:12px;">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="muted" style="text-align:center; padding:24px;">Belum ada data karyawan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:12px 16px; border-top:1px solid var(--line); display:flex; gap:8px;">
            <a href="{{ route('employees.create') }}" class="quick-action-btn primary" style="font-size:12px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Karyawan
            </a>
            <a href="{{ url('/app/attendance') }}" class="quick-action-btn" style="font-size:12px;">Import Kehadiran</a>
        </div>
    </div>

</div>

@endif

    </div>{{-- end x-show="!loading" --}}
</div>{{-- end x-data --}}

{{-- Chart.js untuk dashboard HR --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('hrPayrollChart');
    if (!canvas) return;

    function renderHrChart() {
        if (typeof Chart === 'undefined') {
            return setTimeout(renderHrChart, 80);
        }
        const months = ['Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul'];
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Gross Pay',
                        data: [185, 192, 188, 204, 198, 210],
                        backgroundColor: 'rgba(15,52,115,.75)',
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                    {
                        label: 'Net Pay',
                        data: [156, 162, 159, 172, 168, 178],
                        backgroundColor: 'rgba(34,197,94,.75)',
                        borderRadius: 6,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ' Rp ' + ctx.parsed.y + ' Jt'
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, border: { display: false } },
                    y: {
                        grid: { color: '#f1f5f9' },
                        border: { display: false },
                        ticks: {
                            callback: (v) => 'Rp ' + v + ' Jt',
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
    }

    renderHrChart();
});
</script>
