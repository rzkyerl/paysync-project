<div class="grid grid-4">
    @foreach ([['Karyawan','248'],['Gross Income','Rp1,68 M'],['Total Deduction','Rp438 jt'],['Take-home Pay','Rp1,24 M']] as $kpi)
        <div class="card kpi"><span class="muted">{{ $kpi[0] }}</span><div class="value">{{ $kpi[1] }}</div></div>
    @endforeach
</div>
<section class="card" style="margin-top:16px;">
    <div class="section-title"><h2>Payroll Juli 2026</h2><span class="badge badge-amber">Needs Review</span></div>
    <div class="section-body">
        <div class="grid grid-6" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:8px;">
            @foreach (['Data Input','Kalkulasi','Review Anomali','Approval','Finalisasi','Disbursement'] as $i => $step)
                <span class="badge {{ $i < 2 ? 'badge-green' : ($i === 2 ? 'badge-amber' : '') }}">{{ $step }}</span>
            @endforeach
        </div>
    </div>
    @include('payflow.pages.parts.payroll-table')
    <div class="section-body" style="display:flex; gap:10px; flex-wrap:wrap;"><button class="btn btn-secondary">Recalculate</button><button class="btn btn-secondary">Save Draft</button><a class="btn btn-primary" href="/app/approval">Submit for Approval</a></div>
</section>
