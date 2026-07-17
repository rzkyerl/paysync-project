<div class="grid grid-4">
    @foreach ([['Karyawan Aktif','248','12 probation'],['Kehadiran Periode Ini','96%','4 data belum lengkap'],['Status Payroll','Needs Review','3 anomali kritis'],['Estimasi Take-home Pay','Rp1,24 M','Naik 2,8%']] as $kpi)
        <div class="card kpi"><span class="muted">{{ $kpi[0] }}</span><div class="value">{{ $kpi[1] }}</div><span class="badge badge-blue">{{ $kpi[2] }}</span></div>
    @endforeach
</div>
<div class="grid grid-2" style="margin-top:16px;">
    <section class="card">
        <div class="section-title"><h2>Payroll Timeline</h2><span class="badge badge-amber">Review Anomali aktif</span></div>
        <div class="section-body timeline">
            @foreach (['Import Kehadiran','Kalkulasi','Review Anomali','Approval','Slip Terbit','Transfer'] as $i => $step)
                <div class="timeline-row"><span class="dot {{ $i < 2 ? 'done' : ($i === 2 ? 'warn' : '') }}">{{ $i + 1 }}</span><strong>{{ $step }}</strong><span class="badge {{ $i < 2 ? 'badge-green' : ($i === 2 ? 'badge-amber' : '') }}">{{ $i < 2 ? 'Selesai' : ($i === 2 ? 'Aktif' : 'Menunggu') }}</span></div>
            @endforeach
        </div>
    </section>
    <section class="card">
        <div class="section-title"><h2>Action Center</h2><span class="badge badge-red">4 item</span></div>
        <div class="section-body grid">
            @foreach (['Rekening belum terverifikasi: 7 karyawan','Data kehadiran belum lengkap: 4 baris','Payroll memiliki anomali: 3 kritis','Payroll menunggu approval Finance'] as $item)
                <div class="card" style="padding:12px; box-shadow:none;"><strong>{{ $item }}</strong><p class="muted" style="margin:4px 0 0;">Tinjau detail sebelum payroll dikirim.</p></div>
            @endforeach
        </div>
    </section>
</div>
<section class="card" style="margin-top:16px;">
    <div class="section-title"><h2>Perubahan Karyawan Terbaru</h2><a class="btn btn-secondary" href="/app/employees">Lihat Semua</a></div>
    @include('payflow.pages.parts.employee-table')
</section>
