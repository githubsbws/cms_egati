@extends('web.layouts.mainlayout')

@php
    $locale = app()->getLocale();
    $isThai = $locale === 'th';
    $shown = fn (int $y) => $isThai ? $y + 543 : $y;
    $types = config('publications.types');
    $type = null;
    $compact = true;
@endphp

@section('title', __web('documents.publications').' | '.__web('site.title'))
@section('body_class', 'about-overview-color')

@section('content')
    <div class="pub-page">
        @include('web.documents.header')

        <section class="pub-overview">
            <div class="pub-section-heading pub-overview-heading">
                <div>
                    <span class="pub-eyebrow"><i></i>{{ __web('documents.01_document_types') }}</span>
                    <h2>{!! __web('documents.choose_a_publication_category') !!}</h2>
                </div>
                <p>{!! __web('documents.publications_are_organised_into_three_categories') !!}</p>
            </div>
            <div class="pub-category-grid">
                @foreach ($types as $key => $meta)
                    <a class="pub-category-card pub-category-card--{{ $key }}" href="{{ web_url('documents/'.$key) }}" style="--pub-accent:{{ $meta['color'] }}">
                        <div class="pub-category-top"><span class="pub-category-icon">@include('web.documents.icon', ['name' => $key])</span><span>0{{ $loop->iteration }}</span></div>

                        @if ($key === 'credit')
                            <div class="pub-category-visual pub-rating-medals" aria-hidden="true">
                                @foreach ($ratings as $agency => $rating)
                                    @if ($rating)
                                        <span class="pub-rating-medal {{ config('publications.agencies')[$agency] ? 'is-blue' : 'is-gold' }}"><b>{{ $rating->rating }}</b><small>{{ explode(' ', $agency)[0] }} · {{ $shown($rating->year) }}</small></span>
                                    @endif
                                @endforeach
                            </div>
                        @elseif ($key === 'finance')
                            <div class="pub-category-visual pub-quarter-bars" aria-hidden="true">
                                <small>{{ $financeYear ? mb_strtoupper(__web('documents.year')) : '' }} {{ $financeYear ? $shown($financeYear) : '' }}</small>
                                <div>
                                    @foreach (['1' => 'Q1', '2' => 'Q2', '3' => 'Q3', 'annual' => 'FY'] as $period => $label)
                                        <span class="{{ in_array((string) $period, $financeDone, true) ? 'is-ready' : '' }}">{{ $label }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="pub-category-visual pub-report-fan" aria-hidden="true">
                                @foreach ($covers as $report)
                                    <span class="pub-mini-report pub-mini-report--{{ $loop->index }}"><span><b>EGAT<i>i</i></b><small>ANNUAL REPORT</small><strong>{{ $report->year }}</strong></span><img src="{{ $report->cover_url }}" alt="" loading="lazy"></span>
                                @endforeach
                            </div>
                        @endif

                        <div class="pub-category-copy">
                            <span class="pub-eyebrow">{{ $meta['tag'] }}</span>
                            <h2>{{ __web($meta['title']) }}</h2>
                            <p>{{ __web($meta['description']) }}</p>
                            <div class="pub-category-bottom">
                                <span>{{ $counts[$key] ?? 0 }} {{ __web($key === 'finance' ? 'documents.periods' : ($key === 'annual' ? 'documents.reports' : 'documents.records')) }}</span>
                                <b>{{ __web('documents.explore') }} <i aria-hidden="true">→</i></b>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="pub-overview-annual">
            @include('web.documents.latest-annual')
        </section>

        <div class="pub-footnote">
            <span>{{ __web('documents.information_and_documents_from_the_egati') }}</span>
            <a href="{{ config('publications.source_base') }}" target="_blank" rel="noopener">{{ __web('documents.view_source_website') }} ↗</a>
        </div>
    </div>
@endsection
