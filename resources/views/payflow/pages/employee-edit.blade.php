<section class="card"
    x-data="{
        fields: {
            nip:                { value: '{{ old('nip', $employee->nip) }}',                         touched: false, error: '' },
            name:               { value: '{{ old('name', $employee->name) }}',                       touched: false, error: '' },
            department:         { value: '{{ old('department', $employee->department) }}',           touched: false, error: '' },
            position:           { value: '{{ old('position', $employee->position) }}',               touched: false, error: '' },
            work_status:        { value: '{{ old('work_status', $employee->work_status) }}',         touched: false, error: '' },
            join_date:          { value: '{{ old('join_date', $employee->join_date?->format('Y-m-d')) }}', touched: false, error: '' },
            basic_salary:       { value: '{{ old('basic_salary', $employee->basic_salary) }}',       touched: false, error: '' },
            bank_account_status:{ value: '{{ old('bank_account_status', $employee->bank_account_status) }}', touched: false, error: '' },
            bank_account_number:{ value: '{{ old('bank_account_number', $employee->bank_account_number) }}', touched: false, error: '' },
            bank_name:          { value: '{{ old('bank_name', $employee->bank_name) }}',             touched: false, error: '' }
        },
        validateNip(val) {
            if (!val || !val.trim()) return 'NIP wajib diisi.';
            if (!/^EMP-\d+$/.test(val.trim())) return 'Format NIP: EMP-XXXX (contoh: EMP-0001).';
            return '';
        },
        validateRequired(val, label) {
            if (!val || !val.trim()) return label + ' wajib diisi.';
            if (val.trim().length < 2) return label + ' minimal 2 karakter.';
            return '';
        },
        validateSelect(val, label) { return (!val || val === '') ? label + ' wajib dipilih.' : ''; },
        validateSalary(val) {
            if (val === '' || val === null) return 'Gaji Pokok wajib diisi.';
            if (isNaN(Number(val))) return 'Gaji Pokok harus berupa angka.';
            if (Number(val) < 0) return 'Gaji Pokok tidak boleh negatif.';
            return '';
        },
        onBlur(f) { this.fields[f].touched = true; this.runValidation(f); },
        onInput(f) {
            this.fields[f].value = this.$refs[f] ? this.$refs[f].value : this.fields[f].value;
            if (this.fields[f].touched) this.runValidation(f);
        },
        runValidation(f) {
            const v = this.fields[f].value;
            const e = this.fields;
            if (f==='nip')                   e.nip.error = this.validateNip(v);
            else if (f==='name')             e.name.error = this.validateRequired(v,'Nama Lengkap');
            else if (f==='department')       e.department.error = this.validateRequired(v,'Departemen');
            else if (f==='position')         e.position.error = this.validateRequired(v,'Jabatan');
            else if (f==='work_status')      e.work_status.error = this.validateSelect(v,'Status Kerja');
            else if (f==='join_date')        e.join_date.error = (!v||!v.trim()) ? 'Tanggal wajib diisi.' : '';
            else if (f==='basic_salary')     e.basic_salary.error = this.validateSalary(v);
            else if (f==='bank_account_status') e.bank_account_status.error = this.validateSelect(v,'Status Rekening');
        },
        getClass(f) {
            if (!this.fields[f].touched) return '';
            return this.fields[f].error ? 'error' : 'valid';
        }
    }">

    {{-- Header --}}
    <div class="section-header">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:44px; height:44px; border-radius:12px; background:var(--brand-soft); display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:18px; font-weight:800; color:var(--brand);">
                {{ strtoupper(substr($employee->name, 0, 1)) }}
            </div>
            <div>
                <div style="font-size:16px; font-weight:700; color:var(--navy);">Edit Karyawan</div>
                <div class="muted" style="font-size:13px; margin-top:2px;">{{ $employee->name }} · {{ $employee->nip }}</div>
            </div>
        </div>
        <a href="{{ route('employees.show', $employee) }}" class="btn btn-secondary" style="font-size:13px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            Lihat Detail
        </a>
    </div>

    <div class="section-content">

        {{-- Server-side error summary --}}
        @if($errors->any())
        <div class="form-error-summary" role="alert" style="margin-bottom:24px;">
            <svg class="form-error-summary-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16.5" r=".5" fill="currentColor" stroke="none"/></svg>
            <div class="form-error-summary-body">
                <p class="form-error-summary-title">{{ $errors->count() }} kesalahan yang perlu diperbaiki:</p>
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('employees.update', $employee) }}" novalidate>
            @csrf @method('PUT')

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0 32px;">

                {{-- LEFT: Info Personal --}}
                <div>
                    <div style="font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:16px; padding-bottom:8px; border-bottom:1px solid var(--line);">
                        Informasi Personal
                    </div>

                    <div class="field" style="margin-bottom:16px;">
                        <label class="label" for="nip">NIP <span style="color:var(--red)">*</span></label>
                        <input id="nip" name="nip" type="text" x-ref="nip"
                            :class="['input', getClass('nip')]"
                            @blur="onBlur('nip')" @input="onInput('nip')"
                            value="{{ old('nip', $employee->nip) }}" placeholder="EMP-0001"
                            style="max-width:200px;">
                        @error('nip')<span class="field-error">{{ $message }}</span>@enderror
                        <span class="field-error" x-show="fields.nip.touched && fields.nip.error" x-text="fields.nip.error" x-cloak></span>
                    </div>

                    <div class="field" style="margin-bottom:16px;">
                        <label class="label" for="name">Nama Lengkap <span style="color:var(--red)">*</span></label>
                        <input id="name" name="name" type="text" x-ref="name"
                            :class="['input', getClass('name')]"
                            @blur="onBlur('name')" @input="onInput('name')"
                            value="{{ old('name', $employee->name) }}" placeholder="Nama karyawan">
                        @error('name')<span class="field-error">{{ $message }}</span>@enderror
                        <span class="field-error" x-show="fields.name.touched && fields.name.error" x-text="fields.name.error" x-cloak></span>
                    </div>

                    <div class="field" style="margin-bottom:16px;">
                        <label class="label" for="department">Departemen <span style="color:var(--red)">*</span></label>
                        <input id="department" name="department" type="text" x-ref="department"
                            :class="['input', getClass('department')]"
                            @blur="onBlur('department')" @input="onInput('department')"
                            value="{{ old('department', $employee->department) }}"
                            placeholder="e.g. Engineering" list="dept-list">
                        @isset($departments)
                            <datalist id="dept-list">
                                @foreach($departments as $d)<option value="{{ $d }}">@endforeach
                            </datalist>
                        @endisset
                        @error('department')<span class="field-error">{{ $message }}</span>@enderror
                        <span class="field-error" x-show="fields.department.touched && fields.department.error" x-text="fields.department.error" x-cloak></span>
                    </div>

                    <div class="field" style="margin-bottom:16px;">
                        <label class="label" for="position">Jabatan <span style="color:var(--red)">*</span></label>
                        <input id="position" name="position" type="text" x-ref="position"
                            :class="['input', getClass('position')]"
                            @blur="onBlur('position')" @input="onInput('position')"
                            value="{{ old('position', $employee->position) }}" placeholder="e.g. Software Engineer">
                        @error('position')<span class="field-error">{{ $message }}</span>@enderror
                        <span class="field-error" x-show="fields.position.touched && fields.position.error" x-text="fields.position.error" x-cloak></span>
                    </div>

                    <div class="field" style="margin-bottom:16px;">
                        <label class="label" for="work_status">Status Kerja <span style="color:var(--red)">*</span></label>
                        <select id="work_status" name="work_status" x-ref="work_status"
                            :class="['input', getClass('work_status')]"
                            @blur="onBlur('work_status')"
                            @change="fields.work_status.value=$event.target.value; onBlur('work_status')"
                            style="max-width:200px;">
                            <option value="">-- Pilih Status --</option>
                            @foreach(['active'=>'Aktif','probation'=>'Probation','contract'=>'Kontrak','inactive'=>'Tidak Aktif'] as $val=>$lbl)
                                <option value="{{ $val }}" {{ old('work_status',$employee->work_status)===$val?'selected':'' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        @error('work_status')<span class="field-error">{{ $message }}</span>@enderror
                        <span class="field-error" x-show="fields.work_status.touched && fields.work_status.error" x-text="fields.work_status.error" x-cloak></span>
                    </div>

                    <div class="field" style="margin-bottom:16px;">
                        <label class="label" for="join_date">Tanggal Bergabung <span style="color:var(--red)">*</span></label>
                        <input id="join_date" name="join_date" type="date" x-ref="join_date"
                            :class="['input', getClass('join_date')]"
                            @blur="onBlur('join_date')" @input="onInput('join_date')"
                            value="{{ old('join_date', $employee->join_date?->format('Y-m-d')) }}"
                            style="max-width:200px;">
                        @error('join_date')<span class="field-error">{{ $message }}</span>@enderror
                        <span class="field-error" x-show="fields.join_date.touched && fields.join_date.error" x-text="fields.join_date.error" x-cloak></span>
                    </div>

                    <div class="field" style="margin-bottom:0;">
                        <label class="label" for="basic_salary">Gaji Pokok <span style="color:var(--red)">*</span></label>
                        <div style="position:relative; max-width:240px;">
                            <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--muted); font-size:13px; pointer-events:none;">Rp</span>
                            <input id="basic_salary" name="basic_salary" type="number" min="0" step="1000"
                                x-ref="basic_salary"
                                :class="['input', getClass('basic_salary')]"
                                @blur="onBlur('basic_salary')" @input="onInput('basic_salary')"
                                value="{{ old('basic_salary', $employee->basic_salary) }}"
                                style="padding-left:36px;">
                        </div>
                        @error('basic_salary')<span class="field-error">{{ $message }}</span>@enderror
                        <span class="field-error" x-show="fields.basic_salary.touched && fields.basic_salary.error" x-text="fields.basic_salary.error" x-cloak></span>
                    </div>
                </div>

                {{-- RIGHT: Info Rekening --}}
                <div>
                    <div style="font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:16px; padding-bottom:8px; border-bottom:1px solid var(--line);">
                        Informasi Rekening Bank
                    </div>

                    <div class="field" style="margin-bottom:16px;">
                        <label class="label" for="bank_name">Nama Bank</label>
                        <input id="bank_name" name="bank_name" type="text" x-ref="bank_name"
                            class="input @error('bank_name') error @enderror"
                            @input="onInput('bank_name')"
                            value="{{ old('bank_name', $employee->bank_name) }}"
                            placeholder="e.g. BCA, BNI, Mandiri">
                        @error('bank_name')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field" style="margin-bottom:16px;">
                        <label class="label" for="bank_account_number">Nomor Rekening</label>
                        <input id="bank_account_number" name="bank_account_number" type="text" x-ref="bank_account_number"
                            class="input @error('bank_account_number') error @enderror"
                            @input="onInput('bank_account_number')"
                            value="{{ old('bank_account_number', $employee->bank_account_number) }}"
                            placeholder="e.g. 1234567890" style="font-family:monospace;">
                        @error('bank_account_number')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field" style="margin-bottom:0;">
                        <label class="label" for="bank_account_status">Status Rekening <span style="color:var(--red)">*</span></label>
                        <select id="bank_account_status" name="bank_account_status" x-ref="bank_account_status"
                            :class="['input', getClass('bank_account_status')]"
                            @blur="onBlur('bank_account_status')"
                            @change="fields.bank_account_status.value=$event.target.value; onBlur('bank_account_status')"
                            style="max-width:200px;">
                            <option value="">-- Pilih Status --</option>
                            @foreach(['verified'=>'Verified','unverified'=>'Unverified','rejected'=>'Rejected'] as $val=>$lbl)
                                <option value="{{ $val }}" {{ old('bank_account_status',$employee->bank_account_status)===$val?'selected':'' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        @error('bank_account_status')<span class="field-error">{{ $message }}</span>@enderror
                        <span class="field-error" x-show="fields.bank_account_status.touched && fields.bank_account_status.error" x-text="fields.bank_account_status.error" x-cloak></span>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div style="display:flex; gap:10px; margin-top:28px; padding-top:20px; border-top:1px solid var(--line);">
                <button type="submit" class="btn btn-primary">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Simpan Perubahan
                </button>
                <a href="{{ route('employees.show', $employee) }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</section>
