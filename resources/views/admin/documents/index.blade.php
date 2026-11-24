@extends('admin.layouts.mainlayout')

@php
    $pageTitle = __('admin/menuleft.document_sub.document_'.$type);
    $route = fn (string $name, $param = null) => route("admin.documents.{$type}.{$name}", $param);
    $offset = config('publications.buddhist_offset');
@endphp

@section('title', $pageTitle)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
@endsection

@section('content')
    @include('admin.layouts.partials.alerts')

    <div class="d-flex justify-content-end mb-3">
        <a href="{{ $route('create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> {{ __('admin/documents.add_item') }}</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 130px;">{{ __('admin/documents.col_year') }}</th>
                        @if ($type === 'credit')
                            <th>{{ __('admin/documents.col_agency') }}</th>
                            <th style="width: 110px;">{{ __('admin/documents.col_rating') }}</th>
                        @endif
                        @if ($type === 'finance')
                            <th>{{ __('admin/documents.col_period') }}</th>
                        @endif
                        @if ($type === 'annual')
                            <th style="width: 90px;"></th>
                        @endif
                        <th>{{ __('admin/documents.col_files') }}</th>
                        <th style="width: 110px;">{{ __('admin/documents.col_status') }}</th>
                        <th style="width: 110px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td>{{ $item->year + $offset }} <span class="text-muted small">({{ $item->year }})</span></td>
                            @if ($type === 'credit')
                                <td>{{ $item->agency }}</td>
                                <td><span class="badge text-bg-info">{{ $item->rating }}</span></td>
                            @endif
                            @if ($type === 'finance')
                                <td>{{ $item->period === 'annual' ? __('admin/documents.period_annual') : __('admin/documents.period_quarter', ['n' => $item->period]) }}</td>
                            @endif
                            @if ($type === 'annual')
                                <td>@if ($item->cover_url)<img src="{{ $item->cover_url }}" alt="" style="height: 44px;">@endif</td>
                            @endif
                            <td class="text-nowrap">
                                @if ($url = $item->documentUrl('th'))
                                    <a href="{{ $url }}" target="_blank" rel="noopener" class="badge text-bg-light border text-decoration-none">PDF{{ $type === 'finance' ? ' '.__('admin/documents.file_th') : '' }} ↗</a>
                                @endif
                                @if ($type === 'finance' && ($url = $item->documentUrl('en')))
                                    <a href="{{ $url }}" target="_blank" rel="noopener" class="badge text-bg-light border text-decoration-none">PDF {{ __('admin/documents.file_en') }} ↗</a>
                                @endif
                            </td>
                            <td><span class="badge {{ $item->is_published ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $item->is_published ? __('admin/common.active') : __('admin/common.inactive') }}</span></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ $route('edit', $item) }}" class="btn btn-sm btn-warning" title="{{ __('admin/common.edit') }}"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ $route('destroy', $item) }}" class="d-inline" onsubmit="return confirm(@js(__('admin/common.confirm_delete_message')));">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="{{ __('admin/common.delete') }}"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">{{ __('admin/common.no_data') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
