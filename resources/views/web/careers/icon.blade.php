@php
    $paths = [
        'arrow' => '<path d="M5 19 19 5M5 5h14v14"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="14" rx="3"/><path d="M8 7V4h8v3M3 12c5 4 13 4 18 0M10 14h4"/>',
        'people' => '<circle cx="9" cy="7" r="3"/><path d="M3 21v-4a6 6 0 0 1 12 0v4M17 5a3 3 0 0 1 0 6m2 10v-4a5 5 0 0 0-2-4"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="3"/><path d="M7 3v4m10-4v4M3 11h18m-14 4h3m4 0h3m-10 3h3"/>',
        'file' => '<path d="M5 3h9l5 5v13H5zM14 3v6h5M9 13h6m-6 4h6"/>',
        'mail' => '<rect x="2" y="5" width="20" height="15" rx="3"/><path d="m3 7 9 7 9-7"/>',
        'growth' => '<path d="M4 21V11m6 10V7m6 14V3M2 7l7-5m-3 0h3v3"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
    ];
@endphp
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $paths[$name] ?? '' !!}</svg>
