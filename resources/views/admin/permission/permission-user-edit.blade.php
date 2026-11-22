@extends('admin.layouts.mainlayout')

@section('title', __('admin/common.edit'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.permission.user') }}">{{ __('admin/menuleft.permis_sub.admin_list') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('admin/common.manage') }}</li>
@endsection

@section('content')

    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card">

                <div class="card-header bg-warning">
                    <h3 class="card-title">
                        {{ __('admin/common.edit') }}
                    </h3>
                </div>

                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.permission.user.update', $user) }}">
                        @csrf
                        @method('PUT')

                        {{-- 1. ชื่อผู้ใช้ --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin/common.username') }}<span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
                            </div>
                        </div>

                        {{-- 3-4. รหัสผ่าน / ยืนยันรหัสผ่าน --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin/common.password') }}</label>
                                <input type="password" name="password" class="form-control" autocomplete="new-password">
                                <div class="form-text">{{ __('admin/common.password_hint') }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin/common.password_confirmation') }}</label>
                                <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                            </div>
                        </div>

                        {{-- 5-7. คำนำหน้า / ชื่อ / นามสกุล --}}
                        <div class="row mb-3">
                            {{-- <div class="col-md-4">
                                <label class="form-label">{{ __('admin/common.title_name') }}</label>
                                <select name="title_id" class="form-select">
                                    <option value="">-</option>
                                </select>
                            </div> --}}
                            <div class="col-md-4">
                                <label class="form-label">{{ __('admin/common.firstname') }}<span class="text-danger">*</span></label>
                                <input type="text" name="firstname" class="form-control" value="{{ old('firstname', $profile->firstname ?? '') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('admin/common.lastname') }}<span class="text-danger">*</span></label>
                                <input type="text" name="lastname" class="form-control" value="{{ old('lastname', $profile->lastname ?? '') }}" required>
                            </div>
                        </div>

                        {{-- 8. เบอร์โทร --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin/common.phone') }}</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $profile->phone ?? '') }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin/permission.group_name') }}<span class="text-danger">*</span></label>
                                <select name="group_id" class="form-select" required>
                                    @foreach ($adminGroups as $adminGroup)
                                        <option value="{{ $adminGroup->id }}" {{ (int) old('group_id', $user->group_id) === $adminGroup->id ? 'selected' : '' }}>{{ $adminGroup->group_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{-- 12. สถานะ (อ่านจาก del_status อย่างเดียว ไม่ให้แก้ตรงนี้) --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin/common.status') }}</label>
                                <div>
                                    @if ((int) $user->del_status === 1)
                                        <span class="badge bg-danger">{{ __('admin/common.inactive') }}</span>
                                    @else
                                        <span class="badge bg-success">{{ __('admin/common.active') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <a href="{{ route('admin.permission.user') }}" class="btn btn-outline-secondary">
                                {{ __('admin/common.cancel') }}
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
