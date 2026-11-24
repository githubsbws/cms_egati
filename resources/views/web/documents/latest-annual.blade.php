{{-- Latest annual report feature. $compact adds the "all annual reports" link. --}}
@if ($latestAnnual)
    @php
        $isThai = app()->getLocale() === 'th';
        $shownYear = $isThai ? $latestAnnual->buddhist_year : $latestAnnual->year;
        $url = $latestAnnual->documentUrl();
    @endphp
    <div class="pub-annual-feature {{ ($compact ?? false) ? 'pub-annual-feature--compact' : '' }}">
        <div class="pub-feature-copy">
            <span class="pub-eyebrow"><i></i>{{ __web('documents.latest_published_report') }}</span>
            <h2>{!! __web('documents.renewable_power_a_sustainable_future') !!}</h2>
            <p>{{ __web('documents.latest_annual_text', null, ['year' => $shownYear]) }}</p>
            <div class="pub-feature-meta"><span>{{ $latestAnnual->year }}</span><i></i><span>PDF</span><i></i><span>EGAT INTERNATIONAL</span></div>
            <div class="pub-feature-actions">
                @if ($url)
                    <a class="pub-button pub-button--gold" href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ __web('documents.open_annual_aria', null, ['year' => $shownYear]) }}">{{ __web('documents.read_the_report') }} <span aria-hidden="true">↗</span></a>
                @endif
                @if ($compact ?? false)
                    <a href="{{ web_url('documents/annual') }}" class="pub-feature-all">{{ __web('documents.all_annual_reports') }} →</a>
                @endif
            </div>
        </div>
        <a class="pub-feature-book" href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ __web('documents.open_annual_short', null, ['year' => $shownYear]) }}">
            <span class="pub-book-orbit"></span><span class="pub-book-year" aria-hidden="true">{{ $latestAnnual->year }}</span>
            <div class="pub-book"><img src="{{ $latestAnnual->cover_url }}" alt="{{ __web('documents.annual_cover_alt', null, ['year' => $shownYear]) }}"><i></i></div>
            <span class="pub-book-caption">{{ __web('documents.explore_the_full_report') }} ↗</span>
        </a>
    </div>
@endif
