<div x-data="{ loading: true }" x-init="$nextTick(() => loading = false)">

    {{-- Skeleton --}}
    <div x-show="loading">
        @include('payflow.partials.skeleton-kpi')
        <div class="grid grid-2" style="margin-top:16px;">
            <div class="card skeleton" style="height:260px;"></div>
            <div class="card skeleton" style="height:260px;"></div>
        </div>
    </div>

    <div x-show="!loading" x-cloak>

@if($isEmpty ?? false)
    {{-- ── Empty State: belum ada data perusahaan ── --}}
    <div style="display:grid; place-items:center; min-height:420px;">
        <div style="text-align:center; max-width:460px; padding:20px;">
            <div style="width:72px; height:72px; border-radius:20px; background:var(--brand-soft); display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                <svg width="34" height="34" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--brand);"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
            </div>
            <h2 style="margin:0 0 10px; font-size:22px; font-weight:800; color:var(--navy); font-family:var(--font-display); letter-spacing:-0.02em;">Belum ada data untuk ditampilkan</h2>
            <p class="muted" style="margin:0 0 24px; font-size:14px; line-height:1.7;">
                Dashboard Finance akan aktif setelah HR menyiapkan data karyawan dan memproses payroll pertama.
            </p>
            <div style="display:flex; flex-direction:column; gap:10px; align-items:center;">
                <div style="display:flex; gap:10px; flex-wrap:wrap; justify-content:center;">
                    <a href="{{ url('/app/approval') }}" class="btn btn-primary">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Cek Approval Queue
                    </a>
                    <a href="{{ url('/app/disbursement') }}" class="btn btn-secondary">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        Batch Transfer
                    </a>
                </div>
                <p class="muted" style="font-size:12px; margin:4px 0 0;">Hubungi HR Manager untuk memulai proses payroll.</p>
            </div>
        </div>
    </div>
@else

@if(isset($error) && $error)
    <div class="card" style="border-left: 4px solid var(--red); padding: 16px; margin-bottom: 16px; display:flex; align-items:center; gap:10px;">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--red);flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div>
            <strong style="color:var(--red);">Gagal memuat data</strong>
            <span class="muted" style="margin-left:6px;">{{ $error }}</span>
            <a class="btn btn-secondary" style="margin-left:10px; padding:4px 10px; font-size:12px;" href="{{ request()->url() }}">Coba Lagi</a>
        </div>
    </div>
@else

{{-- ═══ KPI Cards ═══ --}}
<div class="grid grid-4">
    @php
        $finKpiMeta = [
            ['color' => 'amber', 'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>'],
            ['color' => 'blue',  'icon' => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>'],
            ['color' => 'green', 'icon' => '<polyline points="20 6 9 17 4 12"/>'],
            ['color' => 'red',   'icon' => '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>'],
        ];
    @endphp
    @foreach ($kpis as $i => $kpi)
        @php $meta = $finKpiMeta[$i] ?? ['color'=>'blue','icon'=>'']; @endphp
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
    @endforeach
</div>

{{-- ═══ Row 2: Transfer Status Chart + Approval Queue ═══ --}}
<div class="grid grid-2" style="margin-top:18px;">

    {{-- Transfer Status Chart --}}
    <div class="section-card">
        <div class="section-header">
            <h2>Transfer Batch Status</h2>
            <span class="badge badge-blue">Payroll Operations</span>
        </div>
        <div class="section-content">
            {{-- Donut Chart --}}
            <div style="display:flex; align-items:center; gap:24px; flex-wrap:wrap;">
                <div style="position:relative; width:160px; height:160px; flex-shrink:0;">
                    <canvas id="transferDonutChart" width="160" height="160"></canvas>
                </div>
                <div style="flex:1; display:grid; gap:14px; min-width:120px;">
                    @foreach ($transferBatchStatus as $row)
                        <div class="progress-modern">
                            <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:600;">
                                <span>{{ $row['label'] }}</span>
                                <span>{{ $row['percent'] }}%</span>
                            </div>
                            <div class="progress-modern-bar">
                                @php $barClass = match($row['label']) { 'Success' => 'bar-green', 'Failed' => 'bar-red', default => 'bar-blue' }; @endphp
                                <span class="{{ $barClass }}" style="width:{{ $row['percent'] }}%;"></span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Rekonsiliasi Summary --}}
            <div style="margin-top:18px; padding:14px; border-radius:12px; background:var(--brand-soft); border:1px solid var(--brand-line);">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
                    <div>
                        <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--brand); margin-bottom:4px;">Rekonsiliasi</div>
                        <div style="font-weight:700; color:var(--navy);">Matched {{ $reconciliation['matched_label'] }}</div>
                        @if($reconciliation['has_mismatch'])
                            <div class="muted" style="font-size:12px; margin-top:2px;">Mismatch {{ $reconciliation['mismatch_label'] }}</div>
                        @else
                            <div style="font-size:12px; color:var(--green); font-weight:600; margin-top:2px;">✓ Tidak ada selisih</div>
                        @endif
                    </div>
                    <a href="/app/reconciliation" class="btn btn-primary" style="padding:8px 14px; font-size:13px; flex-shrink:0;">
                        @if($reconciliation['has_mismatch']) Selesaikan Selisih @else Lihat Rekonsiliasi @endif
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Approval Queue --}}
    <div class="section-card">
        <div class="section-header">
            <h2>Approval Queue</h2>
            <div style="display:flex; gap:8px; align-items:center;">
                <span class="badge badge-amber">{{ $approvalQueue->count() }} payroll</span>
                <a href="/app/approval" class="btn btn-secondary" style="padding:6px 12px; font-size:12px;">Lihat Semua →</a>
            </div>
        </div>
        @if($approvalQueue->isEmpty())
            <div class="section-content" style="text-align:center; padding:32px 20px;">
                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--green); margin-bottom:10px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <p class="muted" style="margin:0; font-size:13px;">Tidak ada payroll yang menunggu approval.</p>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table style="min-width:0;">
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th>Net Pay</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($approvalQueue as $payroll)
                            <tr>
                                <td>
                                    <div style="font-weight:600; font-size:13px;">{{ $payroll->period_label }}</div>
                                    <div class="muted" style="font-size:11px;">{{ $payroll->submitter?->name ?? '-' }}</div>
                                </td>
                                <td style="font-weight:700;">Rp {{ number_format((float)$payroll->net_total, 0, ',', '.') }}</td>
                                <td><span class="badge badge-amber">Menunggu</span></td>
                                <td>
                                    <a href="/app/approval" class="btn btn-primary" style="padding:5px 12px; font-size:12px;">Review</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:12px 16px; border-top:1px solid var(--line); display:flex; gap:8px;">
                <a href="/app/approval" class="quick-action-btn primary" style="font-size:12px;">Proses Semua Approval</a>
                <a href="/app/disbursement" class="quick-action-btn" style="font-size:12px;">Lihat Disbursement</a>
            </div>
        @endif
    </div>

</div>

@endif
@endif

    </div>{{-- end x-show="!loading" --}}
</div>{{-- end x-data --}}

{{-- Chart.js untuk dashboard Finance --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('transferDonutChart');
    if (!canvas) return;
    const data = @json($transferBatchStatus ?? []);

    function renderFinanceChart() {
        if (typeof Chart === 'undefined') {
            return setTimeout(renderFinanceChart, 80);
        }
        const labels = data.map(r => r.label);
        const values = data.map(r => r.percent);
        const hasData = values.some(v => v > 0);
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: hasData ? values : [1],
                    backgroundColor: hasData
                        ? ['rgba(34,197,94,.80)', 'rgba(15,52,115,.75)', 'rgba(220,38,38,.75)']
                        : ['#e2e8f0'],
                    borderWidth: 0,
                    hoverOffset: 4,
                }]
            },
            options: {
                responsive: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ' ' + ctx.label + ': ' + ctx.parsed + '%'
                        }
                    }
                }
            }
        });
    }

    renderFinanceChart();
});
</script>
