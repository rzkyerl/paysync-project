<div class="table-wrap">
    <table>
        <thead><tr><th>Batch Ref</th><th>Payroll Period</th><th>Transfer</th><th>Total Amount</th><th>Success</th><th>Failed</th><th>Status</th><th>Created By</th><th>Aksi</th></tr></thead>
        <tbody>
            @foreach ([['BT-202607-001','Juli 2026',248,'Rp1,24 M',231,17,'Partially Completed','Budi'],['BT-202606-001','Juni 2026',241,'Rp1,19 M',241,0,'Completed','Budi'],['BT-THR-2026','THR 2026',240,'Rp959 jt',0,0,'Ready','Rina']] as $row)
                <tr>@foreach(array_slice($row,0,6) as $cell)<td>{{ $cell }}</td>@endforeach<td><span class="badge {{ $row[6] === 'Completed' ? 'badge-green' : ($row[6] === 'Ready' ? 'badge-blue' : 'badge-amber') }}">{{ $row[6] }}</span></td><td>{{ $row[7] }}</td><td><button class="btn btn-secondary">Detail</button></td></tr>
            @endforeach
        </tbody>
    </table>
</div>
