@extends('admin.layouts.mainlayout')

@section('title', __('admin/menuleft.set_sub.language'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('admin/menuleft.set_sub.language') }}</li>
@endsection

@section('content')
    @include('admin.layouts.partials.alerts')

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                <a href="{{ route('admin.settings.language.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>
                    {{ __('admin/language.add') }}
                </a>
                @if ($languages->firstWhere('is_default', 'y'))
                    <a href="{{ route('admin.settings.language.download', $languages->firstWhere('is_default', 'y')) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-download me-1"></i>
                        {{ __('admin/language.download_template', ['code' => $languages->firstWhere('is_default', 'y')->code]) }}
                    </a>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 70px;">{{ __('admin/language.col_order') }}</th>
                            <th style="width: 70px;">{{ __('admin/language.field_flag') }}</th>
                            <th style="width: 90px;">{{ __('admin/language.col_code') }}</th>
                            <th>{{ __('admin/language.col_name') }}</th>
                            <th style="width: 110px;">{{ __('admin/language.col_direction') }}</th>
                            <th style="width: 150px;">{{ __('admin/language.col_status') }}</th>
                            <th style="width: 130px;">{{ __('admin/language.col_strings') }}</th>
                            <th style="width: 170px;">{{ __('admin/common.manage') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($languages as $language)
                            <tr>
                                <td>{{ $language->sort_order }}</td>
                                <td>
                                    @if ($language->flag)
                                        <img src="{{ web_file_url($language->flag) }}" alt="" style="height: 20px; width: 30px; object-fit: cover;" class="border">
                                    @endif
                                </td>
                                <td><code>{{ $language->code }}</code></td>
                                <td>{{ $language->native_name }} <span class="text-muted small">({{ $language->name }})</span></td>
                                <td>{{ strtoupper($language->direction) }}</td>
                                <td>
                                    @if ($language->is_default === 'y')
                                        <span class="badge text-bg-primary">{{ __('admin/language.default_badge') }}</span>
                                    @endif
                                    @if ($language->is_active === 'y')
                                        <span class="badge text-bg-success">{{ __('admin/common.active') }}</span>
                                    @else
                                        <span class="badge text-bg-secondary">{{ __('admin/common.inactive') }}</span>
                                    @endif
                                </td>
                                <td>{{ __('admin/language.keys', ['count' => $language->strings_count]) }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.settings.language.edit', $language) }}" class="btn btn-sm btn-warning" title="{{ __('admin/common.edit') }}"><i class="bi bi-pencil"></i></a>
                                        <a href="{{ route('admin.settings.language.strings', $language) }}" class="btn btn-sm btn-info" title="{{ __('admin/language.edit_text') }}"><i class="bi bi-braces"></i></a>
                                        <a href="{{ route('admin.settings.language.download', $language) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('admin/language.download') }}"><i class="bi bi-download"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection