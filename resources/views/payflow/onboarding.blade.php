<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Onboarding - PaySync</title>
    @include('payflow.partials.styles')
</head>
<body>
    <main class="auth-main" style="min-height:100vh; align-items:start; padding-top:36px;">
        <section class="card" style="width:min(980px,100%); padding:24px;">
            <div style="display:flex; justify-content:space-between; gap:16px; align-items:center; margin-bottom:20px;">
                @include('payflow.partials.brand')
                <span class="badge badge-blue">Langkah 6 dari 6</span>
            </div>
            <div class="grid grid-6" style="display:grid; grid-template-columns:repeat(6,1fr); gap:8px; margin-bottom:22px;">
                @foreach (['Profil Perusahaan','Struktur Organisasi','Pengaturan Payroll','Tambah Karyawan','Pengaturan Pembayaran','Review'] as $i => $step)
                    <div>
                        <div class="progress"><span style="width:{{ $i < 5 ? 100 : 60 }}%;"></span></div>
                        <small class="muted">{{ $step }}</small>
                    </div>
                @endforeach
            </div>
            <div class="grid grid-2">
                <div>
                    <h1 style="margin-top:0;">Review Setup Perusahaan</h1>
                    <p class="muted">Periksa kembali konfigurasi workspace sebelum masuk dashboard.</p>
                    <div class="grid">
                        @foreach ([
                            ['Profil','PT Nusantara, Asia/Jakarta, Rupiah'],
                            ['Organisasi','4 departemen, 12 jabatan aktif'],
                            ['Payroll','Cut-off 25, pay date 30, lembur aktif'],
                            ['Karyawan','Siapkan 248 data karyawan'],
                            ['Pembayaran','Payroll Wallet, saldo tersedia Rp1.280.000.000'],
                        ] as $row)
                            <div class="card" style="padding:14px; box-shadow:none;"><strong>{{ $row[0] }}</strong><p class="muted" style="margin:5px 0 0;">{{ $row[1] }}</p></div>
                        @endforeach
                    </div>
                </div>
                <div class="card" style="padding:18px; box-shadow:none; align-self:start;">
                    <span class="badge badge-blue">Payroll Payment</span>
                    <h2>Pengaturan Pembayaran</h2>
                    <p class="muted">Atur sumber dana, validasi rekening, proses batch transfer, dan retry pembayaran dari workflow Finance.</p>
                    <div class="grid grid-3">
                        <button class="btn btn-secondary">Tambah Manual</button>
                        <button class="btn btn-secondary">Import CSV</button>
                        <button class="btn btn-primary">Gunakan Template Data</button>
                    </div>
                </div>
            </div>
            <div style="display:flex; justify-content:space-between; gap:12px; margin-top:22px;">
                <a class="btn btn-secondary" href="/verify">Kembali</a>
                <a class="btn btn-primary" href="/app/dashboard-hr">Selesaikan Setup lalu Masuk Dashboard</a>
            </div>
        </section>
    </main>
</body>
</html>
