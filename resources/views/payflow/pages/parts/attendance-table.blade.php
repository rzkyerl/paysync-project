<div class="table-wrap">
    <table>
        <thead><tr><th>Karyawan</th><th>Hari Kerja</th><th>Hadir</th><th>Terlambat</th><th>Tidak Hadir</th><th>Cuti</th><th>Menit Lembur</th><th>Status Data</th></tr></thead>
        <tbody>
            @foreach ([['Rina Maharani',20,19,1,0,1,90,'Valid'],['Budi Santoso',20,20,0,0,0,45,'Valid'],['Sari Wijaya',20,16,2,1,1,120,'Warning'],['Andi Pratama',20,0,0,0,0,0,'Error']] as $row)
                <tr>@foreach(array_slice($row,0,7) as $cell)<td>{{ $cell }}</td>@endforeach<td><span class="badge {{ $row[7] === 'Valid' ? 'badge-green' : ($row[7] === 'Error' ? 'badge-red' : 'badge-amber') }}">{{ $row[7] }}</span></td></tr>
            @endforeach
        </tbody>
    </table>
</div>
