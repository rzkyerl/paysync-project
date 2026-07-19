{{--
    payroll.blade.php
    Variables: $payrolls (LengthAwarePaginator), $statusFilter, $periodFilter,
               $allowedStatuses, $sortBy, $sortDir, $perPage
--}}
@if($isEmpty ?? false)
    <x-empty-state
        icon="payroll"
        title="Belum ada payroll"
        description="Tambahkan karyawan terlebih dahulu, lengkapi komponen gaji, lalu mulai proses payroll pertama."
        cta-label="Mulai Proses Payroll"
        :cta-url="route('employees.index')"
    />
@else
@php
    // Get the active (most recent non-draft) payroll for the KPI section, if available
    $activePayroll = isset($payrolls) ? $payrolls->firstWhere(fn($p) => $p->status !== 'draft') ?? $payrolls->first() : null;
    $statusLabels = [
        'draft'            => ['label' => 'Draft',             'badge' => ''],
        'needs_review'     => ['label' => 'Needs Review',      'badge' => 'badge-amber'],
        'pending_approval' => ['label' => 'Pending Approval',  'badge' => 'badge-blue'],
        'approved'         => ['label' => 'Approved',          'badge' => 'badge-green'],
        'disbursed'        => ['label' => 'Disbursed',         'badge' => 'badge-green'],
    ];
@endphp

{{-- KPI Summary from active payroll --}}
@if($activePayroll)
<div class="grid grid-4">
    <div class="card kpi"><span class="muted">Karyawan</span><div class="value">{{ number_format($activePayroll->employee_count) }}</div></div>
    <div class="card kpi"><span class="muted">Gross Income</span><div class="value">Rp{{ number_format($activePayroll->gross_total / 1e6, 2) }} jt</div></div>
    <div class="card kpi"><span class="muted">Total Deduction</span><div class="value">Rp{{ number_format($activePayroll->deduction_total / 1e6, 2) }} jt</div></div>
    <div class="card kpi"><span class="muted">Take-home Pay</span><div class="value">Rp{{ number_format($activePayroll->net_total / 1e6, 2) }} jt</div></div>
</div>
<div style="height:16px;"></div>
@endif

{{-- Active Payroll workspace --}}
@if($activePayroll)
<section class="card" style="margin-bottom:16px; padding:0; overflow:visible;">
    <div class="section-title">
        <h2>Payroll {{ $activePayroll->period_label }}</h2>
        <span class="badge {{ $statusLabels[$activePayroll->status]['badge'] ?? '' }}">{{ $statusLabels[$activePayroll->status]['label'] ?? ucfirst($activePayroll->status) }}</span>
    </div>
    <div class="section-body">
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:8px;">
            @foreach (['Data Input','Kalkulasi','Review Anomali','Approval','Finalisasi','Disbursement'] as $i => $step)
                <span class="badge {{ $i < 2 ? 'badge-green' : ($i === 2 ? 'badge-amber' : '') }}">{{ $step }}</span>
            @endforeach
        </div>
    </div>
    @include('payflow.pages.parts.payroll-table')
    <div class="section-body" style="display:flex; gap:10px; flex-wrap:wrap; border-top:1px solid var(--line);">
        @if ($isSuperAdminViewing ?? false)
            <span class="badge badge-amber" title="Hanya tersedia untuk Tim HR/Finance">Mode Pantau: aksi dinonaktifkan</span>
        @else
            <form method="POST" action="{{ route('payroll.recalculate', $activePayroll->id) }}" style="display:inline;">
                @csrf
                @method('PUT')
                <button type="submit" class="btn btn-secondary">Recalculate</button>
            </form>
            <button type="button" class="btn btn-secondary">Save Draft</button>
            @if($activePayroll->status === 'needs_review')
                <form method="POST" action="{{ route('payroll.submit', $activePayroll->id) }}" style="display:inline;">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-primary">Submit for Approval</button>
                </form>
            @else
                <a class="btn btn-primary" href="{{ url('/app/approval') }}">Lihat Approval</a>
            @endif
            <button
                type="button"
                class="btn btn-danger"
                x-data
                @click="$store.confirm.show({
                    title: 'Batalkan Payroll',
                    message: 'Yakin ingin membatalkan Payroll {{ addslashes($activePayroll->period_label) }}? Semua kalkulasi akan dihapus dan status kembali ke Draft. Aksi ini tidak dapat dibatalkan.',
                    actionUrl: '#',
                    actionMethod: 'DELETE'
                })"
            >Batalkan Payroll</button>
        @endif
    </div>
</section>
@endif

{{-- Filter toolbar for payroll list --}}
<form method="GET" action="{{ route('payroll.index') }}" id="payroll-filter-form">
    {{-- Preserve sort state across filter changes --}}
    @if(request('sort'))
        <input type="hidden" name="sort" value="{{ request('sort') }}">
    @endif
    @if(request('dir'))
        <input type="hidden" name="dir" value="{{ request('dir') }}">
    @endif
    <div class="toolbar">
        <input
            class="input"
            style="max-width:200px;"
            type="text"
            name="period"
            value="{{ $periodFilter }}"
            placeholder="Filter periode (misal: 2026-07)"
            x-on:input.debounce.300ms="$el.form.submit()"
        >
        <select class="input" style="max-width:200px;" name="status" @change="$el.form.submit()">
            <option value="">Semua Status</option>
            @foreach($allowedStatuses ?? [] as $s)
                <option value="{{ $s }}" {{ $statusFilter === $s ? 'selected' : '' }}>
                    {{ ucwords(str_replace('_', ' ', $s)) }}
                </option>
            @endforeach
        </select>
        @if($periodFilter || $statusFilter)
            <a href="{{ route('payroll.index') }}" class="btn btn-secondary">Reset Filter</a>
        @endif
    </div>
</form>

{{-- Payroll list table --}}
<section class="card" style="padding:0;">
    <div class="section-title" style="padding:16px 16px 12px;">
        <h2>Riwayat Payroll</h2>
        <span class="muted" style="font-size:13px;">{{ $payrolls->total() }} periode ditemukan</span>
    </div>
    <div class="table-wrap" style="border:none; border-radius:0; border-top:1px solid var(--line);">
        <table>
            <thead>
                <tr>
                    @include('payflow.partials.sort-header', ['column' => 'period', 'label' => 'Periode', 'currentSort' => $sortBy ?? 'period', 'currentDir' => $sortDir ?? 'desc'])
                    @include('payflow.partials.sort-header', ['column' => 'status', 'label' => 'Status', 'currentSort' => $sortBy ?? 'period', 'currentDir' => $sortDir ?? 'desc'])
                    @include('payflow.partials.sort-header', ['column' => 'employee_count', 'label' => 'Karyawan', 'currentSort' => $sortBy ?? 'period', 'currentDir' => $sortDir ?? 'desc'])
                    @include('payflow.partials.sort-header', ['column' => 'gross_total', 'label' => 'Gross', 'currentSort' => $sortBy ?? 'period', 'currentDir' => $sortDir ?? 'desc'])
                    <th>Deduction</th>
                    @include('payflow.partials.sort-header', ['column' => 'net_total', 'label' => 'Net Pay', 'currentSort' => $sortBy ?? 'period', 'currentDir' => $sortDir ?? 'desc'])
                    <th>Anomali</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrolls ?? [] as $payroll)
                    @php
                        $sl = $statusLabels[$payroll->status] ?? ['label' => ucfirst($payroll->status), 'badge' => ''];
                    @endphp
                    <tr>
                        <td><strong>{{ $payroll->period_label }}</strong><div class="muted" style="font-size:12px;">{{ $payroll->period }}</div></td>
                        <td><span class="badge {{ $sl['badge'] }}">{{ $sl['label'] }}</span></td>
                        <td style="text-align:right;">{{ number_format($payroll->employee_count) }}</td>
                        <td style="text-align:right;">Rp{{ number_format($payroll->gross_total) }}</td>
                        <td style="text-align:right;">Rp{{ number_format($payroll->deduction_total) }}</td>
                        <td style="text-align:right;"><strong>Rp{{ number_format($payroll->net_total) }}</strong></td>
                        <td style="text-align:right;">
                            @if($payroll->anomaly_count > 0)
                                <span class="badge badge-red">{{ $payroll->anomaly_count }}</span>
                            @else
                                <span class="muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex; gap:6px;">
                                <form method="POST" action="{{ route('payroll.recalculate', $payroll->id) }}" style="display:inline;">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-secondary" style="padding:6px 12px; font-size:12px;">Recalculate</button>
                                </form>
                                @if($payroll->status === 'needs_review')
                                    <form method="POST" action="{{ route('payroll.submit', $payroll->id) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-primary" style="padding:6px 12px; font-size:12px;">Submit</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center; padding:40px; color:var(--muted);">
                            @if($periodFilter || $statusFilter)
                                Tidak ada payroll yang sesuai filter. <a href="{{ route('payroll.index') }}" style="color:var(--brand);">Reset filter</a>
                            @else
                                Belum ada data payroll.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('payflow.partials.pagination', ['paginator' => $payrolls])
</section>
@endif
