<div
    x-data="{
        fields: {
            nip: { value: '{{ old('nip') }}', touched: false, error: '' },
            name: { value: '{{ old('name') }}', touched: false, error: '' },
            department: { value: '{{ old('department') }}', touched: false, error: '' },
            position: { value: '{{ old('position') }}', touched: false, error: '' },
            work_status: { value: '{{ old('work_status') }}', touched: false, error: '' },
            join_date: { value: '{{ old('join_date') }}', touched: false, error: '' },
            basic_salary: { value: '{{ old('basic_salary') }}', touched: false, error: '' },
            bank_account_status: { value: '{{ old('bank_account_status') }}', touched: false, error: '' },
            bank_account_number: { value: '{{ old('bank_account_number') }}', touched: false, error: '' },
            bank_name: { value: '{{ old('bank_name') }}', touched: false, error: '' }
        },
        validateNip(v) {
            if (!v || !v.trim()) return 'NIP wajib diisi.';
            if (!/^EMP-\d+$/.test(v.trim())) return 'Format NIP tidak valid. Gunakan EMP-XXXX.';
            return '';
        },
        validateRequired(v, label) {
            if (!v || !v.trim()) return label + ' wajib diisi.';
            if (v.trim().length < 2) return label + ' minimal 2 karakter.';
            return '';
        },
        validateSelect(v, label) { return (!v || v === '') ? label + ' wajib dipilih.' : ''; },
        validateSalary(v) {
            if (v === '' || v === null || v === undefined) return 'Gaji Pokok wajib diisi.';
            if (isNaN(Number(v))) return 'Gaji Pokok harus angka.';
            if (Number(v) < 0) return 'Gaji Pokok tidak boleh negatif.';
            return '';
        },
        validateDate(v) { return (!v || !v.trim()) ? 'Tanggal Bergabung wajib diisi.' : ''; },
        onBlur(f) { this.fields[f].touched = true; this.runValidation(f); },
        onInput(f) {
            this.fields[f].value = this.$refs[f] ? this.$refs[f].value : this.fields[f].value;
            if (this.fields[f].touched) this.runValidation(f);
        },
        runValidation(f) {
            const v = this.fields[f].value;
            const e = {
                nip: () => this.validateNip(v),
                name: () => this.validateRequired(v, 'Nama Lengkap'),
                department: () => this.validateRequired(v, 'Departemen'),
                position: () => this.validateRequired(v, 'Jabatan'),
                work_status: () => this.validateSelect(v, 'Status Kerja'),
                join_date: () => this.validateDate(v),
                basic_salary: () => this.validateSalary(v),
                bank_account_status: () => this.validateSelect(v, 'Status Rekening'),
            };
            if (e[f]) this.fields[f].error = e[f]();
        },
        getClass(f) {
            if (!this.fields[f].touched) return '';
            return this.fields[f].error ? 'error' : 'valid';
        }
    }">

    {{-- Error Summary --}}
    @if($errors->any())
    <div class="form-error-summary" role="alert" style="margin-bottom:20px;">
        <svg class="form-error-summary-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16.5" r=".5" fill="currentColor" stroke="none"/></svg>
        <div class="form-error-summary-body">
            <p class="form-error-summary-title">{{ $errors->count() }} kesalahan perlu diperbaiki:</p>
            <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('employees.store') }}" novalidate>
        @csrf

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; align-items:start;">

            {{-- KOLOM KIRI --}}
            <div style="display:grid; gap:16px;">

                {{-- Section: Informasi Dasar --}}
                <div class="section-card">
                    <div class="section-header">
                        <h2>Informasi Dasar</h2>
                        <span class="badge badge-blue">Wajib</span>
                    </div>
                    <div class="section-content" style="display:grid; gap:14px;">

                        {{-- NIP --}}
                        <div class="field">
                            <label for="nip">NIP <span style="color:var(--red)">*</span></label>
                            <input id="nip" name="nip" type="text" x-ref="nip"
                                :class="['input', getClass('nip')]"
                                @blur="onBlur('nip')" @input="onInput('nip')"
                                value="{{ old('nip') }}" placeholder="EMP-0001">
                            @error('nip')<span class="field-error">{{ $message }}</span>@enderror
                            <span class="field-error" x-show="fields.nip.touched && fields.nip.error" x-text="fields.nip.error" x-cloak></span>
                        </div>

                        {{-- Nama --}}
                        <div class="field">
                            <label for="name">Nama Lengkap <span style="color:var(--red)">*</span></label>
                            <input id="name" name="name" type="text" x-ref="name"
                                :class="['input', getClass('name')]"
                                @blur="onBlur('name')" @input="onInput('name')"
                                value="{{ old('name') }}" placeholder="Nama karyawan">
                            @error('name')<span class="field-error">{{ $message }}</span>@enderror
                            <span class="field-error" x-show="fields.name.touched && fields.name.error" x-text="fields.name.error" x-cloak></span>
                        </div>

                        {{-- Departemen + Jabatan --}}
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="field">
                                <label for="department">Departemen <span style="color:var(--red)">*</span></label>
                                <input id="department" name="department" type="text" x-ref="department"
                                    :class="['input', getClass('department')]"
                                    @blur="onBlur('department')" @input="onInput('department')"
                                    value="{{ old('department') }}" placeholder="e.g. Engineering"
                                    list="department-list">
                                @isset($departments)
                                    <datalist id="department-list">
                                        @foreach($departments as $dept)<option value="{{ $dept }}">@endforeach
                                    </datalist>
                                @endisset
                                @error('department')<span class="field-error">{{ $message }}</span>@enderror
                                <span class="field-error" x-show="fields.department.touched && fields.department.error" x-text="fields.department.error" x-cloak></span>
                            </div>
                            <div class="field">
                                <label for="position">Jabatan <span style="color:var(--red)">*</span></label>
                                <input id="position" name="position" type="text" x-ref="position"
                                    :class="['input', getClass('position')]"
                                    @blur="onBlur('position')" @input="onInput('position')"
                                    value="{{ old('position') }}" placeholder="e.g. Engineer">
                                @error('position')<span class="field-error">{{ $message }}</span>@enderror
                                <span class="field-error" x-show="fields.position.touched && fields.position.error" x-text="fields.position.error" x-cloak></span>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Section: Status & Penggajian --}}
                <div class="section-card">
                    <div class="section-header">
                        <h2>Status & Penggajian</h2>
                        <span class="badge badge-blue">Wajib</span>
                    </div>
                    <div class="section-content" style="display:grid; gap:14px;">

                        {{-- Status Kerja + Tanggal Bergabung --}}
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="field">
                                <label for="work_status">Status Kerja <span style="color:var(--red)">*</span></label>
                                <select id="work_status" name="work_status" x-ref="work_status"
                                    :class="['input', getClass('work_status')]"
                                    @blur="onBlur('work_status')"
                                    @change="fields.work_status.value = $event.target.value; onBlur('work_status')">
                                    <option value="">-- Pilih --</option>
                                    @foreach(['active'=>'Aktif','probation'=>'Probation','contract'=>'Kontrak','inactive'=>'Tidak Aktif'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ old('work_status') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                                @error('work_status')<span class="field-error">{{ $message }}</span>@enderror
                                <span class="field-error" x-show="fields.work_status.touched && fields.work_status.error" x-text="fields.work_status.error" x-cloak></span>
                            </div>
                            <div class="field">
                                <label for="join_date">Tanggal Bergabung <span style="color:var(--red)">*</span></label>
                                <input id="join_date" name="join_date" type="date" x-ref="join_date"
                                    :class="['input', getClass('join_date')]"
                                    @blur="onBlur('join_date')" @input="onInput('join_date')"
                                    value="{{ old('join_date') }}">
                                @error('join_date')<span class="field-error">{{ $message }}</span>@enderror
                                <span class="field-error" x-show="fields.join_date.touched && fields.join_date.error" x-text="fields.join_date.error" x-cloak></span>
                            </div>
                        </div>

                        {{-- Gaji Pokok --}}
                        <div class="field">
                            <label for="basic_salary">Gaji Pokok <span style="color:var(--red)">*</span></label>
                            <div style="position:relative;">
                                <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--muted); font-weight:600; font-size:13px; pointer-events:none;">Rp</span>
                                <input id="basic_salary" name="basic_salary" type="number" min="0" step="1000" x-ref="basic_salary"
                                    :class="['input', getClass('basic_salary')]"
                                    @blur="onBlur('basic_salary')" @input="onInput('basic_salary')"
                                    value="{{ old('basic_salary') }}" placeholder="5000000"
                                    style="padding-left:36px;">
                            </div>
                            @error('basic_salary')<span class="field-error">{{ $message }}</span>@enderror
                            <span class="field-error" x-show="fields.basic_salary.touched && fields.basic_salary.error" x-text="fields.basic_salary.error" x-cloak></span>
                        </div>

                    </div>
                </div>

            </div>

            {{-- KOLOM KANAN --}}
            <div style="display:grid; gap:16px;">

                {{-- Section: Informasi Rekening --}}
                <div class="section-card">
                    <div class="section-header">
                        <h2>Informasi Rekening</h2>
                        <span class="badge">Opsional</span>
                    </div>
                    <div class="section-content" style="display:grid; gap:14px;">

                        {{-- Status Rekening --}}
                        <div class="field">
                            <label for="bank_account_status">Status Rekening <span style="color:var(--red)">*</span></label>
                            <select id="bank_account_status" name="bank_account_status" x-ref="bank_account_status"
                                :class="['input', getClass('bank_account_status')]"
                                @blur="onBlur('bank_account_status')"
                                @change="fields.bank_account_status.value = $event.target.value; onBlur('bank_account_status')">
                                <option value="">-- Pilih Status --</option>
                                @foreach(['verified'=>'Verified','unverified'=>'Unverified','rejected'=>'Rejected'] as $val => $lbl)
                                    <option value="{{ $val }}" {{ old('bank_account_status') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                            @error('bank_account_status')<span class="field-error">{{ $message }}</span>@enderror
                            <span class="field-error" x-show="fields.bank_account_status.touched && fields.bank_account_status.error" x-text="fields.bank_account_status.error" x-cloak></span>
                        </div>

                        {{-- Nama Bank + Nomor Rekening --}}
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="field">
                                <label for="bank_name">Nama Bank</label>
                                <input id="bank_name" name="bank_name" type="text" x-ref="bank_name"
                                    class="input @error('bank_name') error @enderror"
                                    @input="onInput('bank_name')"
                                    value="{{ old('bank_name') }}" placeholder="BCA / Mandiri">
                                @error('bank_name')<span class="field-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="field">
                                <label for="bank_account_number">No. Rekening</label>
                                <input id="bank_account_number" name="bank_account_number" type="text" x-ref="bank_account_number"
                                    class="input @error('bank_account_number') error @enderror"
                                    @input="onInput('bank_account_number')"
                                    value="{{ old('bank_account_number') }}" placeholder="1234567890">
                                @error('bank_account_number')<span class="field-error">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div style="padding:12px 14px; background:var(--brand-soft); border:1px solid var(--brand-line); border-radius:10px; font-size:13px; color:var(--brand); line-height:1.6;">
                            <strong>ℹ Info:</strong> Data rekening digunakan untuk proses disbursement gaji. Pastikan nomor rekening sudah benar sebelum memverifikasi.
                        </div>

                    </div>
                </div>

                {{-- Summary Card --}}
                <div class="section-card">
                    <div class="section-header">
                        <h2>Ringkasan</h2>
                    </div>
                    <div class="section-content" style="display:grid; gap:10px;">
                        <div class="detail-row">
                            <span class="detail-label">NIP</span>
                            <span class="detail-value" x-text="fields.nip.value || '—'" style="font-family:monospace;"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Nama</span>
                            <span class="detail-value" x-text="fields.name.value || '—'"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Departemen</span>
                            <span class="detail-value" x-text="fields.department.value || '—'"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status Kerja</span>
                            <span class="detail-value" x-text="fields.work_status.value || '—'"></span>
                        </div>
                        <div class="detail-row" style="border-bottom:0;">
                            <span class="detail-label">Gaji Pokok</span>
                            <span class="detail-value" x-text="fields.basic_salary.value ? 'Rp ' + Number(fields.basic_salary.value).toLocaleString('id-ID') : '—'"></span>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div style="display:flex; gap:10px;">
                    <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Simpan Karyawan
                    </button>
                    <a href="{{ route('employees.index') }}" class="btn btn-secondary">Batal</a>
                </div>

            </div>

        </div>

    </form>
</div>
