<div class="grid grid-2">
    @foreach (['Profil','Payroll','Pembayaran','Notifikasi','Data Operasional'] as $tab)
        <section class="card feature-card"><span class="badge badge-blue">{{ $tab }}</span><h3>Pengaturan {{ $tab }}</h3><p class="muted">Form konfigurasi dengan validasi inline, loading state, dan konfirmasi kuat untuk aksi berisiko.</p>@if($tab==='Data Operasional')<button class="btn btn-danger">Reset Data Operasional</button>@else<button class="btn btn-primary">Simpan</button>@endif</section>
    @endforeach
</div>
