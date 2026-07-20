@php
    $isSuperAdmin = $isSuperAdminViewing ?? false;
    $queue = $approvalQueue ?? collect();
    $totalNet = $queue->sum('net_total');
    $totalEmp = $queue->sum('employee_count');
    $totalPeriods = $queue->count();
@endphp

{{-- ═══ Summary KPI strip ═══ --}}
<div class="grid grid-4" style="margin-bottom:20px;">
    <div class="kpi-modern kpi-amber">
        <div class="kpi-icon-wrap amber">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <div class="kpi-label" style="padding-right:52px;">Menunggu Approval</div>
        <div class="kpi-value">{{ $totalPeriods }}</div>
        <div class="kpi-footer"><span class="badge badge-amber">{{ $totalPeriods > 0 ? 'Perlu ditindaklanjuti' : 'Semua beres' }}</span></div>
    </div>
    <div class="kpi-modern kpi-blue">
        <div class="kpi-icon-wrap blue">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="kpi-label" style="padding-right:52px;">Total Karyawan</div>
        <div class="kpi-value">{{ number_format($totalEmp, 0, ',', '.') }}</div>
        <div class="kpi-footer"><span class="badge badge-blue">Semua periode</span></div>
    </div>
    <div class="kpi-modern kpi-green">
        <div class="kpi-icon-wrap green">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="kpi-label" style="padding-right:52px;">Total Net Pay</div>
        <div class="kpi-value" style="font-size:22px;">Rp {{ number_format($totalNet / 1_000_000, 2, ',', '.') }} Jt</div>
        <div class="kpi-footer"><span class="badge badge-green">Semua periode</span></div>
    </div>
    <div class="kpi-modern kpi-red">
        <div class="kpi-icon-wrap red">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div class="kpi-label" style="padding-right:52px;">Total Anomali</div>
        <div class="kpi-value">{{ $queue->sum('anomaly_count') }}</div>
        <div class="kpi-footer"><span class="badge {{ $queue->sum('anomaly_count') > 0 ? 'badge-red' : 'badge-green' }}">{{ $queue->sum('anomaly_count') > 0 ? 'Perlu perhatian' : 'Bersih' }}</span></div>
    </div>
</div>

{{-- ═══ Queue List ═══ --}}
@if($queue->isEmpty())
    <div class="section-card">
        <div class="section-content" style="text-align:center; padding:48px 20px;">
            <svg width="52" height="52" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" style="color:var(--green); margin-bottom:14px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <div style="font-size:18px; font-weight:700; color:var(--navy); margin-bottom:6px;">Semua payroll sudah diproses</div>
            <p class="muted" style="margin:0; font-size:14px;">Tidak ada payroll yang menunggu persetujuan Finance saat ini.</p>
        </div>
    </div>
@else
    <div style="display:grid; gap:16px;">
        @foreach($queue as $payroll)
        <div class="section-card" x-data="{ rejectOpen: false, rejectNote: '', approveOpen: false }">

            {{-- Card Header --}}
            <div class="section-header">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:42px; height:42px; border-radius:12px; background:var(--brand-soft); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="color:var(--brand);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    </div>
                    <div>
                        <div style="font-size:16px; font-weight:700; color:var(--navy);">Payroll {{ $payroll->period_label }}</div>
                        <div class="muted" style="font-size:12px;">
                            Disubmit oleh <strong>{{ $payroll->submitter?->name ?? '-' }}</strong>
                            @if($payroll->updated_at) · {{ $payroll->updated_at->diffForHumans() }} @endif
                        </div>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    @if($payroll->anomaly_count > 0)
                        <span class="badge badge-red">{{ $payroll->anomaly_count }} anomali</span>
                    @else
                        <span class="badge badge-green">✓ Bersih</span>
                    @endif
                    <span class="badge badge-amber">Menunggu Approval</span>
                </div>
            </div>

            {{-- Financial Summary --}}
            <div class="section-content">
                <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1px; background:var(--line); border:1px solid var(--line); border-radius:12px; overflow:hidden; margin-bottom:16px;">
                    @php
                        $cols = [
                            ['label' => 'Karyawan', 'value' => number_format($payroll->employee_count, 0, ',', '.') . ' orang'],
                            ['label' => 'Gross Pay', 'value' => 'Rp ' . number_format((float)$payroll->gross_total / 1_000_000, 2, ',', '.') . ' Jt'],
                            ['label' => 'Deduction', 'value' => 'Rp ' . number_format((float)$payroll->deduction_total / 1_000_000, 2, ',', '.') . ' Jt'],
                            ['label' => 'Net Pay', 'value' => 'Rp ' . number_format((float)$payroll->net_total / 1_000_000, 2, ',', '.') . ' Jt'],
                        ];
                    @endphp
                    @foreach($cols as $i => $col)
                        <div style="padding:14px 16px; background:#fff; {{ $i === 3 ? 'background:var(--brand-soft);' : '' }}">
                            <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:4px;">{{ $col['label'] }}</div>
                            <div style="font-size:16px; font-weight:800; color:{{ $i === 3 ? 'var(--brand)' : 'var(--navy)' }}; font-family:var(--font-display);">{{ $col['value'] }}</div>
                        </div>
                    @endforeach
                </div>

                {{-- Action Buttons --}}
                @if($isSuperAdmin)
                    <div style="padding:12px 14px; background:#fffbeb; border:1px solid #fde68a; border-radius:10px; font-size:13px; color:#92400e;">
                        Anda dalam Mode Pantau — aksi approval hanya tersedia untuk Finance Manager.
                    </div>
                @else
                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                        {{-- Approve trigger --}}
                        <button type="button" class="btn btn-primary" @click="approveOpen = true">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Approve Payroll
                        </button>

                        {{-- Reject trigger --}}
                        <button type="button" class="btn btn-danger" @click="rejectOpen = true">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Reject
                        </button>

                        {{-- Review detail --}}
                        <a href="{{ route('payroll.show', $payroll) }}" class="btn btn-secondary">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            Lihat Detail
                        </a>
                    </div>

                    {{-- Approve Modal --}}
                    <template x-teleport="body">
                    <div x-show="approveOpen" x-cloak
                        class="modal-overlay"
                        @click.self="approveOpen = false"
                        @keydown.window.escape="approveOpen = false"
                        role="dialog" aria-modal="true">
                        <div class="modal-dialog" x-transition>
                            {{-- Header --}}
                            <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px;">
                                <div style="display:flex; align-items:center; gap:14px;">
                                    <div class="modal-icon modal-icon--info" style="margin-bottom:0; flex-shrink:0; background:#dcfce7; color:#16a34a;">
                                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                    </div>
                                    <div>
                                        <div class="modal-title" style="margin:0;">Approve Payroll</div>
                                        <div class="muted" style="font-size:13px; margin-top:2px;">{{ $payroll->period_label }} · {{ $payroll->employee_count }} karyawan</div>
                                    </div>
                                </div>
                                <button type="button" @click="approveOpen = false"
                                    style="width:32px;height:32px;border-radius:8px;border:1px solid var(--line);background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted);flex-shrink:0;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>

                            {{-- Summary breakdown --}}
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1px; background:var(--line); border:1px solid var(--line); border-radius:12px; overflow:hidden; margin-bottom:18px;">
                                <div style="padding:14px 16px; background:#f8fafc;">
                                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:4px;">Gross Pay</div>
                                    <div style="font-size:15px; font-weight:800; color:var(--navy); font-family:var(--font-display);">Rp {{ number_format((float)$payroll->gross_total / 1_000_000, 2, ',', '.') }} Jt</div>
                                </div>
                                <div style="padding:14px 16px; background:#f8fafc;">
                                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:4px;">Deduction</div>
                                    <div style="font-size:15px; font-weight:800; color:var(--navy); font-family:var(--font-display);">Rp {{ number_format((float)$payroll->deduction_total / 1_000_000, 2, ',', '.') }} Jt</div>
                                </div>
                                <div style="padding:14px 16px; background:var(--brand-soft); grid-column:span 2;">
                                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:4px;">Net Pay (akan disetujui)</div>
                                    <div style="font-size:22px; font-weight:800; color:var(--brand); font-family:var(--font-display);">Rp {{ number_format((float)$payroll->net_total / 1_000_000, 2, ',', '.') }} Jt</div>
                                </div>
                            </div>

                            {{-- Warning if anomalies --}}
                            @if($payroll->anomaly_count > 0)
                            <div style="padding:12px 14px; background:#fef3c7; border:1px solid #fde68a; border-radius:10px; font-size:13px; color:#92400e; margin-bottom:18px; line-height:1.6; display:flex; gap:10px; align-items:flex-start;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0; margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                <span>Terdapat <strong>{{ $payroll->anomaly_count }} anomali</strong> pada payroll ini. Pastikan sudah ditinjau sebelum menyetujui.</span>
                            </div>
                            @else
                            <div style="padding:12px 14px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; font-size:13px; color:#166534; margin-bottom:18px; line-height:1.6; display:flex; gap:10px; align-items:flex-start;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0; margin-top:1px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                <span>Payroll ini bersih dari anomali dan siap untuk disetujui.</span>
                            </div>
                            @endif

                            {{-- Confirm message --}}
                            <p style="font-size:14px; color:var(--muted); margin:0 0 20px; line-height:1.65;">
                                Dengan menyetujui, payroll periode <strong style="color:var(--navy);">{{ $payroll->period_label }}</strong> akan diteruskan ke proses disbursement untuk <strong style="color:var(--navy);">{{ $payroll->employee_count }} karyawan</strong>. Tindakan ini tidak dapat dibatalkan.
                            </p>

                            <div class="modal-actions">
                                <button type="button" class="btn btn-secondary" @click="approveOpen = false">Batal</button>
                                <form method="POST" action="{{ route('payroll.approve', $payroll) }}" style="margin:0;">
                                    @csrf
                                    <button type="submit" class="btn btn-primary" style="background:#16a34a; border-color:#16a34a;">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                        Ya, Approve Payroll
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    </template>

                    {{-- Reject Modal (inline, per card) --}}
                    <template x-teleport="body">
                    <div x-show="rejectOpen" x-cloak
                        class="modal-overlay"
                        @click.self="rejectOpen = false"
                        @keydown.window.escape="rejectOpen = false"
                        role="dialog" aria-modal="true">
                        <div class="modal-dialog" x-transition>
                            {{-- Header --}}
                            <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px;">
                                <div style="display:flex; align-items:center; gap:14px;">
                                    <div class="modal-icon modal-icon--danger" style="margin-bottom:0; flex-shrink:0;">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </div>
                                    <div>
                                        <div class="modal-title" style="margin:0;">Reject Payroll</div>
                                        <div class="muted" style="font-size:13px; margin-top:2px;">{{ $payroll->period_label }} · Rp {{ number_format((float)$payroll->net_total / 1_000_000, 2, ',', '.') }} Jt</div>
                                    </div>
                                </div>
                                <button type="button" @click="rejectOpen = false"
                                    style="width:32px;height:32px;border-radius:8px;border:1px solid var(--line);background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted);flex-shrink:0;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>

                            {{-- Info box --}}
                            <div style="padding:12px 14px; background:#fef3c7; border:1px solid #fde68a; border-radius:10px; font-size:13px; color:#92400e; margin-bottom:18px; line-height:1.6;">
                                Payroll akan dikembalikan ke HR dengan alasan penolakan. HR perlu memperbaiki data sebelum mengajukan ulang.
                            </div>

                            <form method="POST" action="{{ route('payroll.reject', $payroll) }}">
                                @csrf
                                <div class="field" style="margin-bottom:20px;">
                                    <label style="font-size:13px; font-weight:700; color:#334155; display:block; margin-bottom:6px;">
                                        Alasan Penolakan <span style="color:var(--red)">*</span>
                                    </label>
                                    <textarea name="rejection_note" x-model="rejectNote" rows="4"
                                        class="input" style="resize:vertical;"
                                        placeholder="Jelaskan alasan penolakan agar HR dapat memperbaiki data..."></textarea>
                                    <div style="font-size:12px; color:var(--muted); margin-top:4px;" x-text="rejectNote.trim().length + ' karakter (min. 5)'"></div>
                                </div>
                                <div class="modal-actions">
                                    <button type="button" class="btn btn-secondary" @click="rejectOpen = false">Batal</button>
                                    <button type="submit" class="btn btn-danger"
                                        :disabled="rejectNote.trim().length < 5"
                                        :class="rejectNote.trim().length < 5 ? 'btn-disabled' : ''">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        Konfirmasi Reject
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    </template>
                @endif
            </div>
        </div>
        @endforeach
    </div>
@endif
