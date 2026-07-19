<div class="table-wrap" style="padding: 8px;">
    @for ($i = 0; $i < 5; $i++)
        <div class="skeleton skeleton-row" style="display: flex; align-items: center; gap: 12px; padding: 0 14px;">
            {{-- placeholder: kolom 1 (mis. nama/NIP) --}}
            <div class="skeleton-text" style="width: 20%; border-radius: 6px;"></div>
            {{-- placeholder: kolom 2 (mis. departemen) --}}
            <div class="skeleton-text" style="width: 18%; border-radius: 6px;"></div>
            {{-- placeholder: kolom 3 (mis. posisi/jabatan) --}}
            <div class="skeleton-text" style="width: 22%; border-radius: 6px;"></div>
            {{-- placeholder: kolom 4 (mis. status badge) --}}
            <div class="skeleton-text" style="width: 14%; height: 20px; border-radius: 999px;"></div>
            {{-- placeholder: kolom 5 (mis. aksi/tanggal) --}}
            <div class="skeleton-text" style="width: 16%; border-radius: 6px; margin-left: auto;"></div>
        </div>
    @endfor
</div>
