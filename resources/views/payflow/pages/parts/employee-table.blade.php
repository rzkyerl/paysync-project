<div class="table-wrap">
    <table>
        <thead><tr><th><input type="checkbox"></th><th>NIP</th><th>Nama</th><th>Departemen</th><th>Jabatan</th><th>Status Kerja</th><th>Bergabung</th><th>Status Rekening</th><th>Aksi</th></tr></thead>
        <tbody>
            @foreach ([
                ['EMP-1001','Rina Maharani','People','HR Manager','Active','12 Jan 2023','Verified'],
                ['EMP-1008','Budi Santoso','Finance','Finance Lead','Active','03 Apr 2022','Verified'],
                ['EMP-1024','Sari Wijaya','Operations','Payroll Specialist','Probation','08 Jul 2026','Unverified'],
                ['EMP-1031','Andi Pratama','Engineering','Backend Developer','Contract','18 Mar 2024','Rejected'],
                ['EMP-1044','Maya Putri','Sales','Account Executive','Active','25 Nov 2023','Verified'],
            ] as $row)
                <tr>
                    <td><input type="checkbox"></td>
                    <td>{{ $row[0] }}</td>
                    <td><strong>{{ $row[1] }}</strong><div class="muted">avatar initials</div></td>
                    <td>{{ $row[2] }}</td>
                    <td>{{ $row[3] }}</td>
                    <td><span class="badge {{ $row[4] === 'Active' ? 'badge-green' : 'badge-amber' }}">{{ $row[4] }}</span></td>
                    <td>{{ $row[5] }}</td>
                    <td><span class="badge {{ $row[6] === 'Verified' ? 'badge-green' : ($row[6] === 'Rejected' ? 'badge-red' : 'badge-amber') }}">{{ $row[6] }}</span></td>
                    <td><button class="btn btn-secondary">Detail</button></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
