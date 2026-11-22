@extends('admin.layouts.mainlayout')

@section('title', __('admin/menuleft.permis_sub.admin_group'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('admin/menuleft.permis_sub.admin_group') }}</li>
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- =========================================================
         Card: ปุ่มเพิ่ม (ซ้ายมือ) + ตาราง
    ========================================================== --}}
    <div class="card">

        <div class="card-body">

            <div class="mb-2">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminGroupModal">
                    <i class="bi bi-plus-lg me-1"></i>
                    {{ __('admin/permission.add_button') }}
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px;">{{ __('admin/common.no') }}</th>
                            <th>{{ __('admin/permission.group_name') }}</th>
                            <th>{{ __('admin/common.manage') }}</th>
                            <th>{{ __('admin/common.delete') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($adminGroups as $index => $adminGroup)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $adminGroup->group_name }}</td>
                                <td>
                                    @if ($adminGroup->is_superuser === true)
                                        <span class="text-muted">{{ __('admin/common.cannot_edit') }}</span>
                                    @else
                                        <a
                                            href="{{ route('admin.permission.group.edit', $adminGroup) }}"
                                            class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    @if ($adminGroup->is_superuser === true)
                                        <span class="text-muted">{{ __('admin/common.cannot_delete') }}</span>
                                    @else
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteAdminGroupModal{{ $adminGroup->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    {{ __('admin/common.no_data') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>

    {{-- =========================================================
         Modal: เพิ่มกลุ่มผู้ใช้งาน
    ========================================================== --}}
    <div class="modal fade" id="addAdminGroupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.permission.group.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('admin/permission.modal_add_title') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="group_name" class="form-label">{{ __('admin/permission.group_name_th') }}</label>
                            <input type="text" class="form-control" id="group_name" name="group_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            {{ __('admin/common.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ __('admin/common.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach ($adminGroups as $adminGroup)
        {{-- =========================================================
             Modal: ยืนยันการลบกลุ่มผู้ใช้งาน
        ========================================================== --}}
        <div class="modal fade" id="deleteAdminGroupModal{{ $adminGroup->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('admin/common.confirm_delete') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {{ __('admin/common.confirm_delete_message') }}
                        <div class="fw-bold mt-2">{{ $adminGroup->group_name }}</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            {{ __('admin/common.cancel') }}
                        </button>
                        <form method="POST" action="{{ route('admin.permission.group.destroy', $adminGroup) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                {{ __('admin/common.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    @endforeach

@endsection
