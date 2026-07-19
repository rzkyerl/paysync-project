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
    // When server validation fails, restore to the step that had the error
    $restoreStep = 1;
    if ($errors->hasAny(['size']))              $restoreStep = 2;
    if ($errors->hasAny(['payroll_cut_off', 'pay_date'])) $restoreStep = 3;
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setup Workspace - PaySync</title>
    @include('payflow.partials.styles')
    <style>
        /* ── Alpine cloak ───────────────────────────────── */
        [x-cloak] { display: none !important; }

        /* ── Onboarding shell ───────────────────────────── */
        .onboarding-shell {
            min-height: 100vh;
            padding: 40px 20px 60px;
            background: var(--page);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .onboarding-card {
            width: min(720px, 100%);
        }

        /* ── Step indicator ─────────────────────────────── */
        .step-indicator {
            display: flex;
            align-items: center;
            gap: 0;
            margin: 24px 0 28px;
        }
        .step-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: #94a3b8;
            flex: 1;
            position: relative;
        }
        .step-item:not(:last-child)::after {
            content: '';
            flex: 1;
            height: 2px;
            background: #e2e8f0;
            margin: 0 10px;
            border-radius: 999px;
        }
        .step-item.done:not(:last-child)::after {
            background: #16a34a;
        }
        .step-item.active { color: var(--navy); font-weight: 700; }
        .step-item.done   { color: #16a34a; font-weight: 600; }
        .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-weight: 700;
            font-size: 13px;
            border: 2px solid #cbd5e1;
            background: #fff;
            flex-shrink: 0;
            transition: background .2s, border-color .2s, color .2s;
        }
        .step-item.active .step-number {
            background: var(--brand);
            border-color: var(--brand);
            color: #fff;
        }
        .step-item.done .step-number {
            background: #16a34a;
            border-color: #16a34a;
            color: #fff;
        }

        /* ── Size card grid ─────────────────────────────── */
        .size-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin-top: 22px;
        }
        .size-option {
            display: block;
            cursor: pointer;
            position: relative;
        }
        .size-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
            width: 0;
            height: 0;
        }
        .size-card {
            display: block;
            border: 2px solid var(--line);
            border-radius: 12px;
            padding: 20px 8px;
            text-align: center;
            background: #fff;
            font-size: 14px;
            font-weight: 600;
            color: #475569;
            transition: border-color .15s, box-shadow .15s, color .15s;
        }
        .size-option input[type="radio"]:checked + .size-card {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(15, 52, 115, .12);
            color: var(--brand);
        }
        .size-option:hover .size-card {
            border-color: var(--brand-line);
        }

        /* ── Wizard actions bar ─────────────────────────── */
        .wizard-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid var(--line);
        }

        /* ── Flash error ────────────────────────────────── */
        .flash-error {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 14px 16px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            color: #991b1b;
            font-size: 14px;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        /* ── Responsive ─────────────────────────────────── */
        @media (max-width: 600px) {
            .size-grid { grid-template-columns: repeat(2, 1fr); }
            .step-label { display: none; }
            .grid-2 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<main class="onboarding-shell">
    {{--
        Task 6: Standalone layout (no sidebar, no topbar)
        Task 8: Alpine.js component x-data="onboardingWizard()"
    --}}
    <div
        class="card onboarding-card"
        x-data="onboardingWizard()"
        x-init="init()"
    >
        {{-- Header: logo + badge --}}
        <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:4px;">
            @include('payflow.partials.brand')
            <span class="badge badge-blue">Setup Workspace</span>
        </div>

        {{-- Task 6: Greeting with user name --}}
        <div style="margin-top: 20px;">
            <h1 style="margin:0 0 4px; font-size:26px; color:var(--navy);">Halo, {{ $user->name }}!</h1>
            <p class="muted" style="margin:0;">Lengkapi informasi perusahaan untuk mulai menggunakan PaySync.</p>
        </div>

        {{--
            Task 7: Step indicator with active/done/upcoming states
            Steps: Info Perusahaan, Ukuran, Payroll
        --}}
        <nav class="step-indicator" aria-label="Progress onboarding">
            <template x-for="(label, index) in steps" :key="index">
                <div
                    class="step-item"
                    :class="{
                        active: currentStep === index + 1,
                        done:   currentStep > index + 1
                    }"
                    :aria-current="currentStep === index + 1 ? 'step' : undefined"
                >
                    <span
                        class="step-number"
                        :aria-label="currentStep > index + 1 ? 'Selesai' : 'Langkah ' + (index + 1)"
                    >
                        <template x-if="currentStep > index + 1">
                            {{-- Checkmark SVG for completed steps --}}
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </template>
                        <template x-if="currentStep <= index + 1">
                            <span x-text="index + 1"></span>
                        </template>
                    </span>
                    <span class="step-label" x-text="label"></span>
                </div>
            </template>
        </nav>

        {{--
            Task 13: Flash error from DB transaction failure (session('error'))
        --}}
        @if (session('error'))
            <div class="flash-error" role="alert">
                <svg class="icon icon-sm" style="flex-shrink:0; margin-top:1px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        {{--
            Task 8: Form wrapping all steps — single POST submit at step 3
            Task 11: Form action = route('onboarding.store'), method POST + CSRF
            Task 12: formData preserved in Alpine state (not reset on prev())
        --}}
        <form
            x-ref="form"
            method="POST"
            action="{{ route('onboarding.store') }}"
            novalidate
        >
            @csrf

            {{-- ── STEP 1: Info Perusahaan ────────────────────────────────────── --}}
            {{--
                Task 9: name (required, max 150, pre-filled) + industry (optional)
                Task 13: Server-side @error shown below each field
            --}}
            <section x-show="currentStep === 1" x-transition>
                <h2 style="margin:0 0 4px; font-size:18px; color:var(--navy);">Informasi Perusahaan</h2>
                <p class="muted" style="margin:0 0 20px; font-size:14px;">Masukkan identitas dasar workspace Anda.</p>

                <div class="grid grid-2">
                    <div class="field">
                        <label for="input-name">
                            Nama perusahaan
                            <b style="color:var(--red)" aria-hidden="true">*</b>
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
                        >
                        {{-- Task 9: client-side error --}}
                        <span
                            class="field-error"
                            x-show="errors.name"
                            x-text="errors.name"
                            x-cloak
                            role="alert"
                        ></span>
                        {{-- Task 13: server-side error --}}
                        @error('name')
                            <span class="field-error" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="input-industry">
                            Industri
                            <small class="muted" style="font-weight:400;">(opsional)</small>
                        </label>
                        <input
                            id="input-industry"
                            class="input @error('industry') input-error @enderror"
                            type="text"
                            name="industry"
                            x-model="formData.industry"
                            maxlength="100"
                            placeholder="Teknologi, manufaktur, jasa..."
                        >
                        @error('industry')
                            <span class="field-error" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- ── STEP 2: Ukuran Perusahaan ──────────────────────────────────── --}}
            {{--
                Task 10: 5 size cards, pre-selected from $prefill['company_size'],
                         validation: must select before next()
            --}}
            <section x-show="currentStep === 2" x-transition x-cloak>
                <h2 style="margin:0 0 4px; font-size:18px; color:var(--navy);">Ukuran Perusahaan</h2>
                <p class="muted" style="margin:0; font-size:14px;">Pilih jumlah karyawan yang paling sesuai.</p>

                <div class="size-grid" role="radiogroup" aria-label="Ukuran perusahaan">
                    @foreach ($companySizes as $size)
                        <label class="size-option">
                            <input
                                type="radio"
                                name="size"
                                value="{{ $size }}"
                                x-model="formData.size"
                                aria-label="{{ $size }} karyawan"
                            >
                            <span class="size-card">{{ $size }}</span>
                        </label>
                    @endforeach
                </div>

                {{-- Task 10 & 13: validation errors --}}
                <span
                    class="field-error"
                    x-show="errors.size"
                    x-text="errors.size"
                    x-cloak
                    role="alert"
                    style="display:block; margin-top:10px;"
                ></span>
                @error('size')
                    <span class="field-error" role="alert" style="display:block; margin-top:10px;">{{ $message }}</span>
                @enderror
            </section>

            {{-- ── STEP 3: Konfigurasi Payroll ────────────────────────────────── --}}
            {{--
                Task 11: payroll_cut_off (default 25, 1-28) + pay_date (default 30, 1-31)
                         "Selesaikan Setup" button triggers submit()
            --}}
            <section x-show="currentStep === 3" x-transition x-cloak>
                <h2 style="margin:0 0 4px; font-size:18px; color:var(--navy);">Konfigurasi Payroll</h2>
                <p class="muted" style="margin:0 0 20px; font-size:14px;">Tentukan batas perhitungan dan tanggal pembayaran gaji.</p>

                <div class="grid grid-2">
                    <div class="field">
                        <label for="input-payroll-cutoff">
                            Payroll cut-off
                            <b style="color:var(--red)" aria-hidden="true">*</b>
                        </label>
                        <input
                            id="input-payroll-cutoff"
                            class="input @error('payroll_cut_off') input-error @enderror"
                            type="number"
                            name="payroll_cut_off"
                            x-model.number="formData.payroll_cut_off"
                            min="1"
                            max="28"
                            inputmode="numeric"
                        >
                        <small class="muted">Tanggal 1 sampai 28 setiap bulan.</small>
                        <span
                            class="field-error"
                            x-show="errors.payroll_cut_off"
                            x-text="errors.payroll_cut_off"
                            x-cloak
                            role="alert"
                        ></span>
                        @error('payroll_cut_off')
                            <span class="field-error" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="input-pay-date">
                            Tanggal pembayaran
                            <b style="color:var(--red)" aria-hidden="true">*</b>
                        </label>
                        <input
                            id="input-pay-date"
                            class="input @error('pay_date') input-error @enderror"
                            type="number"
                            name="pay_date"
                            x-model.number="formData.pay_date"
                            min="1"
                            max="31"
                            inputmode="numeric"
                        >
                        <small class="muted">Tanggal 1 sampai 31 setiap bulan.</small>
                        <span
                            class="field-error"
                            x-show="errors.pay_date"
                            x-text="errors.pay_date"
                            x-cloak
                            role="alert"
                        ></span>
                        @error('pay_date')
                            <span class="field-error" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </section>

            {{--
                Task 11 & 12: Navigation buttons
                - "Kembali" only decrements currentStep (Task 12: formData preserved)
                - "Lanjut" calls next() which validates first
                - "Selesaikan Setup" calls submit() on step 3
            --}}
            <div class="wizard-actions">
                {{-- Task 12: Back button only decrements step, no data reset --}}
                <button
                    type="button"
                    class="btn btn-secondary"
                    x-show="currentStep > 1"
                    x-cloak
                    @click="prev()"
                >
                    ← Kembali
                </button>
                {{-- Spacer when on step 1 (no back button) --}}
                <span x-show="currentStep === 1"></span>

                {{-- Next button for steps 1 & 2 --}}
                <button
                    type="button"
                    class="btn btn-primary"
                    x-show="currentStep < 3"
                    @click="next()"
                >
                    Lanjut →
                </button>

                {{-- Task 11: Submit button on step 3 --}}
                <button
                    type="button"
                    class="btn btn-primary"
                    x-show="currentStep === 3"
                    x-cloak
                    @click="submit()"
                >
                    Selesaikan Setup
                </button>
            </div>
        </form>
    </div>
</main>

{{--
    Task 8: Alpine.js onboardingWizard() component
    Script is defined BEFORE the Alpine.js loader (Alpine picks it up at init time)
--}}
<script>
    function onboardingWizard() {
        return {
            // Task 8: currentStep property
            currentStep: {{ $restoreStep }},

            steps: ['Info Perusahaan', 'Ukuran', 'Payroll'],

            // Task 8: formData pre-filled from Blade variables
            // Task 12: all data lives here — prev() never resets it
            formData: @js($initialData),

            // Task 8: errors object for client-side validation messages
            errors: {},

            // Called by x-init — no-op currently, hook available for future use
            init() {},

            // Task 7: Expose step state helpers for template bindings
            stepState(index) {
                const step = index + 1;
                if (this.currentStep === step)   return 'active';
                if (this.currentStep > step)     return 'done';
                return 'upcoming';
            },

            // Task 9 / 10 / 11: client-side validation per step
            validateStep() {
                this.errors = {};

                if (this.currentStep === 1) {
                    const name = String(this.formData.name || '').trim();
                    if (!name) {
                        this.errors.name = 'Nama perusahaan wajib diisi.';
                    } else if (name.length > 150) {
                        this.errors.name = 'Nama perusahaan maksimal 150 karakter.';
                    }
                }

                if (this.currentStep === 2) {
                    if (!this.formData.size) {
                        this.errors.size = 'Pilih ukuran perusahaan terlebih dahulu.';
                    }
                }

                if (this.currentStep === 3) {
                    const cutOff  = Number(this.formData.payroll_cut_off);
                    const payDate = Number(this.formData.pay_date);

                    if (!Number.isInteger(cutOff) || cutOff < 1 || cutOff > 28) {
                        this.errors.payroll_cut_off = 'Cut-off harus berupa angka antara 1 dan 28.';
                    }
                    if (!Number.isInteger(payDate) || payDate < 1 || payDate > 31) {
                        this.errors.pay_date = 'Tanggal bayar harus berupa angka antara 1 dan 31.';
                    }
                }

                return Object.keys(this.errors).length === 0;
            },

            // Task 7 / 9 / 10: advance to next step after validation
            next() {
                if (this.validateStep() && this.currentStep < 3) {
                    this.currentStep++;
                }
            },

            // Task 12: go back — ONLY decrements currentStep, formData untouched
            prev() {
                this.errors = {};
                if (this.currentStep > 1) {
                    this.currentStep--;
                }
            },

            // Task 11: submit form to POST /onboarding
            submit() {
                if (this.validateStep()) {
                    this.$refs.form.submit();
                }
            },
        };
    }
</script>

{{-- Alpine.js — must be loaded after the inline script above --}}
<script defer src="{{ asset('vendor/alpinejs/cdn.min.js') }}"></script>
</body>
</html>
