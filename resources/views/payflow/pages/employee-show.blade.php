<section class="card">
    <div class="section-title">
        <h2>{{ $employee->name }}</h2>
        <div style="display:flex; gap:8px;">
            @if ($canEdit ?? false)
                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary">Edit</a>
            @elseif (auth()->user()?->isSuperAdmin())
                <span class="badge badge-amber" title="Hanya tersedia untuk Tim HR/Finance">View Only</span>
            @endif
            <a href="{{ route('employees.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
    <div class="section-body">
        <dl style="display:grid; grid-template-columns: 200px 1fr; gap:12px 16px; align-items:start;">
            <dt class="muted">NIP</dt>
            <dd>{{ $employee->nip }}</dd>

            <dt class="muted">Nama</dt>
            <dd>{{ $employee->name }}</dd>

            <dt class="muted">Departemen</dt>
            <dd>{{ $employee->department }}</dd>

            <dt class="muted">Jabatan</dt>
            <dd>{{ $employee->position }}</dd>

            <dt class="muted">Status Kerja</dt>
            <dd>
                @php
                    $statusMap = ['active'=>['badge-green','Aktif'],'probation'=>['badge-amber','Probation'],'contract'=>['badge-blue','Kontrak'],'inactive'=>['','Tidak Aktif']];
                    [$cls, $lbl] = $statusMap[$employee->work_status] ?? ['', $employee->work_status];
                @endphp
                <span class="badge {{ $cls }}">{{ $lbl }}</span>
            </dd>

            <dt class="muted">Tanggal Bergabung</dt>
            <dd>{{ $employee->join_date?->translatedFormat('d F Y') ?? '-' }}</dd>

            <dt class="muted">Gaji Pokok</dt>
            <dd>Rp {{ number_format($employee->basic_salary, 0, ',', '.') }}</dd>

            <dt class="muted">Status Rekening</dt>
            <dd>
                @php
                    $bankStatusMap = ['verified'=>['badge-green','Verified'],'unverified'=>['badge-amber','Unverified'],'rejected'=>['badge-red','Rejected']];
                    [$bcls, $blbl] = $bankStatusMap[$employee->bank_account_status] ?? ['', $employee->bank_account_status];
                @endphp
                <span class="badge {{ $bcls }}">{{ $blbl }}</span>
            </dd>

            @if ($canVerifyBank ?? false)
                <dt class="muted">Aksi Rekening</dt>
                <dd style="display:flex; gap:8px;">
                    @if ($employee->bank_account_status === 'unverified')
                        <form method="POST" action="{{ route('employees.verify-bank', $employee) }}">@csrf<button class="btn btn-primary" type="submit">Verifikasi</button></form>
                    @endif
                    @if (in_array($employee->bank_account_status, ['unverified', 'verified'], true))
                        <form method="POST" action="{{ route('employees.reject-bank', $employee) }}">@csrf<button class="btn btn-danger" type="submit">Tolak</button></form>
                    @endif
                </dd>
            @endif

            <dt class="muted">Nama Bank</dt>
            <dd>{{ $employee->bank_name ?? '-' }}</dd>

            <dt class="muted">Nomor Rekening</dt>
            <dd>{{ $employee->bank_account_number ?? '-' }}</dd>

            <dt class="muted">Dibuat</dt>
            <dd>{{ $employee->created_at?->translatedFormat('d F Y, H:i') ?? '-' }}</dd>

            <dt class="muted">Diperbarui</dt>
            <dd>{{ $employee->updated_at?->translatedFormat('d F Y, H:i') ?? '-' }}</dd>
        </dl>
    </div>
</section>
