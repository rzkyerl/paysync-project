@php
    $publishedPayrolls = $publishedPayrolls ?? collect();
    $activePayroll     = $activePayroll ?? null;
    $payslipItems      = $payslipItems ?? collect();
    $companyName       = $companyName ?? 'Perusahaan';
@endphp

{{-- ── Period selector ── --}}
<form method="GET" action="{{ route('app', 'payslips') }}" style="display:flex; align-items:center; gap:10px; margin-bottom:20px; flex-wrap:wrap;">
    <select name="period" class="input" style="max-width:200px;" onchange="this.form.submit()">
        <option value="">-- Pilih Periode --</option>
        @foreach($publishedPayrolls as $p)
            <option value="{{ $p->period }}" {{ $activePayroll?->period === $p->period ? 'selected' : '' }}>
                {{ $p->period_label }}
                ({{ ucfirst(str_replace('_', ' ', $p->status)) }})
            </option>
        @endforeach
    </select>
    @if($activePayroll)
        <span class="badge {{ $activePayroll->status === 'disbursed' ? 'badge-green' : 'badge-blue' }}">
            {{ $activePayroll->status === 'disbursed' ? '✓ Transfer Selesai' : 'Disetujui' }}
        </span>
        <a href="{{ route('payroll.show', $activePayroll) }}" class="btn btn-secondary" style="margin-left:auto;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            Lihat Detail Payroll
        </a>
    @endif
</form>

@if($publishedPayrolls->isEmpty())
    {{-- Empty state --}}
    <div class="section-card">
        <div class="section-content" style="text-align:center; padding:56px 20px;">
            <svg width="52" height="52" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" style="color:var(--muted); margin-bottom:14px; display:block; margin:0 auto 14px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            <div style="font-size:18px; font-weight:700; color:var(--navy); margin-bottom:6px;">Belum ada slip gaji yang diterbitkan</div>
            <p class="muted" style="margin:0; font-size:14px;">Slip gaji tersedia setelah payroll disetujui oleh Finance.</p>
        </div>
    </div>

@elseif(!$activePayroll)
    {{-- No period selected --}}
    <div class="section-card">
        <div class="section-content" style="text-align:center; padding:40px 20px;">
            <p class="muted">Pilih periode di atas untuk melihat daftar slip gaji.</p>
        </div>
    </div>

@else
    {{-- ── KPI strip ── --}}
    <div class="grid grid-4" style="margin-bottom:20px;">
        <div class="kpi-modern kpi-blue">
            <div class="kpi-icon-wrap blue">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <div class="kpi-label">Total Karyawan</div>
            <div class="kpi-value">{{ $payslipItems->count() }}</div>
            <div class="kpi-footer"><span class="badge badge-blue">Periode ini</span></div>
        </div>
        <div class="kpi-modern kpi-amber">
            <div class="kpi-icon-wrap amber">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div class="kpi-label">Total Gross</div>
            <div class="kpi-value" style="font-size:20px;">Rp {{ number_format((float)$activePayroll->gross_total / 1_000_000, 2, ',', '.') }} Jt</div>
            <div class="kpi-footer"><span class="badge badge-amber">{{ $activePayroll->period_label }}</span></div>
        </div>
        <div class="kpi-modern kpi-red">
            <div class="kpi-icon-wrap red">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div class="kpi-label">Total Deduction</div>
            <div class="kpi-value" style="font-size:20px;">Rp {{ number_format((float)$activePayroll->deduction_total / 1_000_000, 2, ',', '.') }} Jt</div>
            <div class="kpi-footer"><span class="badge badge-red">Semua potongan</span></div>
        </div>
        <div class="kpi-modern kpi-green">
            <div class="kpi-icon-wrap green">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="kpi-label">Total Net Pay</div>
            <div class="kpi-value" style="font-size:20px;">Rp {{ number_format((float)$activePayroll->net_total / 1_000_000, 2, ',', '.') }} Jt</div>
            <div class="kpi-footer"><span class="badge badge-green">Take-home total</span></div>
        </div>
    </div>

    {{-- ── Employee slip list ── --}}
    <div class="section-card">
        <div class="section-header">
            <div>
                <div style="font-size:16px; font-weight:700; color:var(--navy);">Daftar Slip Gaji — {{ $activePayroll->period_label }}</div>
                <div class="muted" style="font-size:13px; margin-top:2px;">{{ $payslipItems->count() }} karyawan · klik untuk lihat slip individual</div>
            </div>
        </div>
        <div class="section-content" style="padding:0;">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>NIP</th>
                            <th>Jabatan</th>
                            <th style="text-align:right;">Gross Pay</th>
                            <th style="text-align:right;">Potongan</th>
                            <th style="text-align:right;">Net Pay</th>
                            <th>Status Transfer</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payslipItems as $item)
                        <tr>
                            <td>
                                <div style="font-weight:600; color:var(--navy);">{{ $item->employee?->name ?? '-' }}</div>
                            </td>
                            <td><span style="font-family:monospace; font-size:13px; color:var(--muted);">{{ $item->employee?->nip ?? '-' }}</span></td>
                            <td><span class="muted" style="font-size:13px;">{{ $item->employee?->position ?? '-' }}</span></td>
                            <td style="text-align:right;">Rp {{ number_format((float)$item->gross_pay, 0, ',', '.') }}</td>
                            <td style="text-align:right; color:var(--red);">Rp {{ number_format((float)$item->total_deduction, 0, ',', '.') }}</td>
                            <td style="text-align:right; font-weight:700; color:var(--navy);">Rp {{ number_format((float)$item->net_pay, 0, ',', '.') }}</td>
                            <td>
                                @if($item->status === 'transferred')
                                    <span class="badge badge-green">✓ Transferred</span>
                                @elseif($activePayroll->status === 'approved')
                                    <span class="badge badge-blue">Approved</span>
                                @else
                                    <span class="badge badge-amber">Pending</span>
                                @endif
                            </td>
                            <td>
                                @if($item->employee)
                                <a href="{{ route('payroll.payslip', [$activePayroll, $item->employee]) }}"
                                   class="btn btn-secondary" style="padding:5px 12px; font-size:12px;">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    Lihat Slip
                                </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:40px; color:var(--muted);">
                                Belum ada item payroll untuk periode ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
