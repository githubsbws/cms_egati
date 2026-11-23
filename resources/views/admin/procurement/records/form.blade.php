@extends('admin.layouts.mainlayout')

@php
    $editing = $record->exists;
    $listTitle = __('admin/menuleft.procurement_sub.procurement_'.$type);
    $pageTitle = ($editing ? __('admin/procurement.edit') : __('admin/procurement.add')).' - '.$listTitle;
    $show = fn (string $field): bool => in_array($field, $fields, true);
@endphp

@section('title', $pageTitle)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route("admin.procurement.{$type}.index") }}">{{ $listTitle }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $editing ? __('admin/procurement.edit') : __('admin/procurement.add') }}</li>
@endsection

@section('content')
    @include('admin.layouts.partials.alerts')

    <form method="POST" action="{{ $editing ? route("admin.procurement.{$type}.update", $record) : route("admin.procurement.{$type}.store") }}" enctype="multipart/form-data">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="row g-3">
            {{-- Left: the record itself --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="title">{{ __('admin/procurement.field_title') }} <span class="text-danger">*</span></label>
                            <input type="text" id="title" name="title" maxlength="500" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $record->title) }}">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        @if ($show('vendor'))
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label" for="vendor_name">{{ __('admin/procurement.field_vendor') }} <span class="text-danger">*</span></label>
                                    <input type="text" id="vendor_name" name="vendor_name" list="vendor-list" maxlength="255" class="form-control @error('vendor_name') is-invalid @enderror" value="{{ old('vendor_name', $record->vendor?->name) }}">
                                    <datalist id="vendor-list">
                                        @foreach ($vendorNames as $name)
                                            <option value="{{ $name }}">
                                        @endforeach
                                    </datalist>
                                    <div class="form-text">{{ __('admin/procurement.vendor_hint') }}</div>
                                    @error('vendor_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="vendor_tax_id">{{ __('admin/procurement.field_tax') }}</label>
                                    <input type="text" id="vendor_tax_id" name="vendor_tax_id" maxlength="50" class="form-control @error('vendor_tax_id') is-invalid @enderror" value="{{ old('vendor_tax_id', $record->vendor?->tax_id) }}">
                                    @error('vendor_tax_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            @if ($show('agency'))
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="agency_id">{{ __('admin/procurement.field_agency') }}</label>
                                    <select id="agency_id" name="agency_id" class="form-select @error('agency_id') is-invalid @enderror">
                                        <option value="">{{ __('admin/procurement.not_set') }}</option>
                                        @foreach ($agencies as $agency)
                                            <option value="{{ $agency->id }}" @selected((int) old('agency_id', $record->agency_id) === $agency->id)>{{ $agency->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('agency_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endif
                            @if ($show('work_type'))
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="work_type_id">{{ __('admin/procurement.field_work_type') }}</label>
                                    <select id="work_type_id" name="work_type_id" class="form-select @error('work_type_id') is-invalid @enderror">
                                        <option value="">{{ __('admin/procurement.not_set') }}</option>
                                        @foreach ($workTypes as $workType)
                                            <option value="{{ $workType->id }}" @selected((int) old('work_type_id', $record->work_type_id) === $workType->id)>{{ $workType->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('work_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endif
                            @if ($show('method'))
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="method_id">{{ __('admin/procurement.field_method') }}</label>
                                    <select id="method_id" name="method_id" class="form-select @error('method_id') is-invalid @enderror">
                                        <option value="">{{ __('admin/procurement.not_set') }}</option>
                                        @foreach ($methods as $method)
                                            <option value="{{ $method->id }}" @selected((int) old('method_id', $record->method_id) === $method->id)>{{ $method->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('method_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endif
                            @if ($show('amount'))
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="amount">{{ __('admin/procurement.field_amount') }}</label>
                                    <input type="text" inputmode="decimal" id="amount" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $record->amount !== null ? number_format((float) $record->amount, 2, '.', '') : '') }}">
                                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endif
                            @if ($show('date'))
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="published_on">{{ __('admin/procurement.field_date') }} <span class="text-danger">*</span></label>
                                    <input type="date" id="published_on" name="published_on" class="form-control @error('published_on') is-invalid @enderror" value="{{ old('published_on', $record->published_on?->format('Y-m-d')) }}">
                                    @error('published_on')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endif
                            @if ($show('reference'))
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="reference_no">{{ __('admin/procurement.field_reference') }}</label>
                                    <input type="text" id="reference_no" name="reference_no" maxlength="255" class="form-control @error('reference_no') is-invalid @enderror" value="{{ old('reference_no', $record->reference_no) }}">
                                    @error('reference_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endif
                            @if ($show('reason'))
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="reason">{{ __('admin/procurement.field_reason') }}</label>
                                    <input type="text" id="reason" name="reason" maxlength="255" class="form-control @error('reason') is-invalid @enderror" value="{{ old('reason', $record->reason) }}">
                                    @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            {{-- Right: status, year and files --}}
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="status">{{ __('admin/procurement.col_status') }}</label>
                            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="published" @selected(old('status', $record->status) === 'published')>{{ __('admin/procurement.status_published') }}</option>
                                <option value="draft" @selected(old('status', $record->status) === 'draft')>{{ __('admin/procurement.status_draft') }}</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="fiscal_year">{{ __('admin/procurement.field_year') }} <span class="text-danger">*</span></label>
                            <input type="number" id="fiscal_year" name="fiscal_year" min="2000" max="2200" class="form-control @error('fiscal_year') is-invalid @enderror" value="{{ old('fiscal_year', $record->fiscal_year) }}">
                            <div class="form-text">{{ __('admin/procurement.year_hint') }}</div>
                            @error('fiscal_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">{{ __('admin/procurement.section_files') }}</div>
                    <div class="card-body">
                        @foreach ($kinds as $kind)
                            @php $attachment = $record->attachments->firstWhere('kind', $kind); @endphp
                            <div class="{{ $loop->last ? '' : 'mb-4' }}">
                                <label class="form-label fw-semibold" for="file-{{ $kind }}">{{ __('admin/procurement.kind_'.$kind) }}</label>
                                @if ($attachment)
                                    <div class="small mb-2">
                                        {{ __('admin/procurement.current_file') }}
                                        <a href="{{ $attachment->url }}" target="_blank" rel="noopener">{{ __('admin/procurement.open') }} <i class="bi bi-box-arrow-up-right"></i></a>
                                        <div class="form-check mt-1">
                                            <input class="form-check-input" type="checkbox" name="remove[{{ $kind }}]" value="1" id="remove-{{ $kind }}">
                                            <label class="form-check-label" for="remove-{{ $kind }}">{{ __('admin/procurement.remove_file') }}</label>
                                        </div>
                                    </div>
                                @endif
                                @if ($kind !== 'link')
                                    <input type="file" id="file-{{ $kind }}" name="files[{{ $kind }}]" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip" class="form-control form-control-sm mb-2 @error("files.$kind") is-invalid @enderror">
                                    @error("files.$kind")<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <div class="form-text mb-2">{{ __('admin/procurement.file_hint') }}</div>
                                @endif
                                <input type="url" name="urls[{{ $kind }}]" maxlength="1000" placeholder="{{ $kind === 'link' ? 'https://' : __('admin/procurement.or_link') }}" class="form-control form-control-sm @error("urls.$kind") is-invalid @enderror" value="{{ old("urls.$kind", $attachment?->external_url) }}">
                                @error("urls.$kind")<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> {{ __('admin/common.save') }}</button>
            <a href="{{ route("admin.procurement.{$type}.index") }}" class="btn btn-outline-secondary">{{ __('admin/common.back') }}</a>
        </div>
    </form>
@endsection