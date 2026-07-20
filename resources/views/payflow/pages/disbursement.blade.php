@php
    $disbursementPayrolls = $disbursementPayrolls ?? collect();
    $disbReadyCount       = $disbReadyCount ?? 0;
    $disbDisbursedCount   = $disbDisbursedCount ?? 0;
    $disbSuccessAmount    = $disbSuccessAmount ?? 0;
    $disbPendingAmount    = $disbPendingAmount ?? 0;
    $isSuperAdmin         = $isSuperAdminViewing ?? false;

    function fmtJt(float $v): string {
        if ($v >= 1_000_000_000) return 'Rp ' . number_format($v / 1_000_000_000, 2, ',', '.') . ' M';
        if ($v >= 1_000_000)     return 'Rp ' . number_format($v / 1_000_000, 2, ',', '.') . ' Jt';
        return 'Rp ' . number_format($v, 0, ',', '.');
    }
@endphp

{{-- ═══ KPI Strip ═══ --}}
<div class="grid grid-4" style="margin-bottom:20px;">
    <div class="kpi-modern kpi-blue">
        <div class="kpi-icon-wrap blue">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="kpi-label">Siap Transfer</div>
        <div class="kpi-value">{{ $disbReadyCount }}</div>
        <div class="kpi-footer">
            <span class="badge {{ $disbReadyCount > 0 ? 'badge-blue' : 'badge-green' }}">
                {{ $disbReadyCount > 0 ? 'Perlu diproses' : 'Semua beres' }}
            </span>
        </div>
    </div>
    <div class="kpi-modern kpi-green">
        <div class="kpi-icon-wrap green">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="kpi-label">Transfer Selesai</div>
        <div class="kpi-value">{{ $disbDisbursedCount }}</div>
        <div class="kpi-footer"><span class="badge badge-green">Periode selesai</span></div>
    </div>
    <div class="kpi-modern kpi-green">
        <div class="kpi-icon-wrap green">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="kpi-label">Total Tersalurkan</div>
        <div class="kpi-value" style="font-size:20px;">{{ fmtJt((float)$disbSuccessAmount) }}</div>
        <div class="kpi-footer"><span class="badge badge-green">Disbursed</span></div>
    </div>
    <div class="kpi-modern kpi-amber">
        <div class="kpi-icon-wrap amber">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div class="kpi-label">Pending Transfer</div>
        <div class="kpi-value" style="font-size:20px;">{{ fmtJt((float)$disbPendingAmount) }}</div>
        <div class="kpi-footer">
            <span class="badge {{ $disbPendingAmount > 0 ? 'badge-amber' : 'badge-green' }}">
                {{ $disbPendingAmount > 0 ? 'Belum disalurkan' : 'Bersih' }}
            </span>
        </div>
    </div>
</div>

{{-- ═══ Batch Table ═══ --}}
<div class="section-card">
    <div class="section-header">
        <div>
            <div style="font-size:16px; font-weight:700; color:var(--navy);">Penyaluran Gaji</div>
            <div class="muted" style="font-size:13px; margin-top:2px;">
                {{ $disbursementPayrolls->count() }} batch · payroll approved &amp; disbursed
            </div>
        </div>
        @if(!$isSuperAdmin && $disbReadyCount > 0)
            <div style="display:flex; align-items:center; gap:8px;">
                <span class="badge badge-blue">{{ $disbReadyCount }} siap diproses</span>
            </div>
        @endif
    </div>

    @if($disbursementPayrolls->isEmpty())
        <div class="section-content" style="text-align:center; padding:56px 20px;">
            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" style="color:var(--muted); display:block; margin:0 auto 14px;"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            <div style="font-size:17px; font-weight:700; color:var(--navy); margin-bottom:6px;">Belum ada batch transfer</div>
            <p class="muted" style="margin:0; font-size:14px;">Batch transfer tersedia setelah payroll disetujui Finance.</p>
        </div>
    @else
        <div class="section-content" style="padding:0;">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th style="text-align:right;">Karyawan</th>
                            <th style="text-align:right;">Net Pay</th>
                            <th>Disetujui Oleh</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($disbursementPayrolls as $payroll)
                        @php
                            $transferredCount = $payroll->payrollItems->where('status', 'transferred')->count();
                            $totalItems       = $payroll->payrollItems->count();
                            $isReady          = $payroll->status === 'approved';
                            $isDone           = $payroll->status === 'disbursed';
                        @endphp
                        <tr>
                            <td>
                                <div style="font-weight:700; color:var(--navy);">{{ $payroll->period_label }}</div>
                                <div class="muted" style="font-size:12px; font-family:monospace;">
                                    PAY-{{ str_pad($payroll->id, 4, '0', STR_PAD_LEFT) }}
                                </div>
                            </td>
                            <td style="text-align:right;">
                                <span style="font-weight:600;">{{ number_format($payroll->employee_count, 0, ',', '.') }}</span>
                                @if($isDone && $totalItems > 0)
                                    <div class="muted" style="font-size:11px;">{{ $transferredCount }}/{{ $totalItems }} transferred</div>
                                @endif
                            </td>
                            <td style="text-align:right; font-weight:700; color:var(--navy);">
                                {{ fmtJt((float)$payroll->net_total) }}
                            </td>
                            <td>
                                <span style="font-size:13px; color:var(--muted);">
                                    {{ $payroll->approver?->name ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size:13px; color:var(--muted);">
                                    {{ $isDone ? $payroll->disbursed_at?->format('d M Y') : ($payroll->approved_at?->format('d M Y') ?? '-') }}
                                </span>
                            </td>
                            <td>
                                @if($isDone)
                                    <span class="badge badge-green">✓ Disbursed</span>
                                @else
                                    <span class="badge badge-blue">Siap Transfer</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
                                    <a href="{{ route('payroll.show', $payroll) }}"
                                       class="btn btn-secondary" style="padding:5px 10px; font-size:12px;">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        Detail
                                    </a>
                                    @if($isReady && auth()->user()->hasAnyRole(['finance_manager', 'super_admin']))
                                        <div x-data="{ open: false }">
                                            <button type="button" @click="open = true"
                                                class="btn btn-primary" style="padding:5px 10px; font-size:12px; background:#16a34a; border-color:#16a34a;">
                                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                                                Disburse
                                            </button>
                                            <template x-teleport="body">
                                            <div x-show="open" x-cloak class="modal-overlay"
                                                @click.self="open = false"
                                                @keydown.window.escape="open = false"
                                                role="dialog" aria-modal="true">
                                                <div class="modal-dialog" x-transition>
                                                    <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:16px;">
                                                        <div>
                                                            <div class="modal-title" style="margin:0;">Konfirmasi Disburse</div>
                                                            <div class="muted" style="font-size:13px; margin-top:3px;">{{ $payroll->period_label }}</div>
                                                        </div>
                                                        <button type="button" @click="open = false" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--line);background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted);flex-shrink:0;">
                                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                        </button>
                                                    </div>
                                                    <p style="font-size:14px; color:var(--muted); margin:0 0 18px; line-height:1.65;">
                                                        Salurkan gaji <strong style="color:var(--navy);">{{ $payroll->period_label }}</strong> sebesar
                                                        <strong style="color:var(--brand);">{{ fmtJt((float)$payroll->net_total) }}</strong>
                                                        untuk <strong style="color:var(--navy);">{{ $payroll->employee_count }} karyawan</strong>?
                                                    </p>
                                                    <form method="POST" action="{{ route('payroll.disburse', $payroll) }}" enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="field" style="margin-bottom:18px;">
                                                            <label style="font-size:13px; font-weight:700; color:#334155; display:block; margin-bottom:6px;">
                                                                Bukti Transfer <span class="muted" style="font-weight:400;">(opsional)</span>
                                                            </label>
                                                            <input type="file" name="proof" accept=".pdf,.jpg,.jpeg,.png" class="input" style="font-size:13px;">
                                                        </div>
                                                        <div class="modal-actions">
                                                            <button type="button" class="btn btn-secondary" @click="open = false">Batal</button>
                                                            <button type="submit" class="btn btn-primary" style="background:#16a34a; border-color:#16a34a;">
                                                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                                                Ya, Disburse
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                            </template>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div style="padding:12px 20px; background:#f8fafc; border-top:1px solid var(--line); border-radius:0 0 16px 16px; display:flex; align-items:center; gap:8px; font-size:13px; color:var(--muted);">
            <span class="badge badge-blue" style="font-size:11px;">Payroll Operations</span>
            Batch diproses setelah payroll disetujui Finance dan rekening karyawan tervalidasi.
        </div>
    @endif
</div>
