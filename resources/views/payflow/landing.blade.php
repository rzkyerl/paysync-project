<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PaySync - HRIS dan Payroll Simulasi</title>
    @include('payflow.partials.styles')
</head>
<body>
    <nav class="landing-nav">
        <div class="container">
            @include('payflow.partials.brand')
            <div class="landing-links">
                <a href="#produk">Produk</a>
                <a href="#fitur">Fitur</a>
                <a href="#cara-kerja">Cara Kerja</a>
                <a href="#keamanan">Keamanan</a>
                <a href="#faq">FAQ</a>
            </div>
            <div style="display:flex; gap:10px; align-items:center;">
                <a class="btn btn-secondary" href="/login">Masuk</a>
                <a class="btn btn-primary" href="/register">Daftar Gratis</a>
            </div>
        </div>
    </nav>

    <main>
        <section class="hero">
            <div class="container hero-grid">
                <div>
                    <span class="badge badge-blue">Platform HR dan Payroll Terintegrasi</span>
                    <h1>Kelola HR, Payroll, dan Penyaluran Gaji dalam Satu Platform</h1>
                    <p>PaySync membantu perusahaan mengelola data karyawan, kehadiran, payroll, approval, slip digital, dan simulasi transfer massal dengan data dummy yang mudah diaudit.</p>
                    <div style="display:flex; flex-wrap:wrap; gap:12px; margin:26px 0 14px;">
                        <a class="btn btn-primary" href="/app/dashboard-hr">Mulai Demo</a>
                        <a class="btn btn-secondary" href="#cara-kerja">Lihat Cara Kerja</a>
                    </div>
                    <span class="muted" style="font-size:13px;">Seluruh rekening dan transaksi pembayaran pada demo bersifat dummy dan tidak terhubung ke bank atau payment gateway nyata.</span>
                </div>
                <div class="mockup">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                        <div>
                            <strong>Payroll Juli 2026</strong>
                            <div class="muted" style="font-size:13px;">Needs Review - 4 anomali</div>
                        </div>
                        <span class="badge badge-blue">Data Simulasi</span>
                    </div>
                    <div class="grid grid-3">
                        <div class="card kpi"><span class="muted">Karyawan</span><div class="value">248</div><span class="badge badge-green">Aktif</span></div>
                        <div class="card kpi"><span class="muted">Approval</span><div class="value">3</div><span class="badge badge-amber">Menunggu</span></div>
                        <div class="card kpi"><span class="muted">Transfer Batch</span><div class="value">92%</div><span class="badge badge-green">Matched</span></div>
                    </div>
                    <div class="card" style="padding:16px; margin-top:16px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:12px;"><strong>Workflow Payroll</strong><span class="muted">Step 4/6</span></div>
                        <div class="timeline">
                            @foreach (['Import Kehadiran','Kalkulasi','Review Anomali','Approval Finance'] as $i => $step)
                                <div class="timeline-row">
                                    <span class="dot {{ $i < 3 ? 'done' : 'warn' }}">{{ $i + 1 }}</span>
                                    <span>{{ $step }}</span>
                                    <span class="badge {{ $i < 3 ? 'badge-green' : 'badge-amber' }}">{{ $i < 3 ? 'Selesai' : 'Aktif' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mini-chart" style="margin-top:16px;">
                        @foreach ([44,76,58,92,64,82] as $h)
                            <span style="height:{{ $h }}px"></span>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section id="produk" class="landing-section">
            <div class="container">
                <div class="section-head">
                    <h2>Proses HR manual membuat data tersebar</h2>
                    <p class="muted">Payroll rentan salah, approval terlambat, dan status pembayaran sulit ditelusuri ketika employee data, attendance, dan finance review berada di tempat berbeda.</p>
                </div>
                <div class="grid grid-4">
                    @foreach ([
                        ['Data tersebar','Informasi karyawan sulit disinkronkan antar tim.','users'],
                        ['Payroll rawan keliru','Komponen gaji dan potongan tidak konsisten.','warning'],
                        ['Approval lambat','Finance tidak punya konteks anomali yang cukup.','clock'],
                        ['Transfer sulit dilacak','Status batch dan rekonsiliasi tidak transparan.','link'],
                    ] as $item)
                        <div class="card feature-card"><div class="icon-box">@include('payflow.partials.icon', ['name' => $item[2], 'class' => 'icon icon-lg'])</div><h3>{{ $item[0] }}</h3><p class="muted">{{ $item[1] }}</p></div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="fitur" class="landing-section alt">
            <div class="container">
                <div class="section-head">
                    <h2>Fitur utama PaySync</h2>
                    <p class="muted">Dirancang untuk alur HR, Finance, dan Employee portal dalam demonstrasi akademik.</p>
                </div>
                <div class="grid grid-3">
                    @foreach ([
                        ['Employee Management','Kelola profil, organisasi, status kerja, dan kelengkapan data karyawan.','users'],
                        ['Attendance','Import CSV, validasi anomali, dan kunci periode sebelum payroll.','calendar'],
                        ['Automated Payroll','Hitung gaji, tunjangan, potongan, lembur, dan adjustment dummy.','payroll'],
                        ['Approval Workflow','Finance meninjau payroll dengan modal approve dan reject beralasan.','approval'],
                        ['Digital Payslip','Slip gaji A4 dengan label Confidential dan Data Simulasi.','file'],
                        ['Salary Disbursement Simulation','Batch transfer dummy, retry gagal, dan rekonsiliasi tanpa integrasi bank nyata.','bank'],
                    ] as $item)
                        <div class="card feature-card"><div class="icon-box">@include('payflow.partials.icon', ['name' => $item[2], 'class' => 'icon icon-lg'])</div><h3>{{ $item[0] }}</h3><p class="muted">{{ $item[1] }}</p></div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="cara-kerja" class="landing-section">
            <div class="container">
                <div class="section-head"><h2>Cara kerja end-to-end</h2><p class="muted">Dari data karyawan sampai rekonsiliasi payroll dalam enam langkah.</p></div>
                <div class="grid grid-3">
                    @foreach (['Kelola data karyawan','Impor kehadiran','Hitung payroll','Review dan approval','Terbitkan slip','Simulasi transfer dan rekonsiliasi'] as $i => $step)
                        <div class="card feature-card"><span class="dot">{{ $i + 1 }}</span><h3>{{ $step }}</h3><p class="muted">Setiap tahap memiliki status, validasi, dan audit trail yang jelas.</p></div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="landing-section alt">
            <div class="container grid grid-3">
                @foreach ([
                    ['HR','Memantau data karyawan, attendance, payroll run, dan anomali.','dashboard'],
                    ['Finance','Meninjau approval, total nominal, batch transfer, dan rekonsiliasi.','bank'],
                    ['Employee','Melihat slip gaji, riwayat kehadiran, dan data profil sendiri.','users'],
                ] as $role)
                    <div class="card feature-card"><div class="icon-box">@include('payflow.partials.icon', ['name' => $role[2], 'class' => 'icon icon-lg'])</div><h3>Workspace {{ $role[0] }}</h3><p class="muted">{{ $role[1] }}</p></div>
                @endforeach
            </div>
        </section>

        <section id="keamanan" class="landing-section">
            <div class="container">
                <div class="section-head"><h2>Kontrol aplikasi yang realistis</h2><p class="muted">Desain hanya menyebut kontrol yang umum di aplikasi Laravel demo: role-based access, password hashing, CSRF protection, database transaction, audit log, dan data simulation.</p></div>
                <div class="grid grid-3">
                    @foreach (['Role-based access','Password hashing','CSRF protection','Database transaction','Audit log','Data simulation'] as $item)
                        <div class="card feature-card"><strong>{{ $item }}</strong><p class="muted">Dinyatakan sebagai kemampuan aplikasi, bukan klaim integrasi bank atau kepatuhan eksternal.</p></div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="faq" class="landing-section alt">
            <div class="container grid grid-2">
                <div>
                    <h2>Daftarkan Perusahaan Anda</h2>
                    <p class="muted">Buat workspace demo, lanjutkan onboarding, lalu eksplor alur payroll dan disbursement simulasi.</p>
                    <a class="btn btn-primary" href="/register">Daftar Gratis</a>
                </div>
                <div class="card feature-card">
                    <h3>FAQ</h3>
                    <p><strong>Apakah ada transfer nyata?</strong><br><span class="muted">Tidak. Semua rekening dan transaksi adalah data dummy.</span></p>
                    <p><strong>Apakah employee melihat data orang lain?</strong><br><span class="muted">Tidak. Portal employee dirancang hanya untuk data pengguna sendiri.</span></p>
                </div>
            </div>
        </section>
    </main>

    <footer style="background:#0f172a; color:#cbd5e1; padding:28px 0;">
        <div class="container" style="display:flex; flex-wrap:wrap; gap:18px; justify-content:space-between;">
            <strong style="color:#fff;">PaySync</strong>
            <span>Produk</span><span>Dokumentasi</span><span>Kebijakan Privasi</span><span>Ketentuan Penggunaan</span><span>kontak@paysync.test</span>
            <span>Academic Demonstration Project</span>
        </div>
    </footer>
</body>
</html>
