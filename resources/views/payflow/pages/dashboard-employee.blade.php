{{-- Dashboard Employee — real data from DashboardController::employee() --}}
{{-- Variables: $kpis, $lastPayslip, $employee, $error --}}

<div x-data="{ loading: true }" x-init="$nextTick(() => loading = false)">

    {{-- Skeleton (shown while loading) --}}
    <div x-show="loading">
        @include('payflow.partials.skeleton-kpi')
        <div class="grid grid-2" style="margin-top:20px;">
            <div class="card skeleton" style="height:220px;"></div>
            <div class="card skeleton" style="height:220px;"></div>
        </div>
    </div>

    {{-- Real Content (hidden until loading is false) --}}
    <div x-show="!loading" x-cloak>

@if($isEmpty ?? false)
    <div class="grid grid-4">
        @foreach(['Next Payday', 'Kehadiran Bulan Ini', 'Lembur Bulan Ini', 'Take-home Pay Terakhir'] as $label)
            <div class="card kpi"><span class="muted">{{ $label }}</span><div class="value">—</div><span class="badge">Belum ada data</span></div>
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
    <div class="card" style="border-left: 4px solid var(--red, #ef4444); padding: 16px 20px; margin-bottom: 20px;">
        <p style="margin:0; color: var(--red, #ef4444); font-weight: 600;">⚠ {{ $error }}</p>
        <p style="margin:4px 0 0; font-size: 13px; color: var(--muted);">Silakan muat ulang halaman atau hubungi administrator.</p>
    </div>
@endif

{{-- ============================================================
     KPI Cards (Requirement 3.1)
     ============================================================ --}}
<div class="grid grid-4">

    {{-- Next Payday --}}
    <div class="card kpi">
        <span class="muted" style="font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Next Payday</span>
        <div class="value" style="font-size:28px; font-weight:800; margin: 6px 0 4px;">
            {{ $kpis['next_payday_date'] ?? '-' }}
        </div>
        @php
            $days = $kpis['next_payday_days'] ?? null;
            $daysLabel = '-';
            $daysBadge = 'badge-blue';
            if ($days !== null) {
                if ($days > 0) {
                    $daysLabel = $days . ' hari lagi';
                    $daysBadge = 'badge-blue';
                } elseif ($days === 0) {
                    $daysLabel = 'Hari ini!';
                    $daysBadge = 'badge-green';
                } else {
                    $daysLabel = abs($days) . ' hari lalu';
                    $daysBadge = 'badge-amber';
                }
            }
        @endphp
        <span class="badge {{ $daysBadge }}">{{ $daysLabel }}</span>
    </div>

    {{-- Kehadiran Bulan Ini --}}
    <div class="card kpi">
        <span class="muted" style="font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Kehadiran Bulan Ini</span>
        <div class="value" style="font-size:28px; font-weight:800; margin: 6px 0 4px;">
            {{ $kpis['attendance_this_month'] ?? 0 }}/{{ $kpis['attendance_total_days'] ?? 0 }}
        </div>
        <span class="badge badge-blue">Estimasi hari kerja</span>
    </div>

    {{-- Lembur Bulan Ini --}}
    <div class="card kpi">
        <span class="muted" style="font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Lembur Bulan Ini</span>
        <div class="value" style="font-size:28px; font-weight:800; margin: 6px 0 4px;">
            {{ number_format($kpis['overtime_hours'] ?? 0, 1, ',', '.') }} jam
        </div>
        <span class="badge badge-blue">
            {{ ($kpis['overtime_hours'] ?? 0) > 0 ? 'Disetujui' : 'Tidak ada lembur' }}
        </span>
    </div>

    {{-- Take-home Pay Terakhir --}}
    <div class="card kpi">
        <span class="muted" style="font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Take-home Pay Terakhir</span>
        <div class="value" style="font-size:28px; font-weight:800; margin: 6px 0 4px;">
            {{ $kpis['last_take_home'] ?? '-' }}
        </div>
        @if($lastPayslip)
            <span class="badge badge-green">
                {{ $lastPayslip->status === 'disbursed' ? 'Berhasil' : 'Disetujui' }}
            </span>
        @else
            <span class="badge badge-amber">Belum ada data</span>
        @endif
    </div>

</div>

{{-- ============================================================
     Bottom Cards: Slip Gaji + Profil & Rekening
     ============================================================ --}}
<div class="grid grid-2" style="margin-top:20px;">

    {{-- Slip Gaji Terbaru (Requirement 3.2) --}}
    <section class="card">
        <div class="section-title">
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
        <div class="section-body">
            @if($lastPayslip)
                <p style="margin:0 0 4px;">
                    <strong>Periode {{ $lastPayslip->period_label }}</strong>
                </p>
                <p class="muted" style="margin:0 0 12px;">
                    Net pay
                    Rp {{ number_format((float) $lastPayslip->net_total, 0, ',', '.') }}
                    &mdash;
                    {{ $lastPayslip->status === 'disbursed' ? 'pembayaran berhasil' : 'menunggu disbursement' }}.
                </p>
                <a class="btn btn-primary" href="{{ route('app', 'payslips') }}">Lihat Detail</a>
                <button class="btn btn-secondary" style="margin-left:8px;" disabled title="Fitur PDF belum tersedia">
                    Download PDF
                </button>
            @else
                {{-- Empty state --}}
                <div style="text-align:center; padding: 24px 0;">
                    <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--muted); margin-bottom:8px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0121 9.414V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="muted" style="margin:0; font-size:13px;">Belum ada slip gaji yang tersedia.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- Profil dan Rekening (Requirement 3.3) --}}
    <section class="card">
        <div class="section-title">
            <h2>Profil dan Rekening</h2>
            @if($employee)
                @if($employee->bank_account_status === 'verified')
                    <span class="badge badge-green">Terverifikasi</span>
                @elseif($employee->bank_account_status === 'rejected')
                    <span class="badge badge-red">Ditolak</span>
                @else
                    <span class="badge badge-amber">Perlu Verifikasi</span>
                @endif
            @else
                <span class="badge badge-amber">Tidak Terhubung</span>
            @endif
        </div>
        <div class="section-body">
            @if($employee)
                <table style="width:100%; font-size:14px; border-collapse:collapse;">
                    <tbody>
                        <tr>
                            <td class="muted" style="padding: 5px 0; width:45%;">Nama</td>
                            <td style="padding: 5px 0; font-weight:600;">{{ $employee->name }}</td>
                        </tr>
                        <tr>
                            <td class="muted" style="padding: 5px 0;">NIP</td>
                            <td style="padding: 5px 0;">{{ $employee->nip }}</td>
                        </tr>
                        <tr>
                            <td class="muted" style="padding: 5px 0;">Bank</td>
                            <td style="padding: 5px 0;">{{ $employee->bank_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="muted" style="padding: 5px 0;">No. Rekening</td>
                            <td style="padding: 5px 0;">
                                @if($employee->bank_account_number)
                                    {{-- Show only last 4 digits for privacy --}}
                                    •••• {{ substr($employee->bank_account_number, -4) }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="muted" style="padding: 5px 0;">Status Rekening</td>
                            <td style="padding: 5px 0;">
                                @if($employee->bank_account_status === 'verified')
                                    <span class="badge badge-green" style="font-size:11px;">Terverifikasi</span>
                                @elseif($employee->bank_account_status === 'rejected')
                                    <span class="badge badge-red" style="font-size:11px;">Ditolak</span>
                                @else
                                    <span class="badge badge-amber" style="font-size:11px;">Menunggu verifikasi HR</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>

                @if($employee->bank_account_status === 'rejected')
                    <p class="muted" style="margin-top:12px; font-size:13px;">
                        ⚠ Rekening Anda ditolak. Silakan hubungi HR untuk memperbarui data rekening.
                    </p>
                @elseif($employee->bank_account_status === 'unverified')
                    <p class="muted" style="margin-top:12px; font-size:13px;">
                        Rekening sedang dalam proses verifikasi oleh HR.
                    </p>
                @endif
            @else
                {{-- Empty state: user not linked to an employee record --}}
                <div style="text-align:center; padding: 24px 0;">
                    <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--muted); margin-bottom:8px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <p class="muted" style="margin:0; font-size:13px;">Akun Anda belum terhubung ke data karyawan.</p>
                    <p class="muted" style="margin:4px 0 0; font-size:12px;">Silakan hubungi HR untuk menghubungkan akun Anda.</p>
                </div>
            @endif
        </div>
    </section>

</div>

@endif

    </div>{{-- end x-show="!loading" --}}
</div>{{-- end x-data --}}
