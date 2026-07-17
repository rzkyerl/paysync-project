<div class="toolbar"><input class="input" style="max-width:220px;" placeholder="Actor"><select class="input" style="max-width:180px;"><option>Module</option></select><input class="input" style="max-width:220px;" type="date"></div>
<section class="card">
    <div class="section-title"><h2>Audit Log Read-only</h2><span class="badge">Before/after tanpa data sensitif</span></div>
    <div class="table-wrap"><table><thead><tr><th>Timestamp</th><th>Actor</th><th>Action</th><th>Subject</th><th>Module</th><th>IP</th><th>Aksi</th></tr></thead><tbody>
        @foreach ([['17 Jul 2026 09:12','Rina','UPDATED','Payroll JUL-2026','Payroll','127.0.0.1'],['17 Jul 2026 09:45','Budi','APPROVED','Payroll JUL-2026','Approval','127.0.0.1'],['17 Jul 2026 10:03','System','RETRIED','BT-202607-001','Disbursement','127.0.0.1']] as $row)
            <tr>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach<td><button class="btn btn-secondary">Detail</button></td></tr>
        @endforeach
    </tbody></table></div>
</section>
