@php
    $prefill = $prefill ?? [];
    $initialData = [
        'name'            => old('name', $prefill['company_name'] ?? ''),
        'industry'        => old('industry', $prefill['industry'] ?? ''),
        'size'            => old('size', $prefill['company_size'] ?? ''),
        'payroll_cut_off' => (int) old('payroll_cut_off', 25),
        'pay_date'        => (int) old('pay_date', 30),
    ];
    $companySizes = ['1–20', '21–50', '51–100', '101–500', '> 500'];
    $restoreStep = 1;
    if ($errors->hasAny(['size']))                        $restoreStep = 2;
    if ($errors->hasAny(['payroll_cut_off', 'pay_date'])) $restoreStep = 3;
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setup Workspace — PaySync</title>
    @include('payflow.partials.styles')
    <style>
        [x-cloak] { display: none !important; }

        .onb-page {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1.1fr;
            background: var(--page);
        }

        /* ── Left Hero Panel ── */
        .onb-hero {
            background: radial-gradient(circle at top left, #1e5aa3 0, #0f3473 42%, #071a3d 100%);
            color: #fff;
            padding: 56px 52px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 100vh;
        }
        /* Force white logo inside hero */
        .onb-hero .brand-logo-blue  { display: none; }
        .onb-hero .brand-logo-white { display: block; }
        .onb-hero-steps {
            display: grid;
            gap: 14px;
            margin-top: 28px;
        }
        .onb-hero-step {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            border-radius: 14px;
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.10);
            transition: background .2s, border-color .2s;
        }
        .onb-hero-step.active {
            background: rgba(255,255,255,.14);
            border-color: rgba(255,255,255,.22);
        }
        .onb-hero-step.done {
            background: rgba(22,163,74,.15);
            border-color: rgba(22,163,74,.25);
        }
        .onb-step-num {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 13px;
            font-weight: 800;
            flex-shrink: 0;
            border: 2px solid rgba(255,255,255,.25);
            background: rgba(255,255,255,.08);
            color: rgba(255,255,255,.6);
        }
        .onb-hero-step.active .onb-step-num {
            background: #fff;
            border-color: #fff;
            color: var(--brand);
        }
        .onb-hero-step.done .onb-step-num {
            background: #16a34a;
            border-color: #16a34a;
            color: #fff;
        }
        .onb-step-text strong {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
        }
        .onb-step-text span {
            font-size: 12px;
            color: rgba(255,255,255,.55);
        }
        .onb-hero-footer {
            font-size: 12px;
            color: rgba(255,255,255,.4);
        }

        /* ── Right Form Panel ── */
        .onb-main {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 56px 40px;
            min-height: 100vh;
        }
        .onb-form-wrap {
            width: min(520px, 100%);
        }
        .onb-step-header {
            margin-bottom: 28px;
        }
        .onb-step-header h2 {
            margin: 0 0 6px;
            font-size: 24px;
            font-weight: 800;
            color: var(--navy);
            font-family: var(--font-display);
            letter-spacing: -0.02em;
        }
        .onb-step-header p {
            margin: 0;
            font-size: 14px;
            color: var(--muted);
            line-height: 1.65;
        }

        /* ── Size cards ── */
        .size-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin-top: 16px;
        }
        .size-option { display: block; cursor: pointer; position: relative; }
        .size-option input[type="radio"] {
            position: absolute; opacity: 0; pointer-events: none; width: 0; height: 0;
        }
        .size-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            border: 2px solid var(--line);
            border-radius: 12px;
            padding: 18px 6px;
            text-align: center;
            background: #fff;
            font-size: 13px;
            font-weight: 700;
            color: #475569;
            transition: border-color .15s, box-shadow .15s, color .15s, background .15s;
            min-height: 68px;
        }
        .size-option input[type="radio"]:checked + .size-card {
            border-color: var(--brand);
            background: var(--brand-soft);
            box-shadow: 0 0 0 3px rgba(15,52,115,.10);
            color: var(--brand);
        }
        .size-option:hover .size-card { border-color: var(--brand-line); }

        /* ── Wizard nav ── */
        .onb-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 36px;
            padding-top: 24px;
            border-top: 1px solid var(--line);
        }

        /* ── Progress dots ── */
        .onb-dots {
            display: flex;
            gap: 6px;
            align-items: center;
        }
        .onb-dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: var(--line);
            transition: background .2s, width .2s;
        }
        .onb-dot.active {
            background: var(--brand);
            width: 18px;
        }
        .onb-dot.done { background: #16a34a; }

        /* ── Flash error ── */
        .flash-error {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 12px 14px; background: #fef2f2;
            border: 1px solid #fecaca; border-radius: 10px;
            color: #991b1b; font-size: 13px; margin-bottom: 20px; line-height: 1.5;
        }

        /* ── Input number styling ── */
        .onb-number-wrap {
            display: flex;
            align-items: center;
            border: 1px solid var(--line);
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
        }
        .onb-number-wrap:focus-within {
            border-color: var(--brand);
            outline: 3px solid rgba(15,52,115,.12);
        }
        .onb-number-btn {
            width: 40px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            border: none;
            cursor: pointer;
            font-size: 18px;
            font-weight: 700;
            color: var(--muted);
            flex-shrink: 0;
            transition: background .12s, color .12s;
            user-select: none;
        }
        .onb-number-btn:hover { background: var(--brand-soft); color: var(--brand); }
        .onb-number-input {
            flex: 1;
            border: none !important;
            outline: none !important;
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            color: var(--navy);
            background: transparent;
            padding: 0;
            min-width: 0;
        }
        .onb-number-divider { width: 1px; height: 24px; background: var(--line); flex-shrink: 0; }

        @media (max-width: 860px) {
            .onb-page { grid-template-columns: 1fr; }
            .onb-hero { display: none; }
            .onb-main { padding: 32px 20px; }
            .size-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 480px) {
            .size-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
<div class="onb-page">

    {{-- ── Left Hero Panel ── --}}
    <aside class="onb-hero">
        <div>
            @include('payflow.partials.brand')
            <div style="margin-top:28px;">
                <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:rgba(255,255,255,.45); margin-bottom:10px;">Setup Workspace</div>
                <h1 style="margin:0 0 8px; font-size:28px; font-weight:800; line-height:1.2; font-family:var(--font-display); letter-spacing:-0.02em;">
                    Halo, {{ $user->name }}! 👋
                </h1>
                <p style="margin:0; font-size:15px; color:rgba(255,255,255,.65); line-height:1.7;">
                    Lengkapi 3 langkah berikut untuk mulai menggunakan PaySync di perusahaan Anda.
                </p>
            </div>

            <div class="onb-hero-steps" x-data="onboardingWizard()" x-init="init()">
                @foreach([
                    ['Info Perusahaan', 'Nama & industri perusahaan'],
                    ['Ukuran',          'Jumlah karyawan'],
                    ['Konfigurasi Payroll', 'Cut-off & tanggal gajian'],
                ] as $i => [$title, $desc])
                <div class="onb-hero-step"
                     :class="{ active: currentStep === {{ $i+1 }}, done: currentStep > {{ $i+1 }} }">
                    <div class="onb-step-num">
                        <template x-if="currentStep > {{ $i+1 }}">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        </template>
                        <template x-if="currentStep <= {{ $i+1 }}">
                            <span>{{ $i+1 }}</span>
                        </template>
                    </div>
                    <div class="onb-step-text">
                        <strong>{{ $title }}</strong>
                        <span>{{ $desc }}</span>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="onb-hero-footer" style="margin-top:40px;">
                © {{ date('Y') }} PaySync · Semua data terenkripsi
            </div>
        </div>
    </aside>

    {{-- ── Right Form Panel ── --}}
    <main class="onb-main">
        <div
            class="onb-form-wrap"
            x-data="onboardingWizard()"
            x-init="init()"
        >
            {{-- Mobile brand --}}
            <div style="display:none; margin-bottom:24px;" class="mobile-brand">
                @include('payflow.partials.brand')
            </div>

            @if (session('error'))
            <div class="flash-error" role="alert">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0; margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ session('error') }}
            </div>
            @endif

            <form x-ref="form" method="POST" action="{{ route('onboarding.store') }}" novalidate>
                @csrf

                {{-- ══ STEP 1: Info Perusahaan ══ --}}
                <section x-show="currentStep === 1" x-transition>
                    <div class="onb-step-header">
                        <h2>Informasi Perusahaan</h2>
                        <p>Masukkan identitas dasar perusahaan Anda sebagai workspace di PaySync.</p>
                    </div>

                    <div style="display:grid; gap:16px;">
                        <div class="field">
                            <label for="input-name">
                                Nama Perusahaan <span style="color:var(--red);">*</span>
                            </label>
                            <input
                                id="input-name"
                                class="input @error('name') input-error @enderror"
                                type="text"
                                name="name"
                                x-model="formData.name"
                                maxlength="150"
                                autocomplete="organization"
                                placeholder="PT Nusantara Jaya"
                                style="font-size:15px; padding:13px 14px;"
                            >
                            <span class="field-error" x-show="errors.name" x-text="errors.name" x-cloak role="alert"></span>
                            @error('name')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="field">
                            <label for="input-industry">
                                Industri <span class="muted" style="font-weight:400;">(opsional)</span>
                            </label>
                            <input
                                id="input-industry"
                                class="input @error('industry') input-error @enderror"
                                type="text"
                                name="industry"
                                x-model="formData.industry"
                                maxlength="100"
                                placeholder="Teknologi, Manufaktur, Jasa..."
                                style="font-size:15px; padding:13px 14px;"
                            >
                            @error('industry')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </section>

                {{-- ══ STEP 2: Ukuran ══ --}}
                <section x-show="currentStep === 2" x-transition x-cloak>
                    <div class="onb-step-header">
                        <h2>Ukuran Perusahaan</h2>
                        <p>Pilih jumlah karyawan yang paling mendekati kondisi perusahaan Anda saat ini.</p>
                    </div>

                    <div class="size-grid" role="radiogroup" aria-label="Ukuran perusahaan">
                        @foreach ($companySizes as $size)
                        <label class="size-option">
                            <input type="radio" name="size" value="{{ $size }}"
                                x-model="formData.size" aria-label="{{ $size }} karyawan">
                            <span class="size-card">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="opacity:.5;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                {{ $size }}
                            </span>
                        </label>
                        @endforeach
                    </div>

                    <span class="field-error" x-show="errors.size" x-text="errors.size" x-cloak
                          role="alert" style="display:block; margin-top:12px;"></span>
                    @error('size')<span class="field-error" role="alert" style="display:block; margin-top:12px;">{{ $message }}</span>@enderror
                </section>

                {{-- ══ STEP 3: Konfigurasi Payroll ══ --}}
                <section x-show="currentStep === 3" x-transition x-cloak>
                    <div class="onb-step-header">
                        <h2>Konfigurasi Payroll</h2>
                        <p>Tentukan batas perhitungan dan tanggal pembayaran gaji setiap bulannya.</p>
                    </div>

                    <div style="display:grid; gap:20px;">
                        {{-- Cut-off --}}
                        <div class="field">
                            <label>Payroll Cut-off <span style="color:var(--red);">*</span></label>
                            <div style="display:flex; align-items:center; gap:12px; margin-top:4px;">
                                <div class="onb-number-wrap" style="max-width:180px;">
                                    <button type="button" class="onb-number-btn"
                                        @click="formData.payroll_cut_off = Math.max(1, formData.payroll_cut_off - 1)">−</button>
                                    <div class="onb-number-divider"></div>
                                    <input type="number" name="payroll_cut_off" class="onb-number-input"
                                        x-model.number="formData.payroll_cut_off" min="1" max="28" inputmode="numeric">
                                    <div class="onb-number-divider"></div>
                                    <button type="button" class="onb-number-btn"
                                        @click="formData.payroll_cut_off = Math.min(28, formData.payroll_cut_off + 1)">+</button>
                                </div>
                                <div>
                                    <div style="font-size:13px; font-weight:600; color:var(--navy);">
                                        Tanggal <span x-text="formData.payroll_cut_off"></span> setiap bulan
                                    </div>
                                    <div class="muted" style="font-size:12px;">Antara tanggal 1–28</div>
                                </div>
                            </div>
                            <span class="field-error" x-show="errors.payroll_cut_off" x-text="errors.payroll_cut_off" x-cloak role="alert"></span>
                            @error('payroll_cut_off')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        {{-- Pay date --}}
                        <div class="field">
                            <label>Tanggal Pembayaran Gaji <span style="color:var(--red);">*</span></label>
                            <div style="display:flex; align-items:center; gap:12px; margin-top:4px;">
                                <div class="onb-number-wrap" style="max-width:180px;">
                                    <button type="button" class="onb-number-btn"
                                        @click="formData.pay_date = Math.max(1, formData.pay_date - 1)">−</button>
                                    <div class="onb-number-divider"></div>
                                    <input type="number" name="pay_date" class="onb-number-input"
                                        x-model.number="formData.pay_date" min="1" max="31" inputmode="numeric">
                                    <div class="onb-number-divider"></div>
                                    <button type="button" class="onb-number-btn"
                                        @click="formData.pay_date = Math.min(31, formData.pay_date + 1)">+</button>
                                </div>
                                <div>
                                    <div style="font-size:13px; font-weight:600; color:var(--navy);">
                                        Tanggal <span x-text="formData.pay_date"></span> setiap bulan
                                    </div>
                                    <div class="muted" style="font-size:12px;">Antara tanggal 1–31</div>
                                </div>
                            </div>
                            <span class="field-error" x-show="errors.pay_date" x-text="errors.pay_date" x-cloak role="alert"></span>
                            @error('pay_date')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        {{-- Summary box --}}
                        <div style="padding:16px; background:var(--brand-soft); border:1px solid var(--brand-line); border-radius:12px;">
                            <div style="font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--brand); margin-bottom:10px;">Ringkasan</div>
                            <div style="display:grid; gap:6px; font-size:13px; color:var(--navy);">
                                <div style="display:flex; justify-content:space-between;">
                                    <span class="muted">Nama perusahaan</span>
                                    <strong x-text="formData.name || '—'"></strong>
                                </div>
                                <div style="display:flex; justify-content:space-between;">
                                    <span class="muted">Ukuran</span>
                                    <strong x-text="formData.size || '—'"></strong>
                                </div>
                                <div style="display:flex; justify-content:space-between;">
                                    <span class="muted">Cut-off</span>
                                    <strong>Tanggal <span x-text="formData.payroll_cut_off"></span></strong>
                                </div>
                                <div style="display:flex; justify-content:space-between;">
                                    <span class="muted">Hari gajian</span>
                                    <strong>Tanggal <span x-text="formData.pay_date"></span></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ══ Navigation ══ --}}
                <div class="onb-nav">
                    <button type="button" class="btn btn-secondary"
                        x-show="currentStep > 1" x-cloak @click="prev()">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                        Kembali
                    </button>
                    <span x-show="currentStep === 1"></span>

                    {{-- Progress dots --}}
                    <div class="onb-dots">
                        <div class="onb-dot" :class="{ active: currentStep===1, done: currentStep>1 }"></div>
                        <div class="onb-dot" :class="{ active: currentStep===2, done: currentStep>2 }"></div>
                        <div class="onb-dot" :class="{ active: currentStep===3, done: currentStep>3 }"></div>
                    </div>

                    <button type="button" class="btn btn-primary" style="min-width:120px;"
                        x-show="currentStep < 3" @click="next()">
                        Lanjut
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>

                    <button type="button" class="btn btn-primary" style="min-width:160px; background:#16a34a; border-color:#16a34a;"
                        x-show="currentStep === 3" x-cloak @click="submit()">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Selesaikan Setup
                    </button>
                </div>

            </form>
        </div>
    </main>
</div>

<script>
    function onboardingWizard() {
        return {
            currentStep: {{ $restoreStep }},
            steps: ['Info Perusahaan', 'Ukuran', 'Payroll'],
            formData: @js($initialData),
            errors: {},

            init() {},

            validateStep() {
                this.errors = {};
                if (this.currentStep === 1) {
                    const name = String(this.formData.name || '').trim();
                    if (!name) this.errors.name = 'Nama perusahaan wajib diisi.';
                    else if (name.length > 150) this.errors.name = 'Maksimal 150 karakter.';
                }
                if (this.currentStep === 2) {
                    if (!this.formData.size) this.errors.size = 'Pilih ukuran perusahaan terlebih dahulu.';
                }
                if (this.currentStep === 3) {
                    const c = Number(this.formData.payroll_cut_off);
                    const p = Number(this.formData.pay_date);
                    if (!Number.isInteger(c) || c < 1 || c > 28)
                        this.errors.payroll_cut_off = 'Cut-off harus antara 1 dan 28.';
                    if (!Number.isInteger(p) || p < 1 || p > 31)
                        this.errors.pay_date = 'Tanggal bayar harus antara 1 dan 31.';
                }
                return Object.keys(this.errors).length === 0;
            },

            next() {
                if (this.validateStep() && this.currentStep < 3) this.currentStep++;
            },
            prev() {
                this.errors = {};
                if (this.currentStep > 1) this.currentStep--;
            },
            submit() {
                if (this.validateStep()) this.$refs.form.submit();
            },
        };
    }
</script>
<script defer src="{{ asset('vendor/alpinejs/cdn.min.js') }}"></script>
</body>
</html>
