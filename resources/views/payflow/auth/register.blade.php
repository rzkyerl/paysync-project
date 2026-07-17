<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrasi - PaySync</title>
    @include('payflow.partials.styles')
</head>
<body>
    <div class="register-page">
        {{-- Top nav --}}
        <nav class="register-nav">
            <div class="register-nav-inner">
                @include('payflow.partials.brand')
                <span style="font-size:13px; color:var(--muted);">
                    Sudah punya akun?
                    <a href="/login" style="color:var(--brand); font-weight:700;">Masuk</a>
                </span>
            </div>
        </nav>

        <main class="register-main">
            <div class="register-card card">

                {{-- Header --}}
                <div class="register-header">
                    <h1>Buat Workspace Perusahaan</h1>
                    <p class="muted">Daftarkan perusahaan Anda untuk mulai menggunakan PaySync.</p>
                </div>

                {{-- Global errors --}}
                @if ($errors->any() && ! $errors->has('email') && ! $errors->has('password') && ! $errors->has('name') && ! $errors->has('company') && ! $errors->has('company_size') && ! $errors->has('terms') && ! $errors->has('password_confirmation'))
                    <div class="auth-alert auth-alert-error" role="alert">
                        <svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Periksa kembali data yang kamu masukkan.
                    </div>
                @endif

                <form method="POST" action="{{ route('register.store') }}" class="register-form" novalidate id="register-form">
                    @csrf

                    {{-- Section: Identitas --}}
                    <div class="register-section-label">Identitas Pendaftar</div>
                    <div class="grid grid-2">
                        <div class="field">
                            <label for="name">Nama lengkap <span class="req">*</span></label>
                            <input
                                id="name"
                                class="input @error('name') input-error @enderror"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                autocomplete="name"
                                placeholder="John Doe"
                                required
                            >
                            @error('name')<span class="field-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="field">
                            <label for="email">Email kerja <span class="req">*</span></label>
                            <input
                                id="email"
                                class="input @error('email') input-error @enderror"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                autocomplete="email"
                                placeholder="nama@perusahaan.com"
                                required
                            >
                            @error('email')<span class="field-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="field">
                            <label for="phone">Nomor handphone</label>
                            <div class="input-phone-wrap">
                                <span class="phone-prefix">+62</span>
                                <input
                                    id="phone"
                                    class="input input-phone"
                                    type="tel"
                                    name="phone"
                                    value="{{ old('phone') }}"
                                    autocomplete="tel"
                                    placeholder="812 3456 7890"
                                    inputmode="tel"
                                >
                            </div>
                        </div>

                        <div class="field">
                            <label for="position">Jabatan pendaftar</label>
                            <input
                                id="position"
                                class="input"
                                type="text"
                                name="position"
                                value="{{ old('position') }}"
                                placeholder="HR Manager, CEO, Finance Head..."
                            >
                        </div>
                    </div>

                    {{-- Section: Perusahaan --}}
                    <div class="register-section-label" style="margin-top:28px;">Data Perusahaan</div>
                    <div class="grid grid-2">
                        <div class="field">
                            <label for="company">Nama perusahaan <span class="req">*</span></label>
                            <input
                                id="company"
                                class="input @error('company') input-error @enderror"
                                type="text"
                                name="company"
                                value="{{ old('company') }}"
                                placeholder="PT Nusantara Jaya"
                                required
                            >
                            @error('company')<span class="field-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="field">
                            <label for="company_size">Jumlah karyawan <span class="req">*</span></label>
                            <select
                                id="company_size"
                                class="input @error('company_size') input-error @enderror"
                                name="company_size"
                                required
                            >
                                <option value="" disabled {{ old('company_size') ? '' : 'selected' }}>Pilih jumlah karyawan</option>
                                @foreach (['1–20', '21–50', '51–100', '101–500', '> 500'] as $size)
                                    <option value="{{ $size }}" {{ old('company_size') === $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                            @error('company_size')<span class="field-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="field" style="grid-column: span 2;">
                            <label for="industry">Bidang usaha <span style="color:var(--muted); font-weight:400;">(opsional)</span></label>
                            <input
                                id="industry"
                                class="input"
                                type="text"
                                name="industry"
                                value="{{ old('industry') }}"
                                placeholder="Teknologi, manufaktur, jasa, ritel..."
                            >
                        </div>
                    </div>

                    {{-- Section: Password --}}
                    <div class="register-section-label" style="margin-top:28px;">Keamanan Akun</div>
                    <div class="grid" style="gap:16px;">
                        <div class="field">
                            <label for="password">Password <span class="req">*</span></label>
                            <div class="input-wrap">
                                <input
                                    id="password"
                                    class="input @error('password') input-error @enderror"
                                    type="password"
                                    name="password"
                                    autocomplete="new-password"
                                    placeholder="Min. 8 karakter"
                                    required
                                    oninput="checkStrength(this.value)"
                                >
                                <button type="button" class="input-eye" aria-label="Tampilkan password" onclick="togglePwd(this)">
                                    <svg class="icon icon-sm eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                    <svg class="icon icon-sm eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                            <div class="pwd-meter" id="pwd-meter" aria-hidden="true">
                                <div class="pwd-bar" id="pwd-bar"></div>
                            </div>
                            <span class="pwd-label" id="pwd-label"></span>
                            @error('password')<span class="field-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="field">
                            <label for="password_confirmation">Konfirmasi password <span class="req">*</span></label>
                            <div class="input-wrap">
                                <input
                                    id="password_confirmation"
                                    class="input"
                                    type="password"
                                    name="password_confirmation"
                                    autocomplete="new-password"
                                    placeholder="Ulangi password"
                                    required
                                >
                                <button type="button" class="input-eye" aria-label="Tampilkan konfirmasi password" onclick="togglePwd(this)">
                                    <svg class="icon icon-sm eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                    <svg class="icon icon-sm eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Info box --}}
                    <div class="register-info-box">
                        <svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <p>Anda akan menjadi <strong>Company Owner</strong> dan dapat mengundang anggota tim HR serta Finance setelah workspace dibuat.</p>
                    </div>

                    {{-- Terms --}}
                    <div class="field" style="margin-top:4px;">
                        <label class="checkbox-label @error('terms') checkbox-label-error @enderror">
                            <input
                                type="checkbox"
                                name="terms"
                                value="1"
                                {{ old('terms') ? 'checked' : '' }}
                                required
                            >
                            <span>Saya menyetujui <a href="#" style="color:var(--brand);">Ketentuan Penggunaan</a> dan <a href="#" style="color:var(--brand);">Kebijakan Privasi</a> PaySync.</span>
                        </label>
                        @error('terms')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn btn-primary" style="width:100%; padding:13px; font-size:15px; margin-top:8px;">
                        Buat Akun Perusahaan
                    </button>

                    <p style="text-align:center; font-size:13px; color:var(--muted); margin:12px 0 0;">
                        Sudah punya akun?
                        <a href="/login" style="color:var(--brand); font-weight:700;">Masuk di sini</a>
                    </p>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Password show/hide toggle
        function togglePwd(btn) {
            const wrap = btn.closest('.input-wrap');
            const input = wrap.querySelector('input');
            const eyeOff = btn.querySelector('.eye-off');
            const eyeOn  = btn.querySelector('.eye-on');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            eyeOff.style.display = show ? 'none' : '';
            eyeOn.style.display  = show ? '' : 'none';
            btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
        }

        // Password strength meter
        function checkStrength(val) {
            const bar   = document.getElementById('pwd-bar');
            const label = document.getElementById('pwd-label');
            if (!bar || !label) return;

            let score = 0;
            if (val.length >= 8)                        score++;
            if (val.length >= 12)                       score++;
            if (/[A-Z]/.test(val))                      score++;
            if (/[0-9]/.test(val))                      score++;
            if (/[^A-Za-z0-9]/.test(val))               score++;

            const levels = [
                { pct: '20%',  color: '#dc2626', text: 'Sangat lemah' },
                { pct: '40%',  color: '#d97706', text: 'Lemah'        },
                { pct: '60%',  color: '#f59e0b', text: 'Cukup'        },
                { pct: '80%',  color: '#22c55e', text: 'Kuat'         },
                { pct: '100%', color: '#16a34a', text: 'Sangat kuat'  },
            ];

            if (!val) {
                bar.style.width = '0';
                label.textContent = '';
                return;
            }

            const lvl = levels[Math.min(score - 1, 4)] || levels[0];
            bar.style.width       = lvl.pct;
            bar.style.background  = lvl.color;
            label.textContent     = 'Kekuatan: ' + lvl.text;
            label.style.color     = lvl.color;
        }
    </script>
</body>
</html>
