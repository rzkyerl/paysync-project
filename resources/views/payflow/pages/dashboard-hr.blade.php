<div x-data="{ loading: true }" x-init="$nextTick(() => loading = false)">

    {{-- Skeleton (shown while loading) --}}
    <div x-show="loading">
        @include('payflow.partials.skeleton-kpi')
        <div class="grid grid-2" style="margin-top:16px;">
            <div class="card skeleton" style="height:280px;"></div>
            <div class="card skeleton" style="height:280px;"></div>
        </div>
        <div class="card skeleton" style="height:200px; margin-top:16px;"></div>
    </div>

    {{-- Real Content (hidden until loading is false) --}}
    <div x-show="!loading" x-cloak>

@if($isDemoUser ?? false)
    <div class="card" style="margin-bottom:16px; padding:14px 16px; border-left:4px solid var(--amber);">
        <span class="badge badge-amber">Mode Demo</span>
        <span class="muted" style="margin-left:8px;">Data ini adalah contoh milik {{ $companyName }}.</span>
    </div>
@endif

@if($isEmpty ?? false)
    <div class="grid grid-4">
        @foreach(['Karyawan Aktif', 'Kehadiran Periode Ini', 'Status Payroll', 'Estimasi Take-home Pay'] as $label)
            <div class="card kpi"><span class="muted">{{ $label }}</span><div class="value">—</div><span class="badge">Belum ada data</span></div>
        @endforeach
    </div>
    <x-empty-state
        style="margin-top:16px;"
        icon="users"
        title="Belum ada karyawan"
        description="Tambahkan karyawan pertama untuk mulai menyiapkan payroll perusahaan."
        :cta-label="$isSuperAdminViewing ? null : 'Tambah Karyawan'"
        :cta-url="$isSuperAdminViewing ? null : route('employees.create')"
    />
@else

@if ($error ?? null)
    <div class="card" style="border-left: 4px solid var(--red, #ef4444); padding: 16px; margin-bottom: 16px;">
        <strong style="color: var(--red, #ef4444);">Gagal memuat data</strong>
        <p class="muted" style="margin: 4px 0 0;">{{ $error }}</p>
    </div>
@endif

{{-- KPI Cards --}}
<div class="grid grid-4">
    @forelse ($kpis as $kpi)
        <div class="card kpi">
            <span class="muted">{{ $kpi['label'] }}</span>
            <div class="value">{{ $kpi['value'] }}</div>
            <span class="badge {{ $kpi['badge'] }}">{{ $kpi['sub'] }}</span>
        </div>
    @empty
        <div class="card kpi"><span class="muted">Karyawan Aktif</span><div class="value">—</div></div>
        <div class="card kpi"><span class="muted">Kehadiran Periode Ini</span><div class="value">—</div></div>
        <div class="card kpi"><span class="muted">Status Payroll</span><div class="value">—</div></div>
        <div class="card kpi"><span class="muted">Estimasi Take-home Pay</span><div class="value">—</div></div>
    @endforelse
</div>

<div class="grid grid-2" style="margin-top:16px;">

    {{-- Payroll Timeline --}}
    <section class="card">
        <div class="section-title">
            <h2>Payroll Timeline</h2>
            <span class="badge badge-amber">{{ $activeStageLabel ?? 'Draft' }}</span>
        </div>
        <div class="section-body timeline">
            @forelse ($payrollTimeline as $t)
                <div class="timeline-row">
                    <span class="dot {{ $t['dotClass'] }}">{{ $t['step'] }}</span>
                    <strong>{{ $t['label'] }}</strong>
                    <span class="badge {{ $t['badgeClass'] }}">{{ $t['statusText'] }}</span>
                </div>
            @empty
                <p class="muted">Tidak ada data payroll aktif.</p>
            @endforelse
        </div>
    </section>

    {{-- Action Center --}}
    <section class="card">
        <div class="section-title">
            <h2>Action Center</h2>
            @if (($actionItemCount ?? 0) > 0)
                <span class="badge badge-red">{{ $actionItemCount }} item</span>
            @else
                <span class="badge badge-green">Semua beres</span>
            @endif
        </div>
        <div class="section-body grid">
            @forelse ($actionItems as $item)
                @php
                    $cardBorder = match ($item['level']) {
                        'danger'  => 'border-left: 3px solid var(--red, #ef4444);',
                        'warning' => 'border-left: 3px solid var(--amber, #f59e0b);',
                        default   => 'border-left: 3px solid var(--brand, #3b82f6);',
                    };
                @endphp
                <div class="card" style="padding:12px; box-shadow:none; {{ $cardBorder }}">
                    <strong>{{ $item['text'] }}</strong>
                    <p class="muted" style="margin:4px 0 0;">Tinjau detail sebelum payroll dikirim.</p>
                </div>
            @empty
                <div class="card" style="padding:12px; box-shadow:none; text-align:center;">
                    <p class="muted">Tidak ada item yang perlu ditindaklanjuti.</p>
                </div>
            @endforelse
        </div>
    </section>

</div>

{{-- Recent Employee Changes --}}
<section class="card" style="margin-top:16px;">
    <div class="section-title">
        <h2>Perubahan Karyawan Terbaru</h2>
        <a class="btn btn-secondary" href="{{ route('employees.index') }}">Lihat Semua</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>NIP</th>
                    <th>Nama</th>
                    <th>Departemen</th>
                    <th>Jabatan</th>
                    <th>Status Kerja</th>
                    <th>Bergabung</th>
                    <th>Status Rekening</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentEmployees as $employee)
                    @php
                        $workStatusBadge = match ($employee->work_status) {
                            'active'    => 'badge-green',
                            'probation' => 'badge-amber',
                            'contract'  => 'badge-blue',
                            'inactive'  => 'badge-red',
                            default     => '',
                        };
                        $workStatusLabel = match ($employee->work_status) {
                            'active'    => 'Aktif',
                            'probation' => 'Probation',
                            'contract'  => 'Kontrak',
                            'inactive'  => 'Nonaktif',
                            default     => $employee->work_status,
                        };
                        $bankBadge = match ($employee->bank_account_status) {
                            'verified'   => 'badge-green',
                            'unverified' => 'badge-amber',
                            'rejected'   => 'badge-red',
                            default      => '',
                        };
                        $bankLabel = match ($employee->bank_account_status) {
                            'verified'   => 'Terverifikasi',
                            'unverified' => 'Belum Verifikasi',
                            'rejected'   => 'Ditolak',
                            default      => $employee->bank_account_status,
                        };
                    @endphp
                    <tr>
                        <td>{{ $employee->nip }}</td>
                        <td>
                            <strong>{{ $employee->name }}</strong>
                            <div class="muted" style="font-size:11px;">{{ $employee->position }}</div>
                        </td>
                        <td>{{ $employee->department }}</td>
                        <td>{{ $employee->position }}</td>
                        <td><span class="badge {{ $workStatusBadge }}">{{ $workStatusLabel }}</span></td>
                        <td>{{ $employee->join_date?->format('d M Y') ?? '-' }}</td>
                        <td><span class="badge {{ $bankBadge }}">{{ $bankLabel }}</span></td>
                        <td>
                            <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-secondary">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center; padding:24px;" class="muted">
                            Belum ada data karyawan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@endif

    </div>{{-- end x-show="!loading" --}}
</div>{{-- end x-data --}}
