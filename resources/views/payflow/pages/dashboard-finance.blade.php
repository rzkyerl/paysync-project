<div class="grid grid-4">
    @foreach ([['Payroll Menunggu Persetujuan','3','Prioritas tinggi'],['Total Nominal Disetujui','Rp2,82 M','2 periode'],['Transfer Berhasil','1.192','98,4%'],['Transfer Gagal','19','Perlu retry']] as $kpi)
        <div class="card kpi"><span class="muted">{{ $kpi[0] }}</span><div class="value">{{ $kpi[1] }}</div><span class="badge badge-blue">{{ $kpi[2] }}</span></div>
    @endforeach
</div>
<div class="grid grid-2" style="margin-top:16px;">
    <section class="card">
        <div class="section-title"><h2>Approval Queue</h2><span class="badge badge-amber">3 payroll</span></div>
        @include('payflow.pages.parts.approval-table')
    </section>
    <section class="card">
        <div class="section-title"><h2>Transfer Batch Status</h2><span class="badge badge-blue">Payroll Operations</span></div>
        <div class="section-body grid">
            @foreach ([['Success',78,'badge-green'],['Processing',14,'badge-blue'],['Failed',8,'badge-red']] as $row)
                <div><div style="display:flex; justify-content:space-between;"><strong>{{ $row[0] }}</strong><span>{{ $row[1] }}%</span></div><div class="progress"><span style="width:{{ $row[1] }}%;"></span></div></div>
            @endforeach
            <div class="card" style="padding:14px; box-shadow:none;"><strong>Rekonsiliasi</strong><p class="muted">Matched Rp1,18 M, mismatch Rp18,4 jt.</p><a class="btn btn-primary" href="/app/reconciliation">Selesaikan Selisih</a></div>
        </div>
    </section>
</div>
