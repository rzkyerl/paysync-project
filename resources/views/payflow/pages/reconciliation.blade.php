@php
    $reconPayrolls    = $reconPayrolls    ?? collect();
    $reconTotalNet    = $reconTotalNet    ?? 0;
    $reconTransferred = $reconTransferred ?? 0;
    $reconDifference  = $reconDifference  ?? 0;
    $isSuperAdmin     = $isSuperAdminViewing ?? false;

    function fmtRecon(float $v): string {
        if ($v >= 1_000_000_000) return 'Rp ' . number_format($v / 1_000_000_000, 2, ',', '.') . ' M';
        if ($v >= 1_000_000)     return 'Rp ' . number_format($v / 1_000_000, 2, ',', '.') . ' Jt';
        return 'Rp ' . number_format($v, 0, ',', '.');
    }

    $isBalanced = abs((float)$reconDifference) < 1;
@endphp

{{-- ═══ KPI Strip ═══ --}}
<div class="grid grid-4" style="margin-bottom:20px;">
    <div class="kpi-modern kpi-blue">
        <div class="kpi-icon-wrap blue">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="kpi-label">Total Net Payroll</div>
        <div class="kpi-value" style="font-size:20px;">{{ fmtRecon((float)$reconTotalNet) }}</div>
        <div class="kpi-footer"><span class="badge badge-blue">{{ $reconPayrolls->count() }} periode</span></div>
    </div>
    <div class="kpi-modern kpi-green">
        <div class="kpi-icon-wrap green">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="kpi-label">Total Transferred</div>
        <div class="kpi-value" style="font-size:20px;">{{ fmtRecon((float)$reconTransferred) }}</div>
        <div class="kpi-footer"><span class="badge badge-green">Berhasil ditransfer</span></div>
    </div>
    <div class="kpi-modern {{ $isBalanced ? 'kpi-green' : 'kpi-red' }}">
        <div class="kpi-icon-wrap {{ $isBalanced ? 'green' : 'red' }}">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </div>
        <div class="kpi-label">Selisih</div>
        <div class="kpi-value" style="font-size:20px;">{{ fmtRecon(abs((float)$reconDifference)) }}</div>
        <div class="kpi-footer">
            <span class="badge {{ $isBalanced ? 'badge-green' : 'badge-red' }}">
                {{ $isBalanced ? '✓ Balance' : 'Ada selisih' }}
            </span>
        </div>
    </div>
    <div class="kpi-modern kpi-amber">
        <div class="kpi-icon-wrap amber">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div class="kpi-label">Pending Transfer</div>
        <div class="kpi-value" style="font-size:20px;">{{ fmtRecon(max(0, (float)$reconTotalNet - (float)$reconTransferred)) }}</div>
        <div class="kpi-footer">
            <span class="badge {{ $isBalanced ? 'badge-green' : 'badge-amber' }}">
                {{ $isBalanced ? 'Selesai' : 'Belum tersalurkan' }}
            </span>
        </div>
    </div>
</div>

{{-- ═══ Per-Payroll Reconciliation ═══ --}}
@if($reconPayrolls->isEmpty())
    <div class="section-card">
        <div class="section-content" style="text-align:center; padding:56px 20px;">
            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" style="color:var(--muted); display:block; margin:0 auto 14px;"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            <div style="font-size:17px; font-weight:700; color:var(--navy); margin-bottom:6px;">Belum ada data rekonsiliasi</div>
            <p class="muted" style="margin:0; font-size:14px;">Data tersedia setelah payroll disetujui dan disbursement dijalankan.</p>
        </div>
    </div>
@else
    <div style="display:grid; gap:16px;">
        @foreach($reconPayrolls as $payroll)
        @php
            $items           = $payroll->payrollItems ?? collect();
            $payrollNet      = (float)$payroll->net_total;
            $transferredSum  = $items->where('status', 'transferred')->sum('net_pay');
            $diff            = $payrollNet - $transferredSum;
            $matched         = $items->where('status', 'transferred')->count();
            $unmatched       = $items->where('status', '!=', 'transferred')->count();
            $balanced        = abs($diff) < 1;
        @endphp
        <div class="section-card">
            <div class="section-header">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:42px; height:42px; border-radius:12px; background:{{ $balanced ? '#f0fdf4' : '#fef2f2' }}; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        @if($balanced)
                            <svg width="20" height="20" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        @else
                            <svg width="20" height="20" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        @endif
                    </div>
                    <div>
                        <div style="font-size:16px; font-weight:700; color:var(--navy);">Rekonsiliasi — {{ $payroll->period_label }}</div>
                        <div class="muted" style="font-size:12px; margin-top:2px;">
                            PAY-{{ str_pad($payroll->id, 4, '0', STR_PAD_LEFT) }}
                            · {{ $matched }} matched · {{ $unmatched }} belum transfer
                            @if($payroll->approver) · Disetujui oleh {{ $payroll->approver->name }} @endif
                        </div>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span class="badge {{ $balanced ? 'badge-green' : 'badge-red' }}">
                        {{ $balanced ? '✓ Balance' : 'Mismatch' }}
                    </span>
                    <a href="{{ route('payroll.reconcile', $payroll) }}" class="btn btn-secondary" style="padding:5px 12px; font-size:12px;">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        Detail
                    </a>
                </div>
            </div>

            <div class="section-content" style="padding-top:0;">
                {{-- Summary row --}}
                <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1px; background:var(--line); border:1px solid var(--line); border-radius:12px; overflow:hidden; margin-bottom:14px;">
                    <div style="padding:12px 16px; background:#f8fafc;">
                        <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:3px;">Net Payroll</div>
                        <div style="font-size:15px; font-weight:800; color:var(--navy);">{{ fmtRecon($payrollNet) }}</div>
                    </div>
                    <div style="padding:12px 16px; background:#f8fafc;">
                        <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:3px;">Transferred</div>
                        <div style="font-size:15px; font-weight:800; color:#16a34a;">{{ fmtRecon($transferredSum) }}</div>
                    </div>
                    <div style="padding:12px 16px; background:{{ $balanced ? '#f0fdf4' : '#fef2f2' }};">
                        <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:3px;">Selisih</div>
                        <div style="font-size:15px; font-weight:800; color:{{ $balanced ? '#16a34a' : '#dc2626' }};">{{ fmtRecon(abs($diff)) }}</div>
                    </div>
                </div>

                {{-- Items table --}}
                @if($items->isNotEmpty())
                <div class="table-wrap" style="border-radius:10px; border:1px solid var(--line); overflow:hidden;">
                    <table>
                        <thead>
                            <tr>
                                <th>Karyawan</th>
                                <th style="text-align:right;">Net Pay</th>
                                <th style="text-align:right;">Transferred</th>
                                <th style="text-align:right;">Selisih</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            @php
                                $itemNet   = (float)$item->net_pay;
                                $itemXfer  = $item->status === 'transferred' ? $itemNet : 0;
                                $itemDiff  = $itemNet - $itemXfer;
                                $itemOk    = $item->status === 'transferred';
                            @endphp
                            <tr style="{{ !$itemOk ? 'background:#fffbeb;' : '' }}">
                                <td>
                                    <div style="font-weight:600; color:var(--navy);">{{ $item->employee?->name ?? '-' }}</div>
                                    <div class="muted" style="font-size:12px;">{{ $item->employee?->nip ?? '' }}</div>
                                </td>
                                <td style="text-align:right; font-weight:600;">Rp {{ number_format($itemNet, 0, ',', '.') }}</td>
                                <td style="text-align:right; color:{{ $itemOk ? '#16a34a' : 'var(--muted)' }}; font-weight:600;">
                                    {{ $itemOk ? 'Rp ' . number_format($itemXfer, 0, ',', '.') : '—' }}
                                </td>
                                <td style="text-align:right; color:{{ $itemOk ? 'var(--muted)' : '#dc2626' }}; font-weight:600;">
                                    {{ $itemOk ? 'Rp 0' : 'Rp ' . number_format($itemDiff, 0, ',', '.') }}
                                </td>
                                <td>
                                    @if($itemOk)
                                        <span class="badge badge-green">✓ Matched</span>
                                    @elseif($payroll->status === 'approved')
                                        <span class="badge badge-blue">Pending</span>
                                    @else
                                        <span class="badge badge-amber">Unreconciled</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
@endif
