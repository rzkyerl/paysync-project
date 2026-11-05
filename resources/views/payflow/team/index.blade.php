<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manajemen Tim - PaySync</title>
    @include('payflow.partials.styles')
</head>
<body>
    <main style="max-width:1100px; margin:0 auto; padding:32px 20px;">
        @include('payflow.partials.brand')
        <div class="page-head" style="margin-top:28px;">
            <div>
                <h1>Manajemen Tim</h1>
                <p class="muted">Kelola akun admin (HR & Finance) untuk workspace {{ $company?->name ?? '' }}. Undang karyawan sebagai pengguna ESS dari halaman <a href="{{ route('employees.index') }}" style="color:var(--brand);">detail karyawan</a>.</p>
            </div>
            <a class="btn btn-secondary" href="{{ route('dashboard.hr') }}">Kembali ke Dashboard</a>
        </div>

        @if (session('status'))
            <div class="card" style="margin-bottom:16px; border-left:4px solid var(--green); padding:14px;">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="card" style="margin-bottom:16px; border-left:4px solid var(--red); padding:14px;">{{ session('error') }}</div>
        @endif

        {{-- ── Undang Admin Baru ── --}}
        <section class="card" style="margin-bottom:16px;">
            <div class="section-title">
                <h2>Undang Admin</h2>
                <span class="badge badge-blue">Link berlaku 7 hari</span>
            </div>
            <p class="muted" style="margin:0 0 16px; font-size:13px;">
                Undang anggota tim dengan role HR Manager atau Finance Manager.
                Untuk mengundang karyawan, gunakan tombol <strong>Kirim Undangan</strong> di halaman detail karyawan.
            </p>
            <form method="POST" action="{{ route('team.invite') }}" class="form-stack">
                @csrf
                <div class="grid grid-3">
                    <label class="field">
                        <span>Nama</span>
                        <input class="input" name="name" value="{{ old('name') }}" required>
                    </label>
                    <label class="field">
                        <span>Email</span>
                        <input class="input" type="email" name="email" value="{{ old('email') }}" required>
                    </label>
                    <label class="field">
                        <span>Role</span>
                        <select class="input" name="role" required>
                            @foreach($roles as $role)
                                <option value="{{ $role }}" @selected(old('role') === $role)>
                                    {{ match($role) {
                                        'hr_manager' => 'HR Manager',
                                        'finance_manager' => 'Finance Manager',
                                        default => ucfirst(str_replace('_', ' ', $role))
                                    } }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>
                @error('name')<small class="auth-error">{{ $message }}</small>@enderror
                @error('email')<small class="auth-error">{{ $message }}</small>@enderror
                @error('role')<small class="auth-error">{{ $message }}</small>@enderror
                <button class="btn btn-primary" type="submit">Kirim Undangan</button>
            </form>
        </section>

        {{-- ── Daftar Tim ── --}}
        <section class="card" style="padding:0; overflow:hidden;">
            <div class="section-title" style="padding:18px 20px;">
                <h2>Anggota Tim</h2>
                <span class="badge">{{ $members->count() }} anggota</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $member)
                            <tr>
                                <td><strong>{{ $member->name }}</strong></td>
                                <td>{{ $member->email }}</td>
                                <td>
                                    <span class="badge {{ match($member->role) {
                                        'super_admin' => 'badge-blue',
                                        'hr_manager' => 'badge-green',
                                        'finance_manager' => 'badge-amber',
                                        'employee' => 'badge-purple',
                                        default => ''
                                    } }}">
                                        {{ match($member->role) {
                                            'super_admin' => 'Super Admin',
                                            'hr_manager' => 'HR Manager',
                                            'finance_manager' => 'Finance Manager',
                                            'employee' => 'Employee',
                                            default => ucfirst(str_replace('_', ' ', $member->role))
                                        } }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $member->status === 'active' ? 'badge-green' : 'badge-amber' }}">
                                        {{ $member->status === 'active' ? 'Aktif' : 'Menunggu' }}
                                    </span>
                                </td>
                                <td style="display:flex; gap:6px; flex-wrap:wrap;">
                                    @if ($member->status === 'invited')
                                        <form method="POST" action="{{ route('team.resend', $member) }}" onsubmit="return confirm('Kirim ulang undangan ke anggota ini?')">
                                            @csrf
                                            <button class="btn btn-secondary" type="submit" style="font-size:12px;">Kirim Ulang</button>
                                        </form>
                                    @endif
                                    @if(!$member->isSuperAdmin())
                                    <form method="POST" action="{{ route('team.remove', $member) }}" onsubmit="return confirm('Nonaktifkan anggota ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger" type="submit" style="font-size:12px;">Nonaktifkan</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center; padding:28px;" class="muted">
                                    Belum ada anggota admin lain.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
