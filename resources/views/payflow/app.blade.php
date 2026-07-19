@php
    $isDemoUser = $isDemoUser ?? (auth()->user()?->isDemoUser() ?? false);
    $companyName = $companyName ?? auth()->user()?->company?->name ?? 'Workspace';
    $isEmpty = $isEmpty ?? auth()->user()?->company_id === null;
    $role = auth()->user()?->role;
    $isSuperAdminViewing = $isSuperAdminViewing ?? ($role === 'super_admin' && ! in_array($page, ['settings', 'team'], true));
    $titles = [
        'dashboard-hr'      => ['Dashboard HR',             'Ringkasan pekerjaan HR hari ini untuk periode payroll aktif.'],
        'dashboard-finance' => ['Dashboard Finance',        'Approval payroll, status transfer, dan rekonsiliasi pembayaran.'],
        'dashboard-employee'=> ['Dashboard Employee',       'Portal personal untuk slip gaji dan kehadiran sendiri.'],
        'employees'         => ['Daftar Karyawan',          'Kelola data karyawan, status kerja, dan kelengkapan rekening.'],
        'employee-create'   => ['Tambah Karyawan',          'Isi formulir untuk menambahkan karyawan baru ke sistem.'],
        'employee-edit'     => ['Edit Karyawan',            'Perbarui data karyawan yang sudah terdaftar.'],
        'employee-show'     => ['Detail Karyawan',          'Lihat detail lengkap data karyawan.'],
        'attendance'        => ['Kehadiran',                'Import CSV, validasi anomali, dan kunci periode payroll.'],
        'payroll'           => ['Proses Payroll',           'Workspace kalkulasi payroll, review anomali, dan finalisasi.'],
        'approval'          => ['Approval Queue',           'Review payroll yang dikirim HR sebelum disetujui Finance.'],
        'payslips'          => ['Slip Gaji Digital',        'Publikasi dan preview slip gaji formal untuk karyawan.'],
        'disbursement'      => ['Batch Transfer',           'Kelola batch penyaluran gaji dan status pembayaran.'],
        'reconciliation'    => ['Rekonsiliasi',             'Cocokkan payroll net pay dengan nilai transfer berhasil.'],
        'reports'           => ['Reports Hub',              'Laporan payroll, attendance, transfer, rekonsiliasi, dan audit.'],
        'settings'          => ['Pengaturan Perusahaan',    'Profil, payroll, pembayaran, notifikasi, dan data operasional.'],
        'audit'             => ['Audit Log',                'Riwayat perubahan read-only untuk modul penting.'],
    ];
    [$title, $description] = $titles[$page] ?? [$page, ''];
    $canSeeHr = in_array($role, ['super_admin', 'hr_manager'], true);
    $canSeeFinance = in_array($role, ['super_admin', 'finance_manager'], true);
    $canSeeEmployee = in_array($role, ['super_admin', 'employee'], true);
    $nav = [];
    if ($canSeeHr || $canSeeFinance || $canSeeEmployee) {
        $nav['Overview'] = array_values(array_filter([
            $canSeeHr ? ['dashboard-hr', 'Dashboard HR'] : null,
            $canSeeFinance ? ['dashboard-finance', 'Dashboard Finance'] : null,
            $canSeeEmployee ? ['dashboard-employee', $role === 'employee' ? 'Dashboard Saya' : 'Dashboard Employee'] : null,
        ]));
    }
    if ($canSeeHr) {
        $nav['People'] = [['employees', 'Karyawan'], ['employees', 'Organisasi'], ['employees', 'Rekening Bank']];
        $nav['Time Management'] = [['attendance', 'Kehadiran'], ['attendance', 'Lembur'], ['attendance', 'Cuti']];
        $nav['Payroll'] = [['payroll', 'Proses Payroll'], ['payroll', 'Komponen Gaji']];
    }
    if ($canSeeFinance) {
        $nav['Payroll'] = array_merge($nav['Payroll'] ?? [], [['approval', 'Persetujuan'], ['payslips', 'Slip Gaji']]);
        $nav['Disbursement'] = [['disbursement', 'Batch Transfer'], ['reconciliation', 'Rekonsiliasi']];
    }
    if ($canSeeEmployee && ! $canSeeHr && ! $canSeeFinance) {
        $nav['Time Management'] = [['attendance', 'Kehadiran Saya']];
        $nav['Payroll'] = [['payslips', 'Slip Gaji']];
    }
    if ($canSeeHr || $canSeeFinance) {
        $nav['Reports'] = [['reports', 'Laporan']];
    }
    if ($role === 'super_admin') {
        $nav['Team'] = [['team', 'Manajemen Tim']];
        $nav['System'] = [['settings', 'Pengaturan Perusahaan'], ['audit', 'Audit Log']];
    }
    $navIcons = [
        'dashboard-hr' => 'dashboard',
        'dashboard-finance' => 'dashboard',
        'dashboard-employee' => 'dashboard',
        'employees' => 'users',
        'attendance' => 'calendar',
        'payroll' => 'payroll',
        'approval' => 'approval',
        'payslips' => 'file',
        'disbursement' => 'bank',
        'reconciliation' => 'link',
        'reports' => 'report',
        'settings' => 'settings',
        'audit' => 'shield',
        'team' => 'users',
    ];
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - PaySync</title>
    @include('payflow.partials.styles')
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar" x-data :class="{ 'collapsed': $store.sidebar.collapsed }">
            <button class="sidebar-toggle" @click="$store.sidebar.toggle()" title="Toggle sidebar">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <line x1="3" y1="12" x2="21" y2="12"/>
                    <line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>
            @include('payflow.partials.brand')
            <div class="workspace">
                <strong>{{ $companyName }}</strong>
                <div><span class="badge" style="margin-top:8px; background:rgba(255,255,255,.08); color:#d9e7ff; border-color:rgba(255,255,255,.12);">Company Workspace</span></div>
            </div>
    @php
        // Build route map so nav links always use the correct registered routes.
        // Pages without a dedicated named route fall back to /app/{slug}.
        $navRoutes = [
            'dashboard-hr'       => route('dashboard.hr'),
            'dashboard-finance'  => route('dashboard.finance'),
            'dashboard-employee' => route('dashboard.employee'),
            'employees'          => route('employees.index'),
            'payroll'            => route('payroll.index'),
        ];
    @endphp
    @foreach ($nav as $group => $items)
        <div class="nav-group">
            <div class="nav-title">{{ $group }}</div>
            @foreach ($items as [$slug, $label])
                <a class="nav-link {{ $page === $slug ? 'active' : '' }}"
                   href="{{ $navRoutes[$slug] ?? url('/app/' . $slug) }}"
                   title="{{ $label }}">
                    @include('payflow.partials.icon', ['name' => $navIcons[$slug] ?? 'dashboard', 'class' => 'icon icon-sm'])
                    <span class="nav-label">{{ $label }}</span>
                </a>
            @endforeach
        </div>
    @endforeach
            <div class="workspace" style="margin-top:24px;">
                <strong style="display:block; font-size:13px; color:#e2e8f0;">{{ auth()->user()?->name ?? 'Pengguna' }}</strong>
                <div class="muted" style="color:#64748b; font-size:12px; margin-top:2px;">{{ auth()->user()?->email }}</div>
                <form method="POST" action="{{ route('logout') }}" style="margin-top:10px;">
                    @csrf
                    <button type="submit" class="nav-link" style="width:100%; background:none; border:none; cursor:pointer; color:#f87171; text-align:left;">
                        @include('payflow.partials.icon', ['name' => 'logout', 'class' => 'icon icon-sm'])
                        <span class="nav-label">Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <main>
            <header class="topbar">
                <div><strong>PaySync</strong> <span class="muted">/ {{ $title }}</span></div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <label style="position:relative;">
                        <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#94a3b8;">@include('payflow.partials.icon', ['name' => 'search', 'class' => 'icon icon-sm'])</span>
                        <input class="input" style="width:220px; padding:8px 10px 8px 34px;" placeholder="Cari...">
                    </label>
                    <span class="badge badge-amber">@include('payflow.partials.icon', ['name' => 'bell', 'class' => 'icon icon-sm']) 3 Notifikasi</span>
                    <span class="badge">@include('payflow.partials.icon', ['name' => 'help', 'class' => 'icon icon-sm']) Help</span>
                </div>
            </header>
            <section class="content">
                <div class="page-head">
                    <div><h1>{{ $title }}</h1><p>{{ $description }}</p></div>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <span class="badge badge-blue">Periode Juli 2026</span>
                        @if (in_array($page, ['disbursement','reconciliation','payslips'], true))<span class="badge badge-blue">Payroll Operations</span>@endif
                        @if ($isSuperAdminViewing)
                            <span class="badge badge-amber" title="Hanya tersedia untuk Tim HR/Finance">Mode Pantau</span>
                        @else
                            <a class="btn btn-primary" href="/app/payroll">Proses Payroll</a>
                        @endif
                    </div>
                </div>

                @if ($isSuperAdminViewing)
                    <div class="card" style="margin-bottom:16px; padding:14px 16px; border-left:4px solid var(--amber);" role="status">
                        <strong>Mode Pantau</strong>
                        <span class="muted" style="margin-left:8px;">Anda sedang melihat data secara read-only. Aksi operasional hanya tersedia untuk Tim HR/Finance.</span>
                    </div>
                @endif

                @if ($page === 'dashboard-hr')
                    @include('payflow.pages.dashboard-hr')
                @elseif ($page === 'dashboard-finance')
                    @include('payflow.pages.dashboard-finance')
                @elseif ($page === 'dashboard-employee')
                    @include('payflow.pages.dashboard-employee')
                @elseif ($page === 'employees')
                    @include('payflow.pages.employees')
                @elseif ($page === 'employee-create')
                    @include('payflow.pages.employee-create')
                @elseif ($page === 'employee-edit')
                    @include('payflow.pages.employee-edit')
                @elseif ($page === 'employee-show')
                    @include('payflow.pages.employee-show')
                @elseif ($page === 'attendance')
                    @include('payflow.pages.attendance')
                @elseif ($page === 'payroll')
                    @include('payflow.pages.payroll')
                @elseif ($page === 'approval')
                    @include('payflow.pages.approval')
                @elseif ($page === 'payslips')
                    @include('payflow.pages.payslips')
                @elseif ($page === 'disbursement')
                    @include('payflow.pages.disbursement')
                @elseif ($page === 'reconciliation')
                    @include('payflow.pages.reconciliation')
                @elseif ($page === 'reports')
                    @include('payflow.pages.reports')
                @elseif ($page === 'settings')
                    @include('payflow.pages.settings')
                @elseif ($page === 'audit')
                    @include('payflow.pages.audit')
                @endif
            </section>
        </main>
    </div>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('sidebar', {
                collapsed: localStorage.getItem('sidebar-collapsed') === 'true',
                toggle() {
                    this.collapsed = !this.collapsed;
                    localStorage.setItem('sidebar-collapsed', this.collapsed);
                }
            });

            // 11.1 — Confirm dialog store
            Alpine.store('confirm', {
                open: false,
                title: '',
                message: '',
                actionUrl: '',
                actionMethod: 'DELETE',
                show(config) {
                    Object.assign(this, config, { open: true });
                },
                close() {
                    this.open = false;
                }
            });

            // 9.1 — Toast store
            Alpine.store('toast', {
                items: [],
                add(type, message, duration = 4000) {
                    const id = Date.now();
                    this.items.push({ id, type, message, duration });
                    if (this.items.length > 5) {
                        this.items.shift(); // remove oldest when cap exceeded
                    }
                    setTimeout(() => this.remove(id), duration);
                },
                remove(id) {
                    this.items = this.items.filter(t => t.id !== id);
                }
            });

            const pendingToast = sessionStorage.getItem('paysync-pending-toast');
            if (pendingToast) {
                sessionStorage.removeItem('paysync-pending-toast');
                const toast = JSON.parse(pendingToast);
                Alpine.store('toast').add(toast.type, toast.message);
            }
        });
    </script>
    <script defer src="{{ asset('vendor/alpinejs/cdn.min.js') }}"></script>

    {{-- 9.5 — Flash session integration: trigger toast from Laravel flash --}}
    @if(session('toast'))
    <meta name="paysync-toast" data-type="{{ session('toast.type') }}" data-message="{{ session('toast.message') }}">
    <script>
        document.addEventListener('alpine:init', () => {
            @php $t = session('toast') @endphp
            Alpine.store('toast').add('{{ $t["type"] }}', '{{ addslashes($t["message"]) }}');
        });
    </script>
    @endif

    {{-- 11.2 — Confirm dialog modal (11.4 keyboard shortcuts, 11.7 method-spoofing) --}}
    <div
        x-data
        x-cloak
        x-show="$store.confirm.open"
        x-effect="if ($store.confirm.open) $nextTick(() => $refs.cancelButton.focus())"
        @keydown.window.escape="$store.confirm.open && $store.confirm.close()"
        @keydown.window.enter="$store.confirm.open && $refs.confirmForm.requestSubmit()"
        class="modal-overlay"
        @click.self="$store.confirm.close()"
        role="dialog"
        aria-modal="true"
        :aria-label="$store.confirm.title"
        x-transition:enter="modal-fade-enter"
        x-transition:leave="modal-fade-leave"
    >
        <div class="modal-dialog">
            <h2 class="modal-title" x-text="$store.confirm.title"></h2>
            <p class="modal-message" x-text="$store.confirm.message"></p>
            {{-- 11.7: POST form with _method spoofing --}}
            <form x-ref="confirmForm" method="POST" :action="$store.confirm.actionUrl">
                @csrf
                <input type="hidden" name="_method" :value="$store.confirm.actionMethod">
                <div class="modal-actions">
                    <button x-ref="cancelButton" type="button" class="btn btn-secondary" @click="$store.confirm.close()" autofocus>Batal</button>
                    <button type="submit" class="btn btn-danger">Konfirmasi</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 9.2 — Toast container: fixed bottom-right, high z-index --}}
    <div
        x-data
        x-cloak
        style="position:fixed; bottom:24px; right:24px; z-index:9999; display:flex; flex-direction:column-reverse; align-items:flex-end; pointer-events:none;"
        aria-live="polite"
        aria-atomic="false"
    >
        <template x-for="toast in $store.toast.items" :key="toast.id">
            <div
                :class="'toast toast-' + toast.type"
                style="pointer-events:auto;"
                x-transition:enter="toast-enter"
                x-transition:leave="toast-leave"
                role="alert"
            >
                {{-- Icon --}}
                <svg class="toast-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <template x-if="toast.type === 'success'">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14M22 4 12 14.01l-3-3"/>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0zM12 9v4M12 17h.01"/>
                    </template>
                    <template x-if="toast.type === 'warning'">
                        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0zM12 9v4M12 17h.01"/>
                    </template>
                    <template x-if="toast.type === 'info'">
                        <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zM12 16v-4M12 8h.01"/>
                    </template>
                </svg>
                {{-- Message --}}
                <div class="toast-body" x-text="toast.message"></div>
                {{-- Dismiss button --}}
                <button class="toast-dismiss" @click="$store.toast.remove(toast.id)" aria-label="Tutup notifikasi" title="Tutup">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
                {{-- Auto-dismiss progress bar --}}
                <div class="toast-bar" :style="'--toast-duration:' + (toast.duration || 4000) + 'ms'"></div>
            </div>
        </template>
    </div>

    {{-- 19.4 — Intercept fetch responses for CSRF error 419 --}}
    <script>
        (function () {
            const originalFetch = window.fetch.bind(window);
            let sessionExpiryHandled = false;

            function handleSessionExpiry() {
                if (sessionExpiryHandled) return;
                sessionExpiryHandled = true;

                const showToast = () => Alpine.store('toast').add(
                    'warning',
                    'Sesi habis, halaman akan dimuat ulang',
                    2000
                );

                if (typeof Alpine !== 'undefined' && Alpine.store('toast')) {
                    showToast();
                } else {
                    document.addEventListener('alpine:init', showToast, { once: true });
                }

                setTimeout(() => window.location.reload(), 2000);
            }

            window.fetch = async function (...args) {
                const response = await originalFetch(...args);
                if (response.status === 419) {
                    handleSessionExpiry();
                }
                return response;
            };

            document.addEventListener('submit', async (event) => {
                const form = event.target;
                const method = (form.getAttribute('method') || 'GET').toUpperCase();

                if (!(form instanceof HTMLFormElement) || method === 'GET' || form.target) return;

                event.preventDefault();

                try {
                    const response = await window.fetch(form.action || window.location.href, {
                        method,
                        body: new FormData(form, event.submitter),
                        credentials: 'same-origin',
                        headers: { 'Accept': 'text/html' },
                    });

                    if (response.status === 419) return;

                    const html = await response.text();
                    const responseDocument = new DOMParser().parseFromString(html, 'text/html');
                    const toastMeta = responseDocument.querySelector('meta[name="paysync-toast"]');

                    if (response.redirected && toastMeta) {
                        sessionStorage.setItem('paysync-pending-toast', JSON.stringify({
                            type: toastMeta.dataset.type,
                            message: toastMeta.dataset.message,
                        }));
                        window.location.assign(response.url);
                        return;
                    }

                    if (response.redirected) {
                        window.history.replaceState({}, '', response.url);
                    }
                    document.open();
                    document.write(html);
                    document.close();
                } catch (error) {
                    // Preserve normal browser behaviour when fetch is unavailable/fails.
                    HTMLFormElement.prototype.submit.call(form);
                }
            });
        })();
    </script>
</body>
</html>
