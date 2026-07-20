<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500&display=swap" rel="stylesheet">
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
        --font-sans: "Plus Jakarta Sans", "Segoe UI", sans-serif;
        --font-display: "Outfit", "Plus Jakarta Sans", sans-serif;
    }

    * { box-sizing: border-box; }
    body {
        margin: 0;
        font-family: var(--font-sans);
        font-optical-sizing: auto;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        color: var(--text);
        background: var(--page);
        line-height: 1.55;
        letter-spacing: -0.011em;
    }
    h1, h2, h3,
    .brand,
    .page-head h1,
    .section-title h2,
    .section-head h2,
    .hero h1,
    .kpi .value,
    .auth-hero h1,
    .sync-core strong {
        font-family: var(--font-display);
        letter-spacing: -0.03em;
    }
    a { color: inherit; text-decoration: none; }
    button, input, select { font: inherit; }
    .container { width: min(1160px, calc(100% - 32px)); margin-inline: auto; }
    .card { background: var(--surface); border: 1px solid var(--line); border-radius: 14px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border-radius: 10px; border: 1px solid transparent; padding: 10px 16px; font-weight: 700; cursor: pointer; white-space: nowrap; }
    .icon { width: 20px; height: 20px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; flex: 0 0 auto; }
    .icon-sm { width: 17px; height: 17px; }
    .icon-lg { width: 24px; height: 24px; }
    .btn-primary { background: var(--brand); color: #fff; }
    .btn-primary:hover { background: var(--brand-dark); }
    .btn-secondary { background: #fff; border-color: var(--line); color: var(--navy); }
    .btn-danger { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
    .btn-danger:hover { background: #fecaca; }
    .btn-disabled { opacity: .45; cursor: not-allowed; pointer-events: none; }
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
    .grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 20px; }
    .field { display: grid; gap: 7px; }
    .field label { font-size: 13px; font-weight: 700; color: #334155; }
    .input { width: 100%; border: 1px solid var(--line); border-radius: 10px; padding: 11px 12px; background: #fff; color: var(--text); }
    .input:focus { outline: 3px solid rgba(15, 52, 115, .16); border-color: var(--brand); }
    .table-wrap { overflow-x: auto; border: 1px solid var(--line); border-radius: 14px; background: #fff; }
    table { width: 100%; border-collapse: collapse; min-width: 760px; }
    th, td { padding: 13px 14px; border-bottom: 1px solid var(--line); text-align: left; font-size: 14px; }
    th { color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0; background: #f8fafc; }
    tr:last-child td { border-bottom: 0; }
    tr:hover td { background: var(--brand-soft); }
    .sort-link {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: inherit;
        text-decoration: none;
        font-weight: 600;
        white-space: nowrap;
    }
    .sort-link:hover {
        color: var(--brand);
    }
    .sort-link.active {
        color: var(--brand);
        font-weight: 700;
    }
    .sort-link .sort-icon {
        font-size: 11px;
        opacity: 0.6;
    }
    .sort-link.active .sort-icon {
        opacity: 1;
    }
    .app-shell { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; transition: grid-template-columns 0.25s cubic-bezier(.2,.8,.2,1); }
    .sidebar { background: var(--navy); color: #e2e8f0; padding: 18px 14px; position: sticky; top: 0; height: 100vh; overflow-y: auto; transition: width 0.25s cubic-bezier(.2,.8,.2,1), padding 0.25s cubic-bezier(.2,.8,.2,1); }
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
    .nav-link { display: flex; align-items: center; gap: 10px; padding: 9px 10px; border-radius: 10px; color: #cbd5e1; font-size: 14px; position: relative; }
    .sidebar .nav-link > .icon {
        width: 17px;
        min-width: 17px;
        max-width: 17px;
        height: 17px;
        min-height: 17px;
        max-height: 17px;
        flex: 0 0 17px;
    }
    .nav-link::after {
        content: "";
        position: absolute;
        left: 10px;
        right: 10px;
        bottom: 4px;
        height: 2px;
        border-radius: 999px;
        background: var(--brand);
        transform: scaleX(0);
        transition: transform .28s cubic-bezier(.2, .8, .2, 1);
    }
    .nav-link.active { color: #fff; }
    .nav-link.active::after,
    .nav-link:hover::after { transform: scaleX(1); }
    .nav-link:hover { color: #fff; }
    .topbar { height: 64px; background: #fff; border-bottom: 1px solid var(--line); display: flex; align-items: center; justify-content: space-between; padding: 0 24px; position: sticky; top: 0; z-index: 200; }
    .content { padding: 24px; }
    .page-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 20px; }
    .page-head h1 { margin: 0; font-size: 28px; line-height: 1.15; color: var(--navy); font-weight: 700; }
    .page-head p { margin: 8px 0 0; color: var(--muted); }
    .kpi { padding: 20px; }
    .kpi .value { font-size: 28px; line-height: 1.1; font-weight: 800; color: var(--navy); margin: 12px 0 4px; }
    .kpi .muted { font-size: 12px; }
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
    .landing-links a {
        position: relative;
        padding: 8px 0;
        transition: color .2s ease;
    }
    .landing-links a::after {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        bottom: 2px;
        height: 2px;
        border-radius: 999px;
        background: var(--brand);
        transform: scaleX(0);
        transform-origin: right;
        transition: transform .28s cubic-bezier(.2, .8, .2, 1);
    }
    .landing-links a.active,
    .landing-links a:hover { color: var(--brand); }
    .landing-links a.active::after,
    .landing-links a:hover::after {
        transform: scaleX(1);
        transform-origin: left;
    }
    .hero { background: linear-gradient(180deg, #eef4ff 0%, #fff 100%); padding:240px 0 46px; }
    .hero-grid { display: grid; grid-template-columns: 1fr .95fr; gap: 34px; align-items: center; }
    .hero-kicker {
        margin: 0 0 10px;
        font-size: 14px;
        font-weight: 650;
        color: var(--brand);
        letter-spacing: -0.01em;
    }
    .hero-art {
        margin: 0;
        display: grid;
        place-items: center;
        min-height: 420px;
        padding: 28px 20px;
        border-radius: 28px;
        overflow: hidden;
        border: 1px solid rgba(15, 52, 115, .10);
        background:
            radial-gradient(circle at 18% 20%, rgba(79,134,207,.14), transparent 36%),
            radial-gradient(circle at 84% 18%, rgba(15,52,115,.08), transparent 30%),
            linear-gradient(180deg, #eef4ff 0%, #f8fbff 100%);
    }
    .hero-art img {
        display: block;
        width: min(100%, 520px);
        height: auto;
        max-height: 420px;
        object-fit: contain;
    }
    .hero h1 { margin: 0 0 16px; font-size: clamp(38px, 5vw, 64px); line-height: 1.05; font-weight: 700; color: var(--navy); }
    .hero p { font-size: 18px; color: #475569; line-height: 1.7; letter-spacing: -0.01em; }

    .landing-art {
        margin: 28px 0 0;
        display: grid;
        place-items: center;
        padding: 28px 20px;
        border-radius: 22px;
        overflow: hidden;
        border: 1px solid rgba(15, 52, 115, .10);
        background:
            radial-gradient(circle at 20% 18%, rgba(79,134,207,.10), transparent 34%),
            linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    }
    .landing-art img {
        display: block;
        width: min(100%, 460px);
        height: auto;
        max-height: 320px;
        object-fit: contain;
    }
    .section-split-art img {
        width: 100%;
        height: 100%;
        max-height: none;
        object-fit: contain;
    }

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
    .office-graphic {
        position: relative;
        min-height: 214px;
        margin-top: 16px;
        overflow: hidden;
        border: 1px solid rgba(15, 52, 115, .12);
        border-radius: 18px;
        background:
            linear-gradient(180deg, rgba(234, 241, 251, .85), rgba(255,255,255,.95)),
            radial-gradient(circle at 20% 15%, rgba(79,134,207,.18), transparent 28%),
            radial-gradient(circle at 82% 20%, rgba(15,52,115,.10), transparent 24%);
    }
    .office-panel {
        position: absolute;
        z-index: 2;
        width: 168px;
        padding: 12px;
        border-radius: 14px;
        background: rgba(255,255,255,.78);
        border: 1px solid rgba(15,52,115,.12);
        box-shadow: 0 14px 30px rgba(15,23,42,.08);
        backdrop-filter: blur(10px);
        animation: panel-float 3.6s ease-in-out infinite;
    }
    .office-panel strong,
    .office-panel small { display: block; }
    .office-panel strong { color: var(--navy); font-size: 13px; margin: 7px 0 3px; }
    .office-panel small { color: var(--muted); font-size: 12px; }
    .panel-left { left: 18px; top: 20px; }
    .panel-right { right: 18px; bottom: 22px; animation-delay: .45s; }
    .panel-dot {
        width: 9px;
        height: 9px;
        display: inline-block;
        border-radius: 999px;
        background: var(--amber);
        box-shadow: 0 0 0 5px rgba(217,119,6,.12);
    }
    .panel-dot.success {
        background: var(--green);
        box-shadow: 0 0 0 5px rgba(22,163,74,.12);
    }
    .office-desk {
        position: absolute;
        left: 50%;
        bottom: 30px;
        width: min(310px, 78%);
        height: 118px;
        transform: translateX(-50%);
    }
    .office-desk::after {
        content: "";
        position: absolute;
        left: 20px;
        right: 20px;
        bottom: 0;
        height: 16px;
        border-radius: 999px;
        background: rgba(15,52,115,.16);
        filter: blur(1px);
    }
    .laptop {
        position: absolute;
        left: 50%;
        bottom: 28px;
        width: 120px;
        height: 76px;
        transform: translateX(-50%);
        border-radius: 12px 12px 8px 8px;
        background: linear-gradient(135deg, #173f82, var(--brand));
        box-shadow: 0 18px 34px rgba(15,52,115,.25);
        display: grid;
        align-content: center;
        gap: 7px;
        padding: 18px;
        animation: laptop-in .9s cubic-bezier(.2,.8,.2,1) both;
    }
    .laptop::after {
        content: "";
        position: absolute;
        left: -14px;
        right: -14px;
        bottom: -13px;
        height: 14px;
        border-radius: 0 0 18px 18px;
        background: #d9e4f3;
        border: 1px solid rgba(15,52,115,.12);
    }
    .laptop span {
        height: 6px;
        border-radius: 999px;
        background: rgba(255,255,255,.78);
        animation: data-pulse 1.8s ease-in-out infinite;
    }
    .laptop span:nth-child(2) { width: 76%; animation-delay: .25s; }
    .laptop span:nth-child(3) { width: 52%; animation-delay: .50s; }
    .office-person {
        position: absolute;
        bottom: 30px;
        width: 64px;
        height: 96px;
        animation: person-float 3.4s ease-in-out infinite;
    }
    .person-left { left: 10px; }
    .person-right { right: 10px; animation-delay: .35s; }
    .office-person .head {
        position: absolute;
        left: 20px;
        top: 5px;
        width: 24px;
        height: 24px;
        border-radius: 999px;
        background: #d7e2f2;
        border: 5px solid var(--brand);
    }
    .office-person .body {
        position: absolute;
        left: 13px;
        top: 35px;
        width: 38px;
        height: 48px;
        border-radius: 18px 18px 10px 10px;
        background: linear-gradient(180deg, #4f86cf, var(--brand));
    }
    .flow-line {
        position: absolute;
        height: 2px;
        background: linear-gradient(90deg, transparent, rgba(15,52,115,.42), transparent);
        transform-origin: left;
        animation: flow-sweep 2.4s ease-in-out infinite;
    }
    .line-one { left: 125px; top: 72px; width: 190px; transform: rotate(13deg); }
    .line-two { right: 120px; bottom: 78px; width: 190px; transform: rotate(-12deg); animation-delay: .5s; }
    .landing-section { padding: 84px 0; background: #fff; }
    .landing-section.alt { background: var(--page); }
    .section-split {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 48px;
        align-items: center;
    }
    .section-split-content { min-width: 0; }
    .section-split .section-head { max-width: 100%; margin-bottom: 24px; }
    .section-split .landing-art {
        margin: 0;
    }
    .section-split-art {
        margin: 0 !important;
        align-self: stretch;
        min-height: 0;
    }
    .section-split-art img {
        width: 100%;
        height: 100%;
        max-height: none;
        object-fit: contain;
    }
    .section-split-reverse {
        /* figure is placed first in HTML, content second — natural order gives illustration left, content right */
    }
    .section-split .workflow-list {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        border-radius: 14px;
    }
    .section-split .workflow-item {
        border-right: none;
        border-bottom: 1px solid var(--line);
    }
    .section-split .workflow-item:nth-child(odd) {
        border-right: 1px solid var(--line);
    }
    .section-split .workflow-item:nth-last-child(-n+2) {
        border-bottom: 0;
    }
    .section-head { max-width: 680px; margin-bottom: 24px; }
    .section-head h2 { margin: 0 0 10px; font-size: 32px; color: var(--navy); font-weight: 700; }
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
        gap: 0;
        border: 1px solid var(--line);
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
    }
    .role-item {
        display: grid;
        grid-template-columns: 42px 1fr;
        gap: 14px;
        padding: 20px 22px;
        border-bottom: 1px solid var(--line);
    }
    .role-item:last-child { border-bottom: 0; }
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
    .landing-visual {
        position: relative;
        min-height: 240px;
        margin-top: 28px;
        overflow: hidden;
        border: 1px solid rgba(15, 52, 115, .12);
        border-radius: 22px;
        background:
            radial-gradient(circle at 14% 20%, rgba(79,134,207,.16), transparent 28%),
            radial-gradient(circle at 86% 18%, rgba(22,163,74,.10), transparent 24%),
            linear-gradient(180deg, rgba(255,255,255,.88), rgba(234,241,251,.64));
    }
    .sync-node {
        position: absolute;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        gap: 9px;
        min-width: 138px;
        padding: 12px 14px;
        border-radius: 999px;
        color: var(--brand);
        background: rgba(255,255,255,.82);
        border: 1px solid rgba(15,52,115,.13);
        box-shadow: 0 16px 34px rgba(15,23,42,.08);
        backdrop-filter: blur(10px);
        animation: panel-float 3.8s ease-in-out infinite;
    }
    .sync-node span { color: var(--navy); font-size: 13px; font-weight: 750; }
    .node-hr { left: 9%; top: 26px; }
    .node-time { right: 10%; top: 34px; animation-delay: .25s; }
    .node-finance { left: 12%; bottom: 28px; animation-delay: .5s; }
    .node-slip { right: 11%; bottom: 34px; animation-delay: .75s; }
    .sync-core {
        position: absolute;
        left: 50%;
        top: 50%;
        z-index: 3;
        width: 170px;
        height: 170px;
        transform: translate(-50%, -50%);
        border-radius: 999px;
        display: grid;
        place-items: center;
        align-content: center;
        text-align: center;
        color: #fff;
        background: linear-gradient(135deg, #1e5aa3, var(--brand));
        box-shadow: 0 26px 60px rgba(15,52,115,.24);
    }
    .sync-core strong { font-size: 24px; }
    .sync-core small { margin-top: 4px; color: rgba(255,255,255,.72); }
    .core-ring {
        position: absolute;
        inset: -14px;
        border-radius: inherit;
        border: 1px solid rgba(15,52,115,.16);
        animation: core-ring-pulse 2.7s ease-in-out infinite;
    }
    .sync-path {
        position: absolute;
        z-index: 1;
        height: 2px;
        background: linear-gradient(90deg, transparent, rgba(15,52,115,.28), transparent);
        animation: flow-sweep 2.6s ease-in-out infinite;
    }
    .path-a { left: 23%; top: 82px; width: 220px; transform: rotate(13deg); }
    .path-b { right: 23%; top: 88px; width: 220px; transform: rotate(-13deg); animation-delay: .25s; }
    .path-c { left: 24%; bottom: 78px; width: 220px; transform: rotate(-13deg); animation-delay: .5s; }
    .path-d { right: 24%; bottom: 83px; width: 220px; transform: rotate(13deg); animation-delay: .75s; }
    .dashboard-visual { min-height: 310px; }
    .dashboard-window {
        position: absolute;
        left: 50%;
        top: 28px;
        width: min(720px, calc(100% - 56px));
        height: 250px;
        transform: translateX(-50%);
        border-radius: 18px;
        background: rgba(255,255,255,.86);
        border: 1px solid rgba(15,52,115,.12);
        box-shadow: 0 24px 60px rgba(15,23,42,.10);
        overflow: hidden;
    }
    .window-top {
        height: 38px;
        display: flex;
        gap: 7px;
        align-items: center;
        padding: 0 16px;
        border-bottom: 1px solid var(--line);
        background: rgba(248,250,252,.78);
    }
    .window-top span { width: 9px; height: 9px; border-radius: 999px; background: #cbd5e1; }
    .window-grid { display: grid; grid-template-columns: 150px 1fr; height: calc(100% - 38px); }
    .window-side { padding: 16px; display: grid; align-content: start; gap: 12px; background: rgba(15,52,115,.05); border-right: 1px solid var(--line); }
    .window-side span { height: 10px; border-radius: 999px; background: rgba(15,52,115,.18); }
    .window-main { padding: 16px; display: grid; gap: 14px; }
    .metric-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .metric-row span { height: 48px; border-radius: 14px; background: linear-gradient(135deg, rgba(15,52,115,.12), rgba(79,134,207,.18)); }
    .approval-card { padding: 14px; border-radius: 14px; background: #fff; border: 1px solid var(--line); }
    .approval-card strong { display: block; margin-bottom: 10px; color: var(--navy); }
    .approval-card div { height: 8px; border-radius: 999px; margin-top: 8px; background: #dbe7f6; animation: data-pulse 2s ease-in-out infinite; }
    .approval-card div:nth-child(3) { width: 72%; animation-delay: .25s; }
    .approval-card div:nth-child(4) { width: 54%; animation-delay: .5s; }
    .payroll-bars { display: flex; align-items: end; gap: 10px; height: 54px; }
    .payroll-bars span { flex: 1; border-radius: 8px 8px 0 0; background: linear-gradient(180deg, #4f86cf, var(--brand)); animation: chart-rise 1.3s cubic-bezier(.2,.8,.2,1) both, chart-breathe 3s ease-in-out 1.4s infinite; }
    .payroll-bars span:nth-child(1) { height: 38%; }
    .payroll-bars span:nth-child(2) { height: 72%; animation-delay: .08s, 1.48s; }
    .payroll-bars span:nth-child(3) { height: 52%; animation-delay: .16s, 1.56s; }
    .payroll-bars span:nth-child(4) { height: 88%; animation-delay: .24s, 1.64s; }
    .floating-note {
        position: absolute;
        z-index: 3;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 12px;
        border-radius: 999px;
        background: rgba(255,255,255,.86);
        border: 1px solid rgba(15,52,115,.12);
        color: var(--brand);
        font-weight: 800;
        font-size: 12px;
        box-shadow: 0 14px 30px rgba(15,23,42,.10);
        animation: panel-float 3.4s ease-in-out infinite;
    }
    .note-one { left: 7%; top: 74px; }
    .note-two { right: 7%; bottom: 38px; animation-delay: .45s; }
    .documents-visual { min-height: 300px; }
    .document-stack {
        position: absolute;
        left: 50%;
        top: 50%;
        width: 210px;
        height: 238px;
        transform: translate(-50%, -50%);
    }
    .doc-card {
        position: absolute;
        inset: 0;
        border-radius: 18px;
        background: #fff;
        border: 1px solid rgba(15,52,115,.12);
        box-shadow: 0 22px 50px rgba(15,23,42,.10);
    }
    .doc-back { transform: rotate(-10deg) translate(-58px, 18px); opacity: .62; }
    .doc-mid { transform: rotate(8deg) translate(54px, 16px); opacity: .78; }
    .doc-front { padding: 22px; display: grid; align-content: start; gap: 13px; animation: panel-float 3.5s ease-in-out infinite; }
    .doc-front > span:not(.doc-logo) { height: 8px; border-radius: 999px; background: #dbe7f6; }
    .doc-logo { width: 34px; height: 34px; border-radius: 12px; background: var(--brand); }
    .doc-total { margin-top: 10px; padding: 12px; border-radius: 14px; background: var(--brand-soft); color: var(--brand); font-weight: 900; }
    .transfer-chip,
    .audit-chip {
        position: absolute;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 999px;
        background: rgba(255,255,255,.86);
        border: 1px solid rgba(15,52,115,.12);
        box-shadow: 0 16px 34px rgba(15,23,42,.10);
        font-size: 13px;
        font-weight: 800;
        color: var(--brand);
        animation: panel-float 3.2s ease-in-out infinite;
    }
    .transfer-chip { left: 9%; top: 58px; }
    .audit-chip { right: 9%; bottom: 58px; animation-delay: .42s; }
    .security-visual {
        min-height: 250px;
        display: grid;
        place-items: center;
    }
    .security-lock {
        z-index: 3;
        width: 82px;
        height: 82px;
        border-radius: 28px;
        display: grid;
        place-items: center;
        color: #fff;
        background: linear-gradient(135deg, #1e5aa3, var(--brand));
        box-shadow: 0 24px 55px rgba(15,52,115,.22);
    }
    .security-lock .icon { width: 32px; height: 32px; }
    .security-rings span {
        position: absolute;
        left: 50%;
        top: 50%;
        width: 110px;
        height: 110px;
        border-radius: 999px;
        border: 1px solid rgba(15,52,115,.14);
        transform: translate(-50%, -50%);
        animation: ring-pulse 3s ease-in-out infinite;
    }
    .security-rings span:nth-child(2) { width: 170px; height: 170px; animation-delay: .3s; }
    .security-rings span:nth-child(3) { width: 230px; height: 230px; animation-delay: .6s; }
    .audit-lines {
        position: absolute;
        right: 10%;
        top: 50%;
        display: grid;
        gap: 10px;
        width: 190px;
        transform: translateY(-50%);
    }
    .audit-lines span {
        height: 10px;
        border-radius: 999px;
        background: rgba(15,52,115,.13);
        animation: data-pulse 2s ease-in-out infinite;
    }
    .audit-lines span:nth-child(2) { width: 74%; animation-delay: .2s; }
    .audit-lines span:nth-child(3) { width: 88%; animation-delay: .4s; }
    .audit-lines span:nth-child(4) { width: 58%; animation-delay: .6s; }
    .site-footer {
        background: #071a3d;
        color: #cbd5e1;
        padding: 54px 0 24px;
    }
    .site-footer .brand-logo-blue { display: none; }
    .site-footer .brand-logo-white { display: block; }
    .footer-grid {
        display: grid;
        grid-template-columns: minmax(240px, 1.6fr) repeat(3, minmax(130px, .7fr));
        gap: 36px;
        padding-bottom: 34px;
    }
    .footer-brand p {
        max-width: 430px;
        margin: 16px 0 0;
        color: #9fb1ce;
        line-height: 1.7;
    }
    .footer-col {
        display: grid;
        gap: 10px;
        align-content: start;
    }
    .footer-col strong {
        color: #fff;
        margin-bottom: 4px;
    }
    .footer-col a,
    .footer-col span {
        color: #9fb1ce;
        font-size: 14px;
    }
    .footer-col a:hover { color: #fff; }
    .footer-bottom {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        flex-wrap: wrap;
        padding-top: 22px;
        border-top: 1px solid rgba(255,255,255,.12);
        color: #8fa4c5;
        font-size: 13px;
    }
    .footer-bottom div {
        display: flex;
        gap: 18px;
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
    @keyframes panel-float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-7px); }
    }
    @keyframes person-float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-4px); }
    }
    @keyframes laptop-in {
        0% { opacity: 0; transform: translateX(-50%) translateY(14px) scale(.94); }
        100% { opacity: 1; transform: translateX(-50%) translateY(0) scale(1); }
    }
    @keyframes data-pulse {
        0%, 100% { opacity: .45; transform: scaleX(.72); transform-origin: left; }
        50% { opacity: 1; transform: scaleX(1); }
    }
    @keyframes flow-sweep {
        0% { opacity: 0; clip-path: inset(0 100% 0 0); }
        35% { opacity: 1; }
        100% { opacity: 0; clip-path: inset(0 0 0 100%); }
    }
    @keyframes ring-pulse {
        0%, 100% { opacity: .42; transform: translate(-50%, -50%) scale(.96); }
        50% { opacity: .95; transform: translate(-50%, -50%) scale(1.04); }
    }
    @keyframes core-ring-pulse {
        0%, 100% { opacity: .42; transform: scale(.96); }
        50% { opacity: .95; transform: scale(1.04); }
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
    .hero .hero-art,
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
        .section-split, .section-split.section-split-reverse { grid-template-columns: 1fr; }
        .section-split-reverse .section-split-art { order: -1; }
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
        .hero-art { min-height: 280px; padding: 18px 12px; }
        .hero-art img { max-height: 260px; }
        .landing-art { padding: 20px 12px; }
        .landing-art img { max-height: 240px; }
        .office-graphic { min-height: 260px; }
        .office-panel { width: 150px; }
        .panel-left { left: 12px; top: 14px; }
        .panel-right { right: 12px; bottom: 14px; }
        .line-one, .line-two { display: none; }
        .footer-grid { grid-template-columns: 1fr; gap: 24px; }
        .footer-bottom { flex-direction: column; }
        .landing-visual { min-height: 360px; }
        .sync-core { width: 138px; height: 138px; }
        .sync-core strong { font-size: 20px; }
        .sync-node { min-width: 126px; padding: 10px 12px; }
        .node-hr { left: 6%; top: 20px; }
        .node-time { right: 6%; top: 76px; }
        .node-finance { left: 6%; bottom: 78px; }
        .node-slip { right: 6%; bottom: 20px; }
        .sync-path { display: none; }
        .dashboard-visual { min-height: 330px; }
        .dashboard-window { width: calc(100% - 28px); height: 245px; }
        .window-grid { grid-template-columns: 92px 1fr; }
        .floating-note { display: none; }
        .documents-visual { min-height: 340px; }
        .transfer-chip { left: 16px; top: 18px; }
        .audit-chip { right: 16px; bottom: 18px; }
        .audit-lines { display: none; }
        .sidebar { position: static; height: auto; }
        .landing-links { display: none; }
        .mobile-only { display: inline-flex; }
        .page-head { flex-direction: column; }
        .topbar { padding-inline: 16px; }
        .content { padding: 16px; }
        .auth-hero { padding: 30px; min-height: 360px; }
        .faq-list { max-width: 100%; }
        .cta-register { padding: 32px 24px; text-align: center; }
        .cta-register-content p { max-width: 100%; }
    }

    /* ── Register page ─────────────────────────────────────────── */
    .register-page {
        min-height: 100vh;
        background: var(--page);
        display: flex;
        flex-direction: column;
    }
    .register-nav {
        background: #fff;
        border-bottom: 1px solid var(--line);
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .register-nav-inner {
        width: min(900px, calc(100% - 32px));
        margin-inline: auto;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .register-main {
        flex: 1;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 40px 16px 60px;
    }
    .register-card {
        width: min(780px, 100%);
        padding: 36px 40px;
    }
    .register-header {
        margin-bottom: 28px;
        padding-bottom: 22px;
        border-bottom: 1px solid var(--line);
    }
    .register-header h1 {
        margin: 0 0 6px;
        font-size: 26px;
        color: var(--navy);
    }
    .register-header p { margin: 0; font-size: 14px; }
    .register-form { display: grid; gap: 16px; }
    .register-section-label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--muted);
        padding-bottom: 10px;
        border-bottom: 1px solid var(--line);
        margin-bottom: 4px;
    }
    .register-info-box {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 14px 16px;
        background: var(--brand-soft);
        border: 1px solid var(--brand-line);
        border-radius: 12px;
        color: var(--brand);
        font-size: 13px;
        line-height: 1.6;
        margin-top: 4px;
    }
    .register-info-box svg { flex-shrink: 0; margin-top: 1px; }
    .register-info-box p { margin: 0; }
    .req { color: var(--red); font-weight: 700; }
    .input-phone-wrap {
        display: flex;
        align-items: center;
        border: 1px solid var(--line);
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        transition: border-color .2s ease;
    }
    .input-phone-wrap:focus-within {
        border-color: var(--brand);
        outline: 3px solid rgba(15, 52, 115, .12);
    }
    .phone-prefix {
        padding: 11px 12px;
        font-size: 14px;
        font-weight: 600;
        color: var(--navy);
        background: #f8fafc;
        border-right: 1px solid var(--line);
        white-space: nowrap;
        flex-shrink: 0;
    }
    .input-phone {
        border: none !important;
        border-radius: 0 !important;
        outline: none !important;
        flex: 1;
    }
    .input-phone:focus { outline: none !important; }
    .pwd-meter {
        height: 4px;
        background: var(--line);
        border-radius: 999px;
        margin-top: 8px;
        overflow: hidden;
    }
    .pwd-bar {
        height: 100%;
        width: 0;
        border-radius: 999px;
        transition: width .3s ease, background .3s ease;
    }
    .pwd-label {
        font-size: 12px;
        font-weight: 600;
        margin-top: 4px;
        display: block;
    }
    .checkbox-label-error { color: var(--red); }

    /* ── Auth form extras ──────────────────────────────────────── */
    .input-wrap {
        position: relative;
    }
    .input-wrap .input {
        padding-right: 44px;
    }
    .input-eye {
        position: absolute;
        right: 11px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: var(--muted);
        padding: 4px;
        display: grid;
        place-items: center;
        border-radius: 6px;
        transition: color .15s ease;
    }
    .input-eye:hover { color: var(--navy); }
    .input-error {
        border-color: var(--red) !important;
        background: #fff5f5;
    }
    .input-error:focus {
        outline-color: rgba(220, 38, 38, .18) !important;
    }
    .field-error {
        font-size: 12px;
        color: var(--red);
        font-weight: 600;
        display: block;
        margin-top: 2px;
    }
    .auth-alert {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        padding: 12px 14px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 16px;
        line-height: 1.5;
    }
    .auth-alert-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
    .auth-alert-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
    }
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 7px;
        cursor: pointer;
        color: var(--muted);
        user-select: none;
    }
    .checkbox-label input[type="checkbox"] {
        width: 15px;
        height: 15px;
        accent-color: var(--brand);
        cursor: pointer;
        flex-shrink: 0;
    }
    .auth-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 20px 0;
        color: var(--muted);
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .auth-divider::before,
    .auth-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--line);
    }

    /* ── FAQ Accordion ─────────────────────────────────────────── */
    .faq-list {
        max-width: 720px;
        margin-inline: auto;
        display: grid;
        gap: 12px;
    }
    .faq-item {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 14px;
        overflow: hidden;
        transition: border-color .2s ease, box-shadow .2s ease;
    }
    .faq-item:hover {
        border-color: var(--brand-line);
        box-shadow: 0 4px 16px rgba(15, 52, 115, .07);
    }
    .faq-item.faq-open {
        border-color: var(--brand-line);
        box-shadow: 0 6px 24px rgba(15, 52, 115, .10);
    }
    .faq-trigger {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 22px;
        background: none;
        border: none;
        cursor: pointer;
        text-align: left;
        font-size: 15px;
        font-weight: 650;
        color: var(--navy);
        line-height: 1.4;
        transition: color .18s ease;
    }
    .faq-item.faq-open .faq-trigger { color: var(--brand); }
    .faq-icon-wrap {
        flex: 0 0 32px;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: grid;
        place-items: center;
        background: var(--brand-soft);
        color: var(--brand);
        transition: background .2s ease, transform .3s cubic-bezier(.2, .8, .2, 1);
    }
    .faq-item.faq-open .faq-icon-wrap {
        background: var(--brand);
        color: #fff;
        transform: rotate(180deg);
    }
    .faq-icon {
        width: 16px;
        height: 16px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2.4;
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    .faq-panel {
        max-height: 0;
        overflow: hidden;
        transition: max-height .35s cubic-bezier(.2, .8, .2, 1);
    }
    .faq-panel-inner {
        padding: 0 22px 20px;
        border-top: 1px solid var(--line);
        padding-top: 16px;
    }
    .faq-panel-inner p {
        margin: 0;
        font-size: 14px;
        line-height: 1.75;
        color: var(--muted);
    }

    /* ── CTA Register (no illustration) ────────────────────────── */
    .cta-register {
        text-align: center;
        padding: 64px 32px;
        border-radius: 22px;
        background: linear-gradient(135deg, var(--brand) 0%, #1e5aa3 100%);
        border: 1px solid rgba(255,255,255,.10);
        box-shadow: 0 24px 60px rgba(15, 52, 115, .22);
    }
    .cta-register h2 {
        margin: 0 0 12px;
        font-size: clamp(26px, 3vw, 38px);
        color: #fff;
        font-family: var(--font-display);
        letter-spacing: -0.03em;
    }
    .cta-register p {
        margin: 0 auto 28px;
        max-width: 480px;
        color: rgba(255,255,255,.80);
        font-size: 16px;
        line-height: 1.65;
    }
    .cta-register .btn-primary {
        background: #fff;
        color: var(--brand);
        padding: 13px 28px;
        font-size: 15px;
        box-shadow: 0 8px 24px rgba(15, 52, 115, .20);
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .cta-register .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 32px rgba(15, 52, 115, .28);
    }

    /* ── Alpine.js x-cloak ─────────────────────────────────────── */
    [x-cloak] { display: none !important; }

    /* ── Sidebar Collapsed State ───────────────────────────────── */
    .app-shell:has(.sidebar.collapsed) { grid-template-columns: 64px 1fr; }
    .sidebar.collapsed { width: 64px; padding: 18px 8px; overflow-x: hidden; }
    .sidebar.collapsed .nav-label { display: none; }
    .sidebar.collapsed .nav-title { display: none; }
    .sidebar.collapsed .workspace { padding: 8px; }
    .sidebar.collapsed .workspace strong,
    .sidebar.collapsed .workspace .muted,
    .sidebar.collapsed .workspace form { display: none; }
    .sidebar.collapsed .workspace .badge { display: none; }
    .sidebar.collapsed .nav-link {
        justify-content: center;
        gap: 0;
        padding: 11px 0;
        width: 48px;
        margin-inline: auto;
    }
    .sidebar.collapsed .nav-link > .icon {
        width: 20px;
        min-width: 20px;
        max-width: 20px;
        height: 20px;
        min-height: 20px;
        max-height: 20px;
        flex: 0 0 20px;
    }
    .sidebar.collapsed .brand-logo { width: 32px; height: 32px; object-fit: contain; }
    .sidebar.collapsed .brand { justify-content: center; }
    .sidebar.collapsed .sidebar-toggle { margin-inline: auto; }

    /* Tooltip saat collapsed — pakai ::after pada nav-link */
    .sidebar.collapsed .nav-link {
        position: relative;
    }
    .sidebar.collapsed .nav-link::before {
        content: attr(title);
        position: absolute;
        left: calc(100% + 10px);
        top: 50%;
        transform: translateY(-50%);
        background: #1e293b;
        color: #e2e8f0;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
        padding: 5px 10px;
        border-radius: 7px;
        pointer-events: none;
        opacity: 0;
        transition: opacity .15s ease;
        z-index: 999;
        box-shadow: 0 4px 12px rgba(0,0,0,.2);
    }
    .sidebar.collapsed .nav-link:hover::before {
        opacity: 1;
    }
    /* Override ::after underline on collapsed */
    .sidebar.collapsed .nav-link::after {
        display: none;
    }

    /* ── Responsive Breakpoints ─────────────────────────────────── */
    @media (max-width: 768px) {
        .app-shell,
        .app-shell:has(.sidebar.collapsed) { grid-template-columns: minmax(0, 1fr); }
        .topbar {
            height: auto;
            min-height: 64px;
            padding: 12px 16px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .topbar > div:last-child {
            width: 100%;
            min-width: 0;
            flex-wrap: wrap;
        }
        .grid-4 { grid-template-columns: repeat(2, 1fr); }
        .grid-2 { grid-template-columns: 1fr; }
        /* 17.5 — Touch target: ensure 44px minimum for all tappable elements */
        button,
        a[href] {
            min-height: 44px;
        }
        a[href] {
            display: inline-flex;
            align-items: center;
        }
    }

    @media (max-width: 480px) {
        .grid-4 { grid-template-columns: 1fr; }
    }

    /* ── Skeleton Loader ────────────────────────────────────────── */
    @keyframes skeleton-shimmer {
        0%   { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    .skeleton {
        background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
        background-size: 200% 100%;
        animation: skeleton-shimmer 1.5s infinite;
        border-radius: 8px;
        will-change: background-position;
    }
    .skeleton-card   { height: 120px; }
    .skeleton-row    { height: 44px; margin-bottom: 8px; border-radius: 8px; }
    .skeleton-text   { height: 14px; width: 80%; margin-bottom: 4px; border-radius: 8px; }
    .skeleton-circle { height: 40px; width: 40px; border-radius: 50%; }

    /* ── Toast Notification System (9.3 + 9.4) ─────────────────── */
    @keyframes toast-progress {
        0%   { width: 100%; }
        100% { width: 0%; }
    }
    @keyframes toast-in {
        0%   { opacity: 0; transform: translateX(40px) scale(.96); }
        100% { opacity: 1; transform: translateX(0)    scale(1);   }
    }
    @keyframes toast-out {
        0%   { opacity: 1; transform: translateX(0)    scale(1);   max-height: 120px; margin-bottom: 10px; }
        100% { opacity: 0; transform: translateX(40px) scale(.96); max-height: 0;     margin-bottom: 0;   }
    }
    .toast {
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        min-width: 300px;
        max-width: 420px;
        padding: 13px 14px 20px;
        border-radius: 12px;
        border: 1px solid transparent;
        background: #fff;
        box-shadow: 0 8px 28px rgba(15, 23, 42, .14), 0 2px 8px rgba(15, 23, 42, .06);
        font-size: 14px;
        font-weight: 500;
        line-height: 1.45;
        overflow: hidden;
        margin-bottom: 10px;
        animation: toast-in .28s cubic-bezier(.2, .8, .2, 1) both;
    }
    .toast-icon {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        margin-top: 1px;
    }
    .toast-body {
        flex: 1;
        min-width: 0;
    }
    .toast-dismiss {
        flex-shrink: 0;
        width: 24px;
        height: 24px;
        border-radius: 6px;
        border: none;
        background: none;
        cursor: pointer;
        display: grid;
        place-items: center;
        color: inherit;
        opacity: .55;
        transition: opacity .15s ease, background .15s ease;
        padding: 0;
        margin-top: -1px;
    }
    .toast-dismiss:hover { opacity: 1; background: rgba(0,0,0,.07); }
    .toast-bar {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        border-radius: 0 0 12px 12px;
        animation: toast-progress var(--toast-duration, 4000ms) linear forwards;
    }
    /* Variant: success */
    .toast-success {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #14532d;
    }
    .toast-success .toast-icon { color: #16a34a; }
    .toast-success .toast-bar  { background: #16a34a; }
    /* Variant: error */
    .toast-error {
        background: #fef2f2;
        border-color: #fecaca;
        color: #7f1d1d;
    }
    .toast-error .toast-icon { color: #dc2626; }
    .toast-error .toast-bar  { background: #dc2626; }
    /* Variant: warning */
    .toast-warning {
        background: #fffbeb;
        border-color: #fde68a;
        color: #78350f;
    }
    .toast-warning .toast-icon { color: #d97706; }
    .toast-warning .toast-bar  { background: #d97706; }
    /* Variant: info */
    .toast-info {
        background: var(--brand-soft);
        border-color: var(--brand-line);
        color: var(--brand);
    }
    .toast-info .toast-icon { color: var(--brand); }
    .toast-info .toast-bar  { background: var(--brand); }

    /* ── Inline Form Validation (10.1) ─────────────────────────── */
    .input.error {
        border-color: var(--red);
        box-shadow: 0 0 0 3px rgba(220, 38, 38, .12);
    }
    .input.error:focus {
        outline: 3px solid rgba(220, 38, 38, .18);
        border-color: var(--red);
    }
    .input.valid {
        border-color: var(--green, #16a34a);
        box-shadow: 0 0 0 3px rgba(22, 163, 74, .10);
    }
    .input.valid:focus {
        outline: 3px solid rgba(22, 163, 74, .16);
        border-color: var(--green, #16a34a);
    }
    .field-error {
        color: var(--red);
        font-size: 12px;
        font-weight: 600;
        margin-top: 4px;
        display: block;
    }
    .form-error-summary {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 16px;
        border-radius: 12px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #7f1d1d;
        margin-bottom: 20px;
    }
    .form-error-summary-icon {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        color: #dc2626;
        margin-top: 1px;
    }
    .form-error-summary-body { flex: 1; min-width: 0; }
    .form-error-summary-title {
        font-size: 13px;
        font-weight: 700;
        margin: 0 0 6px;
        color: #991b1b;
    }
    .form-error-summary ul {
        margin: 0;
        padding-left: 18px;
        font-size: 13px;
        line-height: 1.6;
    }
    .form-error-summary ul li { margin-bottom: 2px; }

    /* ── Confirm Modal (11.2 + 11.3) ───────────────────────────── */
    @keyframes modal-fade-in {
        0%   { opacity: 0; transform: scale(.96) translateY(-8px); }
        100% { opacity: 1; transform: scale(1)   translateY(0);    }
    }
    @keyframes modal-fade-out {
        0%   { opacity: 1; transform: scale(1)   translateY(0);    }
        100% { opacity: 0; transform: scale(.96) translateY(-8px); }
    }
    .modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(15, 23, 42, .55);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }
    /* When Alpine x-show re-enables the overlay, restore flex */
    .modal-overlay[style*="display: block"],
    .modal-overlay[style*="display:block"] {
        display: flex !important;
    }
    /* Any visible modal-overlay (x-cloak removed by Alpine) should be flex */
    .modal-overlay:not([x-cloak]):not([style*="display: none"]):not([style*="display:none"]) {
        display: flex !important;
    }
    .modal-dialog {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 20px;
        padding: 32px;
        width: min(500px, 100%);
        max-height: calc(100vh - 48px);
        overflow-y: auto;
        box-shadow: 0 32px 80px rgba(15, 23, 42, .28), 0 4px 16px rgba(15, 23, 42, .10);
        animation: modal-fade-in .22s cubic-bezier(.2, .8, .2, 1) both;
        position: relative;
    }
    .modal-fade-enter { animation: modal-fade-in .22s cubic-bezier(.2, .8, .2, 1) both; }
    .modal-fade-leave { animation: modal-fade-out .18s cubic-bezier(.2, .8, .2, 1) both; }
    .modal-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
    }
    .modal-icon--danger { background: #fee2e2; color: #dc2626; }
    .modal-icon--warning { background: #fef3c7; color: #d97706; }
    .modal-icon--info { background: var(--brand-soft); color: var(--brand); }
    .modal-title {
        margin: 0 0 6px;
        font-size: 20px;
        font-weight: 700;
        color: var(--navy);
        font-family: var(--font-display);
        letter-spacing: -0.02em;
    }
    .modal-message {
        margin: 0 0 24px;
        font-size: 14px;
        color: var(--muted);
        line-height: 1.65;
    }
    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    /* ── Pagination (Task 12) ───────────────────────────────────── */
    .pagination-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding: 14px 16px;
        border-top: 1px solid var(--line);
        background: #fafbfc;
        border-radius: 0 0 14px 14px;
    }
    .pagination-info {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        font-size: 13px;
    }
    .per-page-form {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .per-page-select {
        width: auto;
        padding: 6px 10px;
        font-size: 13px;
        border-radius: 8px;
        cursor: pointer;
    }
    .pagination-nav {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 34px;
        padding: 0 6px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        color: var(--navy);
        background: #fff;
        border: 1px solid var(--line);
        text-decoration: none;
        transition: background .15s, border-color .15s, color .15s;
        cursor: pointer;
        user-select: none;
    }
    .page-btn:hover {
        background: var(--brand-soft);
        border-color: var(--brand-line);
        color: var(--brand);
    }
    .page-btn-active {
        background: var(--brand);
        border-color: var(--brand);
        color: #fff;
        cursor: default;
        pointer-events: none;
    }
    .page-btn-disabled {
        background: #f8fafc;
        border-color: var(--line);
        color: #cbd5e1;
        cursor: default;
        pointer-events: none;
    }
    .page-ellipsis {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 34px;
        font-size: 13px;
        color: var(--muted);
        user-select: none;
    }

    /* ══════════════════════════════════════════════════════════════
       MODERN TOPBAR — profile moved to navbar right side
    ══════════════════════════════════════════════════════════════ */
    .topbar {
        height: 64px;
        background: rgba(255,255,255,.96);
        backdrop-filter: blur(12px);
        border-bottom: 1px solid var(--line);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        position: sticky;
        top: 0;
        z-index: 200;
        gap: 16px;
        overflow: visible;
    }
    .topbar-left {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }
    .topbar-menu-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 9px;
        border: 1px solid var(--line);
        background: #fff;
        cursor: pointer;
        color: var(--muted);
        flex-shrink: 0;
        transition: background .15s, color .15s;
    }
    .topbar-menu-btn:hover { background: var(--brand-soft); color: var(--brand); }
    .topbar-breadcrumb {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 14px;
        white-space: nowrap;
        overflow: hidden;
    }
    .topbar-brand { font-weight: 800; color: var(--brand); font-family: var(--font-display); }
    .topbar-page {
        color: var(--navy);
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .topbar-right {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
    }
    .topbar-search {
        position: relative;
        display: block;
    }
    .topbar-search-input {
        width: 200px;
        padding: 7px 10px 7px 32px !important;
        font-size: 13px;
        border-radius: 9px;
        height: 34px;
        line-height: 1;
    }

    /* Icon buttons (bell, etc.) */
    .topbar-icon-btn {
        position: relative;
        display: flex;
        align-items: center;
    }
    .topbar-icon-btn-inner {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 9px;
        border: 1px solid var(--line);
        background: #fff;
        cursor: pointer;
        color: var(--muted);
        flex-shrink: 0;
        transition: background .15s, color .15s;
        padding: 0;
    }
    .topbar-icon-btn-inner:hover { background: var(--brand-soft); color: var(--brand); }
    .topbar-icon-btn-inner svg {
        display: block;
        flex-shrink: 0;
    }
    .topbar-notif-dot {
        position: absolute;
        top: 7px;
        right: 7px;
        width: 7px;
        height: 7px;
        border-radius: 999px;
        background: #ef4444;
        border: 2px solid #fff;
        pointer-events: none;
    }

    /* Topbar dropdown shared */
    .topbar-dropdown {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        z-index: 500;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 14px;
        box-shadow: 0 16px 48px rgba(15,23,42,.14), 0 4px 16px rgba(15,23,42,.07);
        padding: 8px;
        min-width: 220px;
    }
    .topbar-dropdown--right {
        left: auto;
        right: 0;
        min-width: 240px;
    }
    .topbar-dropdown-head {
        padding: 8px 10px 10px;
        font-size: 13px;
        color: var(--muted);
        font-weight: 600;
        border-bottom: 1px solid var(--line);
        margin-bottom: 6px;
    }
    .topbar-dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 10px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        color: var(--navy);
        cursor: default;
    }
    .topbar-dropdown-item svg {
        flex-shrink: 0;
        color: var(--muted);
    }
    .topbar-dropdown-item--link {
        cursor: pointer;
        transition: background .12s;
        text-decoration: none;
    }
    .topbar-dropdown-item--link:hover { background: var(--brand-soft); color: var(--brand); }
    .topbar-dropdown-item--link:hover svg { color: var(--brand); }
    .topbar-dropdown-item--danger:hover { background: #fef2f2; color: var(--red); }
    .topbar-dropdown-item--danger:hover svg { color: var(--red); }
    .topbar-dropdown-dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        flex-shrink: 0;
    }
    .topbar-dropdown-dot--amber { background: var(--amber); }
    .topbar-dropdown-dot--blue  { background: var(--brand); }
    .topbar-dropdown-dot--green { background: var(--green); }
    .topbar-dropdown-footer {
        display: block;
        text-align: center;
        font-size: 12px;
        font-weight: 700;
        color: var(--brand);
        padding: 10px;
        border-top: 1px solid var(--line);
        margin-top: 6px;
        border-radius: 0 0 10px 10px;
        transition: background .12s;
    }
    .topbar-dropdown-footer:hover { background: var(--brand-soft); }

    /* User profile button — simple: avatar + name + chevron */
    .topbar-user-wrap { position: relative; }
    .topbar-user-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 4px 10px 4px 4px;
        border-radius: 999px;
        border: 1px solid var(--line);
        background: #fff;
        cursor: pointer;
        transition: background .15s, border-color .15s;
        height: 38px;
    }
    .topbar-user-btn:hover { background: var(--brand-soft); border-color: var(--brand-line); }
    .topbar-avatar {
        width: 30px;
        height: 30px;
        border-radius: 999px;
        background: linear-gradient(135deg, #1e5aa3, var(--brand));
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        letter-spacing: 0;
        line-height: 1;
    }
    .topbar-user-name {
        font-size: 13px;
        font-weight: 700;
        color: var(--navy);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 140px;
        line-height: 1;
    }

    /* Sidebar bottom stub */
    .sidebar-bottom {
        margin-top: 24px;
        padding: 10px 12px;
    }
    .sidebar-version {
        font-size: 11px;
        color: #475569;
        font-weight: 600;
    }

    /* ══════════════════════════════════════════════════════════════
       MODERN KPI CARDS
    ══════════════════════════════════════════════════════════════ */
    .kpi-modern {
        padding: 20px;
        border-radius: 16px;
        background: var(--surface);
        border: 1px solid var(--line);
        box-shadow: 0 1px 6px rgba(0,0,0,.06);
        display: flex;
        flex-direction: column;
        gap: 8px;
        position: relative;
        overflow: hidden;
        transition: box-shadow .2s, transform .2s;
    }
    .kpi-modern:hover {
        box-shadow: 0 6px 24px rgba(15,52,115,.10);
        transform: translateY(-1px);
    }

    .kpi-icon-wrap {
        position: absolute;
        top: 0;
        right: 0;
        width: 52px;
        height: 52px;
        border-radius: 0 16px 0 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .kpi-icon-wrap svg { display: block; }
    .kpi-icon-wrap.blue  { background: rgba(15,52,115,.10);  color: var(--brand); }
    .kpi-icon-wrap.green { background: rgba(22,163,74,.10);  color: var(--green); }
    .kpi-icon-wrap.amber { background: rgba(217,119,6,.10);  color: var(--amber); }
    .kpi-icon-wrap.red   { background: rgba(220,38,38,.10);  color: var(--red);   }

    .kpi-label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--muted);
    }
    .kpi-value {
        font-size: 30px;
        font-weight: 800;
        color: var(--navy);
        line-height: 1.1;
        font-family: var(--font-display);
        letter-spacing: -0.03em;
    }
    .kpi-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    /* ══════════════════════════════════════════════════════════════
       DASHBOARD CHART CONTAINER
    ══════════════════════════════════════════════════════════════ */
    .chart-wrap {
        position: relative;
        width: 100%;
    }
    .chart-wrap canvas {
        max-height: 220px;
    }

    /* ══════════════════════════════════════════════════════════════
       DASHBOARD SECTION ENHANCEMENTS
    ══════════════════════════════════════════════════════════════ */
    .section-card {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,.05);
    }
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 20px 14px;
        border-bottom: 1px solid var(--line);
    }
    .section-header h2 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: var(--navy);
    }
    .section-content { padding: 20px; }

    /* Timeline modern */
    .timeline-modern { display: grid; gap: 0; }
    .timeline-item {
        display: grid;
        grid-template-columns: 36px 1fr auto;
        gap: 12px;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid var(--line);
    }
    .timeline-item:last-child { border-bottom: 0; }
    .timeline-step {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        font-size: 13px;
        font-weight: 800;
        flex-shrink: 0;
    }
    .timeline-step.done  { background: #dcfce7; color: #166534; }
    .timeline-step.active { background: #fef3c7; color: #92400e; }
    .timeline-step.wait  { background: #f1f5f9; color: #94a3b8; }

    /* Action Center cards */
    .action-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 14px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid var(--line);
    }
    .action-dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        flex-shrink: 0;
        margin-top: 5px;
    }

    /* Progress bar modern */
    .progress-modern {
        display: grid;
        gap: 6px;
    }
    .progress-modern-bar {
        height: 8px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }
    .progress-modern-bar span {
        display: block;
        height: 100%;
        border-radius: inherit;
        transform-origin: left;
        animation: progress-fill .9s cubic-bezier(.2, .8, .2, 1) both;
    }
    .bar-green { background: linear-gradient(90deg, #16a34a, #22c55e); }
    .bar-blue  { background: linear-gradient(90deg, var(--brand), #4f86cf); }
    .bar-red   { background: linear-gradient(90deg, #dc2626, #f87171); }

    /* Payslip detail list */
    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 9px 0;
        border-bottom: 1px solid var(--line);
        font-size: 14px;
    }
    .detail-row:last-child { border-bottom: 0; }
    .detail-label { color: var(--muted); }
    .detail-value { font-weight: 600; color: var(--navy); }

    /* Quick action buttons */
    .quick-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }
    .quick-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        border: 1px solid var(--line);
        background: #fff;
        cursor: pointer;
        color: var(--navy);
        text-decoration: none;
        transition: background .15s, border-color .15s, color .15s;
    }
    .quick-action-btn:hover { background: var(--brand-soft); border-color: var(--brand-line); color: var(--brand); }
    .quick-action-btn.primary { background: var(--brand); color: #fff; border-color: var(--brand); }
    .quick-action-btn.primary:hover { background: var(--brand-dark); }
</style>
