@php
    $user = auth()->user();
    $isHr = $user->isHrManager();
    $isFinance = $user->isFinanceManager();
    $isSuperAdmin = $user->isSuperAdmin();
    $statusLabels = [
        'draft'            => ['label' => 'Draft',             'badge' => 'badge-amber'],
        'needs_review'     => ['label' => 'Perlu Review',      'badge' => 'badge-amber'],
        'pending_approval' => ['label' => 'Menunggu Approval', 'badge' => 'badge-blue'],
        'approved'         => ['label' => 'Disetujui',         'badge' => 'badge-green'],
        'disbursed'        => ['label' => 'Transfer Selesai',  'badge' => 'badge-green'],
    ];
    $statusInfo = $statusLabels[$payroll->status] ?? ['label' => $payroll->status, 'badge' => ''];
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payroll {{ $payroll->period_label }} – PaySync</title>
    @include('payflow.partials.styles')
</head>
<body>
<div style="min-height:100vh; background:var(--bg); padding:32px 20px; max-width:1100px; margin:0 auto;">
    @include('payflow.partials.brand')

    {{-- Page head --}}
    <div class="page-head" style="margin-top:24px; margin-bottom:20px;">
        <div>
            <h1>Payroll {{ $payroll->period_label }}</h1>
            <div style="display:flex; align-items:center; gap:8px; margin-top:4px; flex-wrap:wrap;">
                <span class="badge {{ $statusInfo['badge'] }}">{{ $statusInfo['label'] }}</span>
                @if($payroll->submitter)
                    <span class="muted" style="font-size:12px;">Disubmit oleh <strong>{{ $payroll->submitter->name }}</strong></span>
                @endif
                @if($payroll->approver)
                    <span class="muted" style="font-size:12px;">· Disetujui oleh <strong>{{ $payroll->approver->name }}</strong></span>
                @endif
            </div>
        </div>
        <a href="{{ route('payroll.index') }}" class="btn btn-secondary">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Kembali
        </a>
    </div>

    {{-- Flash messages --}}
    @if(session('status'))
    <div style="padding:14px 16px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; color:#166534; font-size:14px; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        {{ session('status') }}
    </div>
    @endif
    @if(session('error'))
    <div style="padding:14px 16px; background:#fef2f2; border:1px solid #fecaca; border-radius:12px; color:#dc2626; font-size:14px; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ session('error') }}
    </div>
    @endif
    @if($payroll->rejection_note)
    <div style="padding:14px 16px; background:#fef3c7; border:1px solid #fde68a; border-radius:12px; color:#92400e; font-size:14px; margin-bottom:20px; display:flex; gap:10px; align-items:flex-start;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0; margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div><strong>Catatan Penolakan:</strong> {{ $payroll->rejection_note }}</div>
    </div>
    @endif

    {{-- ═══ KPI Summary ═══ --}}
    <div class="grid grid-4" style="margin-bottom:20px;">
        <div class="kpi-modern kpi-blue">
            <div class="kpi-icon-wrap blue">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <div class="kpi-label">Karyawan</div>
            <div class="kpi-value">{{ number_format($payroll->employee_count, 0, ',', '.') }}</div>
            <div class="kpi-footer"><span class="badge badge-blue">Total</span></div>
        </div>
        <div class="kpi-modern kpi-amber">
            <div class="kpi-icon-wrap amber">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div class="kpi-label">Gross Pay</div>
            <div class="kpi-value" style="font-size:20px;">Rp {{ number_format((float)$payroll->gross_total/1_000_000,2,',','.') }} Jt</div>
            <div class="kpi-footer"><span class="badge badge-amber">Sebelum potongan</span></div>
        </div>
        <div class="kpi-modern kpi-red">
            <div class="kpi-icon-wrap red">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div class="kpi-label">Deduction</div>
            <div class="kpi-value" style="font-size:20px;">Rp {{ number_format((float)$payroll->deduction_total/1_000_000,2,',','.') }} Jt</div>
            <div class="kpi-footer"><span class="badge badge-red">Total potongan</span></div>
        </div>
        <div class="kpi-modern kpi-green">
            <div class="kpi-icon-wrap green">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="kpi-label">Net Pay</div>
            <div class="kpi-value" style="font-size:20px;">Rp {{ number_format((float)$payroll->net_total/1_000_000,2,',','.') }} Jt</div>
            <div class="kpi-footer">
                <span class="badge {{ $payroll->anomaly_count > 0 ? 'badge-red' : 'badge-green' }}">
                    {{ $payroll->anomaly_count > 0 ? $payroll->anomaly_count.' anomali' : '✓ Bersih' }}
                </span>
            </div>
        </div>
    </div>

    {{-- ═══ Action buttons + Modals ═══ --}}
    <div x-data="{ rejectOpen: false, rejectNote: '', approveOpen: false }" style="margin-bottom:20px;">
        <div class="section-card">
            <div class="section-content">
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    @if($isHr && $payroll->status === 'draft')
                        <a href="{{ route('payroll.attendance.import', $payroll) }}" class="btn btn-secondary">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            Import Kehadiran
                        </a>
                        <form method="POST" action="{{ route('payroll.calculate', $payroll) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="10" x2="16" y2="10"/><line x1="8" y1="14" x2="11" y2="14"/></svg>
                                Hitung Payroll
                            </button>
                        </form>
                    @elseif($isHr && $payroll->status === 'needs_review' && $canSubmit)
                        <form method="POST" action="{{ route('payroll.submit', $payroll) }}">
                            @csrf @method('PUT')
                            <button type="submit" class="btn btn-primary">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                Submit ke Finance
                            </button>
                        </form>
                    @elseif(($isFinance || $isSuperAdmin) && $payroll->status === 'pending_approval')
                        <button type="button" class="btn btn-primary" style="background:#16a34a; border-color:#16a34a;" @click="approveOpen = true">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Approve Payroll
                        </button>
                        <button type="button" class="btn btn-danger" @click="rejectOpen = true">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Reject
                        </button>
                    @elseif(($isFinance || $isSuperAdmin) && $payroll->status === 'approved')
                        <form method="POST" action="{{ route('payroll.disburse', $payroll) }}" enctype="multipart/form-data" style="display:flex; gap:8px; align-items:center;">
                            @csrf
                            <input type="file" name="proof" accept=".pdf,.jpg,.jpeg,.png" class="input" style="max-width:220px; font-size:13px;">
                            <button type="submit" class="btn btn-primary">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                                Disburse
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('payroll.reconcile', $payroll) }}" class="btn btn-secondary">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        Rekonsiliasi
                    </a>
                </div>
            </div>
        </div>

        {{-- Approve Modal --}}
        <template x-teleport="body">
        <div x-show="approveOpen" x-cloak class="modal-overlay"
            @click.self="approveOpen = false" @keydown.window.escape="approveOpen = false"
            role="dialog" aria-modal="true">
            <div class="modal-dialog" x-transition>
                <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px;">
                    <div style="display:flex; align-items:center; gap:14px;">
                        <div class="modal-icon" style="margin-bottom:0; flex-shrink:0; background:#dcfce7; color:#16a34a;">
                            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <div>
                            <div class="modal-title" style="margin:0;">Approve Payroll</div>
                            <div class="muted" style="font-size:13px; margin-top:2px;">{{ $payroll->period_label }} · {{ $payroll->employee_count }} karyawan</div>
                        </div>
                    </div>
                    <button type="button" @click="approveOpen = false" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--line);background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted);flex-shrink:0;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1px; background:var(--line); border:1px solid var(--line); border-radius:12px; overflow:hidden; margin-bottom:18px;">
                    <div style="padding:14px 16px; background:#f8fafc;">
                        <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:4px;">Gross Pay</div>
                        <div style="font-size:15px; font-weight:800; color:var(--navy);">Rp {{ number_format((float)$payroll->gross_total/1_000_000,2,',','.') }} Jt</div>
                    </div>
                    <div style="padding:14px 16px; background:#f8fafc;">
                        <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:4px;">Deduction</div>
                        <div style="font-size:15px; font-weight:800; color:var(--navy);">Rp {{ number_format((float)$payroll->deduction_total/1_000_000,2,',','.') }} Jt</div>
                    </div>
                    <div style="padding:14px 16px; background:var(--brand-soft); grid-column:span 2;">
                        <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:4px;">Net Pay</div>
                        <div style="font-size:22px; font-weight:800; color:var(--brand);">Rp {{ number_format((float)$payroll->net_total/1_000_000,2,',','.') }} Jt</div>
                    </div>
                </div>
                <p style="font-size:14px; color:var(--muted); margin:0 0 20px; line-height:1.65;">
                    Setujui payroll <strong style="color:var(--navy);">{{ $payroll->period_label }}</strong> untuk <strong style="color:var(--navy);">{{ $payroll->employee_count }} karyawan</strong>? Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" @click="approveOpen = false">Batal</button>
                    <form method="POST" action="{{ route('payroll.approve', $payroll) }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn btn-primary" style="background:#16a34a; border-color:#16a34a;">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Ya, Approve
                        </button>
                    </form>
                </div>
            </div>
        </div>
        </template>

        {{-- Reject Modal --}}
        <template x-teleport="body">
        <div x-show="rejectOpen" x-cloak class="modal-overlay"
            @click.self="rejectOpen = false" @keydown.window.escape="rejectOpen = false"
            role="dialog" aria-modal="true">
            <div class="modal-dialog" x-transition>
                <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px;">
                    <div style="display:flex; align-items:center; gap:14px;">
                        <div class="modal-icon modal-icon--danger" style="margin-bottom:0; flex-shrink:0;">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </div>
                        <div>
                            <div class="modal-title" style="margin:0;">Reject Payroll</div>
                            <div class="muted" style="font-size:13px; margin-top:2px;">{{ $payroll->period_label }}</div>
                        </div>
                    </div>
                    <button type="button" @click="rejectOpen = false" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--line);background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted);flex-shrink:0;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div style="padding:12px 14px; background:#fef3c7; border:1px solid #fde68a; border-radius:10px; font-size:13px; color:#92400e; margin-bottom:18px; line-height:1.6;">
                    Payroll akan dikembalikan ke HR dengan alasan penolakan.
                </div>
                <form method="POST" action="{{ route('payroll.reject', $payroll) }}">
                    @csrf
                    <div class="field" style="margin-bottom:20px;">
                        <label style="font-size:13px; font-weight:700; color:#334155; display:block; margin-bottom:6px;">Alasan Penolakan <span style="color:var(--red)">*</span></label>
                        <textarea name="rejection_note" x-model="rejectNote" rows="4" class="input" style="resize:vertical;" placeholder="Jelaskan alasan penolakan agar HR dapat memperbaiki data..."></textarea>
                        <div style="font-size:12px; color:var(--muted); margin-top:4px;" x-text="rejectNote.trim().length + ' karakter (min. 5)'"></div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" @click="rejectOpen = false">Batal</button>
                        <button type="submit" class="btn btn-danger" :disabled="rejectNote.trim().length < 5">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Konfirmasi Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </template>
    </div>

    {{-- ═══ Payroll Items Table ═══ --}}
    <div class="section-card">
        <div class="section-header">
            <div>
                <div style="font-size:16px; font-weight:700; color:var(--navy);">Rincian Payroll Items</div>
                <div class="muted" style="font-size:13px; margin-top:2px;">{{ $payroll->payrollItems->count() }} karyawan</div>
            </div>
            <span class="badge {{ $payroll->anomaly_count > 0 ? 'badge-red' : 'badge-green' }}">
                {{ $payroll->anomaly_count > 0 ? $payroll->anomaly_count.' anomali' : '✓ Semua bersih' }}
            </span>
        </div>
        <div class="section-content" style="padding:0;">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>NIP</th>
                            <th style="text-align:right;">Gross Pay</th>
                            <th style="text-align:right;">Potongan</th>
                            <th style="text-align:right;">Net Pay</th>
                            <th>Status</th>
                            @if($isHr && $payroll->status === 'needs_review')<th>Aksi</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payroll->payrollItems as $item)
                        <tr style="{{ $item->has_anomaly && !$item->anomaly_acknowledged ? 'background:#fffbeb;' : '' }}">
                            <td>
                                <div style="font-weight:600; color:var(--navy);">{{ $item->employee?->name ?? '-' }}</div>
                                <div class="muted" style="font-size:12px;">{{ $item->employee?->position ?? '' }}</div>
                            </td>
                            <td><span style="font-family:monospace; font-size:13px; color:var(--muted);">{{ $item->employee?->nip ?? '-' }}</span></td>
                            <td style="text-align:right; font-weight:600;">Rp {{ number_format($item->gross_pay, 0, ',', '.') }}</td>
                            <td style="text-align:right; color:var(--red);">Rp {{ number_format($item->total_deduction, 0, ',', '.') }}</td>
                            <td style="text-align:right; font-weight:700; color:var(--navy);">Rp {{ number_format($item->net_pay, 0, ',', '.') }}</td>
                            <td>
                                @if($item->has_anomaly)
                                    <span class="badge {{ $item->anomaly_acknowledged ? 'badge-blue' : 'badge-amber' }}">
                                        {{ $item->anomaly_acknowledged ? 'Acknowledged' : '⚠ Anomali' }}
                                    </span>
                                @else
                                    <span class="badge badge-green">✓ OK</span>
                                @endif
                            </td>
                            @if($isHr && $payroll->status === 'needs_review')
                            <td>
                                @if($item->has_anomaly && !$item->anomaly_acknowledged)
                                <form method="POST" action="{{ route('payroll.anomaly.acknowledge', [$payroll, $item]) }}">
                                    @csrf
                                    <button class="btn btn-secondary" style="padding:5px 10px; font-size:12px;">Acknowledge</button>
                                </form>
                                @endif
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:40px; color:var(--muted);">
                                <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" style="display:block; margin:0 auto 8px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                Belum ada item payroll. Import kehadiran dan hitung payroll terlebih dahulu.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<script defer src="{{ asset('vendor/alpinejs/cdn.min.js') }}"></script>
</body>
</html>
