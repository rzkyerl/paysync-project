{{--
    employees.blade.php
    Variables: $employees (LengthAwarePaginator), $departments (Collection),
               $sortBy, $sortDir, $perPage
--}}

@if(($isEmpty ?? false) && !request()->hasAny(['search','department','status']))
    <x-empty-state
        icon="users"
        title="Belum ada karyawan"
        description="Tambahkan karyawan pertama atau import data CSV untuk mulai menggunakan payroll."
        cta-label="Tambah Karyawan"
        :cta-url="route('employees.create')"
    />
    <div style="display:flex; justify-content:center; margin-top:12px;">
        <button type="button" class="btn btn-secondary">Import CSV</button>
    </div>
@else

<form method="GET" action="{{ route('employees.index') }}" id="employees-filter-form">
    {{-- Preserve sort state across filter changes --}}
    @if(request('sort'))
        <input type="hidden" name="sort" value="{{ request('sort') }}">
    @endif
    @if(request('dir'))
        <input type="hidden" name="dir" value="{{ request('dir') }}">
    @endif

    <div class="toolbar">
        {{-- Search --}}
        <input
            class="input"
            style="max-width:260px;"
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari NIP atau nama"
            x-on:input.debounce.300ms="$el.form.submit()"
        >

        {{-- Department filter --}}
        <select
            class="input"
            style="max-width:180px;"
            name="department"
            @change="$el.form.submit()"
        >
            <option value="">Semua Departemen</option>
            @foreach($departments ?? [] as $dept)
                <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
        </select>

        {{-- Work status filter --}}
        <select
            class="input"
            style="max-width:180px;"
            name="status"
            @change="$el.form.submit()"
        >
            <option value="">Status kerja</option>
            <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Aktif</option>
            <option value="probation" {{ request('status') === 'probation' ? 'selected' : '' }}>Probation</option>
            <option value="contract"  {{ request('status') === 'contract'  ? 'selected' : '' }}>Kontrak</option>
            <option value="inactive"  {{ request('status') === 'inactive'  ? 'selected' : '' }}>Tidak Aktif</option>
        </select>

        {{-- Reset filter button (only shown when filters are active) --}}
        @if(request()->hasAny(['search','department','status']))
            <a href="{{ route('employees.index') }}" class="btn btn-secondary">Reset Filter</a>
        @endif

        <div style="margin-left:auto; display:flex; gap:10px;">
            @if ($isSuperAdminViewing ?? false)
                <span class="badge badge-amber" title="Hanya tersedia untuk Tim HR/Finance">Read-only</span>
            @else
                <a href="{{ route('employees.import') }}" class="btn btn-secondary">
                    @include('payflow.partials.icon', ['name' => 'upload', 'class' => 'icon icon-sm'])
                    Import CSV
                </a>
                <a href="{{ route('employees.create') }}" class="btn btn-primary">
                    @include('payflow.partials.icon', ['name' => 'users', 'class' => 'icon icon-sm'])
                    Tambah Karyawan
                </a>
            @endif
        </div>
    </div>
</form>

<section class="card" style="padding:0;">
    @include('payflow.pages.parts.employee-table')
    @include('payflow.partials.pagination', ['paginator' => $employees])
</section>

@if($employees->isEmpty())
    @if(request()->hasAny(['search','department','status']))
        {{-- No search/filter results --}}
        <div class="card" style="margin-top:16px; text-align:center; padding:48px 24px;">
            <div style="font-size:40px; margin-bottom:12px;">🔍</div>
            <h3 style="margin:0 0 8px; color:var(--navy);">Tidak ada hasil yang ditemukan</h3>
            <p class="muted" style="margin:0 0 20px;">Coba ubah kata kunci pencarian atau filter yang aktif.</p>
            <a href="{{ route('employees.index') }}" class="btn btn-secondary">Reset Filter</a>
        </div>
    @else
        {{-- Completely empty —no employees at all --}}
        <div class="card" style="margin-top:16px; text-align:center; padding:48px 24px;">
            <div style="font-size:40px; margin-bottom:12px;">👥</div>
            <h3 style="margin:0 0 8px; color:var(--navy);">Belum ada karyawan</h3>
            <p class="muted" style="margin:0 0 20px;">Mulai dengan menambahkan karyawan pertama atau impor dari file CSV.</p>
            <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
                @if ($isSuperAdminViewing ?? false)
                    <span class="badge badge-amber" title="Hanya tersedia untuk Tim HR/Finance">Mode Pantau: read-only</span>
                @else
                    <a href="{{ route('employees.create') }}" class="btn btn-primary">Tambah Karyawan Pertama</a>
                    <a href="{{ route('employees.import') }}" class="btn btn-secondary">Import Data CSV</a>
                @endif
            </div>
        </div>
    @endif
@endif

@endif
