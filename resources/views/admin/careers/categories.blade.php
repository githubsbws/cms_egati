@extends('admin.layouts.mainlayout')

@php
    $pageTitle = __('admin/menuleft.work_us_sub.career_categories');
    $route = fn (string $name, $param = null) => route("admin.careers.categories.{$name}", $param);
@endphp

@section('title', $pageTitle)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
@endsection

@section('content')
    @include('admin.layouts.partials.alerts')

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <p class="text-muted mb-0">{{ __('admin/careers.category_intro') }}</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                    <i class="bi bi-plus-lg me-1"></i> {{ __('admin/careers.add_category') }}
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px;">{{ __('admin/common.no') }}</th>
                            <th>{{ __('admin/careers.col_name') }}</th>
                            <th>{{ __('admin/careers.col_name_en') }}</th>
                            <th style="width: 150px;">{{ __('admin/careers.col_used') }}</th>
                            <th style="width: 130px;">{{ __('admin/common.status') }}</th>
                            <th style="width: 130px;">{{ __('admin/common.manage') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->name_en ?? '-' }}</td>
                                <td>{{ $item->jobs_count }} {{ __('admin/careers.positions') }}</td>
                                <td>
                                    @if ($item->is_active)
                                        <span class="badge text-bg-success">{{ __('admin/common.active') }}</span>
                                    @else
                                        <span class="badge text-bg-secondary">{{ __('admin/common.inactive') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editItemModal{{ $item->id }}" title="{{ __('admin/common.edit') }}"><i class="bi bi-pencil"></i></button>
                                        @if ($item->jobs_count === 0)
                                            <form method="POST" action="{{ $route('destroy', $item) }}" onsubmit="return confirm(@js(__('admin/common.confirm_delete_message')));">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="{{ __('admin/common.delete') }}"><i class="bi bi-trash"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">{{ __('admin/common.no_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add --}}
    <div class="modal fade" id="addItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ $route('store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('admin/careers.add_category') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label" for="new-name">{{ __('admin/careers.col_name') }}</label>
                    <input type="text" id="new-name" name="name" maxlength="255" class="form-control mb-3" required>
                    <label class="form-label" for="new-name-en">{{ __('admin/careers.col_name_en') }}</label>
                    <input type="text" id="new-name-en" name="name_en" maxlength="255" class="form-control">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('admin/common.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('admin/common.save') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit (one modal per row) --}}
    @foreach ($items as $item)
        <div class="modal fade" id="editItemModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ $route('update', $item) }}" class="modal-content">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('admin/common.edit') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label" for="name-{{ $item->id }}">{{ __('admin/careers.col_name') }}</label>
                        <input type="text" id="name-{{ $item->id }}" name="name" value="{{ $item->name }}" maxlength="255" class="form-control mb-3" required>
                        <label class="form-label" for="name-en-{{ $item->id }}">{{ __('admin/careers.col_name_en') }}</label>
                        <input type="text" id="name-en-{{ $item->id }}" name="name_en" value="{{ $item->name_en }}" maxlength="255" class="form-control mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active-{{ $item->id }}" @checked($item->is_active)>
                            <label class="form-check-label" for="active-{{ $item->id }}">{{ __('admin/careers.lookup_active') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('admin/common.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('admin/common.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endsection