{{-- Dashboard Employee — real data from DashboardController::employee() --}}
{{-- Variables: $kpis, $lastPayslip, $employee, $error --}}

<div x-data="{ loading: true }" x-init="$nextTick(() => loading = false)">

    {{-- Skeleton --}}
    <div x-show="loading">
        @include('payflow.partials.skeleton-kpi')
        <div class="grid grid-2" style="margin-top:20px;">
            <div class="card skeleton" style="height:220px;"></div>
            <div class="card skeleton" style="height:220px;"></div>
        </div>
    </div>

    <div x-show="!loading" x-cloak>

@if($isEmpty ?? false)
    <div class="grid grid-4">
        @foreach(['Next Payday', 'Kehadiran Bulan Ini', 'Lembur Bulan Ini', 'Take-home Pay Terakhir'] as $label)
            <div class="kpi-modern kpi-blue">
                <div class="kpi-label">{{ $label }}</div>
                <div class="kpi-value">—</div>
                <div class="kpi-footer"><span class="badge">Belum ada data</span></div>
            </div>
        @endforeach
    </div>
    <x-empty-state
        style="margin-top:20px;"
        icon="users"
        title="Profil karyawan belum terhubung"
        description="Hubungi HR perusahaan untuk menghubungkan akun Anda dengan data karyawan dan slip gaji."
    />
@else

@if(!empty($error))
    <div class="card" style="border-left: 4px solid var(--red); padding: 14px 16px; margin-bottom: 20px; display:flex; align-items:center; gap:10px;">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--red);flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div>
            <strong style="color:var(--red);">⚠ {{ $error }}</strong>
            <span class="muted" style="margin-left:8px; font-size:13px;">Silakan muat ulang halaman atau hubungi administrator.</span>
        </div>
    </div>
@endif

{{-- ═══ KPI Cards (Requirement 3.1) ═══ --}}
<div class="grid grid-4">

    {{-- Next Payday --}}
    @php
        $days = $kpis['next_payday_days'] ?? null;
        $daysLabel = '-'; $daysBadge = 'badge-blue'; $kpiColor = 'blue';
        if ($days !== null) {
            if ($days > 0)      { $daysLabel = $days.' hari lagi'; $daysBadge = 'badge-blue'; $kpiColor = 'blue'; }
            elseif ($days === 0){ $daysLabel = 'Hari ini!';        $daysBadge = 'badge-green'; $kpiColor = 'green'; }
            else                { $daysLabel = abs($days).' hari lalu'; $daysBadge = 'badge-amber'; $kpiColor = 'amber'; }
        }
    @endphp
    <div class="kpi-modern kpi-{{ $kpiColor }}">
        <div class="kpi-icon-wrap {{ $kpiColor }}">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div class="kpi-label" style="padding-right:52px;">Next Payday</div>
        <div class="kpi-value">{{ $kpis['next_payday_date'] ?? '-' }}</div>
        <div class="kpi-footer"><span class="badge {{ $daysBadge }}">{{ $daysLabel }}</span></div>
    </div>

    {{-- Kehadiran Bulan Ini --}}
    @php
        $attended = $kpis['attendance_this_month'] ?? 0;
        $total    = $kpis['attendance_total_days'] ?? 0;
        $attPct   = $total > 0 ? round(($attended / $total) * 100) : 0;
        $attColor = $attPct >= 90 ? 'green' : ($attPct >= 70 ? 'blue' : 'amber');
    @endphp
    <div class="kpi-modern kpi-{{ $attColor }}">
        <div class="kpi-icon-wrap {{ $attColor }}">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </div>
        <div class="kpi-label" style="padding-right:52px;">Kehadiran Bulan Ini</div>
        <div class="kpi-value">{{ $attended }}<span style="font-size:16px; font-weight:600; color:var(--muted);">/{{ $total }}</span></div>
        <div class="kpi-footer">
            <span class="badge badge-blue">Estimasi hari kerja</span>
            <span style="font-size:12px; font-weight:700; color:var(--{{ $attColor }});">{{ $attPct }}%</span>
        </div>
    </div>

    {{-- Lembur Bulan Ini --}}
    @php $ot = $kpis['overtime_hours'] ?? 0; @endphp
    <div class="kpi-modern kpi-{{ $ot > 0 ? 'amber' : 'blue' }}">
        <div class="kpi-icon-wrap {{ $ot > 0 ? 'amber' : 'blue' }}">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="kpi-label" style="padding-right:52px;">Lembur Bulan Ini</div>
        <div class="kpi-value">{{ number_format($ot, 1, ',', '.') }}<span style="font-size:16px; font-weight:600; color:var(--muted);"> jam</span></div>
        <div class="kpi-footer">
            <span class="badge {{ $ot > 0 ? 'badge-amber' : 'badge-blue' }}">{{ $ot > 0 ? 'Disetujui' : 'Tidak ada lembur' }}</span>
        </div>
    </div>

    {{-- Take-home Pay Terakhir --}}
    <div class="kpi-modern kpi-green">
        <div class="kpi-icon-wrap green">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="kpi-label" style="padding-right:52px;">Take-home Pay Terakhir</div>
        <div class="kpi-value" style="font-size:22px;">{{ $kpis['last_take_home'] ?? '-' }}</div>
        <div class="kpi-footer">
            @if($lastPayslip)
                <span class="badge {{ $lastPayslip->status === 'disbursed' ? 'badge-green' : 'badge-blue' }}">
                    {{ $lastPayslip->status === 'disbursed' ? 'Transfer Selesai' : 'Disetujui' }}
                </span>
            @else
                <span class="badge badge-amber">Belum ada data</span>
            @endif
        </div>
    </div>

</div>

{{-- ═══ Row 2: Slip Gaji + Profil & Rekening ═══ --}}
<div class="grid grid-2" style="margin-top:20px;">

    {{-- Slip Gaji Terbaru --}}
    <div class="section-card">
        <div class="section-header">
            <h2>Slip Gaji Terbaru</h2>
            @if($lastPayslip)
                @if($lastPayslip->status === 'disbursed')
                    <span class="badge badge-green">Transfer Selesai</span>
                @elseif($lastPayslip->status === 'approved')
                    <span class="badge badge-blue">Disetujui</span>
                @else
                    <span class="badge">{{ $lastPayslip->status }}</span>
                @endif
            @else
                <span class="badge badge-amber">Belum Ada</span>
            @endif
        </div>
        <div class="section-content">
            @if($lastPayslip)
                {{-- Period label --}}
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
                    <div style="width:44px; height:44px; border-radius:12px; background:var(--brand); display:grid; place-items:center; color:#fff; flex-shrink:0;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                    <div>
                        <div style="font-weight:700; color:var(--navy);">Periode {{ $lastPayslip->period_label }}</div>
                        <div class="muted" style="font-size:12px;">{{ $lastPayslip->status === 'disbursed' ? 'Pembayaran berhasil dikirim' : 'Menunggu disbursement' }}</div>
                    </div>
                </div>

                {{-- Detail rows --}}
                <div style="border:1px solid var(--line); border-radius:12px; overflow:hidden; margin-bottom:16px;">
                    <div class="detail-row" style="padding:10px 14px;">
                        <span class="detail-label">Gross Pay</span>
                        <span class="detail-value">Rp {{ number_format((float)($lastPayslip->gross_total ?? 0), 0, ',', '.') }}</span>
                    </div>
                    <div class="detail-row" style="padding:10px 14px;">
                        <span class="detail-label">Potongan</span>
                        <span class="detail-value" style="color:var(--red);">- Rp {{ number_format(max(0, (float)($lastPayslip->gross_total ?? 0) - (float)($lastPayslip->net_total ?? 0)), 0, ',', '.') }}</span>
                    </div>
                    <div class="detail-row" style="padding:10px 14px; background:var(--brand-soft);">
                        <span class="detail-label" style="font-weight:700; color:var(--navy);">Net Pay</span>
                        <span class="detail-value" style="font-size:16px; color:var(--brand);">Rp {{ number_format((float)$lastPayslip->net_total, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="quick-actions">
                    <a href="{{ route('app', 'payslips') }}" class="quick-action-btn primary">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        Lihat Detail
                    </a>
                    <a href="{{ route('app', 'payslips') }}" class="quick-action-btn">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Riwayat Slip
                    </a>
                </div>
            @else
                <div style="text-align:center; padding:32px 0;">
                    <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--muted); margin-bottom:10px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0121 9.414V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="muted" style="margin:0 0 8px; font-size:13px;">Belum ada slip gaji yang tersedia.</p>
                    <a href="{{ route('app', 'payslips') }}" class="quick-action-btn" style="display:inline-flex;">Cek Halaman Slip Gaji</a>
                </div>
            @endif
        </div>
    </div>

    {{-- Profil dan Rekening --}}
    <div class="section-card">
        <div class="section-header">
            <h2>Profil & Rekening</h2>
            @if($employee)
                @if($employee->bank_account_status === 'verified')
                    <span class="badge badge-green">✓ Terverifikasi</span>
                @elseif($employee->bank_account_status === 'rejected')
                    <span class="badge badge-red">Ditolak</span>
                @else
                    <span class="badge badge-amber">Perlu Verifikasi</span>
                @endif
            @else
                <span class="badge badge-amber">Tidak Terhubung</span>
            @endif
        </div>
        <div class="section-content">
            @if($employee)
                {{-- Avatar + Name --}}
                <div style="display:flex; align-items:center; gap:12px; padding:12px 0 16px; border-bottom:1px solid var(--line); margin-bottom:14px;">
                    <div style="width:48px; height:48px; border-radius:999px; background:linear-gradient(135deg,#1e5aa3,var(--brand)); color:#fff; display:grid; place-items:center; font-size:16px; font-weight:800; flex-shrink:0; letter-spacing:0;">
                        {{ strtoupper(substr($employee->name, 0, 2)) }}
                    </div>
                    <div>
                        <div style="font-weight:700; font-size:15px; color:var(--navy);">{{ $employee->name }}</div>
                        <div class="muted" style="font-size:12px;">{{ $employee->position }} · {{ $employee->department }}</div>
                    </div>
                </div>

                {{-- Detail --}}
                <div style="border:1px solid var(--line); border-radius:12px; overflow:hidden;">
                    <div class="detail-row" style="padding:9px 14px;">
                        <span class="detail-label">NIP</span>
                        <span class="detail-value">{{ $employee->nip }}</span>
                    </div>
                    <div class="detail-row" style="padding:9px 14px;">
                        <span class="detail-label">Bank</span>
                        <span class="detail-value">{{ $employee->bank_name ?? '-' }}</span>
                    </div>
                    <div class="detail-row" style="padding:9px 14px;">
                        <span class="detail-label">No. Rekening</span>
                        <span class="detail-value">
                            @if($employee->bank_account_number)
                                •••• {{ substr($employee->bank_account_number, -4) }}
                            @else -
                            @endif
                        </span>
                    </div>
                    <div class="detail-row" style="padding:9px 14px; border-bottom:0;">
                        <span class="detail-label">Status Rekening</span>
                        <span>
                            @if($employee->bank_account_status === 'verified')
                                <span class="badge badge-green">Terverifikasi</span>
                            @elseif($employee->bank_account_status === 'rejected')
                                <span class="badge badge-red">Ditolak</span>
                            @else
                                <span class="badge badge-amber">Menunggu verifikasi HR</span>
                            @endif
                        </span>
                    </div>
                </div>

                @if($employee->bank_account_status === 'rejected')
                    <div style="margin-top:12px; padding:10px 14px; border-radius:10px; background:#fef2f2; border:1px solid #fecaca; font-size:13px; color:#991b1b;">
                        ⚠ Rekening Anda ditolak. Hubungi HR untuk memperbarui data rekening.
                    </div>
                @elseif($employee->bank_account_status === 'unverified')
                    <div style="margin-top:12px; padding:10px 14px; border-radius:10px; background:var(--brand-soft); border:1px solid var(--brand-line); font-size:13px; color:var(--brand);">
                        ℹ Rekening sedang dalam proses verifikasi oleh HR.
                    </div>
                @endif

                <div class="quick-actions">
                    <a href="{{ route('app', 'payslips') }}" class="quick-action-btn primary" style="font-size:12px;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Semua Slip Gaji
                    </a>
                    <a href="{{ url('/app/attendance') }}" class="quick-action-btn" style="font-size:12px;">Kehadiran Saya</a>
                </div>
            @else
                <div style="text-align:center; padding:32px 0;">
                    <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--muted); margin-bottom:10px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <p class="muted" style="margin:0 0 4px; font-size:13px;">Akun Anda belum terhubung ke data karyawan.</p>
                    <p class="muted" style="margin:0; font-size:12px;">Silakan hubungi HR untuk menghubungkan akun Anda.</p>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- ═══ Attendance Mini Chart ═══ --}}
@php $total = $kpis['attendance_total_days'] ?? 0; @endphp
@if($total > 0)
<div class="section-card" style="margin-top:20px;">
    <div class="section-header">
        <h2>Kehadiran Bulan Ini</h2>
        <span class="badge badge-blue">{{ date('F Y') }}</span>
    </div>
    <div class="section-content">
        <div style="display:flex; align-items:center; gap:24px; flex-wrap:wrap;">
            <div style="position:relative; width:140px; height:140px; flex-shrink:0;">
                <canvas id="attendanceDonutChart" width="140" height="140"></canvas>
                <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); text-align:center; line-height:1.2;">
                    <div style="font-size:22px; font-weight:800; color:var(--navy); font-family:var(--font-display);">{{ $attPct ?? 0 }}%</div>
                    <div style="font-size:11px; color:var(--muted);">Hadir</div>
                </div>
            </div>
            <div style="flex:1; min-width:200px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div style="padding:14px; background:var(--brand-soft); border-radius:12px; border:1px solid var(--brand-line);">
                        <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--brand);">Hadir</div>
                        <div style="font-size:24px; font-weight:800; color:var(--navy); font-family:var(--font-display); margin:4px 0;">{{ $kpis['attendance_this_month'] ?? 0 }}</div>
                        <div class="muted" style="font-size:12px;">hari</div>
                    </div>
                    <div style="padding:14px; background:#f8fafc; border-radius:12px; border:1px solid var(--line);">
                        <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted);">Total Hari Kerja</div>
                        <div style="font-size:24px; font-weight:800; color:var(--navy); font-family:var(--font-display); margin:4px 0;">{{ $kpis['attendance_total_days'] ?? 0 }}</div>
                        <div class="muted" style="font-size:12px;">hari</div>
                    </div>
                </div>
                <div style="margin-top:14px;">
                    <div style="display:flex; justify-content:space-between; font-size:12px; font-weight:600; margin-bottom:6px;">
                        <span>Progress kehadiran</span>
                        <span>{{ $attPct ?? 0 }}%</span>
                    </div>
                    <div class="progress-modern-bar" style="height:10px;">
                        <span class="{{ ($attPct ?? 0) >= 90 ? 'bar-green' : (($attPct ?? 0) >= 70 ? 'bar-blue' : 'bar-red') }}" style="width:{{ $attPct ?? 0 }}%;"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endif

    </div>{{-- end x-show="!loading" --}}
</div>{{-- end x-data --}}

{{-- Chart.js untuk dashboard Employee --}}
@php $attPctForJs = $attPct ?? 0; @endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('attendanceDonutChart');
    if (!canvas) return;
    const pct = {{ (int)$attPctForJs }};

    function renderAttChart() {
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [pct, 100 - pct],
                    backgroundColor: [
                        pct >= 90 ? 'rgba(34,197,94,.80)' : (pct >= 70 ? 'rgba(15,52,115,.75)' : 'rgba(220,38,38,.75)'),
                        '#f1f5f9'
                    ],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: false,
                cutout: '72%',
                plugins: { legend: { display: false }, tooltip: { enabled: false } }
            }
        });
    }

    if (typeof Chart === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
        script.onload = renderAttChart;
        document.head.appendChild(script);
    } else {
        renderAttChart();
    }
});
</script>
