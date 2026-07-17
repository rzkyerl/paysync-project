@php
    $titles = [
        'dashboard-hr' => ['Dashboard HR', 'Ringkasan pekerjaan HR hari ini untuk periode payroll aktif.'],
        'dashboard-finance' => ['Dashboard Finance', 'Approval payroll, status transfer, dan rekonsiliasi pembayaran.'],
        'dashboard-employee' => ['Dashboard Employee', 'Portal personal untuk slip gaji dan kehadiran sendiri.'],
        'employees' => ['Daftar Karyawan', 'Kelola data karyawan, status kerja, dan kelengkapan rekening.'],
        'attendance' => ['Kehadiran', 'Import CSV, validasi anomali, dan kunci periode payroll.'],
        'payroll' => ['Proses Payroll', 'Workspace kalkulasi payroll, review anomali, dan finalisasi.'],
        'approval' => ['Approval Queue', 'Review payroll yang dikirim HR sebelum disetujui Finance.'],
        'payslips' => ['Slip Gaji Digital', 'Publikasi dan preview slip gaji formal untuk karyawan.'],
        'disbursement' => ['Batch Transfer', 'Kelola batch penyaluran gaji dan status pembayaran.'],
        'reconciliation' => ['Rekonsiliasi', 'Cocokkan payroll net pay dengan nilai transfer berhasil.'],
        'reports' => ['Reports Hub', 'Laporan payroll, attendance, transfer, rekonsiliasi, dan audit.'],
        'settings' => ['Pengaturan Perusahaan', 'Profil, payroll, pembayaran, notifikasi, dan data operasional.'],
        'audit' => ['Audit Log', 'Riwayat perubahan read-only untuk modul penting.'],
    ];
    [$title, $description] = $titles[$page];
    $nav = [
        'Overview' => [['dashboard-hr','Dashboard HR'], ['dashboard-finance','Dashboard Finance'], ['dashboard-employee','Dashboard Employee']],
        'People' => [['employees','Karyawan'], ['employees','Organisasi'], ['employees','Rekening Bank']],
        'Time Management' => [['attendance','Kehadiran'], ['attendance','Lembur'], ['attendance','Cuti']],
        'Payroll' => [['payroll','Proses Payroll'], ['payroll','Komponen Gaji'], ['approval','Persetujuan'], ['payslips','Slip Gaji']],
        'Disbursement' => [['disbursement','Batch Transfer'], ['reconciliation','Rekonsiliasi']],
        'Reports' => [['reports','Laporan']],
        'System' => [['settings','Pengaturan'], ['audit','Audit Log']],
    ];
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
        <aside class="sidebar">
            @include('payflow.partials.brand')
            <div class="workspace">
                <strong>PT Nusantara</strong>
                <div><span class="badge" style="margin-top:8px; background:rgba(255,255,255,.08); color:#d9e7ff; border-color:rgba(255,255,255,.12);">Company Workspace</span></div>
            </div>
            @foreach ($nav as $group => $items)
                <div class="nav-group">
                    <div class="nav-title">{{ $group }}</div>
                    @foreach ($items as [$slug, $label])
                        <a class="nav-link {{ $page === $slug ? 'active' : '' }}" href="/app/{{ $slug }}">
                            @include('payflow.partials.icon', ['name' => $navIcons[$slug] ?? 'dashboard', 'class' => 'icon icon-sm'])
                            {{ $label }}
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
                        Keluar
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
                        <a class="btn btn-primary" href="/app/payroll">Proses Payroll</a>
                    </div>
                </div>

                @if ($page === 'dashboard-hr')
                    @include('payflow.pages.dashboard-hr')
                @elseif ($page === 'dashboard-finance')
                    @include('payflow.pages.dashboard-finance')
                @elseif ($page === 'dashboard-employee')
                    @include('payflow.pages.dashboard-employee')
                @elseif ($page === 'employees')
                    @include('payflow.pages.employees')
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
</body>
</html>
