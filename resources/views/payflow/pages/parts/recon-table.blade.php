<div class="table-wrap">
    <table>
        <thead><tr><th>Employee</th><th>Payroll Net Pay</th><th>Transferred Amount</th><th>Difference</th><th>Status</th><th>Reason</th></tr></thead>
        <tbody>
            @foreach ([['Rina Maharani','Rp10.230.000','Rp10.230.000','Rp0','Matched','-'],['Sari Wijaya','Rp6.750.000','Rp0','Rp6.750.000','Mismatch','Invalid Account'],['Andi Pratama','Rp8.950.000','Rp0','Rp8.950.000','Unreconciled','Provider Timeout'],['Maya Putri','Rp7.400.000','Rp7.400.000','Rp0','Matched','-']] as $row)
                <tr>@foreach(array_slice($row,0,4) as $cell)<td>{{ $cell }}</td>@endforeach<td><span class="badge {{ $row[4] === 'Matched' ? 'badge-green' : 'badge-red' }}">{{ $row[4] }}</span></td><td>{{ $row[5] }}</td></tr>
            @endforeach
        </tbody>
    </table>
</div>
