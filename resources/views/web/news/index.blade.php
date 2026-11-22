@extends('web.layouts.mainlayout')

@section('title', __web('news.newsroom').' | '.__web('site.title'))
@section('body_class', 'about-overview-color')

@section('content')
    @php
        $locale = app()->getLocale();
        $lead = $featured->first();
    @endphp

    <div class="nr-page">
        {{-- Masthead --}}
        <section class="nr-masthead ac-page-header">
            @include('web.news.crumb')
            <div class="nr-masthead-row">
                <div>
                    <span class="nr-kicker"><i></i> EGATi NEWSROOM</span>
                    <h1>{!! __web('news.stories_that_connect_us') !!}</h1>
                    <p>{{ __web('news.explore_the_latest_updates_partnerships') }}</p>
                </div>
                <div class="nr-orbit" aria-hidden="true"><i></i><b>i</b><span>NEWS / PEOPLE / ENERGY</span></div>
            </div>
            @include('web.news.tabs', ['video' => false])
        </section>

        {{-- Featured: latest story + 4 side cards --}}
        <section class="nr-featured" aria-label="{{ __web('news.featured_news') }}">
            @if ($lead)
                @php $leadTranslation = $lead->translation($locale); @endphp
                <a class="nr-lead" href="{{ web_url('news/'.$leadTranslation->slug) }}" style="--nr-accent:{{ $lead->category?->accent_color ?? '#0756a0' }}">
                    <div class="nr-lead-photo">
                        <img src="{{ $lead->cover_url }}" alt="">
                        <span class="nr-feature-badge"><i></i>{{ __web('news.in_the_spotlight') }}</span>
                    </div>
                    <div class="nr-lead-copy">
                        <h2>{{ $leadTranslation->title }}</h2>
                        <p>{{ \Illuminate\Support\Str::limit($leadTranslation->excerpt, 260) }}</p>
                        <div class="nr-lead-meta">
                            <span class="nr-category">{{ $lead->category?->nameFor($locale) }}</span>
                            <span class="nr-round-arrow" aria-hidden="true">↗</span>
                            @include('web.partials.date-chip', ['date' => $lead->published_at, 'class' => 'nr-date-chip'])
                        </div>
                    </div>
                </a>
            @endif
            <div class="nr-feature-side">
                @foreach ($featured->slice(1) as $post)
                    @include('web.news.card', ['post' => $post, 'style' => 'nr-card--side'])
                @endforeach
            </div>
        </section>
        {{-- Archive: search, category + year filters, results --}}
        <section class="nr-archive" id="nr-archive">
            <div class="nr-section-title">
                <div>
                    <span class="nr-kicker">EXPLORE THE NEWS</span>
                    <h2>{!! __web('news.all_our_updates') !!}</h2>
                </div>
                <p>{!! __web('news.news_and_activities_from_egati') !!}</p>
            </div>

            {{-- One GET form; the filter buttons and year select below point to it with form="nr-search" --}}
            <form class="nr-search" id="nr-search" role="search" method="get" action="{{ web_url('news') }}#nr-archive">
                <label for="nr-query">{{ __web('news.search_news') }}</label>
                <div>
                    <span aria-hidden="true">⌕</span>
                    <input type="search" id="nr-query" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="{{ __web('news.search_headlines_or_article_text') }}" autocomplete="off">
                    <button type="submit">{{ __web('news.search') }} <span aria-hidden="true">→</span></button>
                </div>
            </form>

            <div class="nr-filterbar">
                <div class="nr-filters" role="group" aria-label="{{ __web('news.filter_by_category') }}">
                    <button type="submit" form="nr-search" name="category" value="" aria-pressed="{{ empty($filters['category']) ? 'true' : 'false' }}">{{ __web('newscat.all_stories') }}</button>
                    @foreach ($categories as $category)
                        <button type="submit" form="nr-search" name="category" value="{{ $category->code }}" aria-pressed="{{ ($filters['category'] ?? null) === $category->code ? 'true' : 'false' }}">{{ $category->nameFor($locale) }}</button>
                    @endforeach
                </div>
                <label class="nr-year-label">{{ __web('news.year') }}
                    <select id="nr-year" name="year" form="nr-search" data-autosubmit aria-label="{{ __web('news.filter_by_year') }}">
                        <option value="">{{ __web('news.all_years') }}</option>
                        @foreach ($years as $year)
                            <option value="{{ $year }}" @selected((int) ($filters['year'] ?? 0) === (int) $year)>{{ $locale === 'th' ? $year + 543 : $year }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="nr-result-line">
                <p id="nr-count" role="status" aria-live="polite">{{ __web('news.result_count', null, ['found' => $total, 'shown' => min($limit, $total)]) }}</p>
                <form method="get" action="{{ web_url('news') }}#nr-archive" style="display:contents">
                    <button type="submit" id="nr-reset">{{ __web('news.reset_filters') }} ↻</button>
                </form>
            </div>

            <div class="nr-grid" id="nr-results">
                @forelse ($posts as $post)
                    @include('web.news.card', ['post' => $post, 'style' => ''])
                @empty
                    <div class="nr-empty">
                        <span aria-hidden="true">⌕</span>
                        <h3>{{ __web('news.no_matching_stories') }}</h3>
                        <p>{{ __web('news.try_another_search_term_or') }}</p>
                    </div>
                @endforelse
            </div>

            @if ($total > $limit)
                <div class="nr-more">
                    <form method="get" action="{{ web_url('news') }}#nr-archive">
                        @foreach (collect($filters)->except('limit')->filter() as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach
                        <input type="hidden" name="limit" value="{{ $limit + $pageSize }}">
                        <button type="submit">{{ __web('news.load_more_stories') }} <span aria-hidden="true">＋</span></button>
                    </form>
                </div>
            @endif

            <p class="nr-source-note">{{ __web('news.news_from_egati_ordered_by') }}</p>
        </section>

        {{-- Link to the corporate video page --}}
        <a class="nr-video-cta" href="{{ web_url('news/videos') }}">
            <div class="nr-cta-play" aria-hidden="true">▶</div>
            <div>
                <span class="nr-kicker">EGATi ON SCREEN</span>
                <h2>{{ __web('news.our_story_in_motion') }}</h2>
                <p>{{ __web('news.watch_the_egati_corporate_presentation') }}</p>
            </div>
            <span class="nr-cta-arrow" aria-hidden="true">↗</span>
        </a>
    </div>
@endsection