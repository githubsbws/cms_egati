@extends('web.layouts.mainlayout')

@php
    $locale = app()->getLocale();
    $isThai = $locale === 'th';
    $shown = fn (int $y) => $isThai ? $y + 543 : $y;
    $otherYear = fn (int $y) => __web('documents.other_calendar_year', null, ['year' => $isThai ? $y : $y + 543]);
    $meta = config('publications.types')[$type];
    $periodLabel = fn ($p) => $p === 'annual' ? __web('documents.full_year') : __web('documents.quarter', null, ['n' => $p]);
    $financeTitle = fn ($p) => $p->period === 'annual'
        ? __web('documents.finance_title_year', null, ['year' => $shown($p->year)])
        : __web('documents.finance_title_quarter', null, ['quarter' => $p->period, 'year' => $shown($p->year)]);
    $pdf = __web('documents.open_pdf');
    $newTab = __web('documents.open_pdf_in_a_new_tab');
    $placeholder = __web("documents.search_{$type}");
    $compact = false;
    $pageUrl = web_url('documents/'.$type);
@endphp

@section('title', __web($meta['title']).' | '.__web('site.title'))
@section('body_class', 'about-overview-color')

@section('content')
    <div class="pub-page {{ $type === 'annual' ? 'pub-annual-page' : '' }}">
        @include('web.documents.header')

        {{-- Intro / summary --}}
        @if ($type === 'annual')
            <section class="pub-annual-intro">
                <div class="pub-section-heading">
                    <div>
                        <span class="pub-eyebrow">EGATi ANNUAL REPORTS</span>
                        <h2>{!! __web('documents.years_of_progress_stories_of_energy') !!}</h2>
                    </div>
                    <p>{!! __web('documents.the_egati_annual_report_collection_our') !!}</p>
                </div>
                @include('web.documents.latest-annual')
            </section>
        @endif

        <section class="{{ $type === 'annual' ? 'pub-report-archive' : 'pub-table-section' }}">
            @if ($type === 'credit')
                <div class="pub-rating-summaries">
                    @foreach ($ratings as $agency => $rating)
                        @continue(! $rating)
                        <div class="pub-rating-summary {{ config('publications.agencies')[$agency] ? '' : 'pub-rating-summary--teal' }}">
                            <div>
                                <span class="pub-eyebrow">{{ config('publications.agencies')[$agency] ? 'INTERNATIONAL RATING' : 'DOMESTIC RATING' }}</span>
                                <h2>{{ $agency }}</h2>
                                <p>{{ __web('documents.latest_record', null, ['year' => $shown($rating->year)]) }}</p>
                            </div>
                            <strong>{{ $rating->rating }}</strong>
                        </div>
                    @endforeach
                </div>
                <p class="pub-rating-note">{{ __web('documents.ratings_follow_the_source_website_s') }}</p>
            @elseif ($type === 'finance' && $latestFinance)
                <div class="pub-finance-summary">
                    <span class="pub-summary-icon">@include('web.documents.icon', ['name' => 'finance'])</span>
                    <div>
                        <span class="pub-eyebrow">{{ __web('documents.latest_published_statement') }}</span>
                        <h2>{{ $financeTitle($latestFinance) }}</h2>
                        <p>{{ __web('documents.available_in_thai_and_english') }}</p>
                    </div>
                    <div class="pub-summary-files">
                        @foreach (['th', 'en'] as $lang)
                            @if ($url = $latestFinance->documentUrl($lang))
                                <a class="pub-file-link" href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $financeTitle($latestFinance).' · '.strtoupper($lang).' · '.$newTab }}">{{ strtoupper($lang) }} <span aria-hidden="true">↗</span></a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($type !== 'annual')
                <div class="pub-section-heading pub-section-heading--table">
                    <div>
                        <span class="pub-eyebrow">DOCUMENT ARCHIVE</span>
                        <h2>{{ $type === 'credit' ? __web('documents.credit_rating_history') : __web('documents.all_financial_statements') }}</h2>
                    </div>
                    <span class="pub-table-format">PDF <i></i> {{ __web('documents.opens_in_a_new_tab') }}</span>
                </div>
            @else
                <div class="pub-section-heading">
                    <div>
                        <span class="pub-eyebrow">THE REPORT COLLECTION</span>
                        <h2>{!! __web('documents.explore_every_chapter') !!}</h2>
                    </div>
                    <span class="pub-archive-total">{{ $total }}<small>{{ __web('documents.reports') }}</small></span>
                </div>
            @endif

            {{-- Filters --}}
            <form class="pub-filters" id="pub-filter-form" role="search" method="get" action="{{ $pageUrl }}">
                <div class="pub-search-field">
                    <label for="pub-query">{{ __web('documents.search_documents') }}</label>
                    <div><span aria-hidden="true">⌕</span><input id="pub-query" type="search" name="q" value="{{ $filters['q'] }}" maxlength="100" placeholder="{{ $placeholder }}" autocomplete="off"></div>
                </div>
                <div class="pub-select-field">
                    <label for="pub-year">{{ __web('documents.year') }}</label>
                    <select id="pub-year" name="year" data-autosubmit>
                        <option value="">{{ __web('documents.all_years') }}</option>
                        @foreach ($years as $y)
                            <option value="{{ $y }}" @selected((int) $filters['year'] === (int) $y)>{{ $shown($y) }}</option>
                        @endforeach
                    </select>
                </div>
                @if ($type === 'credit')
                    <div class="pub-select-field">
                        <label for="pub-kind">{{ __web('documents.rating_agency') }}</label>
                        <select id="pub-kind" name="kind" data-autosubmit>
                            <option value="">{{ __web('documents.all_agencies') }}</option>
                            @foreach (array_keys(config('publications.agencies')) as $agency)
                                <option value="{{ $agency }}" @selected($filters['kind'] === $agency)>{{ $agency }}</option>
                            @endforeach
                        </select>
                    </div>
                @elseif ($type === 'finance')
                    <div class="pub-select-field">
                        <label for="pub-kind">{{ __web('documents.reporting_period') }}</label>
                        <select id="pub-kind" name="kind" data-autosubmit>
                            <option value="">{{ __web('documents.all_periods') }}</option>
                            @foreach (['annual', '1', '2', '3'] as $period)
                                <option value="{{ $period }}" @selected($filters['kind'] === $period)>{{ $periodLabel($period) }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="pub-select-field">
                    <label for="pub-sort">{{ __web('documents.sort_by') }}</label>
                    <select id="pub-sort" name="sort" data-autosubmit>
                        <option value="desc" @selected($filters['sort'] === 'desc')>{{ __web('documents.newest_first') }}</option>
                        <option value="asc" @selected($filters['sort'] === 'asc')>{{ __web('documents.oldest_first') }}</option>
                    </select>
                </div>
            </form>
            <div class="pub-result-toolbar">
                <p id="pub-result-count" role="status" aria-live="polite">
                    {{ __web($type === 'annual' ? 'documents.found_reports' : 'documents.found_records', null, ['count' => $total]) }}@if ($type !== 'annual' && $total > 0) · {{ $items->firstItem() }}–{{ $items->lastItem() }}@endif
                </p>
                <a id="pub-reset" href="{{ $pageUrl }}">{{ __web('documents.reset_filters') }} ↻</a>
            </div>

            @php
                $empty = '<div class="pub-empty"><span aria-hidden="true">⌕</span><h3>'.e(__web('documents.no_matching_documents')).'</h3><p>'.e(__web('documents.try_another_search_term_or_reset')).'</p></div>';
            @endphp

            @if ($type === 'annual')
                <div id="pub-report-grid" class="pub-report-grid">
                    @forelse ($items as $report)
                        @php
                            $reportYear = $shown($report->year);
                            $url = $report->documentUrl();
                            $label = __web('documents.open_annual_short', null, ['year' => $reportYear]);
                        @endphp
                        <article class="pub-report-card" style="--pub-shelf:{{ ['#e5eff8', '#e6f1ef', '#f7eedc', '#eeeaf4'][$loop->index % 4] }}">
                            <a class="pub-report-cover" href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $label }}">
                                <span class="pub-report-year" aria-hidden="true">{{ $report->year }}</span>
                                <div class="pub-report-book"><img src="{{ $report->cover_url }}" alt="{{ __web('documents.annual_cover_alt', null, ['year' => $reportYear]) }}" loading="lazy"></div>
                                <span class="pub-report-hover">{{ __web('documents.read_full_report') }} ↗</span>
                            </a>
                            <div class="pub-report-info">
                                <div>
                                    <span class="pub-eyebrow">ANNUAL REPORT</span>
                                    <h3>{{ __web('documents.annual_title', null, ['year' => $reportYear]) }}</h3>
                                    <span class="pub-report-date">{{ $otherYear($report->year) }} <i></i> PDF</span>
                                </div>
                                <a class="pub-report-open" href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $label }}">↗</a>
                            </div>
                        </article>
                    @empty
                        {!! $empty !!}
                    @endforelse
                </div>
            @else
                <p class="pub-scroll-hint">{{ __web('documents.scroll_horizontally_to_see_every_column') }} ↔</p>
                <div class="pub-table-wrap" tabindex="0" role="region" aria-label="{{ __web($meta['title']) }}">
                    <table class="pub-table {{ $type === 'finance' ? 'pub-table--finance' : '' }}">
                        <caption>{{ __web($meta['title']) }}</caption>
                        <thead>
                            <tr>
                                <th scope="col">{{ __web('documents.year') }}</th>
                                <th scope="col">{{ $type === 'credit' ? __web('documents.rating_agency') : __web('documents.document') }}</th>
                                <th scope="col">{{ $type === 'credit' ? __web('documents.credit_rating') : __web('documents.period') }}</th>
                                @if ($type === 'credit')
                                    <th scope="col">{{ __web('documents.report') }}</th>
                                @else
                                    <th scope="col">{{ __web('documents.thai') }}</th>
                                    <th scope="col">{{ __web('documents.english') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody id="pub-table-body">
                            @forelse ($items as $item)
                                <tr>
                                    <td class="pub-year-cell"><strong>{{ $shown($item->year) }}</strong><small>{{ $otherYear($item->year) }}</small></td>
                                    @if ($type === 'credit')
                                        @php $international = config('publications.agencies')[$item->agency] ?? true; @endphp
                                        <th scope="row">
                                            <div class="pub-agency"><span class="pub-agency-dot {{ $international ? '' : 'is-teal' }}"></span>{{ $item->agency }}</div>
                                            <span class="pub-cell-sub">{{ __web($international ? 'documents.international_rating' : 'documents.domestic_rating') }}</span>
                                        </th>
                                        <td><span class="pub-rating-badge {{ $international ? '' : 'is-teal' }}">{{ $item->rating }}</span></td>
                                        <td>
                                            @if ($url = $item->documentUrl())
                                                <a class="pub-file-link" href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $item->agency.' '.$shown($item->year).' '.__web('documents.open_credit_rating_report_in_a') }}">{{ $pdf }} <span aria-hidden="true">↗</span></a>
                                            @endif
                                        </td>
                                    @else
                                        <th scope="row"><div class="pub-file-title"><span class="pub-pdf-symbol" aria-hidden="true">PDF</span><span>{{ $financeTitle($item) }}</span></div></th>
                                        <td><span class="pub-period-badge {{ $item->period === 'annual' ? 'is-annual' : '' }}">{{ $periodLabel($item->period) }}</span></td>
                                        @foreach (['th', 'en'] as $lang)
                                            <td>
                                                @if ($url = $item->documentUrl($lang))
                                                    <a class="pub-file-link" href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $financeTitle($item).' '.strtoupper($lang).' '.$newTab }}">{{ strtoupper($lang) }} <span aria-hidden="true">↗</span></a>
                                                @endif
                                            </td>
                                        @endforeach
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="{{ $type === 'credit' ? 4 : 5 }}">{!! $empty !!}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($items->hasPages())
                    @php
                        $cur = $items->currentPage();
                        $last = $items->lastPage();
                        $pageLink = fn (int $p) => $items->url($p).'#pub-filter-form';
                    @endphp
                    <div class="pub-pagination" id="pub-pagination" aria-label="{{ __web('documents.document_pagination') }}">
                        @if ($cur > 1)
                            <a href="{{ $pageLink($cur - 1) }}" aria-label="{{ __web('documents.previous_page') }}">←</a>
                        @else
                            <button type="button" disabled aria-label="{{ __web('documents.previous_page') }}">←</button>
                        @endif
                        @foreach (range(1, $last) as $p)
                            <a href="{{ $pageLink($p) }}" @if ($p === $cur) aria-current="page" @endif aria-label="{{ __web('documents.page_label', null, ['n' => $p]) }}">{{ $p }}</a>
                        @endforeach
                        @if ($cur < $last)
                            <a href="{{ $pageLink($cur + 1) }}" aria-label="{{ __web('documents.next_page') }}">→</a>
                        @else
                            <button type="button" disabled aria-label="{{ __web('documents.next_page') }}">→</button>
                        @endif
                    </div>
                @endif
            @endif

            <div class="pub-footnote">
                <span>{{ __web($type === 'annual' ? 'documents.original_covers_and_documents_from_egati' : 'documents.records_and_document_links_as_published') }}</span>
                <a href="{{ config('publications.source_base').$meta['source'] }}/" target="_blank" rel="noopener">{{ __web('documents.view_original_source') }} ↗</a>
            </div>
        </section>

        @include('web.documents.crosslinks')
    </div>
@endsection
