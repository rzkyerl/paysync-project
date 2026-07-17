<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - PaySync</title>
    @include('payflow.partials.styles')
</head>
<body>
    <main class="auth-page">
        <section class="auth-hero">
            @include('payflow.partials.brand')
            <div>
                <h1 style="font-size:clamp(28px,4vw,42px); line-height:1.1; margin:0 0 14px;">Dari Data Karyawan<br>hingga Hari Gajian</h1>
                <p style="color:#cbd5e1; max-width:420px; line-height:1.7;">Masuk ke workspace perusahaan untuk mengelola payroll, approval, slip digital, dan transfer gaji.</p>
                <div style="margin-top:28px; display:flex; flex-direction:column; gap:10px;">
                    @foreach ([
                        ['Workflow payroll terpusat', 'payroll'],
                        ['Audit trail untuk setiap perubahan penting', 'file'],
                        ['Kontrol rekening dan transfer payroll', 'bank'],
                    ] as $feat)
                        <div style="display:flex; align-items:center; gap:10px; color:#e2e8f0; font-size:14px;">
                            <span style="width:20px; height:20px; border-radius:6px; background:rgba(255,255,255,.12); display:grid; place-items:center; flex-shrink:0;">
                                @include('payflow.partials.icon', ['name' => $feat[1], 'class' => 'icon icon-sm'])
                            </span>
                            {{ $feat[0] }}
                        </div>
                    @endforeach
                </div>
            </div>
            <span style="color:#64748b; font-size:13px;">Modern Payroll Operations Platform</span>
        </section>

        <section class="auth-main">
            <div class="card auth-card">
                <div style="margin-bottom:24px;">
                    <h2 style="font-size:26px; margin:0 0 6px; color:var(--navy);">Selamat Datang Kembali</h2>
                    <p class="muted" style="margin:0; font-size:14px;">Masuk untuk melanjutkan ke workspace perusahaan Anda.</p>
                </div>

                {{-- Session error (e.g. account locked) --}}
                @if (session('error'))
                    <div class="auth-alert auth-alert-error" role="alert">
                        <svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Success flash (e.g. after logout) --}}
                @if (session('status'))
                    <div class="auth-alert auth-alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}" class="form-stack" novalidate>
                    @csrf

                    {{-- Email --}}
                    <div class="field">
                        <label for="email">Email</label>
                        <input
                            id="email"
                            class="input @error('email') input-error @enderror"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            autofocus
                            placeholder="nama@perusahaan.com"
                            required
                        >
                        @error('email')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="field">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <input
                                id="password"
                                class="input @error('password') input-error @enderror"
                                type="password"
                                name="password"
                                autocomplete="current-password"
                                placeholder="••••••••"
                                required
                            >
                            <button type="button" class="input-eye" aria-label="Tampilkan password" onclick="togglePassword(this)">
                                <svg class="icon icon-sm eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                <svg class="icon icon-sm eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        @error('password')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Remember + Forgot --}}
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; font-size:13px;">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span>Ingat saya selama 30 hari</span>
                        </label>
                        <a href="#" style="color:var(--brand); font-weight:600;">Lupa password?</a>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; font-size:15px;">
                        Masuk ke Workspace
                    </button>
                </form>

                <div class="auth-divider"><span>atau</span></div>

                <p style="text-align:center; font-size:14px; color:var(--muted); margin:0;">
                    Belum memiliki akun perusahaan?
                    <a href="/register" style="color:var(--brand); font-weight:700;">Daftar Gratis</a>
                </p>
            </div>
        </section>
    </main>

    <script>
        function togglePassword(btn) {
            const wrap = btn.closest('.input-wrap');
            const input = wrap.querySelector('input');
            const eyeOff = btn.querySelector('.eye-off');
            const eyeOn = btn.querySelector('.eye-on');
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            eyeOff.style.display = isHidden ? 'none' : '';
            eyeOn.style.display = isHidden ? '' : 'none';
            btn.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
        }
    </script>
</body>
</html>
