<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PaySync - HRIS dan Payroll Platform</title>
    @include('payflow.partials.styles')
</head>
<body>
    <nav class="landing-nav">
        <div class="container">
            @include('payflow.partials.brand')
            <div class="landing-links">
                <a href="#home">Home</a>
                <a href="#fitur">Fitur</a>
                <a href="#cara-kerja">Cara Kerja</a>
                <a href="#keamanan">Keamanan</a>
                <a href="#faq">FAQ</a>
            </div>
            <div style="display:flex; gap:10px; align-items:center;">
                <a class="btn btn-secondary" href="/login">Masuk</a>
                <a class="btn btn-primary" href="/register">Daftar Gratis</a>
            </div>
        </div>
    </nav>

    <main>
        <section id="home" class="hero reveal">
            <div class="container hero-grid">
                <div>
                    <p class="hero-kicker">Platform HR dan Payroll Terintegrasi</p>
                    <h1>Kelola HR, Payroll, dan Penyaluran Gaji dalam Satu Platform</h1>
                    <p>PaySync membantu perusahaan mengelola data karyawan, kehadiran, payroll, approval, slip digital, dan transfer gaji massal dalam workflow yang mudah diaudit.</p>
                    <div style="display:flex; flex-wrap:wrap; gap:12px; margin:26px 0 14px;">
                        <a class="btn btn-primary" href="/app/dashboard-hr">Mulai Sekarang</a>
                        <a class="btn btn-secondary" href="#cara-kerja">Lihat Cara Kerja</a>
                    </div>
                    <span class="muted" style="font-size:13px;">Dirancang untuk membantu HR dan Finance bekerja dari satu sumber data yang konsisten.</span>
                </div>
                <figure class="hero-art">
                    <img
                        src="/images/ilustrations/home-office.svg"
                        alt="Ilustrasi vector pekerja di meja kerja menggunakan laptop untuk mengelola platform HR dan payroll"
                        width="640"
                        height="480"
                        loading="eager"
                        decoding="async"
                    >
                </figure>
            </div>
        </section>

        <section id="produk" class="landing-section reveal">
            <div class="container section-split">
                <div class="section-split-content">
                    <div class="section-head">
                        <h2>Proses HR manual membuat data tersebar</h2>
                        <p class="muted">Payroll rentan salah, approval terlambat, dan status pembayaran sulit ditelusuri ketika employee data, attendance, dan finance review berada di tempat berbeda.</p>
                    </div>
                    <div class="problem-list">
                        @foreach ([
                            ['Data tersebar','Informasi karyawan sulit disinkronkan antar tim.','users'],
                            ['Payroll rawan keliru','Komponen gaji dan potongan tidak konsisten.','warning'],
                            ['Approval lambat','Finance tidak punya konteks anomali yang cukup.','clock'],
                            ['Transfer sulit dilacak','Status batch dan rekonsiliasi tidak transparan.','link'],
                        ] as $item)
                            <div class="problem-item reveal">
                                <div class="icon-box">@include('payflow.partials.icon', ['name' => $item[2], 'class' => 'icon icon-lg'])</div>
                                <div><h3>{{ $item[0] }}</h3><p class="muted">{{ $item[1] }}</p></div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <figure class="landing-art section-split-art reveal">
                    <img
                        src="/images/ilustrations/paper-documents.svg"
                        alt="Ilustrasi vector data yang tersebar mewakili masalah HR manual yang belum terpusat"
                        width="640"
                        height="400"
                        loading="lazy"
                        decoding="async"
                    >
                </figure>
            </div>
        </section>

        <section id="fitur" class="landing-section alt reveal">
            <div class="container">
                <div class="section-head">
                    <h2>Fitur utama PaySync</h2>
                    <p class="muted">Dirancang untuk alur HR, Finance, dan Employee portal dalam satu platform SaaS.</p>
                </div>
                <div class="feature-list">
                    @foreach ([
                        ['Employee Management','Kelola profil, organisasi, status kerja, dan kelengkapan data karyawan.','users'],
                        ['Attendance','Import CSV, validasi anomali, dan kunci periode sebelum payroll.','calendar'],
                        ['Automated Payroll','Hitung gaji, tunjangan, potongan, lembur, dan adjustment payroll.','payroll'],
                        ['Approval Workflow','Finance meninjau payroll dengan modal approve dan reject beralasan.','approval'],
                        ['Digital Payslip','Slip gaji digital formal dengan label Confidential dan riwayat publikasi.','file'],
                        ['Salary Disbursement','Batch transfer, retry, dan rekonsiliasi pembayaran dalam satu alur.','bank'],
                    ] as $item)
                        <div class="feature-row reveal">
                            <div class="feature-index">@include('payflow.partials.icon', ['name' => $item[2], 'class' => 'icon icon-lg'])</div>
                            <h3>{{ $item[0] }}</h3>
                            <p class="muted">{{ $item[1] }}</p>
                        </div>
                    @endforeach
                </div>

            </div>
        </section>

        <section id="cara-kerja" class="landing-section reveal">
            <div class="container section-split section-split-reverse">
                <figure class="landing-art section-split-art reveal">
                    <img
                        src="/images/ilustrations/to-do-list.svg"
                        alt="Ilustrasi vector alur kerja dan checklist proses payroll"
                        width="640"
                        height="400"
                        loading="lazy"
                        decoding="async"
                    >
                </figure>
                <div class="section-split-content">
                    <div class="section-head"><h2>Cara kerja end-to-end</h2><p class="muted">Dari data karyawan sampai rekonsiliasi payroll dalam enam langkah.</p></div>
                    <div class="workflow-list">
                        @foreach (['Kelola data karyawan','Impor kehadiran','Hitung payroll','Review dan approval','Terbitkan slip','Transfer dan rekonsiliasi'] as $i => $step)
                            <div class="workflow-item reveal">
                                <span class="dot">{{ $i + 1 }}</span>
                                <h3>{{ $step }}</h3>
                                <p class="muted">Setiap tahap memiliki status, validasi, dan audit trail yang jelas.</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="landing-section alt reveal">
            <div class="container section-split">
                <div class="section-split-content">
                    <div class="section-head">
                        <h2>Satu platform, tiga workspace</h2>
                        <p class="muted">Setiap peran mendapat tampilan dan akses yang sesuai dengan tanggung jawabnya.</p>
                    </div>
                    <div class="role-strip">
                        @foreach ([
                            ['HR','Memantau data karyawan, attendance, payroll run, dan anomali.','dashboard'],
                            ['Finance','Meninjau approval, total nominal, batch transfer, dan rekonsiliasi.','bank'],
                            ['Employee','Melihat slip gaji, riwayat kehadiran, dan data profil sendiri.','users'],
                        ] as $role)
                            <div class="role-item reveal"><div class="icon-box">@include('payflow.partials.icon', ['name' => $role[2], 'class' => 'icon icon-lg'])</div><div><h3>Workspace {{ $role[0] }}</h3><p class="muted">{{ $role[1] }}</p></div></div>
                        @endforeach
                    </div>
                </div>
                <figure class="landing-art section-split-art reveal">
                    <img
                        src="/images/ilustrations/team-workspace.svg"
                        alt="Ilustrasi vector kolaborasi tim HR, Finance, dan Employee dalam satu platform"
                        width="640"
                        height="400"
                        loading="lazy"
                        decoding="async"
                    >
                </figure>
            </div>
        </section>

        <section id="keamanan" class="landing-section reveal">
            <div class="container section-split">
                <figure class="landing-art section-split-art reveal">
                    <img
                        src="/images/ilustrations/achievement.svg"
                        alt="Ilustrasi vector pencapaian kontrol aplikasi yang siap operasional dan terverifikasi"
                        width="640"
                        height="400"
                        loading="lazy"
                        decoding="async"
                    >
                </figure>
                <div class="section-split-content">
                    <div class="section-head"><h2>Kontrol aplikasi yang siap operasional</h2><p class="muted">PaySync mendukung role-based access, password hashing, CSRF protection, database transaction, audit log, dan kontrol data payroll.</p></div>
                    <div class="security-list">
                        @foreach (['Role-based access','Password hashing','CSRF protection','Database transaction','Audit log','Payroll data control'] as $item)
                            <div class="security-item reveal"><span class="dot done">@include('payflow.partials.icon', ['name' => 'check', 'class' => 'icon icon-sm'])</span><strong>{{ $item }}</strong></div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section id="faq" class="landing-section reveal">
            <div class="container">
                <div class="section-head" style="text-align:center; margin-inline:auto;">
                    <h2>FAQ</h2>
                    <p class="muted">Pertanyaan yang sering diajukan seputar PaySync.</p>
                </div>
                <div class="faq-list">
                    @foreach ([
                        ['Bagaimana alur pembayaran diproses?', 'Payroll yang sudah disetujui dapat diproses sebagai batch transfer dan direkonsiliasi oleh Finance. Setiap langkah memiliki status yang dapat dipantau secara real-time.'],
                        ['Apakah employee bisa melihat data orang lain?', 'Tidak. Portal employee dirancang hanya untuk data pengguna sendiri. Akses dikontrol menggunakan role-based access sehingga setiap pengguna hanya melihat informasi yang relevan.'],
                        ['Apakah PaySync mendukung multi-perusahaan?', 'Ya. Setiap perusahaan memiliki workspace tersendiri yang terpisah secara data dan konfigurasi.'],
                        ['Bagaimana keamanan data payroll dijaga?', 'PaySync menggunakan password hashing, CSRF protection, database transaction, dan audit log untuk menjaga integritas dan kerahasiaan data payroll.'],
                        ['Apakah bisa mengimpor data kehadiran dari sistem lain?', 'Bisa. PaySync mendukung impor data kehadiran melalui file CSV dengan validasi anomali sebelum diproses ke payroll.'],
                        ['Berapa lama proses onboarding?', 'Onboarding dapat diselesaikan dalam hitungan menit. Cukup daftarkan perusahaan, buat workspace, dan mulai kelola karyawan serta payroll.'],
                    ] as $i => $item)
                        <div class="faq-item reveal">
                            <button class="faq-trigger" aria-expanded="false" aria-controls="faq-panel-{{ $i }}">
                                <span>{{ $item[0] }}</span>
                                <span class="faq-icon-wrap" aria-hidden="true">
                                    <svg class="faq-icon" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </span>
                            </button>
                            <div class="faq-panel" id="faq-panel-{{ $i }}" role="region">
                                <div class="faq-panel-inner">
                                    <p>{{ $item[1] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="landing-section alt reveal">
            <div class="container">
                <div class="cta-register">
                    <h2>Daftarkan Perusahaan Anda</h2>
                    <p>Buat workspace perusahaan, lanjutkan onboarding, lalu kelola payroll dan transfer gaji dari satu tempat.</p>
                    <a class="btn btn-primary" href="/register">Daftar Gratis</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-grid">
            <div class="footer-brand">
                @include('payflow.partials.brand')
                <p>Platform HRIS dan payroll untuk perusahaan yang membutuhkan workflow data, approval, slip gaji, transfer, dan rekonsiliasi dalam satu tempat.</p>
            </div>
            <div class="footer-col">
                <strong>Produk</strong>
                <a href="#fitur">Payroll</a>
                <a href="#cara-kerja">Workflow</a>
                <a href="/app/dashboard-hr">Dashboard</a>
            </div>
            <div class="footer-col">
                <strong>Perusahaan</strong>
                <a href="#produk">Tentang PaySync</a>
                <a href="#keamanan">Keamanan</a>
                <a href="#faq">FAQ</a>
            </div>
            <div class="footer-col">
                <strong>Kontak</strong>
                <a href="mailto:kontak@paysync.test">kontak@paysync.test</a>
                <span>Jakarta, Indonesia</span>
                <span>Modern Payroll Operations Platform</span>
            </div>
        </div>
        <div class="container footer-bottom">
            <span>© {{ date('Y') }} PaySync. All rights reserved.</span>
            <div><a href="#">Kebijakan Privasi</a><a href="#">Ketentuan Penggunaan</a></div>
        </div>
    </footer>
    <script>
        // FAQ accordion
        (() => {
            const faqItems = document.querySelectorAll('.faq-item');
            faqItems.forEach((item) => {
                const trigger = item.querySelector('.faq-trigger');
                const panel = item.querySelector('.faq-panel');
                if (!trigger || !panel) return;

                trigger.addEventListener('click', () => {
                    const isOpen = trigger.getAttribute('aria-expanded') === 'true';

                    // close all others
                    faqItems.forEach((other) => {
                        if (other === item) return;
                        const otherTrigger = other.querySelector('.faq-trigger');
                        const otherPanel = other.querySelector('.faq-panel');
                        if (otherTrigger) otherTrigger.setAttribute('aria-expanded', 'false');
                        if (otherPanel) otherPanel.style.maxHeight = null;
                        other.classList.remove('faq-open');
                    });

                    const next = !isOpen;
                    trigger.setAttribute('aria-expanded', String(next));
                    item.classList.toggle('faq-open', next);
                    panel.style.maxHeight = next ? panel.scrollHeight + 'px' : null;
                });
            });
        })();

        // Scroll reveal
        (() => {
            const items = document.querySelectorAll('.reveal');
            if (!items.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                items.forEach((item) => item.classList.add('is-visible'));
                return;
            }

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.16, rootMargin: '0px 0px -8% 0px' });

            items.forEach((item) => observer.observe(item));
        })();

        // Active nav link
        (() => {
            const sections = document.querySelectorAll('section[id]');
            const navLinks = document.querySelectorAll('.landing-links a[href^="#"]');

            if (!sections.length || !navLinks.length) return;

            const homeLinks = [];
            const linkMap = new Map();

            navLinks.forEach((link) => {
                const href = link.getAttribute('href').slice(1);
                if (!linkMap.has(href)) linkMap.set(href, []);
                linkMap.get(href).push(link);
                if (href === 'home') homeLinks.push(link);
            });

            const setActive = (id) => {
                linkMap.forEach((links, key) => {
                    const isActive = key === id || (id === 'produk' && key === 'home');
                    links.forEach((link) => link.classList.toggle('active', isActive));
                });
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        setActive(entry.target.id);
                    }
                });
            }, { threshold: 0.35, rootMargin: '-80px 0px -35% 0px' });

            sections.forEach((section) => observer.observe(section));
        })();
    </script>
</body>
</html>
