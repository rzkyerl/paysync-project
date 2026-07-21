<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aktifkan Akun - PaySync</title>
    @include('payflow.partials.styles')
</head>
<body><main class="auth-main" style="min-height:100vh;"><div class="card auth-card">
    @include('payflow.partials.brand')
    <h1 style="margin-top:24px;">Aktifkan Akun Anda</h1>
    <p class="muted">Buat password untuk {{ $member->email }} agar dapat mengakses workspace PaySync.</p>
    <form method="POST" action="{{ url('/invite/'.$token) }}" class="form-stack">
        @csrf
        <label class="field"><span>Password</span><input class="input" type="password" name="password" required minlength="8"></label>
        <label class="field"><span>Konfirmasi Password</span><input class="input" type="password" name="password_confirmation" required minlength="8"></label>
        @error('password')<small class="auth-error">{{ $message }}</small>@enderror
        <button class="btn btn-primary" type="submit">Aktifkan dan Masuk</button>
    </form>
</div></main></body>
</html>
