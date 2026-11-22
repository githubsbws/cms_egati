@extends('admin.layouts.mainlayout')

@section('title', __('admin/menuleft.permis_sub.admin_list'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('admin/menuleft.permis_sub.admin_list') }}</li>
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">

            <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminUserModal">
                    <i class="bi bi-plus-lg me-1"></i>
                    {{ __('admin/permission.add_user') }}
                </button>

                <form method="GET" action="{{ route('admin.permission.user') }}" class="d-flex flex-wrap gap-2">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" style="width: 260px;"
                           placeholder="{{ __('admin/permission.search_name_placeholder') }}">
                    <select name="group" class="form-select" style="width: 220px;">
                        <option value="">{{ __('admin/permission.all_groups') }}</option>
                        @foreach ($adminGroups as $adminGroup)
                            <option value="{{ $adminGroup->id }}" {{ (int) request('group') === $adminGroup->id ? 'selected' : '' }}>{{ $adminGroup->group_name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-outline-primary"><i class="bi bi-search"></i> {{ __('admin/common.search') }}</button>
                    @if (request()->hasAny(['q', 'group']))
                        <a href="{{ route('admin.permission.user') }}" class="btn btn-outline-secondary">{{ __('admin/common.reset') }}</a>
                    @endif
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px;">{{ __('admin/common.no') }}</th>
                            <th>{{ __('admin/common.fullname') }}</th>
                            <th style="width: 20%;">Email</th>
                            <th style="width: 20%;">{{ __('admin/common.created_date') }}</th>
                            <th>{{ __('admin/permission.group_name') }}</th>
                            <th style="width: 90px;">{{ __('admin/common.manage') }}</th>
                            <th style="width: 90px;">{{ __('admin/permission.cancel_permission') }}</th>
                        </tr>
                    </thead>                    <tbody>
                        @forelse ($adminUsers as $adminUser)
                            <tr>
                                <td>{{ $adminUsers->firstItem() + $loop->index }}</td>
                                <td>{{ $adminUser->profile?->fullname() ?: '-' }}</td>
                                <td>{{ $adminUser->email }}</td>
                                <td>{{ App\Helpers\DateHelper::formatThaiDate($adminUser->created_at) }}</td>
                                <td>{{ $adminUser->adminGroup?->group_name ?? __('admin/permission.no_group') }}</td>                                <td>
                                    <a href="{{ route('admin.permission.user.edit', $adminUser) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                </td>
                                <td>
                                    @if ($adminUser->is(auth()->user()))
                                        <span class="text-muted">-</span>
                                    @else
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#cancelAdminUserModal{{ $adminUser->id }}">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">{{ __('admin/common.no_data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-end">
                {{ $adminUsers->links() }}
            </div>
        </div>
    </div>

    @foreach ($adminUsers as $adminUser)
        @if (! $adminUser->is(auth()->user()))
            <div class="modal fade" id="cancelAdminUserModal{{ $adminUser->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('admin/permission.cancel_permission') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            {{ __('admin/permission.confirm_cancel_permission_message') }}
                            <div class="fw-bold mt-2">{{ $adminUser->profile?->fullname() ?: $adminUser->email }}</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('admin/common.cancel') }}</button>
                            <form method="POST" action="{{ route('admin.permission.user.cancel', $adminUser) }}">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-danger">{{ __('admin/permission.cancel_permission') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <div class="modal fade" id="addAdminUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.permission.user.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('admin/permission.modal_add_user_title') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin/common.username') }}<span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin/common.email') }}<span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin/common.firstname') }}<span class="text-danger">*</span></label>
                                <input type="text" name="firstname" class="form-control" value="{{ old('firstname') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin/common.lastname') }}<span class="text-danger">*</span></label>
                                <input type="text" name="lastname" class="form-control" value="{{ old('lastname') }}" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin/common.phone') }}</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin/permission.group_name') }}<span class="text-danger">*</span></label>
                                <select name="group_id" class="form-select" required>
                                    <option value="">{{ __('admin/permission.select_group') }}</option>
                                    @foreach ($adminGroups as $adminGroup)
                                        <option value="{{ $adminGroup->id }}" {{ (int) old('group_id') === $adminGroup->id ? 'selected' : '' }}>{{ $adminGroup->group_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin/common.password') }}<span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" autocomplete="new-password" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin/common.password_confirmation') }}<span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('admin/common.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('admin/common.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection