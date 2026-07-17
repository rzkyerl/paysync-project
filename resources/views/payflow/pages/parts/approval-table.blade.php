<div class="table-wrap">
    <table>
        <thead><tr><th>Periode</th><th>Submitter</th><th>Karyawan</th><th>Gross</th><th>Deduction</th><th>Net Pay</th><th>Anomali</th><th>Aksi</th></tr></thead>
        <tbody>
            @foreach ([['Juli 2026','Rina Maharani',248,'Rp1,68 M','Rp438 jt','Rp1,24 M',3],['THR 2026','Sari Wijaya',240,'Rp980 jt','Rp21 jt','Rp959 jt',0],['Juni 2026 Adj.','Rina Maharani',12,'Rp74 jt','Rp5 jt','Rp69 jt',1]] as $row)
                <tr>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach<td><a class="btn btn-secondary" href="/app/approval">Review</a></td></tr>
            @endforeach
        </tbody>
    </table>
</div>
