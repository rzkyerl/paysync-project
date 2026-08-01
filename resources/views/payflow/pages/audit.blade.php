@php
    $auditLogs = $auditLogs ?? collect();
    $categories = [
        'payroll' => 'Payroll',
        'employee' => 'Karyawan',
        'attendance' => 'Kehadiran',
        'team' => 'Tim',
        'settings' => 'Pengaturan',
    ];
@endphp

<form method="GET" action="{{ route('app', 'audit') }}" style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; align-items:center;">
    <select class="input" style="max-width:170px;" name="category">
        <option value="">Semua kategori</option>
        @foreach($categories as $value => $label)
            <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <input class="input" style="max-width:180px;" name="actor" value="{{ request('actor') }}" placeholder="Cari pelaku...">
    <input class="input" style="max-width:190px;" name="event" value="{{ request('event') }}" placeholder="Cari event...">
    <input class="input" style="max-width:155px;" type="date" name="from" value="{{ request('from') }}" title="Dari tanggal">
    <input class="input" style="max-width:155px;" type="date" name="to" value="{{ request('to') }}" title="Sampai tanggal">
    <button type="submit" class="btn btn-secondary">Filter</button>
    @if(request('category') || request('actor') || request('event') || request('from') || request('to'))
        <a href="{{ route('app', 'audit') }}" class="btn btn-secondary">Reset</a>
    @endif
    <span class="muted" style="font-size:13px; margin-left:auto;">Read-only · Aktivitas penting perusahaan</span>
</form>

<div class="section-card">
    <div class="section-header">
        <div>
            <div style="font-size:16px; font-weight:700; color:var(--navy);">Activity Log</div>
            <div class="muted" style="font-size:13px; margin-top:2px;">
                Jejak aktivitas payroll, karyawan, kehadiran, tim, dan pengaturan
            </div>
        </div>
        <span class="badge badge-amber">Read-only</span>
    </div>

    @if($auditLogs->isEmpty())
        <div class="section-content" style="text-align:center; padding:56px 20px;">
            <div style="font-size:17px; font-weight:700; color:var(--navy); margin-bottom:6px;">Belum ada aktivitas</div>
            <p class="muted" style="margin:0; font-size:14px;">Aktivitas penting perusahaan akan tercatat otomatis di sini.</p>
        </div>
    @else
        <div class="section-content" style="padding:0;">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Pelaku</th>
                            <th>Kategori</th>
                            <th>Aktivitas</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($auditLogs as $log)
                            @php
                                $localOccurredAt = $log->occurred_at?->copy()->setTimezone(config('paysync.activity_timezone', 'Asia/Jakarta'));
                            @endphp
                            <tr>
                                <td>
                                    <div style="font-size:13px; font-weight:600; color:var(--navy);">{{ $localOccurredAt?->format('d M Y') ?? '-' }}</div>
                                    <div class="muted" style="font-size:11px;">{{ $localOccurredAt?->format('H:i:s') }} WIB</div>
                                </td>
                                <td>
                                    <div style="font-weight:600; color:var(--navy); font-size:13px;">{{ $log->user?->name ?? 'System' }}</div>
                                    <div class="muted" style="font-size:11px;">{{ $log->user?->role ? str_replace('_', ' ', $log->user->role) : '' }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $log->category === 'payroll' ? 'badge-blue' : ($log->category === 'team' ? 'badge-amber' : 'badge-green') }}">
                                        {{ $categories[$log->category] ?? ucfirst($log->category) }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size:13px; color:var(--navy);">{{ $log->description }}</div>
                                    <div class="muted" style="font-family:monospace; font-size:11px; margin-top:3px;">{{ $log->event }}</div>
                                </td>
                                <td><span class="muted" style="font-family:monospace; font-size:12px;">{{ $log->ip_address ?? '—' }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if(method_exists($auditLogs, 'hasPages') && $auditLogs->hasPages())
            <div style="padding:14px 20px; border-top:1px solid var(--line); display:flex; justify-content:flex-end;">
                {{ $auditLogs->links() }}
            </div>
        @endif
    @endif
</div>
