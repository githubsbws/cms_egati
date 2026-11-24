@extends('web.layouts.mainlayout')

@php
    $locale = app()->getLocale();
    $icons = ['plan' => 'plan', 'price' => 'price', 'notice' => 'notice', 'winner_in' => 'award', 'winner_out' => 'users'];
    $year = fn ($y) => $locale === 'th' ? $y + 543 : $y;
    $categoryName = fn (string $c) => __web($c === 'all' ? 'proc.cat_all_name' : "proc.cat_{$c}_name");
    $cat = fn (string $c) => \Illuminate\Support\Str::camel($c);
    // Categories are plain links (a form button would lose to the hidden category field of the search form)
    $categoryUrl = fn (string $c) => web_url('procurement').'?'.http_build_query(array_merge(\Illuminate\Support\Arr::except(request()->query(), ['category', 'page']), ['category' => $c])).'#proc-results-top';
    $current = $filters['category'];
    $activeFilters = array_filter([
        $filters['q'] !== '' ? __web('proc.search_2').$filters['q'] : null,
        $filters['year'] ? __web('proc.year_2').$year($filters['year']) : null,
        $filters['method'] ? $methods->firstWhere('id', $filters['method'])?->labelFor($locale) : null,
        $filters['files'] ? __web('proc.with_attachments') : null,
    ]);
@endphp

@section('title', __web('proc.procurement').' | '.__web('site.title'))
@section('body_class', 'about-overview-color')

@section('content')
    {{-- Hero --}}
    <section class="procurement-hero">
        <div class="crumb"><a href="{{ web_url('') }}">{{ __web('proc.home') }}</a><span>/</span>{{ __web('proc.procurement') }}</div>
        <div class="procurement-hero-grid">
            <div>
                <div class="eyebrow">EGATi / PROCUREMENT CENTER</div>
                <h1>{{ __web('proc.procurement') }}<span>{{ __web('proc.clear_information_open_access') }}</span></h1>
                <p>{!! __web('proc.information_for_partners_and_interested') !!}</p>
            </div>
        </div>
    </section>

    <section class="procurement-workspace">
        {{-- One GET form carries the search, the filters and the clicked category / page --}}
        <form id="proc-search-form" method="get" action="{{ web_url('procurement') }}#proc-results-top"></form>

        {{-- Categories --}}
        <div class="proc-category-heading">
            <div>
                <div class="eyebrow">EXPLORE BY CATEGORY</div>
                <h2>{{ __web('proc.find_the_information_you_need') }}</h2>
            </div>
            <a href="{{ $categoryUrl('all') }}" class="proc-all-button {{ $current === 'all' ? 'selected' : '' }}" @if ($current === 'all') aria-current="true" @endif>{{ __web('proc.view_all_categories') }} <span>↗</span></a>
        </div>
        <div class="proc-category-grid">
            @foreach ($categories as $category)
                <a href="{{ $categoryUrl($category) }}" data-proc-cat="{{ $cat($category) }}" class="proc-category-tile {{ $current === $category ? 'selected' : '' }}" @if ($current === $category) aria-current="true" @endif>
                    <div class="proc-tile-top">
                        <span class="proc-tile-icon">@include('web.procurement.icon', ['name' => $icons[$category]])</span>
                        <span class="proc-category-count">{{ number_format($counts[$category] ?? 0) }}</span>
                    </div>
                    <strong>{{ __web("proc.cat_{$category}_short") }}</strong>
                    <span class="proc-tile-sub">{{ __web("proc.cat_{$category}_sub") }}</span>
                    <span class="proc-tile-arrow" aria-hidden="true">↗</span>
                </a>
            @endforeach
        </div>

        {{-- Search and filters --}}
        <div class="proc-search-panel">
            <div class="proc-search-heading">
                <h2>{{ __web('proc.search_announcements') }}</h2>
                <p>{{ __web('proc.search_by_project_title_supplier') }}</p>
            </div>
            <input type="hidden" name="category" value="{{ $current }}" form="proc-search-form">
            <div class="proc-query-row">
                <label class="proc-query-box" for="proc-query">
                    @include('web.procurement.icon', ['name' => 'search'])
                    <input id="proc-query" type="search" name="q" form="proc-search-form" value="{{ $filters['q'] }}" maxlength="100" aria-label="{{ __web('proc.announcement_search_keywords') }}" placeholder="{{ __web('proc.project_title_supplier_name_or') }}" autocomplete="off">
                </label>
                <button type="submit" form="proc-search-form" class="proc-search-button">{{ __web('proc.search') }} @include('web.procurement.icon', ['name' => 'arrow'])</button>
            </div>
            <div class="proc-filter-row">
                <label>{{ __web('proc.year') }}
                    <select name="year" form="proc-search-form" data-autosubmit>
                        <option value="">{{ __web('proc.all_years') }}</option>
                        @foreach ($years as $y)
                            <option value="{{ $y }}" @selected((int) $filters['year'] === (int) $y)>{{ $year($y) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>{{ __web('proc.procurement_method') }}
                    <select name="method" form="proc-search-form" data-autosubmit>
                        <option value="">{{ __web('proc.all_methods') }}</option>
                        @foreach ($methods as $method)
                            <option value="{{ $method->id }}" @selected((int) $filters['method'] === $method->id)>{{ $method->labelFor($locale) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>{{ __web('proc.sort_by') }}
                    <select name="sort" form="proc-search-form" data-autosubmit>
                        @foreach (['new' => 'proc.newest_announcement', 'old' => 'proc.oldest_announcement', 'title' => 'proc.project_title', 'high' => 'proc.amount_high_to_low', 'low' => 'proc.amount_low_to_high'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ __web($label) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="proc-files-filter"><input type="checkbox" name="files" value="1" form="proc-search-form" data-autosubmit @checked($filters['files'])>{{ __web('proc.with_attachments_only') }}</label>
            </div>
        </div>
        {{-- Results --}}
        <form id="proc-clear-form" method="get" action="{{ web_url('procurement') }}"></form>
        <div class="proc-results-heading" id="proc-results-top">
            <div>
                <h2>{{ $categoryName($current) }}</h2>
                <p id="proc-results-count" aria-live="polite">{{ __web('proc.results_count', null, ['total' => number_format($records->total()), 'all' => number_format($total)]) }}</p>
            </div>
            <div class="proc-result-actions">
                <button type="submit" form="proc-clear-form">{{ __web('proc.clear_filters') }}</button>
                <label>{{ __web('proc.show') }}
                    <select name="size" form="proc-search-form" data-autosubmit aria-label="{{ __web('proc.items_per_page') }}">
                        @foreach ([10, 20, 50] as $size)
                            <option value="{{ $size }}" @selected($filters['size'] === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </div>

        <div id="proc-active-filters">
            @foreach ($activeFilters as $chip)
                <span class="proc-active-chip">{{ $chip }}</span>
            @endforeach
        </div>

        <div id="proc-results">
            @forelse ($records as $record)
                @php
                    $type = $record->type;
                    $amountValue = $record->amount !== null ? (float) $record->amount : null;
                    $amountText = $amountValue === null ? null : number_format($amountValue, floor($amountValue) == $amountValue ? 0 : 2);
                    $hasChange = $type === 'plan' && $record->attachments->contains('kind', 'link');
                @endphp
                <article class="proc-announcement" data-proc-category="{{ $cat($type) }}">
                    <div class="proc-record-icon">@include('web.procurement.icon', ['name' => $icons[$type]])</div>
                    <div class="proc-record-main">
                        <div class="proc-record-tags">
                            <span class="proc-type-tag proc-type-{{ $cat($type) }}">{{ __web("proc.cat_{$type}_short") }}</span>
                            @if ($record->method)
                                <span class="proc-method-tag">{{ $record->method->labelFor($locale) }}</span>
                            @endif
                            @if ($hasChange)
                                <span class="proc-change-tag">{{ __web('proc.change_cancellation_document') }}</span>
                            @endif
                        </div>
                        <h3 lang="th"><button type="button" data-proc-detail="proc-detail-{{ $record->id }}">{{ $record->title }}</button></h3>
                        @if ($record->vendor)
                            <p class="proc-vendor"><span>{{ __web('proc.supplier') }}</span><span lang="th">{{ $record->vendor->name }}</span></p>
                        @endif
                        <div class="proc-record-meta">
                            <span>{{ __web('proc.year_3') }} {{ $year($record->fiscal_year) }}</span>
                            <span>{{ $record->published_on ? \App\Helpers\WebDate::plain($record->published_on) : __web('proc.publication_date_not_specified') }}</span>
                            @if ($record->reference_no)
                                <span>{{ __web('proc.ref') }} {{ $record->reference_no }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="proc-record-actions">
                        @if ($amountText)
                            <div class="proc-amount">
                                <span>{{ $type === 'plan' ? __web('proc.budget') : __web('proc.purchase_contract_amount') }}</span>
                                <strong>{{ $amountText }} <small>{{ __web('proc.thb') }}</small></strong>
                            </div>
                        @endif
                        <button type="button" class="proc-detail-button" data-proc-detail="proc-detail-{{ $record->id }}">{{ __web('proc.view_details') }} @include('web.procurement.icon', ['name' => 'arrow'])</button>
                        <span class="proc-attachment-count">@include('web.procurement.icon', ['name' => 'file']){{ $record->attachments->isEmpty() ? __web('proc.no_attachment_listed') : __web('proc.attachment_count', null, ['count' => $record->attachments->count()]) }}</span>
                    </div>
                    @include('web.procurement.detail', ['record' => $record, 'amountText' => $amountText ? $amountText.' '.__web('proc.thb') : null, 'year' => $year])
                </article>
            @empty
                <div class="proc-empty">
                    @include('web.procurement.icon', ['name' => 'search'])
                    <h3>{{ __web('proc.no_matching_announcements') }}</h3>
                    <p>{{ __web('proc.try_fewer_keywords_a_different') }}</p>
                    <button type="submit" form="proc-clear-form" class="btn">{{ __web('proc.clear_all_filters') }}</button>
                </div>
            @endforelse
        </div>

        <nav id="proc-pagination" class="proc-pagination" aria-label="{{ __web('proc.announcement_pages') }}">
            @if ($records->total() > 0)
                @php
                    $last = $records->lastPage();
                    $cur = $records->currentPage();
                    $pages = collect([1, $last, $cur - 1, $cur, $cur + 1])->filter(fn ($p) => $p >= 1 && $p <= $last)->unique()->sort()->values();
                @endphp
                <span class="proc-page-summary">{{ __web('proc.page_summary', null, ['from' => $records->firstItem(), 'to' => $records->lastItem(), 'total' => number_format($records->total())]) }}</span>
                <div class="proc-page-buttons">
                    <button type="submit" form="proc-search-form" name="page" value="{{ max(1, $cur - 1) }}" @disabled($cur === 1) aria-label="{{ __web('proc.previous_page') }}">←</button>
                    @foreach ($pages as $i => $p)
                        @if ($i > 0 && $p - $pages[$i - 1] > 1)
                            <span class="proc-page-ellipsis">…</span>
                        @endif
                        <button type="submit" form="proc-search-form" name="page" value="{{ $p }}" @if ($p === $cur) aria-current="page" @endif aria-label="{{ __web('proc.page') }} {{ $p }}">{{ $p }}</button>
                    @endforeach
                    <button type="submit" form="proc-search-form" name="page" value="{{ min($last, $cur + 1) }}" @disabled($cur === $last) aria-label="{{ __web('proc.next_page') }}">→</button>
                </div>
            @endif
        </nav>

        <div class="proc-data-note">
            @include('web.procurement.icon', ['name' => 'notice'])
            <p>{{ __web('proc.announcement_text_is_retained_in') }}</p>
        </div>

        <div class="proc-contact-band">
            <div>
                <div class="eyebrow">WE&rsquo;RE HERE TO HELP</div>
                <h2>{{ __web('proc.need_procurement_assistance') }}</h2>
                <p>{!! __web('proc.procurement_division_corporate_administration_4th') !!}</p>
            </div>
            <div class="proc-contact-links">
                <span>{{ __web('proc.contact_the_team') }}</span>
                <a href="tel:024366945">02-436-6945 ↗</a>
                <a href="mailto:egati-procure@egati.co.th">egati-procure@egati.co.th ↗</a>
                <small>{{ __web('proc.website_enquiries') }}<br><a href="mailto:webmaster@egati.co.th">webmaster@egati.co.th</a></small>
            </div>
        </div>
    </section>
@endsection
