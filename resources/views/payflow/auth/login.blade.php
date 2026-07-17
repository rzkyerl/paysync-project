<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - PaySync</title>
    @include('payflow.partials.styles')
</head>
<body>
    <main class="auth-page">
        <section class="auth-hero">
            @include('payflow.partials.brand')
            <div>
                <h1 style="font-size:42px; line-height:1.1; margin:0 0 12px;">Dari Data Karyawan hingga Hari Gajian</h1>
                <p style="color:#cbd5e1; max-width:520px;">Masuk ke workspace demo untuk mengelola payroll, approval, slip digital, dan simulasi transfer gaji.</p>
                <div class="grid" style="margin-top:24px;">
                    @foreach (['Workflow payroll terpusat','Audit trail untuk perubahan penting','Data rekening dan transfer selalu dummy'] as $item)
                        <span class="badge" style="background:rgba(255,255,255,.09); color:#e2e8f0; border-color:rgba(255,255,255,.14);">{{ $item }}</span>
                    @endforeach
                </div>
            </div>
            <span style="color:#94a3b8;">Academic Demonstration Project</span>
        </section>
        <section class="auth-main">
            <form class="card auth-card" action="/app/dashboard-hr">
                <h2 style="font-size:28px; margin:0 0 8px;">Selamat Datang Kembali</h2>
                <p class="muted" style="margin-top:0;">Masuk untuk melanjutkan ke workspace perusahaan.</p>
                <div class="form-stack">
                    <div class="field"><label>Email</label><input class="input" type="email" value="rina.hr@nusantara.test"></div>
                    <div class="field"><label>Password</label><input class="input" type="password" value="demodemo"></div>
                    <div style="display:flex; justify-content:space-between; gap:12px; align-items:center; font-size:14px;">
                        <label><input type="checkbox" checked> Ingat saya</label><a style="color:var(--brand);" href="#">Lupa password</a>
                    </div>
                    <button class="btn btn-primary" style="width:100%;">Masuk</button>
                    <div class="badge badge-blue" style="justify-content:center;">Sesi aplikasi demo akan dibuat setelah login</div>
                    <div class="card" style="padding:13px; box-shadow:none;"><strong>Demo Account</strong><p class="muted" style="margin:6px 0 0;">Role HR: rina.hr@nusantara.test<br>Role Finance: budi.finance@nusantara.test</p></div>
                    <p class="muted" style="text-align:center;">Belum memiliki perusahaan? <a href="/register" style="color:var(--brand); font-weight:800;">Daftar Gratis</a></p>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
