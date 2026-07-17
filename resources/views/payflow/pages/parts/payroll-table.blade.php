<div class="table-wrap">
    <table>
        <thead><tr><th>Employee</th><th>Basic Salary</th><th>Earning</th><th>Deduction</th><th>Net Pay</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
            @foreach ([['Rina Maharani','Rp9.000.000','Rp1.950.000','Rp720.000','Rp10.230.000','OK'],['Budi Santoso','Rp12.000.000','Rp2.250.000','Rp1.110.000','Rp13.140.000','OK'],['Sari Wijaya','Rp6.500.000','Rp900.000','Rp7.900.000','Rp-500.000','Anomali'],['Andi Pratama','Rp8.200.000','Rp1.400.000','Rp650.000','Rp8.950.000','OK']] as $row)
                <tr>@foreach(array_slice($row,0,5) as $cell)<td>{{ $cell }}</td>@endforeach<td><span class="badge {{ $row[5] === 'OK' ? 'badge-green' : 'badge-red' }}">{{ $row[5] }}</span></td><td><button class="btn btn-secondary">Review</button></td></tr>
            @endforeach
        </tbody>
    </table>
</div>
