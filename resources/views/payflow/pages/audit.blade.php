@php
    $auditLogs = $auditLogs ?? collect();
@endphp

{{-- ── Filter toolbar ── --}}
<form method="GET" action="{{ route('app', 'audit') }}" style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; align-items:center;">
    <input class="input" style="max-width:200px;" name="actor" value="{{ request('actor') }}" placeholder="Cari nama user...">
    <input class="input" style="max-width:180px;" type="date" name="from" value="{{ request('from') }}" title="Dari tanggal">
    <input class="input" style="max-width:180px;" type="date" name="to" value="{{ request('to') }}" title="Sampai tanggal">
    <button type="submit" class="btn btn-secondary">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        Filter
    </button>
    @if(request('actor') || request('from') || request('to'))
        <a href="{{ route('app', 'audit') }}" class="btn btn-secondary">Reset</a>
    @endif
    <span class="muted" style="font-size:13px; margin-left:auto;">Read-only · Riwayat perubahan pengaturan</span>
</form>

<div class="section-card">
    <div class="section-header">
        <div>
            <div style="font-size:16px; font-weight:700; color:var(--navy);">Audit Log</div>
            <div class="muted" style="font-size:13px; margin-top:2px;">
                Riwayat perubahan data — sebelum &amp; sesudah, tanpa data sensitif
            </div>
        </div>
        <span class="badge badge-amber">Read-only</span>
    </div>

    @if($auditLogs->isEmpty())
        <div class="section-content" style="text-align:center; padding:56px 20px;">
            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" style="color:var(--muted); display:block; margin:0 auto 14px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <div style="font-size:17px; font-weight:700; color:var(--navy); margin-bottom:6px;">Belum ada riwayat perubahan</div>
            <p class="muted" style="margin:0; font-size:14px;">Log akan muncul saat ada perubahan pengaturan perusahaan.</p>
        </div>
    @else
        <div class="section-content" style="padding:0;">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>User</th>
                            <th>Field</th>
                            <th>Nilai Lama</th>
                            <th>Nilai Baru</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($auditLogs as $log)
                        <tr>
                            <td>
                                <div style="font-size:13px; font-weight:600; color:var(--navy);">
                                    {{ $log->changed_at?->format('d M Y') ?? '-' }}
                                </div>
                                <div class="muted" style="font-size:11px;">
                                    {{ $log->changed_at?->format('H:i:s') }}
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:600; color:var(--navy); font-size:13px;">
                                    {{ $log->user?->name ?? 'System' }}
                                </div>
                                <div class="muted" style="font-size:11px;">
                                    {{ $log->user?->role ?? '' }}
                                </div>
                            </td>
                            <td>
                                <span style="font-family:monospace; font-size:12px; background:#f1f5f9; padding:2px 8px; border-radius:6px; color:var(--navy);">
                                    {{ $log->field_changed }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size:13px; color:var(--red);">
                                    {{ $log->old_value ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size:13px; color:#16a34a; font-weight:600;">
                                    {{ $log->new_value ?? '—' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if(method_exists($auditLogs, 'hasPages') && $auditLogs->hasPages())
        <div style="padding:14px 20px; border-top:1px solid var(--line); display:flex; justify-content:flex-end;">
            {{ $auditLogs->links() }}
        </div>
        @endif
    @endif
</div>
