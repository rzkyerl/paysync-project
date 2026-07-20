@php
    $user     = auth()->user();
    $company  = $employee->company;
    $statusColor = match($payroll->status) {
        'disbursed' => '#16a34a',
        'approved'  => '#2563eb',
        default     => '#d97706',
    };
    $statusLabel = match($payroll->status) {
        'disbursed' => 'Transfer Selesai',
        'approved'  => 'Disetujui',
        default     => ucfirst(str_replace('_', ' ', $payroll->status)),
    };
    $earnings = [
        'Gaji Pokok'     => (float)$item->basic_salary_snapshot,
        'Bayaran Lembur' => (float)$item->overtime_pay,
    ];
    $deductions = [
        'BPJS TK'        => (float)$item->bpjs_tk_deduction,
        'BPJS Kesehatan' => (float)$item->bpjs_kesehatan_deduction,
        'PPh 21'         => (float)$item->pph21_deduction,
    ];
    $grossPay       = array_sum($earnings);
    $totalDeduction = array_sum($deductions);
    $bankDisplay    = $employee->bank_name
        ? $employee->bank_name . ' •••• ' . substr($employee->bank_account_number ?? '????', -4)
        : '-';
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Slip Gaji – {{ $employee->name }} – {{ $payroll->period_label }}</title>
    @include('payflow.partials.styles')
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .slip-wrapper { box-shadow: none !important; border: 1px solid #e2e8f0 !important; }
        }
        body { background: var(--bg); }
        .slip-wrapper {
            max-width: 720px;
            margin: 32px auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 40px rgba(15,23,42,.10), 0 2px 8px rgba(15,23,42,.06);
            overflow: hidden;
        }
        .slip-header {
            padding: 28px 32px 24px;
            border-bottom: 2px solid var(--line);
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }
        .slip-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .04em;
        }
        .slip-employee {
            padding: 20px 32px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            background: #f8fafc;
            border-bottom: 1px solid var(--line);
        }
        .slip-field label {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--muted);
            display: block;
            margin-bottom: 3px;
        }
        .slip-field span {
            font-size: 14px;
            font-weight: 600;
            color: var(--navy);
        }
        .slip-body {
            padding: 24px 32px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            border-bottom: 1px solid var(--line);
        }
        .slip-section-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted);
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--line);
        }
        .slip-line {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .slip-line-label { color: #475569; }
        .slip-line-value { font-weight: 600; color: var(--navy); font-family: var(--font-display); }
        .slip-line-value.deduction { color: #dc2626; }
        .slip-subtotal {
            display: flex;
            justify-content: space-between;
            padding-top: 10px;
            margin-top: 4px;
            border-top: 1px dashed var(--line);
            font-size: 13px;
            font-weight: 700;
            color: var(--navy);
        }
        .slip-net {
            padding: 20px 32px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(135deg, var(--brand-soft) 0%, #e0f2fe 100%);
        }
        .slip-net-label {
            font-size: 13px;
            font-weight: 700;
            color: var(--brand);
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        .slip-net-value {
            font-size: 28px;
            font-weight: 900;
            color: var(--brand);
            font-family: var(--font-display);
            letter-spacing: -.02em;
        }
        .slip-footer {
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid var(--line);
            font-size: 12px;
            color: var(--muted);
        }
    </style>
</head>
<body>

{{-- Top nav bar --}}
<div class="no-print" style="background:#fff; border-bottom:1px solid var(--line); padding:14px 24px; display:flex; align-items:center; justify-content:space-between; gap:12px; position:sticky; top:0; z-index:100;">
    <div style="display:flex; align-items:center; gap:16px;">
        @include('payflow.partials.brand')
        <span style="color:var(--line);">|</span>
        <span style="font-size:14px; color:var(--muted);">Slip Gaji · {{ $payroll->period_label }}</span>
    </div>
    <div style="display:flex; gap:8px;">
        @php $backUrl = $user->isEmployee() ? route('payroll.my-payslips') : route('app', ['page' => 'payslips']); @endphp
        <a href="{{ $backUrl }}" class="btn btn-secondary no-print">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Kembali
        </a>
        <button onclick="window.print()" class="btn btn-primary no-print">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Cetak / PDF
        </button>
    </div>
</div>

{{-- Slip card --}}
<div class="slip-wrapper">

    {{-- Header: logo + company + status --}}
    <div class="slip-header">
        <div>
            @include('payflow.partials.brand')
            <div style="margin-top:10px;">
                <div style="font-size:13px; font-weight:700; color:var(--navy);">{{ $company?->name ?? 'PT Perusahaan' }}</div>
                @if($company?->industry)
                <div style="font-size:12px; color:var(--muted);">{{ $company->industry }}</div>
                @endif
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:20px; font-weight:900; color:var(--navy); font-family:var(--font-display); letter-spacing:-.02em;">Slip Gaji</div>
            <div style="font-size:13px; color:var(--muted); margin-top:2px;">Periode {{ $payroll->period_label }}</div>
            <div style="margin-top:10px;">
                <span class="slip-status-pill" style="background:{{ $statusColor }}18; color:{{ $statusColor }};">
                    <svg width="8" height="8" fill="{{ $statusColor }}" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
                    {{ $statusLabel }}
                </span>
            </div>
        </div>
    </div>

    {{-- Employee info --}}
    <div class="slip-employee">
        <div class="slip-field">
            <label>Nama Karyawan</label>
            <span>{{ $employee->name }}</span>
        </div>
        <div class="slip-field">
            <label>NIP</label>
            <span style="font-family:monospace;">{{ $employee->nip ?? '-' }}</span>
        </div>
        <div class="slip-field">
            <label>Jabatan</label>
            <span>{{ $employee->position ?? '-' }}</span>
        </div>
        <div class="slip-field">
            <label>Departemen</label>
            <span>{{ $employee->department ?? '-' }}</span>
        </div>
        <div class="slip-field">
            <label>Status</label>
            <span>{{ ucfirst(str_replace('_', ' ', $employee->work_status ?? '-')) }}</span>
        </div>
        <div class="slip-field">
            <label>Rekening Bank</label>
            <span>{{ $bankDisplay }}</span>
        </div>
    </div>

    {{-- Earnings & Deductions --}}
    <div class="slip-body">
        {{-- Earnings --}}
        <div>
            <div class="slip-section-title">Pendapatan</div>
            @foreach($earnings as $label => $amount)
            <div class="slip-line">
                <span class="slip-line-label">{{ $label }}</span>
                <span class="slip-line-value">Rp {{ number_format($amount, 0, ',', '.') }}</span>
            </div>
            @endforeach
            <div class="slip-subtotal">
                <span>Total Pendapatan</span>
                <span>Rp {{ number_format($grossPay, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Deductions --}}
        <div>
            <div class="slip-section-title">Potongan</div>
            @foreach($deductions as $label => $amount)
            <div class="slip-line">
                <span class="slip-line-label">{{ $label }}</span>
                <span class="slip-line-value deduction">Rp {{ number_format($amount, 0, ',', '.') }}</span>
            </div>
            @endforeach
            <div class="slip-subtotal">
                <span>Total Potongan</span>
                <span style="color:#dc2626;">Rp {{ number_format($totalDeduction, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- Net Pay --}}
    <div class="slip-net">
        <div>
            <div class="slip-net-label">Take-home Pay</div>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;">
                Pendapatan Rp {{ number_format($grossPay, 0, ',', '.') }} − Potongan Rp {{ number_format($totalDeduction, 0, ',', '.') }}
            </div>
        </div>
        <div class="slip-net-value">Rp {{ number_format((float)$item->net_pay, 0, ',', '.') }}</div>
    </div>

    {{-- Footer --}}
    <div class="slip-footer">
        <span>Diterbitkan oleh sistem PaySync · {{ now()->format('d M Y') }}</span>
        <span style="font-family:monospace; font-size:11px; color:#cbd5e1;">
            REF: PAY-{{ str_pad($payroll->id, 4, '0', STR_PAD_LEFT) }}-EMP-{{ str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}
        </span>
    </div>

</div>

<script defer src="{{ asset('vendor/alpinejs/cdn.min.js') }}"></script>
</body>
</html>
