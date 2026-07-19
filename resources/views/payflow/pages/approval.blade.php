<section class="card">
    <div class="section-title"><h2>Payroll Menunggu Approval</h2><span class="badge badge-amber">Reject wajib alasan</span></div>
    @include('payflow.pages.parts.approval-table')
    <div class="section-body card" style="margin:16px; box-shadow:none;"><strong>Sticky Approval Bar</strong><p class="muted">Konfirmasi approve menampilkan total nominal Rp1,24 M dan 248 karyawan.</p>@if ($isSuperAdminViewing ?? false)<span class="badge badge-amber" title="Hanya tersedia untuk Tim HR/Finance">View Only</span>@else<button class="btn btn-danger">Reject</button> <button class="btn btn-primary">Approve Payroll</button>@endif</div>
</section>
