<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Import Karyawan - PaySync</title>
    @include('payflow.partials.styles')
</head>
<body>
<main style="max-width:900px; margin:0 auto; padding:32px 20px;">
    @include('payflow.partials.brand')
    <div class="page-head" style="margin-top:28px;"><div><h1>Import Karyawan</h1><p>Upload CSV untuk menambahkan data karyawan sekaligus.</p></div><a class="btn btn-secondary" href="{{ route('employees.index') }}">Kembali</a></div>
    @if ($errors->any())<div class="card" style="margin-bottom:16px; border-left:4px solid var(--red); padding:14px;">{{ $errors->first() }}</div>@endif
    @if (session('import_errors'))
        <section class="card" style="margin-bottom:16px;"><strong>Import gagal. Tidak ada data yang disimpan.</strong><div class="table-wrap" style="margin-top:12px;"><table><thead><tr><th>Baris</th><th>Field</th><th>Pesan</th></tr></thead><tbody>@foreach(session('import_errors') as $error)<tr><td>{{ $error['row'] }}</td><td>{{ $error['field'] }}</td><td>{{ $error['message'] }}</td></tr>@endforeach</tbody></table></div></section>
    @endif
    <section class="card">
        <form method="POST" action="{{ route('employees.import.store') }}" enctype="multipart/form-data" class="form-stack">
            @csrf
            <label class="field"><span>File CSV (maksimal 2 MB)</span><input class="input" type="file" name="file" accept=".csv,text/csv" required></label>
            <div style="display:flex; gap:10px; align-items:center;"><button class="btn btn-primary" type="submit">Import CSV</button><a class="btn btn-secondary" href="{{ route('employees.import.template') }}">Download Template</a></div>
        </form>
        <p class="muted" style="margin-top:16px;">Header wajib: nip, name, department, position, work_status, join_date, basic_salary, bank_name, bank_account_number.</p>
    </section>
</main>
</body>
</html>
