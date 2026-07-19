<div class="grid grid-4">
    @for ($i = 0; $i < 4; $i++)
        <div class="card kpi skeleton skeleton-card">
            {{-- placeholder: label --}}
            <div class="skeleton-text" style="width:55%; border-radius:6px;"></div>
            {{-- placeholder: value --}}
            <div class="skeleton-text" style="width:70%; height:28px; margin:12px 0 8px; border-radius:6px;"></div>
            {{-- placeholder: badge --}}
            <div class="skeleton-text" style="width:40%; height:20px; border-radius:999px;"></div>
        </div>
    @endfor
</div>
