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
        $nav['People'] = [['employees', 'Karyawan']];
        $nav['Time Management'] = [['attendance', 'Kehadiran']];
        $nav['Payroll'] = [['payroll', 'Proses Payroll']];
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
            <div class="sidebar-bottom">
                <div class="sidebar-version">PaySync v1.0</div>
            </div>
        </aside>

        <main x-data>
            <header class="topbar">
                <div class="topbar-left">
                    <button class="topbar-menu-btn" @click="$store.sidebar.toggle()" title="Toggle sidebar" aria-label="Toggle sidebar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                    <div class="topbar-breadcrumb">
                        <span class="topbar-brand">PaySync</span>
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:#cbd5e1;flex-shrink:0;"><polyline points="9 18 15 12 9 6"/></svg>
                        <span class="topbar-page">{{ $title }}</span>
                    </div>
                </div>
                <div class="topbar-right" x-data>
                    <label class="topbar-search">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input class="input topbar-search-input" placeholder="Cari modul, karyawan...">
                    </label>

                    {{-- Notification Bell --}}
                    <div class="topbar-icon-btn" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" class="topbar-icon-btn-inner" aria-label="Notifikasi" title="Notifikasi">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                            </svg>
                            <span class="topbar-notif-dot"></span>
                        </button>
                        <div class="topbar-dropdown topbar-dropdown--right" x-show="open" x-cloak x-transition style="width:300px;">
                            <div class="topbar-dropdown-head">Notifikasi</div>
                            <div class="topbar-dropdown-item">
                                <span class="topbar-dropdown-dot topbar-dropdown-dot--amber"></span>
                                <div><strong>3 rekening belum terverifikasi</strong><div class="muted" style="font-size:12px;">Segera tinjau data karyawan</div></div>
                            </div>
                            <div class="topbar-dropdown-item">
                                <span class="topbar-dropdown-dot topbar-dropdown-dot--blue"></span>
                                <div><strong>Payroll menunggu approval</strong><div class="muted" style="font-size:12px;">Periode Juli 2026</div></div>
                            </div>
                            <div class="topbar-dropdown-item">
                                <span class="topbar-dropdown-dot topbar-dropdown-dot--green"></span>
                                <div><strong>Transfer batch berhasil</strong><div class="muted" style="font-size:12px;">48 karyawan — Juni 2026</div></div>
                            </div>
                            <a href="/app/audit" class="topbar-dropdown-footer">Lihat semua aktivitas →</a>
                        </div>
                    </div>

                    {{-- User Profile --}}
                    <div x-data="{ open: false }" @click.outside="open = false" class="topbar-user-wrap">
                        <button @click="open = !open" class="topbar-user-btn" aria-label="Menu pengguna">
                            <span class="topbar-avatar">{{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 2)) }}</span>
                            <span class="topbar-user-name">{{ auth()->user()?->name ?? 'Pengguna' }}</span>
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="color:#94a3b8;flex-shrink:0;" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div class="topbar-dropdown topbar-dropdown--right" x-show="open" x-cloak x-transition>
                            <div class="topbar-dropdown-head" style="padding-bottom:12px;">
                                <div style="font-weight:700; color:var(--navy);">{{ auth()->user()?->name }}</div>
                                <div class="muted" style="font-size:12px; margin-top:2px;">{{ auth()->user()?->email }}</div>
                            </div>
                            <a href="/app/settings" class="topbar-dropdown-item topbar-dropdown-item--link">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                                Pengaturan
                            </a>
                            <a href="/app/audit" class="topbar-dropdown-item topbar-dropdown-item--link">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                Audit Log
                            </a>
                            <div style="height:1px; background:var(--line); margin:8px 0;"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="topbar-dropdown-item topbar-dropdown-item--link topbar-dropdown-item--danger" style="width:100%; border:none; background:none; cursor:pointer; text-align:left;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
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
                        @elseif ($role === 'hr_manager')
                            <a class="btn btn-primary" href="/app/payroll">Proses Payroll</a>
                        @elseif ($role === 'finance_manager')
                            <a class="btn btn-primary" href="/app/approval">Approval Queue</a>
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
    {{-- Chart.js CDN (loaded once, used by dashboard pages) --}}
    <script>
        if (typeof Chart === 'undefined') {
            var _chartScript = document.createElement('script');
            _chartScript.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
            document.head.appendChild(_chartScript);
        }
    </script>

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

    {{-- 11.2 — Confirm dialog modal --}}
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
            {{-- Icon --}}
            <div class="modal-icon modal-icon--danger">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <h2 class="modal-title" x-text="$store.confirm.title"></h2>
            <p class="modal-message" x-text="$store.confirm.message"></p>
            <form x-ref="confirmForm" method="POST" :action="$store.confirm.actionUrl">
                @csrf
                <input type="hidden" name="_method" :value="$store.confirm.actionMethod">
                <div class="modal-actions">
                    <button x-ref="cancelButton" type="button" class="btn btn-secondary" @click="$store.confirm.close()">Batal</button>
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
