{{-- resources/views/layouts/partials/menu-left.blade.php --}}
<aside class="app-sidebar bg-dark-subtle shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="{{ route('admin.dashboard') }}" class="brand-link">
            <span class="brand-text fw-light">{{ config('app.name') }} Admin</span>
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
                        <a href="{{ ! $hasChildren && $menu->route_name ? route($menu->route_name) : '#' }}"
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