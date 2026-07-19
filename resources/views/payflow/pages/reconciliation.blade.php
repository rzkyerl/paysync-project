<div class="grid grid-4">
    @foreach ([['Payroll Total','Rp1,24 M'],['Transfer Success','Rp1,18 M'],['Pending or Failed','Rp18,4 jt'],['Difference','Rp18,4 jt']] as $kpi)<div class="card kpi"><span class="muted">{{ $kpi[0] }}</span><div class="value">{{ $kpi[1] }}</div></div>@endforeach
</div>
<section class="card" style="margin-top:16px;">
    <div class="section-title"><h2>Rekonsiliasi Batch BT-202607-001</h2><span class="badge badge-red">Mismatch</span></div>
    @include('payflow.pages.parts.recon-table')
    <div class="section-body grid grid-3">@if ($isSuperAdminViewing ?? false)<span class="badge badge-amber" title="Hanya tersedia untuk Tim HR/Finance">View Only</span>@else<button class="btn btn-secondary">Retry Transfer</button><button class="btn btn-secondary">Resolve with Note</button><button class="btn btn-primary">Close Reconciliation</button>@endif</div>
</section>
