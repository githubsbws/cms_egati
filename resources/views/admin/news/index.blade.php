@extends('admin.layouts.mainlayout')

@section('title', __('admin/menuleft.news_sub.news'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('admin/menuleft.home') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('admin/menuleft.news_sub.news') }}</li>
@endsection

@section('content')
    @include('admin.layouts.partials.alerts')

    <div class="card">
        <div class="card-body">
            {{-- Toolbar: add + filters --}}
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>
                    {{ __('admin/news.add') }}
                </a>
                <form method="GET" action="{{ route('admin.news.index') }}" class="d-flex flex-wrap gap-2">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" style="width: 240px;"
                           placeholder="{{ __('admin/news.search_placeholder') }}">
                    <select name="category" class="form-select" style="width: 200px;">
                        <option value="">{{ __('admin/news.all_categories') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) request('category') === $category->id)>{{ $category->nameFor($defaultLocale) }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="form-select" style="width: 160px;">
                        <option value="">{{ __('admin/news.all_status') }}</option>
                        <option value="published" @selected(request('status') === 'published')>{{ __('admin/news.status_published') }}</option>
                        <option value="draft" @selected(request('status') === 'draft')>{{ __('admin/news.status_draft') }}</option>
                    </select>
                    <button type="submit" class="btn btn-outline-primary"><i class="bi bi-search"></i> {{ __('admin/common.search') }}</button>
                    @if (request()->hasAny(['q', 'category', 'status']))
                        <a href="{{ route('admin.news.index') }}" class="btn btn-outline-secondary">{{ __('admin/common.reset') }}</a>
                    @endif
                </form>
            </div>
            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 70px;">{{ __('admin/common.no') }}</th>
                            <th style="width: 110px;">{{ __('admin/news.cover') }}</th>
                            <th>{{ __('admin/news.col_title') }}</th>
                            <th style="width: 15%;">{{ __('admin/news.col_category') }}</th>
                            <th style="width: 120px;">{{ __('admin/news.col_status') }}</th>
                            <th style="width: 15%;">{{ __('admin/news.col_published') }}</th>
                            <th style="width: 150px;">{{ __('admin/common.manage') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($posts as $post)
                            @php
                                $translation = $post->translations->firstWhere('locale', $defaultLocale) ?? $post->translations->first();
                            @endphp
                            <tr>
                                <td>{{ $posts->firstItem() + $loop->index }}</td>
                                <td><img src="{{ $post->cover_url }}" alt="" class="img-thumbnail" style="width: 90px; height: 60px; object-fit: cover;"></td>
                                <td>{{ $translation?->title ?? '-' }}</td>
                                <td>{{ $post->category?->nameFor($defaultLocale) ?? __('admin/news.no_category') }}</td>
                                <td>
                                    @if ($post->status === 'published')
                                        <span class="badge text-bg-success">{{ __('admin/news.status_published') }}</span>
                                    @else
                                        <span class="badge text-bg-secondary">{{ __('admin/news.status_draft') }}</span>
                                    @endif
                                </td>
                                <td>{{ $post->published_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        @if ($post->status === 'published' && $translation)
                                            <a href="{{ web_url('news/'.$translation->slug, $translation->locale) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="{{ __('admin/news.view_on_site') }}"><i class="bi bi-box-arrow-up-right"></i></a>
                                        @endif
                                        <a href="{{ route('admin.news.edit', $post) }}" class="btn btn-sm btn-warning" title="{{ __('admin/common.edit') }}"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" action="{{ route('admin.news.destroy', $post) }}" onsubmit="return confirm(@js(__('admin/news.confirm_delete')));">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="{{ __('admin/common.delete') }}"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">{{ __('admin/common.no_data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-end">
                {{ $posts->links() }}
            </div>
        </div>
    </div>
@endsection