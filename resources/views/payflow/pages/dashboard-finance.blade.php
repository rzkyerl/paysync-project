<div x-data="{ loading: true }" x-init="$nextTick(() => loading = false)">

    {{-- Skeleton (shown while loading) --}}
    <div x-show="loading">
        @include('payflow.partials.skeleton-kpi')
        <div class="grid grid-2" style="margin-top:16px;">
            <div class="card skeleton" style="height:260px;"></div>
            <div class="card skeleton" style="height:260px;"></div>
        </div>
    </div>

    {{-- Real Content (hidden until loading is false) --}}
    <div x-show="!loading" x-cloak>

@if($isEmpty ?? false)
    <div class="grid grid-4">
        @foreach(['Menunggu Approval', 'Total Disetujui', 'Transfer Berhasil', 'Transfer Gagal'] as $label)
            <div class="card kpi"><span class="muted">{{ $label }}</span><div class="value">—</div><span class="badge">Belum ada data</span></div>
        @endforeach
    </div>
    <x-empty-state
        style="margin-top:16px;"
        icon="approval"
        title="Belum ada payroll yang perlu disetujui"
        description="Approval queue akan muncul setelah HR mengirim payroll untuk ditinjau."
    />
@else

@if(isset($error) && $error)
    <div class="card" style="border-left: 4px solid var(--red); padding: 20px; color: var(--red);">
        <strong>Gagal memuat data</strong>
        <p class="muted" style="margin-top:6px;">{{ $error }}</p>
        <a class="btn btn-secondary" style="margin-top:10px;" href="{{ request()->url() }}">Coba Lagi</a>
    </div>
@else

{{-- KPI Cards --}}
<div class="grid grid-4">
    @foreach ($kpis as $kpi)
        <div class="card kpi">
            <span class="muted">{{ $kpi['label'] }}</span>
            <div class="value">{{ $kpi['value'] }}</div>
            <span class="badge {{ $kpi['badge'] }}">{{ $kpi['sub'] }}</span>
        </div>
    @endforeach
</div>

<div class="grid grid-2" style="margin-top:16px;">

    {{-- Approval Queue --}}
    <section class="card">
        <div class="section-title">
            <h2>Approval Queue</h2>
            <span class="badge badge-amber">{{ $approvalQueue->count() }} payroll</span>
        </div>
        @include('payflow.pages.parts.approval-table', ['approvalQueue' => $approvalQueue])
    </section>

    {{-- Transfer Batch Status + Rekonsiliasi --}}
    <section class="card">
        <div class="section-title">
            <h2>Transfer Batch Status</h2>
            <span class="badge badge-blue">Payroll Operations</span>
        </div>
        <div class="section-body grid">
            @foreach ($transferBatchStatus as $row)
                <div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <strong>{{ $row['label'] }}</strong>
                        <span>{{ $row['percent'] }}%</span>
                    </div>
                    <div class="progress">
                        <span style="width:{{ $row['percent'] }}%;"></span>
                    </div>
                </div>
            @endforeach

            <div class="card" style="padding:14px; box-shadow:none; margin-top:8px;">
                <strong>Rekonsiliasi</strong>
                <p class="muted" style="margin-top:6px;">
                    Matched {{ $reconciliation['matched_label'] }}
                    @if($reconciliation['has_mismatch'])
                        , mismatch {{ $reconciliation['mismatch_label'] }}
                    @else
                        — tidak ada selisih
                    @endif
                </p>
                <a class="btn btn-primary" href="/app/reconciliation" style="margin-top:10px; display:inline-block;">
                    @if($reconciliation['has_mismatch'])
                        Selesaikan Selisih
                    @else
                        Lihat Rekonsiliasi
                    @endif
                </a>
            </div>
        </div>
    </section>

</div>

@endif

@endif

    </div>{{-- end x-show="!loading" --}}
</div>{{-- end x-data --}}
