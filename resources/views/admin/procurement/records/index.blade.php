@extends('admin.layouts.mainlayout')

@php
    $pageTitle = __('admin/menuleft.procurement_sub.procurement_'.$type);
    $route = fn (string $name, $param = null) => route("admin.procurement.{$type}.{$name}", $param);
    $show = fn (string $field): bool => in_array($field, $fields, true);
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
            {{-- Toolbar: add + filters --}}
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                <a href="{{ $route('create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> {{ __('admin/procurement.add') }}</a>
                <form method="GET" action="{{ $route('index') }}" class="d-flex flex-wrap gap-2">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" style="width: 240px;" placeholder="{{ __('admin/procurement.search_placeholder') }}">
                    <select name="year" class="form-select" style="width: 120px;">
                        <option value="">{{ __('admin/procurement.all_years') }}</option>
                        @foreach ($years as $year)
                            <option value="{{ $year }}" @selected((int) request('year') === (int) $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                    @if ($show('agency'))
                        <select name="agency" class="form-select" style="width: 170px;">
                            <option value="">{{ __('admin/procurement.all_agencies') }}</option>
                            @foreach ($agencies as $agency)
                                <option value="{{ $agency->id }}" @selected((int) request('agency') === $agency->id)>{{ $agency->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    @if ($show('method'))
                        <select name="method" class="form-select" style="width: 190px;">
                            <option value="">{{ __('admin/procurement.all_methods') }}</option>
                            @foreach ($methods as $method)
                                <option value="{{ $method->id }}" @selected((int) request('method') === $method->id)>{{ $method->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <select name="status" class="form-select" style="width: 150px;">
                        <option value="">{{ __('admin/procurement.all_status') }}</option>
                        <option value="published" @selected(request('status') === 'published')>{{ __('admin/procurement.status_published') }}</option>
                        <option value="draft" @selected(request('status') === 'draft')>{{ __('admin/procurement.status_draft') }}</option>
                    </select>
                    <button type="submit" class="btn btn-outline-primary"><i class="bi bi-search"></i> {{ __('admin/common.search') }}</button>
                    @if (request()->hasAny(['q', 'year', 'agency', 'method', 'status']))
                        <a href="{{ $route('index') }}" class="btn btn-outline-secondary">{{ __('admin/common.reset') }}</a>
                    @endif
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 70px;">{{ __('admin/procurement.col_year') }}</th>
                            <th>{{ __('admin/procurement.col_title') }}</th>
                            @if ($show('vendor'))<th>{{ __('admin/procurement.col_vendor') }}</th>@endif
                            @if ($show('agency'))<th style="width: 110px;">{{ __('admin/procurement.col_agency') }}</th>@endif
                            @if ($show('method'))<th style="width: 150px;">{{ __('admin/procurement.col_method') }}</th>@endif
                            @if ($show('amount'))<th class="text-end" style="width: 130px;">{{ __('admin/procurement.col_amount') }}</th>@endif
                            @if ($show('date'))<th style="width: 110px;">{{ __('admin/procurement.col_date') }}</th>@endif
                            <th style="width: 60px;">{{ __('admin/procurement.col_files') }}</th>
                            <th style="width: 110px;">{{ __('admin/procurement.col_status') }}</th>
                            <th style="width: 100px;">{{ __('admin/common.manage') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $record)
                            <tr>
                                <td>{{ $record->fiscal_year }}</td>
                                <td>{{ $record->title }}</td>
                                @if ($show('vendor'))<td>{{ $record->vendor?->name ?? '-' }}</td>@endif
                                @if ($show('agency'))<td>{{ $record->agency?->name ?? '-' }}</td>@endif
                                @if ($show('method'))<td>{{ $record->method?->name ?? '-' }}</td>@endif
                                @if ($show('amount'))<td class="text-end">{{ $record->amount !== null ? number_format((float) $record->amount, 2) : '-' }}</td>@endif
                                @if ($show('date'))<td>{{ $record->published_on?->format('d/m/Y') ?? '-' }}</td>@endif
                                <td>{{ $record->attachments->count() }}</td>
                                <td>
                                    @if ($record->status === 'published')
                                        <span class="badge text-bg-success">{{ __('admin/procurement.status_published') }}</span>
                                    @else
                                        <span class="badge text-bg-secondary">{{ __('admin/procurement.status_draft') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ $route('edit', $record) }}" class="btn btn-sm btn-warning" title="{{ __('admin/common.edit') }}"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" action="{{ $route('destroy', $record) }}" onsubmit="return confirm(@js(__('admin/procurement.confirm_delete')));">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="{{ __('admin/common.delete') }}"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">{{ __('admin/common.no_data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-end">{{ $records->links() }}</div>
        </div>
    </div>
@endsection