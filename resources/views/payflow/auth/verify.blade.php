<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi - PaySync</title>
    @include('payflow.partials.styles')
</head>
<body>
    <main class="auth-main" style="min-height:100vh;">
        <div class="card auth-card" style="text-align:center;">
            <div class="icon-box" style="margin:0 auto 14px;">@include('payflow.partials.icon', ['name' => 'shield', 'class' => 'icon icon-lg'])</div>
            <h1 style="margin:0 0 8px;">Verifikasi Akun Anda</h1>
            <p class="muted">Kode enam digit telah dikirim ke email kerja Anda.</p>
            <div style="display:grid; grid-template-columns:repeat(6,1fr); gap:8px; margin:22px 0;">
                @foreach (str_split('123456') as $n)<input class="input" style="text-align:center; font-weight:900;" value="{{ $n }}">@endforeach
            </div>
            <div class="badge badge-blue" style="justify-content:center; width:100%; margin-bottom:14px;">Kode verifikasi demo: 123456</div>
            <a class="btn btn-primary" style="width:100%;" href="/onboarding">Verifikasi</a>
            <div style="display:flex; justify-content:space-between; margin-top:14px;"><a href="#">Kirim Ulang Kode</a><a href="/register">Ganti Email</a></div>
        </div>
    </main>
</body>
</html>
