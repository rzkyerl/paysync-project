@props([
    'icon' => 'folder',
    'title',
    'description' => null,
    'ctaLabel' => null,
    'ctaUrl' => null,
])

<div {{ $attributes->class(['card']) }} style="text-align:center; padding:48px 24px;">
    <div class="icon-box" style="margin:0 auto 14px;">
        @include('payflow.partials.icon', ['name' => $icon, 'class' => 'icon icon-lg'])
    </div>
    <h3 style="margin:0; color:var(--navy);">{{ $title }}</h3>
    @if($description)
        <p class="muted" style="margin:8px auto 20px; max-width:520px;">{{ $description }}</p>
    @endif
    @if($ctaLabel && $ctaUrl)
        <a class="btn btn-primary" href="{{ $ctaUrl }}">{{ $ctaLabel }}</a>
    @endif
</div>
