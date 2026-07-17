<style>
    :root {
        --navy: #0f172a;
        --navy-soft: #1e293b;
        --brand: #0f3473;
        --brand-dark: #0a2658;
        --brand-soft: #eaf1fb;
        --brand-line: #bfd0ea;
        --surface: #ffffff;
        --page: #f4f7fb;
        --line: #dbe3ef;
        --muted: #64748b;
        --text: #172033;
        --green: #16a34a;
        --amber: #d97706;
        --red: #dc2626;
        --blue-soft: #e0f2fe;
    }

    * { box-sizing: border-box; }
    body {
        margin: 0;
        font-family: Inter, Geist, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        color: var(--text);
        background: var(--page);
    }
    a { color: inherit; text-decoration: none; }
    button, input, select { font: inherit; }
    .container { width: min(1160px, calc(100% - 32px)); margin-inline: auto; }
    .card { background: var(--surface); border: 1px solid var(--line); border-radius: 14px; box-shadow: 0 12px 30px rgba(15, 23, 42, .06); }
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border-radius: 10px; border: 1px solid transparent; padding: 10px 16px; font-weight: 700; cursor: pointer; white-space: nowrap; }
    .icon { width: 20px; height: 20px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; flex: 0 0 auto; }
    .icon-sm { width: 17px; height: 17px; }
    .icon-lg { width: 24px; height: 24px; }
    .btn-primary { background: var(--brand); color: #fff; }
    .btn-primary:hover { background: var(--brand-dark); }
    .btn-secondary { background: #fff; border-color: var(--line); color: var(--navy); }
    .btn-danger { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
    .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 26px;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 11px;
        line-height: 1;
        font-weight: 750;
        letter-spacing: .01em;
        border: 1px solid rgba(100, 116, 139, .16);
        background: rgba(248, 250, 252, .82);
        color: #475569;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .72);
        white-space: nowrap;
    }
    .badge-green { background: rgba(22, 163, 74, .10); border-color: rgba(22, 163, 74, .18); color: #13703a; }
    .badge-amber { background: rgba(217, 119, 6, .11); border-color: rgba(217, 119, 6, .20); color: #8a4a05; }
    .badge-red { background: rgba(220, 38, 38, .10); border-color: rgba(220, 38, 38, .18); color: #a11d1d; }
    .badge-blue { background: rgba(15, 52, 115, .09); border-color: rgba(15, 52, 115, .18); color: var(--brand); }
    .muted { color: var(--muted); }
    .grid { display: grid; gap: 16px; }
    .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .field { display: grid; gap: 7px; }
    .field label { font-size: 13px; font-weight: 700; color: #334155; }
    .input { width: 100%; border: 1px solid var(--line); border-radius: 10px; padding: 11px 12px; background: #fff; color: var(--text); }
    .input:focus { outline: 3px solid rgba(15, 52, 115, .16); border-color: var(--brand); }
    .table-wrap { overflow-x: auto; border: 1px solid var(--line); border-radius: 14px; background: #fff; }
    table { width: 100%; border-collapse: collapse; min-width: 760px; }
    th, td { padding: 13px 14px; border-bottom: 1px solid var(--line); text-align: left; font-size: 14px; }
    th { color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0; background: #f8fafc; }
    tr:last-child td { border-bottom: 0; }
    .app-shell { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; }
    .sidebar { background: var(--navy); color: #e2e8f0; padding: 18px 14px; position: sticky; top: 0; height: 100vh; overflow-y: auto; }
    .brand { display: inline-flex; align-items: center; gap: 10px; font-weight: 800; color: var(--navy); }
    .sidebar .brand, .auth-hero .brand { color: #fff; }
    .brand-logo { display: block; width: 132px; height: auto; object-fit: contain; }
    .brand-logo-white { display: none; }
    .sidebar .brand-logo-blue, .auth-hero .brand-logo-blue { display: none; }
    .sidebar .brand-logo-white, .auth-hero .brand-logo-white { display: block; }
    .brand-mark { width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center; background: linear-gradient(135deg, #2f65ad, var(--brand)); color: #fff; font-weight: 900; }
    .workspace { margin: 18px 0; padding: 12px; border-radius: 12px; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08); }
    .nav-group { margin-top: 18px; }
    .nav-title { color: #94a3b8; font-size: 11px; font-weight: 800; text-transform: uppercase; margin: 0 10px 8px; }
    .nav-link { display: flex; align-items: center; gap: 10px; padding: 9px 10px; border-radius: 10px; color: #cbd5e1; font-size: 14px; }
    .nav-link.active, .nav-link:hover { background: #173f82; color: #fff; }
    .topbar { height: 64px; background: #fff; border-bottom: 1px solid var(--line); display: flex; align-items: center; justify-content: space-between; padding: 0 24px; position: sticky; top: 0; z-index: 10; }
    .content { padding: 24px; }
    .page-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 20px; }
    .page-head h1 { margin: 0; font-size: 28px; line-height: 1.15; color: var(--navy); letter-spacing: 0; }
    .page-head p { margin: 8px 0 0; color: var(--muted); }
    .kpi { padding: 16px; }
    .kpi .value { font-size: 26px; line-height: 1.1; font-weight: 850; color: var(--navy); margin: 12px 0 4px; }
    .toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-bottom: 14px; }
    .section-title { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 16px 16px 0; }
    .section-title h2 { margin: 0; font-size: 18px; color: var(--navy); }
    .section-body { padding: 16px; }
    .progress { height: 10px; background: #e2e8f0; border-radius: 999px; overflow: hidden; }
    .progress span { display: block; height: 100%; background: var(--brand); border-radius: inherit; transform-origin: left; animation: progress-fill .9s cubic-bezier(.2, .8, .2, 1) both; }
    .timeline { display: grid; gap: 10px; }
    .timeline-row { display: grid; grid-template-columns: 32px 1fr auto; gap: 10px; align-items: center; }
    .dot { width: 28px; height: 28px; border-radius: 999px; display: grid; place-items: center; background: var(--brand-soft); color: var(--brand); font-weight: 900; font-size: 12px; }
    .dot.done { background: #dcfce7; color: #166534; }
    .dot.warn { background: #fef3c7; color: #92400e; }
    .auth-page { min-height: 100vh; display: grid; grid-template-columns: minmax(360px, .9fr) minmax(420px, 1.1fr); }
    .auth-hero { background: radial-gradient(circle at top left, #1e5aa3 0, #0f3473 42%, #071a3d 100%); color: #fff; padding: 48px; display: flex; flex-direction: column; justify-content: space-between; }
    .auth-main { display: grid; place-items: center; padding: 32px; background: var(--page); }
    .auth-card { width: min(460px, 100%); padding: 26px; }
    .form-stack { display: grid; gap: 14px; }
    .landing-nav { position: sticky; top: 0; z-index: 20; backdrop-filter: blur(14px); background: rgba(255,255,255,.92); border-bottom: 1px solid var(--line); }
    .landing-nav .container { height: 72px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
    .landing-links { display: flex; align-items: center; gap: 22px; color: #475569; font-size: 14px; font-weight: 650; }
    .hero { background: linear-gradient(180deg, #eef4ff 0%, #fff 100%); padding: 70px 0 46px; }
    .hero-grid { display: grid; grid-template-columns: 1fr .95fr; gap: 34px; align-items: center; }
    .hero h1 { margin: 14px 0 16px; font-size: clamp(38px, 5vw, 64px); line-height: 1.02; letter-spacing: 0; color: var(--navy); }
    .hero p { font-size: 18px; color: #475569; line-height: 1.7; }
    .mockup { padding: 18px; background: #fff; border: 1px solid var(--line); border-radius: 18px; box-shadow: 0 28px 70px rgba(15,23,42,.14); }
    .mini-chart { display: grid; grid-template-columns: repeat(6, 1fr); gap: 8px; align-items: end; height: 105px; }
    .mini-chart span {
        border-radius: 8px 8px 0 0;
        background: linear-gradient(180deg, #4f86cf, var(--brand));
        min-height: 24px;
        transform-origin: bottom;
        animation: chart-rise 1.2s cubic-bezier(.2, .8, .2, 1) both, chart-breathe 2.8s ease-in-out 1.25s infinite;
    }
    .mini-chart span:nth-child(2) { animation-delay: .08s, 1.33s; }
    .mini-chart span:nth-child(3) { animation-delay: .16s, 1.41s; }
    .mini-chart span:nth-child(4) { animation-delay: .24s, 1.49s; }
    .mini-chart span:nth-child(5) { animation-delay: .32s, 1.57s; }
    .mini-chart span:nth-child(6) { animation-delay: .40s, 1.65s; }
    .landing-section { padding: 58px 0; background: #fff; }
    .landing-section.alt { background: var(--page); }
    .section-head { max-width: 680px; margin-bottom: 24px; }
    .section-head h2 { margin: 0 0 10px; font-size: 32px; color: var(--navy); letter-spacing: 0; }
    .feature-card { padding: 18px; }
    .icon-box { width: 38px; height: 38px; border-radius: 10px; display: grid; place-items: center; background: var(--brand-soft); color: var(--brand); font-weight: 900; }
    .problem-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        border-top: 1px solid var(--line);
        border-bottom: 1px solid var(--line);
    }
    .problem-item {
        display: grid;
        grid-template-columns: 42px 1fr;
        gap: 14px;
        padding: 22px 24px;
        border-bottom: 1px solid var(--line);
    }
    .problem-item:nth-child(odd) { border-right: 1px solid var(--line); }
    .problem-item:nth-last-child(-n+2) { border-bottom: 0; }
    .problem-item h3,
    .feature-row h3,
    .workflow-item h3,
    .role-item h3 { margin: 0 0 6px; color: var(--navy); }
    .problem-item p,
    .feature-row p,
    .workflow-item p,
    .role-item p { margin: 0; }
    .feature-list {
        display: grid;
        border-top: 1px solid var(--line);
    }
    .feature-row {
        display: grid;
        grid-template-columns: 64px minmax(180px, .55fr) 1fr;
        gap: 18px;
        align-items: center;
        padding: 20px 0;
        border-bottom: 1px solid var(--line);
    }
    .feature-index {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        color: var(--brand);
        background: rgba(15, 52, 115, .08);
        border: 1px solid rgba(15, 52, 115, .12);
    }
    .workflow-list {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 0;
        border: 1px solid var(--line);
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
    }
    .workflow-item {
        padding: 20px 18px;
        border-right: 1px solid var(--line);
    }
    .workflow-item:last-child { border-right: 0; }
    .workflow-item .dot { margin-bottom: 16px; }
    .role-strip {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0;
        border-top: 1px solid var(--line);
        border-bottom: 1px solid var(--line);
    }
    .role-item {
        display: grid;
        grid-template-columns: 42px 1fr;
        gap: 14px;
        padding: 24px;
        border-right: 1px solid var(--line);
    }
    .role-item:last-child { border-right: 0; }
    .security-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .security-item {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        min-height: 42px;
        border-radius: 999px;
        padding: 8px 14px 8px 8px;
        background: rgba(248, 250, 252, .78);
        border: 1px solid rgba(100, 116, 139, .14);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.8);
    }
    .mobile-only { display: none; }

    @keyframes chart-rise {
        0% { transform: scaleY(.08); opacity: .45; filter: saturate(.8); }
        68% { transform: scaleY(1.08); opacity: 1; }
        100% { transform: scaleY(1); opacity: 1; filter: saturate(1); }
    }
    @keyframes chart-breathe {
        0%, 100% { transform: scaleY(1); }
        50% { transform: scaleY(.82); }
    }
    @keyframes surface-in {
        0% { opacity: 0; transform: translateY(10px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    @keyframes progress-fill {
        0% { transform: scaleX(.08); opacity: .55; }
        100% { transform: scaleX(1); opacity: 1; }
    }
    .hero .card,
    .hero .mockup,
    .content > .grid .card,
    .content > .card {
        animation: surface-in .55s cubic-bezier(.2, .8, .2, 1) both;
    }
    .grid > .card:nth-child(2) { animation-delay: .05s; }
    .grid > .card:nth-child(3) { animation-delay: .10s; }
    .grid > .card:nth-child(4) { animation-delay: .15s; }
    .grid > .card:nth-child(5) { animation-delay: .20s; }
    .grid > .card:nth-child(6) { animation-delay: .25s; }
    .timeline-row {
        animation: surface-in .45s cubic-bezier(.2, .8, .2, 1) both;
    }
    .timeline-row:nth-child(2) { animation-delay: .05s; }
    .timeline-row:nth-child(3) { animation-delay: .10s; }
    .timeline-row:nth-child(4) { animation-delay: .15s; }
    .timeline-row:nth-child(5) { animation-delay: .20s; }
    .timeline-row:nth-child(6) { animation-delay: .25s; }
    .reveal {
        opacity: 0;
        transform: translateY(18px);
        transition: opacity .7s cubic-bezier(.2, .8, .2, 1), transform .7s cubic-bezier(.2, .8, .2, 1);
    }
    .reveal.is-visible {
        opacity: 1;
        transform: translateY(0);
    }
    .problem-item.reveal:nth-child(2),
    .feature-row.reveal:nth-child(2),
    .workflow-item.reveal:nth-child(2),
    .role-item.reveal:nth-child(2),
    .security-item.reveal:nth-child(2) { transition-delay: .05s; }
    .problem-item.reveal:nth-child(3),
    .feature-row.reveal:nth-child(3),
    .workflow-item.reveal:nth-child(3),
    .role-item.reveal:nth-child(3),
    .security-item.reveal:nth-child(3) { transition-delay: .10s; }
    .problem-item.reveal:nth-child(4),
    .feature-row.reveal:nth-child(4),
    .workflow-item.reveal:nth-child(4),
    .security-item.reveal:nth-child(4) { transition-delay: .15s; }
    .feature-row.reveal:nth-child(5),
    .workflow-item.reveal:nth-child(5),
    .security-item.reveal:nth-child(5) { transition-delay: .20s; }
    .feature-row.reveal:nth-child(6),
    .workflow-item.reveal:nth-child(6),
    .security-item.reveal:nth-child(6) { transition-delay: .25s; }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: .001ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .001ms !important;
            scroll-behavior: auto !important;
        }
    }

    @media (max-width: 900px) {
        .grid-2, .grid-3, .grid-4, .hero-grid, .app-shell, .auth-page { grid-template-columns: 1fr; }
        .problem-list,
        .feature-row,
        .workflow-list,
        .role-strip { grid-template-columns: 1fr; }
        .problem-item,
        .problem-item:nth-child(odd),
        .workflow-item,
        .role-item {
            border-right: 0;
            border-bottom: 1px solid var(--line);
        }
        .problem-item:nth-last-child(-n+2) { border-bottom: 1px solid var(--line); }
        .problem-item:last-child,
        .workflow-item:last-child,
        .role-item:last-child { border-bottom: 0; }
        .feature-row { align-items: start; gap: 10px; }
        .sidebar { position: static; height: auto; }
        .landing-links { display: none; }
        .mobile-only { display: inline-flex; }
        .page-head { flex-direction: column; }
        .topbar { padding-inline: 16px; }
        .content { padding: 16px; }
        .auth-hero { padding: 30px; min-height: 360px; }
    }
</style>
