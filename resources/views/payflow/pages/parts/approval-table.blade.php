<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Periode</th>
                <th>Submitter</th>
                <th>Karyawan</th>
                <th>Gross</th>
                <th>Deduction</th>
                <th>Net Pay</th>
                <th>Anomali</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($approvalQueue as $payroll)
                <tr>
                    <td>{{ $payroll->period_label }}</td>
                    <td>{{ $payroll->submitter?->name ?? '-' }}</td>
                    <td>{{ number_format($payroll->employee_count, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format((float) $payroll->gross_total / 1_000_000, 2, ',', '.') }} Jt</td>
                    <td>Rp {{ number_format((float) $payroll->deduction_total / 1_000_000, 2, ',', '.') }} Jt</td>
                    <td>Rp {{ number_format((float) $payroll->net_total / 1_000_000, 2, ',', '.') }} Jt</td>
                    <td>
                        @if ($payroll->anomaly_count > 0)
                            <span class="badge badge-red">{{ $payroll->anomaly_count }}</span>
                        @else
                            <span class="badge badge-green">0</span>
                        @endif
                    </td>
                    <td>
                        @if ($isSuperAdminViewing ?? false)
                            <span class="badge badge-amber" title="Hanya tersedia untuk Tim HR/Finance">View Only</span>
                        @else
                            <a class="btn btn-secondary" href="/app/approval">Review</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:32px; color: var(--muted);">
                        <div style="display:flex; flex-direction:column; align-items:center; gap:8px;">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Tidak ada payroll yang menunggu persetujuan</span>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
