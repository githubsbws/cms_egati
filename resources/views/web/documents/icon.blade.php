@php
    $paths = [
        'credit' => '<path d="m12 3 8 3v5c0 5-5 9-8 11-3-2-8-6-8-11V6z"/><path d="m8 12 3 3 5-6"/>',
        'finance' => '<rect x="4" y="3" width="16" height="19" rx="2"/><path d="M8 7h8M8 11h2m4 0h2M8 15h2m4 0h2M8 19h2m4 0h2"/>',
        'annual' => '<path d="M12 6c-3-2-6-3-10-2v15c4-1 7 0 10 2 3-2 6-3 10-2V4c-4-1-7 0-10 2v15"/>',
    ];
@endphp
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $paths[$name] ?? '' !!}</svg>
