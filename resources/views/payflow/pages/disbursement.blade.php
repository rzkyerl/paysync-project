<div class="grid grid-4">
    @foreach ([['Ready to Transfer','248'],['Processing','17'],['Successful Amount','Rp1,18 M'],['Failed Amount','Rp18,4 jt']] as $kpi)<div class="card kpi"><span class="muted">{{ $kpi[0] }}</span><div class="value">{{ $kpi[1] }}</div></div>@endforeach
</div>
<section class="card" style="margin-top:16px;">
    <div class="section-title"><h2>Penyaluran Gaji</h2><button class="btn btn-primary">Buat Batch Transfer</button></div>
    @include('payflow.pages.parts.transfer-table')
    <div class="section-body"><span class="badge badge-blue">Payroll Operations</span> <span class="muted">Batch diproses setelah payroll disetujui dan rekening karyawan tervalidasi.</span></div>
</section>
