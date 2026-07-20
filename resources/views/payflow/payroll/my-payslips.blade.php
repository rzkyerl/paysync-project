<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Slip Gaji Saya – PaySync</title>
    @include('payflow.partials.styles')
</head>
<body>
<div style="min-height:100vh; background:var(--bg); padding:32px 20px; max-width:960px; margin:0 auto;">
    @include('payflow.partials.brand')

    <div class="page-head" style="margin-top:24px; margin-bottom:24px;">
        <div>
            <h1>Slip Gaji Saya</h1>
            <p class="muted">Riwayat slip gaji Anda dari semua periode yang sudah diterbitkan.</p>
        </div>
        <a href="/app/dashboard-employee" class="btn btn-secondary">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Kembali
        </a>
    </div>

    @if($items->isEmpty())
        <div class="section-card">
            <div class="section-content" style="text-align:center; padding:56px 20px;">
                <svg width="52" height="52" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" style="color:var(--muted); display:block; margin:0 auto 14px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <div style="font-size:18px; font-weight:700; color:var(--navy); margin-bottom:6px;">Belum ada slip gaji</div>
                <p class="muted" style="margin:0; font-size:14px;">Slip gaji akan muncul di sini setelah payroll Anda disetujui.</p>
            </div>
        </div>
    @else
        <div class="section-card">
            <div class="section-content" style="padding:0;">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Periode</th>
                                <th>Status</th>
                                <th style="text-align:right;">Gross Pay</th>
                                <th style="text-align:right;">Potongan</th>
                                <th style="text-align:right;">Net Pay</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr>
                                <td>
                                    <div style="font-weight:600; color:var(--navy);">{{ $item->payroll->period_label }}</div>
                                    @if($item->disbursed_at)
                                        <div class="muted" style="font-size:12px;">Dibayar {{ $item->disbursed_at->format('d M Y') }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($item->status === 'transferred')
                                        <span class="badge badge-green">✓ Transferred</span>
                                    @elseif($item->payroll->status === 'approved')
                                        <span class="badge badge-blue">Disetujui</span>
                                    @else
                                        <span class="badge badge-amber">{{ ucfirst($item->payroll->status) }}</span>
                                    @endif
                                </td>
                                <td style="text-align:right;">Rp {{ number_format((float)$item->gross_pay, 0, ',', '.') }}</td>
                                <td style="text-align:right; color:var(--red);">Rp {{ number_format((float)$item->total_deduction, 0, ',', '.') }}</td>
                                <td style="text-align:right; font-weight:700; font-size:15px; color:var(--navy);">Rp {{ number_format((float)$item->net_pay, 0, ',', '.') }}</td>
                                <td>
                                    <a href="{{ route('payroll.payslip', [$item->payroll, $item->employee_id]) }}"
                                       class="btn btn-secondary" style="padding:5px 12px; font-size:12px;">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        Lihat Slip
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
<script defer src="{{ asset('vendor/alpinejs/cdn.min.js') }}"></script>
</body>
</html>
