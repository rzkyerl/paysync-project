@php
    $statusMap = [
        'active'   => ['badge-green', 'Aktif'],
        'probation'=> ['badge-amber', 'Probation'],
        'contract' => ['badge-blue',  'Kontrak'],
        'inactive' => ['badge-red',   'Tidak Aktif'],
    ];
    [$workCls, $workLbl] = $statusMap[$employee->work_status] ?? ['', ucfirst($employee->work_status)];

    $bankStatusMap = [
        'verified'   => ['badge-green', 'Verified'],
        'unverified' => ['badge-amber',  'Unverified'],
        'rejected'   => ['badge-red',    'Rejected'],
    ];
    [$bankCls, $bankLbl] = $bankStatusMap[$employee->bank_account_status] ?? ['', $employee->bank_account_status];
@endphp

{{-- ── Hero header ── --}}
<div class="section-card" style="margin-bottom:20px;">
    <div class="section-content">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
            <div style="display:flex; align-items:center; gap:16px;">
                {{-- Avatar --}}
                <div style="width:60px; height:60px; border-radius:16px; background:var(--brand-soft); display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:22px; font-weight:800; color:var(--brand);">
                    {{ strtoupper(substr($employee->name, 0, 1)) }}
                </div>
                <div>
                    <h2 style="margin:0; font-size:20px; font-weight:800; color:var(--navy);">{{ $employee->name }}</h2>
                    <div style="display:flex; align-items:center; gap:8px; margin-top:5px; flex-wrap:wrap;">
                        <span class="muted" style="font-size:13px;">{{ $employee->position ?? '-' }}</span>
                        @if($employee->department)
                            <span style="color:var(--line);">·</span>
                            <span class="muted" style="font-size:13px;">{{ $employee->department }}</span>
                        @endif
                        <span class="badge {{ $workCls }}">{{ $workLbl }}</span>
                    </div>
                </div>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                @if($canEdit ?? false)
                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit
                    </a>
                @endif
                <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                    Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

    {{-- ── Info Personal ── --}}
    <div class="section-card">
        <div class="section-header" style="padding-bottom:12px;">
            <div style="font-size:14px; font-weight:700; color:var(--navy); display:flex; align-items:center; gap:8px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Informasi Personal
            </div>
        </div>
        <div class="section-content" style="padding-top:0;">
            <div style="display:flex; flex-direction:column; gap:14px;">
                <div>
                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:3px;">NIP</div>
                    <div style="font-family:monospace; font-size:14px; font-weight:600; color:var(--navy);">{{ $employee->nip ?? '-' }}</div>
                </div>
                <div>
                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:3px;">Nama Lengkap</div>
                    <div style="font-size:14px; font-weight:600; color:var(--navy);">{{ $employee->name }}</div>
                </div>
                <div>
                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:3px;">Departemen</div>
                    <div style="font-size:14px; color:var(--navy);">{{ $employee->department ?? '-' }}</div>
                </div>
                <div>
                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:3px;">Jabatan</div>
                    <div style="font-size:14px; color:var(--navy);">{{ $employee->position ?? '-' }}</div>
                </div>
                <div>
                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:3px;">Status Kerja</div>
                    <span class="badge {{ $workCls }}">{{ $workLbl }}</span>
                </div>
                <div>
                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:3px;">Tanggal Bergabung</div>
                    <div style="font-size:14px; color:var(--navy);">{{ $employee->join_date?->translatedFormat('d F Y') ?? '-' }}</div>
                </div>
                <div style="padding-top:10px; border-top:1px solid var(--line);">
                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:3px;">Gaji Pokok</div>
                    <div style="font-size:18px; font-weight:800; color:var(--brand); font-family:var(--font-display);">
                        Rp {{ number_format((float)$employee->basic_salary, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Info Rekening ── --}}
    <div style="display:flex; flex-direction:column; gap:20px;">
        <div class="section-card">
            <div class="section-header" style="padding-bottom:12px;">
                <div style="font-size:14px; font-weight:700; color:var(--navy); display:flex; align-items:center; gap:8px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                    Informasi Rekening
                </div>
            </div>
            <div class="section-content" style="padding-top:0;">
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div>
                        <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:3px;">Nama Bank</div>
                        <div style="font-size:14px; font-weight:600; color:var(--navy);">{{ $employee->bank_name ?? '-' }}</div>
                    </div>
                    <div>
                        <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:3px;">Nomor Rekening</div>
                        <div style="font-family:monospace; font-size:14px; font-weight:600; color:var(--navy);">{{ $employee->bank_account_number ?? '-' }}</div>
                    </div>
                    <div>
                        <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:5px;">Status Rekening</div>
                        <span class="badge {{ $bankCls }}">{{ $bankLbl }}</span>
                    </div>
                    @if($canVerifyBank ?? false)
                    <div style="padding-top:10px; border-top:1px solid var(--line); display:flex; gap:8px; flex-wrap:wrap;">
                        @if($employee->bank_account_status === 'unverified')
                            <form method="POST" action="{{ route('employees.verify-bank', $employee) }}">
                                @csrf
                                <button class="btn btn-primary" type="submit" style="font-size:13px; background:#16a34a; border-color:#16a34a;">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                    Verifikasi
                                </button>
                            </form>
                        @endif
                        @if(in_array($employee->bank_account_status, ['unverified', 'verified'], true))
                            <form method="POST" action="{{ route('employees.reject-bank', $employee) }}">
                                @csrf
                                <button class="btn btn-danger" type="submit" style="font-size:13px;">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    Tolak
                                </button>
                            </form>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Metadata --}}
        <div class="section-card">
            <div class="section-content">
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <div style="display:flex; justify-content:space-between; font-size:13px;">
                        <span class="muted">Dibuat</span>
                        <span style="color:var(--navy);">{{ $employee->created_at?->format('d M Y, H:i') ?? '-' }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:13px;">
                        <span class="muted">Diperbarui</span>
                        <span style="color:var(--navy);">{{ $employee->updated_at?->format('d M Y, H:i') ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
