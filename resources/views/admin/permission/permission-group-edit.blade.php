@extends('admin.layouts.mainlayout')

@section('title', __('admin/permission.edit_permission_title'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.permission.group') }}">{{ __('admin/menuleft.permis_sub.permission') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('admin/permission.edit_permission_title') }}</li>
@endsection

@section('content')

    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card">

                <div class="card-header bg-primary">
                    <h3 class="card-title text-white">
                        {{ __('admin/permission.edit_title') }}
                    </h3>
                </div>

                <div class="card-body">

                    <form method="POST" action="{{ route('admin.permission.group.permission.update', $adminGroup) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">
                                {{ __('admin/permission.field_group_name') }}<span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control bg-light" value="{{ $adminGroup->group_name }}" readonly>
                        </div>

                        <p class="text-muted small mb-2">
                            {{ __('admin/permission.select_menu_hint') }}
                        </p>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead>
                                    <tr class="table-primary">
                                        <th style="width: 70px;" class="text-center">
                                            <input type="checkbox" class="form-check-input" id="selectAllMenus">
                                        </th>
                                        <th>{{ __('admin/permission.main_menu_column') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($adminMenus as $adminMenu)
                                        <tr>
                                            <td class="text-center">
                                                <input
                                                    type="checkbox"
                                                    class="form-check-input menu-checkbox"
                                                    name="admin_menu_ids[]"
                                                    value="{{ $adminMenu->id }}"
                                                    id="menu_{{ $adminMenu->id }}"
                                                    {{ in_array($adminMenu->id, $allowedMenuIds, true) ? 'checked' : '' }}>
                                            </td>
                                            <td>
                                                <label for="menu_{{ $adminMenu->id }}" class="form-check-label mb-0">
                                                    {{ $adminMenu->label }}
                                                </label>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-4">
                                                {{ __('admin/common.no_data') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <a href="{{ route('admin.permission.group') }}" class="btn btn-outline-secondary">
                                {{ __('admin/common.back') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ __('admin/common.save') }}
                            </button>
                        </div>

                    </form>

                </div>

            </div>

        </div>
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const selectAll = document.getElementById('selectAllMenus');
    const checkboxes = document.querySelectorAll('.menu-checkbox');

    selectAll.addEventListener('change', function () {
        checkboxes.forEach(function (checkbox) {
            checkbox.checked = selectAll.checked;
        });
    });

});
</script>
@endpush
