<div class="toolbar">
    <input class="input" style="max-width:260px;" placeholder="Cari NIP atau nama">
    <select class="input" style="max-width:180px;"><option>Semua Departemen</option></select>
    <select class="input" style="max-width:180px;"><option>Status kerja</option></select>
    <button class="btn btn-secondary">@include('payflow.partials.icon', ['name' => 'upload', 'class' => 'icon icon-sm']) Import CSV</button>
    <button class="btn btn-primary">@include('payflow.partials.icon', ['name' => 'users', 'class' => 'icon icon-sm']) Tambah Karyawan</button>
</div>
<section class="card">
    @include('payflow.pages.parts.employee-table')
    <div class="section-body" style="display:flex; justify-content:space-between;"><span class="muted">Menampilkan 1-5 dari 248 data</span><div><button class="btn btn-secondary">Sebelumnya</button><button class="btn btn-secondary">Berikutnya</button></div></div>
</section>
<div class="grid grid-2" style="margin-top:16px;">
    <div class="card feature-card"><h3>Empty State</h3><p class="muted">Jika belum ada data, tampilkan CTA Tambah Karyawan atau Import Data.</p><button class="btn btn-primary">Import Data</button></div>
    <div class="card feature-card"><h3>No Result State</h3><p class="muted">Data karyawan tidak ditemukan.</p><button class="btn btn-secondary">Reset Filter</button></div>
</div>
