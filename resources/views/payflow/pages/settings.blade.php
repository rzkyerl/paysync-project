<div class="grid grid-2">
    @foreach (['Profil','Payroll','Simulasi Pembayaran','Notifikasi','Data Demo'] as $tab)
        <section class="card feature-card"><span class="badge badge-blue">{{ $tab }}</span><h3>Pengaturan {{ $tab }}</h3><p class="muted">Form konfigurasi dengan validasi inline, loading state, dan konfirmasi kuat untuk aksi berisiko.</p>@if($tab==='Data Demo')<button class="btn btn-danger">Reset Dataset Demo</button>@else<button class="btn btn-primary">Simpan</button>@endif</section>
    @endforeach
</div>
