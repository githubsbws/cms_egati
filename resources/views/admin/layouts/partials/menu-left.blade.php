{{-- resources/views/layouts/partials/menu-left.blade.php --}}
@php
    $menuConfig = collect(config('admin_menu'))->keyBy('id');

    $adminMenus = \App\Models\AdminMenu::active()->orderBy('sort_order')->get()->map(function ($menu) use ($menuConfig) {
        $meta = $menuConfig->get($menu->id);

        $menu->icon = $meta['icon'] ?? 'bi-dot';
        $menu->route_name = $meta['route'] ?? null;
        $menu->children = $meta['children'] ?? [];

        return $menu;
    });
@endphp
<aside class="app-sidebar bg-dark-subtle shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="{{ route('admin.home') }}" class="brand-link">
            <span class="brand-text fw-light">Egati Admin</span> {{-- {{ config('app.name') }} --}}
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">
                @foreach ($adminMenus as $menu)
                    @php
                        $hasChildren = count($menu->children) > 0;
                        $isOpen = $hasChildren && collect($menu->children)->contains(
                            fn ($child) => ! empty($child['route']) && request()->routeIs($child['route'])
                        );
                    @endphp

                    <li class="nav-item {{ $isOpen ? 'menu-open' : '' }}">
                        <a href="{{ ! $hasChildren && $menu->route_name ? (str_starts_with($menu->route_name, '/') ? url($menu->route_name) : route($menu->route_name)) : '#' }}"
                           class="nav-link {{ $isOpen ? 'active' : '' }}">
                            <i class="nav-icon bi {{ $menu->icon }}"></i>
                            <p>
                                {{ $menu->menu_name }}
                                @if ($hasChildren)
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                @endif
                            </p>
                        </a>

                        @if ($hasChildren)
                            <ul class="nav nav-treeview">
                                @foreach ($menu->children as $child)
                                    <li class="nav-item">
                                        <a href="{{ ! empty($child['route']) ? route($child['route']) : '#' }}"
                                           class="nav-link {{ ! empty($child['route']) && request()->routeIs($child['route']) ? 'active' : '' }}">
                                            {{-- <i class="nav-icon bi bi-circle"></i> --}}
                                            <p>{{ $child['name'] }}</p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
</aside>