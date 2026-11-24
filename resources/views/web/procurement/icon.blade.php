{{-- Procurement icons (from the design). Param: $name --}}
@php
    $shapes = [
        'plan' => '<path d="M8 3h8v4H8zM6 5H4v16h16V5h-2M8 12h8M8 16h5"/>',
        'price' => '<path d="M4 4h16v16H4zM7 8h10M8 12h2M14 12h2M8 16h2M14 16h2"/>',
        'notice' => '<path d="M5 3h10l4 4v14H5zM14 3v5h5M8 12h8M8 16h6"/>',
        'award' => '<circle cx="12" cy="9" r="6"/><path d="m8 14-1 7 5-3 5 3-1-7M10 9l1.5 1.5L15 7"/>',
        'users' => '<circle cx="9" cy="8" r="3"/><path d="M3 20v-3a6 6 0 0 1 12 0v3M16 5a3 3 0 0 1 0 6M18 14a5 5 0 0 1 3 4v2"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
        'file' => '<path d="M5 3h9l5 5v13H5zM14 3v6h5M8 14h8M8 17h5"/>',
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
    ];
@endphp
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $shapes[$name] ?? $shapes['file'] !!}</svg>