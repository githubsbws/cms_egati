{{-- resources/views/layouts/partials/header.blade.php --}}
<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="bi bi-list"></i>
                </a>
            </li>
        </ul>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item d-flex align-items-center me-2">
                <div class="btn-group btn-group-sm" role="group" aria-label="Language">
                    <a href="{{ route('lang.switch', 'th') }}" class="btn btn-outline-secondary {{ app()->getLocale() === 'th' ? 'active' : '' }}">ไทย</a>
                    <a href="{{ route('lang.switch', 'en') }}" class="btn btn-outline-secondary {{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i> {{ auth()->user()->profile?->fullname() ?: auth()->user()->email }} 
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#">{{ __('admin/common.profile') }}</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">{{ __('admin/common.logout') }}</button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>