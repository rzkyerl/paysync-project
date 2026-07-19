<section class="card"
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
        validateNip(val) {
            if (!val || val.trim() === '') return 'NIP wajib diisi.';
            if (!/^EMP-\d+$/.test(val.trim())) return 'Format NIP tidak valid. Gunakan format EMP-XXXX (contoh: EMP-0001).';
            return '';
        },
        validateRequired(val, label) {
            if (!val || val.trim() === '') return label + ' wajib diisi.';
            if (val.trim().length < 2) return label + ' minimal 2 karakter.';
            return '';
        },
        validateSelect(val, label) {
            if (!val || val === '') return label + ' wajib dipilih.';
            return '';
        },
        validateSalary(val) {
            if (val === '' || val === null || val === undefined) return 'Gaji Pokok wajib diisi.';
            if (isNaN(Number(val))) return 'Gaji Pokok harus berupa angka.';
            if (Number(val) < 0) return 'Gaji Pokok tidak boleh negatif.';
            return '';
        },
        validateDate(val) {
            if (!val || val.trim() === '') return 'Tanggal Bergabung wajib diisi.';
            return '';
        },
        onBlur(fieldName) {
            this.fields[fieldName].touched = true;
            this.runValidation(fieldName);
        },
        onInput(fieldName) {
            this.fields[fieldName].value = this.$refs[fieldName] ? this.$refs[fieldName].value : this.fields[fieldName].value;
            if (this.fields[fieldName].touched) {
                this.runValidation(fieldName);
            }
        },
        runValidation(fieldName) {
            const val = this.fields[fieldName].value;
            if (fieldName === 'nip')                  this.fields[fieldName].error = this.validateNip(val);
            else if (fieldName === 'name')            this.fields[fieldName].error = this.validateRequired(val, 'Nama Lengkap');
            else if (fieldName === 'department')      this.fields[fieldName].error = this.validateRequired(val, 'Departemen');
            else if (fieldName === 'position')        this.fields[fieldName].error = this.validateRequired(val, 'Jabatan');
            else if (fieldName === 'work_status')     this.fields[fieldName].error = this.validateSelect(val, 'Status Kerja');
            else if (fieldName === 'join_date')       this.fields[fieldName].error = this.validateDate(val);
            else if (fieldName === 'basic_salary')    this.fields[fieldName].error = this.validateSalary(val);
            else if (fieldName === 'bank_account_status') this.fields[fieldName].error = this.validateSelect(val, 'Status Rekening');
        },
        getClass(fieldName) {
            if (!this.fields[fieldName].touched) return '';
            return this.fields[fieldName].error ? 'error' : 'valid';
        },
        get hasClientErrors() {
            return Object.values(this.fields).some(f => f.touched && f.error !== '');
        }
    }">
    <div class="section-title"><h2>Tambah Karyawan Baru</h2></div>
    <div class="section-body">

        {{-- Error Summary (server-side, task 10.5) --}}
        @if($errors->any())
        <div class="form-error-summary" role="alert">
            <svg class="form-error-summary-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16.5" r=".5" fill="currentColor" stroke="none"/>
            </svg>
            <div class="form-error-summary-body">
                <p class="form-error-summary-title">Terdapat {{ $errors->count() }} kesalahan yang perlu diperbaiki:</p>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('employees.store') }}" novalidate>
            @csrf

            {{-- NIP --}}
            <div class="field" style="margin-bottom:16px;">
                <label class="label" for="nip">NIP <span style="color:var(--red)">*</span></label>
                <input id="nip" name="nip" type="text"
                    x-ref="nip"
                    :class="['input', '{{ $errors->has('nip') ? 'error' : '' }}', getClass('nip')]"
                    @blur="onBlur('nip')"
                    @input="onInput('nip')"
                    value="{{ old('nip') }}" placeholder="EMP-0001" style="max-width:260px;">
                @error('nip')<span class="field-error">{{ $message }}</span>@enderror
                <span class="field-error" x-show="fields.nip.touched && fields.nip.error" x-text="fields.nip.error" x-cloak></span>
            </div>

            {{-- Nama --}}
            <div class="field" style="margin-bottom:16px;">
                <label class="label" for="name">Nama Lengkap <span style="color:var(--red)">*</span></label>
                <input id="name" name="name" type="text"
                    x-ref="name"
                    :class="['input', '{{ $errors->has('name') ? 'error' : '' }}', getClass('name')]"
                    @blur="onBlur('name')"
                    @input="onInput('name')"
                    value="{{ old('name') }}" placeholder="Nama karyawan" style="max-width:360px;">
                @error('name')<span class="field-error">{{ $message }}</span>@enderror
                <span class="field-error" x-show="fields.name.touched && fields.name.error" x-text="fields.name.error" x-cloak></span>
            </div>

            {{-- Departemen --}}
            <div class="field" style="margin-bottom:16px;">
                <label class="label" for="department">Departemen <span style="color:var(--red)">*</span></label>
                <input id="department" name="department" type="text"
                    x-ref="department"
                    :class="['input', '{{ $errors->has('department') ? 'error' : '' }}', getClass('department')]"
                    @blur="onBlur('department')"
                    @input="onInput('department')"
                    value="{{ old('department') }}" placeholder="e.g. Engineering"
                    list="department-list" style="max-width:260px;">
                @isset($departments)
                    <datalist id="department-list">
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}">
                        @endforeach
                    </datalist>
                @endisset
                @error('department')<span class="field-error">{{ $message }}</span>@enderror
                <span class="field-error" x-show="fields.department.touched && fields.department.error" x-text="fields.department.error" x-cloak></span>
            </div>

            {{-- Jabatan --}}
            <div class="field" style="margin-bottom:16px;">
                <label class="label" for="position">Jabatan <span style="color:var(--red)">*</span></label>
                <input id="position" name="position" type="text"
                    x-ref="position"
                    :class="['input', '{{ $errors->has('position') ? 'error' : '' }}', getClass('position')]"
                    @blur="onBlur('position')"
                    @input="onInput('position')"
                    value="{{ old('position') }}" placeholder="e.g. Software Engineer" style="max-width:260px;">
                @error('position')<span class="field-error">{{ $message }}</span>@enderror
                <span class="field-error" x-show="fields.position.touched && fields.position.error" x-text="fields.position.error" x-cloak></span>
            </div>

            {{-- Status Kerja --}}
            <div class="field" style="margin-bottom:16px;">
                <label class="label" for="work_status">Status Kerja <span style="color:var(--red)">*</span></label>
                <select id="work_status" name="work_status"
                    x-ref="work_status"
                    :class="['input', '{{ $errors->has('work_status') ? 'error' : '' }}', getClass('work_status')]"
                    @blur="onBlur('work_status')"
                    @change="fields.work_status.value = $event.target.value; onBlur('work_status')"
                    style="max-width:200px;">
                    <option value="">-- Pilih Status --</option>
                    @foreach(['active'=>'Aktif','probation'=>'Probation','contract'=>'Kontrak','inactive'=>'Tidak Aktif'] as $val => $label)
                        <option value="{{ $val }}" {{ old('work_status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('work_status')<span class="field-error">{{ $message }}</span>@enderror
                <span class="field-error" x-show="fields.work_status.touched && fields.work_status.error" x-text="fields.work_status.error" x-cloak></span>
            </div>

            {{-- Tanggal Bergabung --}}
            <div class="field" style="margin-bottom:16px;">
                <label class="label" for="join_date">Tanggal Bergabung <span style="color:var(--red)">*</span></label>
                <input id="join_date" name="join_date" type="date"
                    x-ref="join_date"
                    :class="['input', '{{ $errors->has('join_date') ? 'error' : '' }}', getClass('join_date')]"
                    @blur="onBlur('join_date')"
                    @input="onInput('join_date')"
                    value="{{ old('join_date') }}" style="max-width:200px;">
                @error('join_date')<span class="field-error">{{ $message }}</span>@enderror
                <span class="field-error" x-show="fields.join_date.touched && fields.join_date.error" x-text="fields.join_date.error" x-cloak></span>
            </div>

            {{-- Gaji Pokok --}}
            <div class="field" style="margin-bottom:16px;">
                <label class="label" for="basic_salary">Gaji Pokok <span style="color:var(--red)">*</span></label>
                <input id="basic_salary" name="basic_salary" type="number" min="0" step="1000"
                    x-ref="basic_salary"
                    :class="['input', '{{ $errors->has('basic_salary') ? 'error' : '' }}', getClass('basic_salary')]"
                    @blur="onBlur('basic_salary')"
                    @input="onInput('basic_salary')"
                    value="{{ old('basic_salary') }}" placeholder="5000000" style="max-width:200px;">
                @error('basic_salary')<span class="field-error">{{ $message }}</span>@enderror
                <span class="field-error" x-show="fields.basic_salary.touched && fields.basic_salary.error" x-text="fields.basic_salary.error" x-cloak></span>
            </div>

            {{-- Status Rekening --}}
            <div class="field" style="margin-bottom:16px;">
                <label class="label" for="bank_account_status">Status Rekening <span style="color:var(--red)">*</span></label>
                <select id="bank_account_status" name="bank_account_status"
                    x-ref="bank_account_status"
                    :class="['input', '{{ $errors->has('bank_account_status') ? 'error' : '' }}', getClass('bank_account_status')]"
                    @blur="onBlur('bank_account_status')"
                    @change="fields.bank_account_status.value = $event.target.value; onBlur('bank_account_status')"
                    style="max-width:200px;">
                    <option value="">-- Pilih Status --</option>
                    @foreach(['verified'=>'Verified','unverified'=>'Unverified','rejected'=>'Rejected'] as $val => $label)
                        <option value="{{ $val }}" {{ old('bank_account_status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('bank_account_status')<span class="field-error">{{ $message }}</span>@enderror
                <span class="field-error" x-show="fields.bank_account_status.touched && fields.bank_account_status.error" x-text="fields.bank_account_status.error" x-cloak></span>
            </div>

            {{-- Nomor Rekening --}}
            <div class="field" style="margin-bottom:16px;">
                <label class="label" for="bank_account_number">Nomor Rekening</label>
                <input id="bank_account_number" name="bank_account_number" type="text"
                    x-ref="bank_account_number"
                    class="input @error('bank_account_number') error @enderror"
                    @input="onInput('bank_account_number')"
                    value="{{ old('bank_account_number') }}" placeholder="1234567890" style="max-width:260px;">
                @error('bank_account_number')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            {{-- Nama Bank --}}
            <div class="field" style="margin-bottom:24px;">
                <label class="label" for="bank_name">Nama Bank</label>
                <input id="bank_name" name="bank_name" type="text"
                    x-ref="bank_name"
                    class="input @error('bank_name') error @enderror"
                    @input="onInput('bank_name')"
                    value="{{ old('bank_name') }}" placeholder="BCA / Mandiri / BRI" style="max-width:260px;">
                @error('bank_name')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary">Simpan Karyawan</button>
                <a href="{{ route('employees.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</section>
