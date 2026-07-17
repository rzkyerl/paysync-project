<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrasi - PaySync</title>
    @include('payflow.partials.styles')
</head>
<body>
    <main class="auth-main" style="min-height:100vh;">
        <form class="card auth-card" style="width:min(760px,100%);" action="/verify">
            @include('payflow.partials.brand')
            <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; margin:22px 0;">
                <div><h1 style="margin:0; font-size:30px;">Buat Workspace Perusahaan</h1><p class="muted">Daftarkan perusahaan untuk memulai demo PaySync.</p></div>
                <a href="/login" style="color:var(--brand); font-weight:800;">Sudah punya akun? Masuk</a>
            </div>
            <div class="grid grid-2">
                @foreach (['Nama lengkap','Email kerja','Nomor handphone','Jabatan pendaftar','Nama perusahaan'] as $label)
                    <div class="field"><label>{{ $label }}</label><input class="input" value="{{ $label === 'Nomor handphone' ? '+62 ' : '' }}"></div>
                @endforeach
                <div class="field"><label>Jumlah karyawan</label><select class="input"><option>51-100</option><option>1-20</option><option>21-50</option><option>101-500</option><option>Lebih dari 500</option></select></div>
                <div class="field"><label>Password</label><input class="input" type="password"><span class="badge badge-green">Kekuatan password: Baik</span></div>
                <div class="field"><label>Konfirmasi password</label><input class="input" type="password"></div>
                <div class="field"><label>Bidang usaha opsional</label><input class="input" placeholder="Teknologi, manufaktur, jasa..."></div>
            </div>
            <div class="card" style="padding:14px; margin:18px 0; box-shadow:none;"><strong>Company Owner</strong><p class="muted" style="margin:6px 0 0;">Pengguna pertama otomatis menjadi Company Owner dan dapat mengundang HR serta Finance setelah registrasi selesai.</p></div>
            <label style="display:flex; gap:8px; margin-bottom:14px;"><input type="checkbox" checked> Saya menyetujui Ketentuan Penggunaan dan Kebijakan Privasi.</label>
            <button class="btn btn-primary" style="width:100%;">Buat Akun Perusahaan</button>
        </form>
    </main>
</body>
</html>
