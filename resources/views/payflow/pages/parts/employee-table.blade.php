<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th><input type="checkbox"></th>
                @include('payflow.partials.sort-header', ['column' => 'nip', 'label' => 'NIP', 'currentSort' => $sortBy ?? 'name', 'currentDir' => $sortDir ?? 'asc'])
                @include('payflow.partials.sort-header', ['column' => 'name', 'label' => 'Nama', 'currentSort' => $sortBy ?? 'name', 'currentDir' => $sortDir ?? 'asc'])
                @include('payflow.partials.sort-header', ['column' => 'department', 'label' => 'Departemen', 'currentSort' => $sortBy ?? 'name', 'currentDir' => $sortDir ?? 'asc'])
                <th>Jabatan</th>
                @include('payflow.partials.sort-header', ['column' => 'work_status', 'label' => 'Status Kerja', 'currentSort' => $sortBy ?? 'name', 'currentDir' => $sortDir ?? 'asc'])
                @include('payflow.partials.sort-header', ['column' => 'join_date', 'label' => 'Bergabung', 'currentSort' => $sortBy ?? 'name', 'currentDir' => $sortDir ?? 'asc'])
                <th>Status Rekening</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $row)
            @php
                $empId   = $row->id;
                $empNip  = $row->nip;
                $empName = $row->name;
                $empDept = $row->department;
                $empPos  = $row->position;
                $empStat = $row->work_status;
                $empJoin = $row->join_date;
                $empBank = $row->bank_account_status;
                $deleteUrl = route('employees.destroy', $empId);
            @endphp
                <tr>
                    <td><input type="checkbox"></td>
                    <td>{{ $empNip }}</td>
                    <td><strong>{{ $empName }}</strong><div class="muted">avatar initials</div></td>
                    <td>{{ $empDept }}</td>
                    <td>{{ $empPos }}</td>
                    <td><span class="badge {{ $empStat === 'active' ? 'badge-green' : 'badge-amber' }}">{{ ucfirst($empStat) }}</span></td>
                    <td>{{ $empJoin?->format('d M Y') ?? '-' }}</td>
                    <td><span class="badge {{ $empBank === 'verified' ? 'badge-green' : ($empBank === 'rejected' ? 'badge-red' : 'badge-amber') }}">{{ ucfirst($empBank) }}</span></td>
                    <td style="display:flex; gap:6px; align-items:center;">
                        <a class="btn btn-secondary" href="{{ route('employees.show', $row) }}">Detail</a>
                        @if (! ($isSuperAdminViewing ?? false))
                            <button
                                type="button"
                                class="btn btn-danger"
                                x-data
                                @click="$store.confirm.show({
                                    title: 'Hapus Karyawan',
                                    message: 'Yakin ingin menghapus {{ addslashes($empName) }} ({{ $empNip }})? Aksi ini tidak dapat dibatalkan.',
                                    actionUrl: '{{ $deleteUrl }}',
                                    actionMethod: 'DELETE'
                                })"
                            >Hapus</button>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
