<div class="grid grid-5" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:16px;">
    @foreach ([['Hadir','4.520'],['Terlambat','83'],['Tidak Hadir','19'],['Cuti','42'],['Total Lembur','612 jam']] as $kpi)
        <div class="card kpi"><span class="muted">{{ $kpi[0] }}</span><div class="value">{{ $kpi[1] }}</div></div>
    @endforeach
</div>
<section class="card" style="margin-top:16px;">
    <div class="section-title"><h2>Import CSV Kehadiran</h2><div><button class="btn btn-secondary">@include('payflow.partials.icon', ['name' => 'upload', 'class' => 'icon icon-sm']) Import CSV</button><button class="btn btn-primary">@include('payflow.partials.icon', ['name' => 'lock', 'class' => 'icon icon-sm']) Kunci Periode</button></div></div>
    <div class="section-body grid grid-3">
        @foreach (['Upload file dan download template','Mapping kolom CSV ke field sistem','Preview validasi dan import data valid'] as $i => $step)
            <div class="card feature-card" style="box-shadow:none;"><span class="dot">{{ $i + 1 }}</span><h3>{{ $step }}</h3><p class="muted">Validasi menampilkan jumlah valid, warning, dan error sebelum import.</p></div>
        @endforeach
    </div>
    @include('payflow.pages.parts.attendance-table')
</section>
