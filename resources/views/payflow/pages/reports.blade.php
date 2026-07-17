<div class="grid grid-3">
    @foreach ([
        ['Payroll Report','payroll'],
        ['Attendance Report','calendar'],
        ['Disbursement Report','bank'],
        ['Reconciliation Report','link'],
        ['Audit Report','shield'],
    ] as $report)
        <div class="card feature-card"><div class="icon-box">@include('payflow.partials.icon', ['name' => $report[1], 'class' => 'icon icon-lg'])</div><h3>{{ $report[0] }}</h3><p class="muted">Last generated 17 Jul 2026. Filter periode wajib untuk laporan transaksi.</p><button class="btn btn-secondary">Open Report</button></div>
    @endforeach
</div>
<section class="card" style="margin-top:16px;"><div class="section-title"><h2>Payroll Report Preview</h2><div><button class="btn btn-secondary">@include('payflow.partials.icon', ['name' => 'download', 'class' => 'icon icon-sm']) Export PDF</button><button class="btn btn-secondary">@include('payflow.partials.icon', ['name' => 'download', 'class' => 'icon icon-sm']) Export Excel</button></div></div>@include('payflow.pages.parts.payroll-table')</section>
