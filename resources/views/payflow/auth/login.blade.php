<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - PaySync</title>
    @include('payflow.partials.styles')
    <style>
        .demo-accounts { margin-top: 4px; border: 1px solid var(--line); border-radius: 12px; background: #f8fafc; overflow: hidden; }
        .demo-accounts[open] { border-color: var(--brand-line); background: var(--brand-soft); }
        .demo-toggle {
            display: flex; align-items: center; gap: 10px; width: 100%;
            padding: 12px 14px; cursor: pointer; list-style: none;
            font-size: 14px; font-weight: 700; color: var(--navy);
        }
        .demo-toggle::-webkit-details-marker { display: none; }
        .demo-toggle-icon { width: 20px; height: 20px; display: grid; place-items: center; flex-shrink: 0; color: var(--brand); transition: transform .25s cubic-bezier(.2,.8,.2,1); }
        .demo-accounts[open] .demo-toggle-icon { transform: rotate(180deg); }
        .demo-toggle-text { flex: 1; }
        .demo-panel { padding: 0 14px 14px; }
        .demo-hint { margin: 0 0 12px; font-size: 12.5px; color: var(--muted); line-height: 1.55; }
        .demo-hint code { background: #fff; border: 1px solid var(--line); border-radius: 5px; padding: 1px 6px; font-size: 12px; color: var(--navy); font-weight: 700; }
        .demo-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
        .demo-role {
            display: flex; align-items: center; gap: 10px; text-align: left;
            padding: 10px 11px; border-radius: 10px; cursor: pointer;
            border: 1px solid var(--line); background: #fff; transition: border-color .15s ease, box-shadow .15s ease, transform .12s ease;
        }
        .demo-role:hover { border-color: var(--brand); box-shadow: 0 4px 12px rgba(15, 52, 115, .10); transform: translateY(-1px); }
        .demo-role:active { transform: translateY(0); }
        .demo-role-badge {
            width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0;
            display: grid; place-items: center; font-size: 11px; font-weight: 800;
            color: var(--brand); background: var(--brand-soft); border: 1px solid var(--brand-line);
        }
        .demo-role-body { display: grid; min-width: 0; }
        .demo-role-body strong { font-size: 12.5px; color: var(--navy); line-height: 1.2; }
        .demo-role-body small { font-size: 11px; color: var(--muted); line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .demo-note { margin: 10px 0 0; font-size: 11.5px; color: var(--muted); line-height: 1.5; }
        @media (max-width: 480px) {
            .demo-grid { grid-template-columns: 1fr; }
        }
    </style>
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
                                placeholder="********"
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

                <details class="demo-accounts" id="demoAccounts">
                    <summary class="demo-toggle">
                        <span class="demo-toggle-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </span>
                        <span class="demo-toggle-text">Coba akun demo</span>
                        <span class="badge badge-blue">Tanpa daftar</span>
                    </summary>
                    <div class="demo-panel">
                        <p class="demo-hint">Pilih peran untuk mengisi form otomatis. Semua akun memakai password <code>password</code>.</p>
                        <div class="demo-grid">
                            <button type="button" class="demo-role" data-email="ceo@paysync.test">
                                <span class="demo-role-badge">CEO</span>
                                <span class="demo-role-body">
                                    <strong>Super Admin</strong>
                                    <small>ceo@paysync.test</small>
                                </span>
                            </button>
                            <button type="button" class="demo-role" data-email="hr@paysync.test">
                                <span class="demo-role-badge">HR</span>
                                <span class="demo-role-body">
                                    <strong>HR Manager</strong>
                                    <small>hr@paysync.test</small>
                                </span>
                            </button>
                            <button type="button" class="demo-role" data-email="finance@paysync.test">
                                <span class="demo-role-badge">FIN</span>
                                <span class="demo-role-body">
                                    <strong>Finance</strong>
                                    <small>finance@paysync.test</small>
                                </span>
                            </button>
                            <button type="button" class="demo-role" data-email="employee@paysync.test">
                                <span class="demo-role-badge">EMP</span>
                                <span class="demo-role-body">
                                    <strong>Karyawan</strong>
                                    <small>employee@paysync.test</small>
                                </span>
                            </button>
                        </div>
                        <p class="demo-note">Data demo sudah terisi: karyawan, kehadiran, payroll, dan anomali untuk eksplorasi.</p>
                    </div>
                </details>

                <p style="text-align:center; font-size:14px; color:var(--muted); margin:16px 0 0;">
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

        (() => {
            const form = document.querySelector('form[action="{{ route('login.attempt') }}"]');
            const details = document.getElementById('demoAccounts');
            if (!form || !details) return;

            const emailInput = form.querySelector('#email');
            const passwordInput = form.querySelector('#password');
            const submitBtn = form.querySelector('button[type="submit"]');

            details.querySelectorAll('.demo-role').forEach((btn) => {
                btn.addEventListener('click', () => {
                    if (!emailInput || !passwordInput) return;
                    emailInput.value = btn.dataset.email;
                    passwordInput.value = 'password';
                    emailInput.classList.remove('input-error');
                    passwordInput.classList.remove('input-error');
                    if (submitBtn) submitBtn.focus();
                });
            });

            const params = new URLSearchParams(window.location.search);
            if (params.get('demo') === '1') {
                details.open = true;
                details.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        })();
    </script>
</body>
</html>
