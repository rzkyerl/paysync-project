<div class="grid grid-4">
    @foreach ([['Next Payday','30 Jul 2026','13 hari lagi'],['Kehadiran Bulan Ini','18/20','2 terlambat'],['Lembur','7,5 jam','Disetujui'],['Take-home Pay Terakhir','Rp8.450.000','Berhasil']] as $kpi)
        <div class="card kpi"><span class="muted">{{ $kpi[0] }}</span><div class="value">{{ $kpi[1] }}</div><span class="badge badge-blue">{{ $kpi[2] }}</span></div>
    @endforeach
</div>
<div class="grid grid-2" style="margin-top:16px;">
    <section class="card">
        <div class="section-title"><h2>Slip Gaji Terbaru</h2><span class="badge badge-green">Published</span></div>
        <div class="section-body"><p><strong>Periode Juni 2026</strong></p><p class="muted">Net pay Rp8.450.000, pembayaran berhasil.</p><a class="btn btn-primary" href="/app/payslips">Lihat Detail</a> <button class="btn btn-secondary">Download PDF</button></div>
    </section>
    <section class="card">
        <div class="section-title"><h2>Profil dan Rekening</h2><span class="badge badge-amber">Perlu dilengkapi</span></div>
        <div class="section-body"><p class="muted">Rekening BCA •••• 6789 menunggu verifikasi HR. Employee tidak melihat data karyawan lain.</p></div>
    </section>
</div>
