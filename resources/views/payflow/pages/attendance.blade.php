@php
    $attendancePayrolls = $attendancePayrolls ?? collect();
    $attendancePayroll  = $attendancePayroll  ?? null;
    $attendanceRecords  = $attendanceRecords  ?? collect();
    $isSuperAdmin       = $isSuperAdminViewing ?? false;

    $totalDaysPresent  = $attendanceRecords->sum('days_present');
    $totalOvertime     = $attendanceRecords->sum('overtime_hours');
    $totalLeave        = $attendanceRecords->sum('leave_days');
    $totalEmployees    = $attendanceRecords->count();
@endphp

{{-- ── Period selector ── --}}
@if($attendancePayrolls->isNotEmpty())
<form method="GET" action="{{ route('app', 'attendance') }}" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:20px;">
    <select name="payroll_id" class="input" style="max-width:220px;" onchange="this.form.submit()">
        <option value="">-- Pilih Periode --</option>
        @foreach($attendancePayrolls as $p)
            <option value="{{ $p->id }}" {{ $attendancePayroll?->id == $p->id ? 'selected' : '' }}>
                {{ $p->period_label }} ({{ ucfirst(str_replace('_',' ',$p->status)) }})
            </option>
        @endforeach
    </select>
    @if($attendancePayroll)
        <span class="badge badge-blue">{{ $attendancePayroll->period_label }}</span>
    @endif
    @if(!$isSuperAdmin && $attendancePayroll)
        <a href="{{ route('payroll.attendance.import', $attendancePayroll) }}"
           class="btn btn-secondary" style="margin-left:auto;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Import CSV Kehadiran
        </a>
        <a href="{{ route('payroll.attendance.template') }}" class="btn btn-secondary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download Template
        </a>
    @endif
</form>
@endif

{{-- ── KPI Strip ── --}}
@if($attendancePayroll)
<div class="grid grid-4" style="margin-bottom:20px;">
    <div class="kpi-modern kpi-blue">
        <div class="kpi-icon-wrap blue">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div class="kpi-label">Total Karyawan</div>
        <div class="kpi-value">{{ $totalEmployees }}</div>
        <div class="kpi-footer"><span class="badge badge-blue">Periode ini</span></div>
    </div>
    <div class="kpi-modern kpi-green">
        <div class="kpi-icon-wrap green">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div class="kpi-label">Total Hari Hadir</div>
        <div class="kpi-value">{{ number_format($totalDaysPresent, 0, ',', '.') }}</div>
        <div class="kpi-footer"><span class="badge badge-green">Semua karyawan</span></div>
    </div>
    <div class="kpi-modern kpi-amber">
        <div class="kpi-icon-wrap amber">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="kpi-label">Total Lembur</div>
        <div class="kpi-value">{{ number_format($totalOvertime, 1, ',', '.') }} <span style="font-size:16px; font-weight:400;">jam</span></div>
        <div class="kpi-footer"><span class="badge badge-amber">Overtime hours</span></div>
    </div>
    <div class="kpi-modern kpi-red">
        <div class="kpi-icon-wrap red">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/></svg>
        </div>
        <div class="kpi-label">Total Cuti</div>
        <div class="kpi-value">{{ number_format($totalLeave, 0, ',', '.') }} <span style="font-size:16px; font-weight:400;">hari</span></div>
        <div class="kpi-footer"><span class="badge badge-red">Leave days</span></div>
    </div>
</div>
@endif

{{-- ── Attendance Records Table ── --}}
<div class="section-card">
    <div class="section-header">
        <div>
            <div style="font-size:16px; font-weight:700; color:var(--navy);">
                Data Kehadiran
                @if($attendancePayroll) — {{ $attendancePayroll->period_label }} @endif
            </div>
            <div class="muted" style="font-size:13px; margin-top:2px;">
                {{ $attendanceRecords->count() }} record kehadiran tersedia
            </div>
        </div>
        @if($attendancePayroll)
        <a href="{{ route('payroll.show', $attendancePayroll) }}" class="btn btn-secondary" style="font-size:12px;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            Lihat Payroll
        </a>
        @endif
    </div>

    @if($attendancePayrolls->isEmpty())
        <div class="section-content" style="text-align:center; padding:56px 20px;">
            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" style="color:var(--muted); display:block; margin:0 auto 14px;"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <div style="font-size:17px; font-weight:700; color:var(--navy); margin-bottom:6px;">Belum ada data payroll</div>
            <p class="muted" style="margin:0; font-size:14px;">Buat payroll terlebih dahulu untuk mulai import kehadiran.</p>
        </div>

    @elseif(!$attendancePayroll)
        <div class="section-content" style="text-align:center; padding:40px 20px;">
            <p class="muted">Pilih periode di atas untuk melihat data kehadiran.</p>
        </div>

    @elseif($attendanceRecords->isEmpty())
        <div class="section-content" style="text-align:center; padding:48px 20px;">
            <svg width="44" height="44" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" style="color:var(--muted); display:block; margin:0 auto 12px;"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <div style="font-size:16px; font-weight:700; color:var(--navy); margin-bottom:6px;">Belum ada data kehadiran</div>
            <p class="muted" style="margin:0; font-size:13px;">Import CSV untuk periode {{ $attendancePayroll->period_label }}.</p>
            @if(!$isSuperAdmin)
            <div style="margin-top:16px;">
                <a href="{{ route('payroll.attendance.import', $attendancePayroll) }}" class="btn btn-primary">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Import CSV Kehadiran
                </a>
            </div>
            @endif
        </div>

    @else
        <div class="section-content" style="padding:0;">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>NIP</th>
                            <th style="text-align:center;">Hari Hadir</th>
                            <th style="text-align:center;">Lembur (jam)</th>
                            <th style="text-align:center;">Cuti (hari)</th>
                            <th>Status Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendanceRecords as $record)
                        @php
                            $hasIssue = $record->days_present === 0;
                        @endphp
                        <tr style="{{ $hasIssue ? 'background:#fffbeb;' : '' }}">
                            <td>
                                <div style="font-weight:600; color:var(--navy);">{{ $record->employee?->name ?? '-' }}</div>
                                <div class="muted" style="font-size:12px;">{{ $record->employee?->position ?? '' }}</div>
                            </td>
                            <td><span style="font-family:monospace; font-size:12px; color:var(--muted);">{{ $record->employee?->nip ?? '-' }}</span></td>
                            <td style="text-align:center; font-weight:600; color:var(--navy);">{{ $record->days_present }}</td>
                            <td style="text-align:center; color:{{ $record->overtime_hours > 0 ? 'var(--amber)' : 'var(--muted)' }}; font-weight:600;">
                                {{ number_format($record->overtime_hours, 1) }}
                            </td>
                            <td style="text-align:center; color:{{ $record->leave_days > 0 ? '#dc2626' : 'var(--muted)' }}; font-weight:600;">
                                {{ $record->leave_days }}
                            </td>
                            <td>
                                @if($hasIssue)
                                    <span class="badge badge-red">⚠ No Data</span>
                                @elseif($record->days_present < 15)
                                    <span class="badge badge-amber">Warning</span>
                                @else
                                    <span class="badge badge-green">✓ Valid</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Import guide --}}
        @if(!$isSuperAdmin && in_array($attendancePayroll->status, ['draft', 'needs_review']))
        <div style="padding:14px 20px; border-top:1px solid var(--line); background:#f8fafc; border-radius:0 0 16px 16px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <div style="display:flex; gap:8px; align-items:center;">
                <span class="badge badge-blue" style="font-size:11px;">Tips</span>
                <span class="muted" style="font-size:13px;">Upload ulang CSV untuk memperbarui data kehadiran sebelum kalkulasi payroll.</span>
            </div>
            <a href="{{ route('payroll.attendance.import', $attendancePayroll) }}" class="btn btn-secondary" style="font-size:12px; margin-left:auto;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Re-import CSV
            </a>
        </div>
        @endif
    @endif
</div>
