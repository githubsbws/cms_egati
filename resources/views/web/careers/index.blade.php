@extends('web.layouts.mainlayout')

@php
    $statusUrl = fn (string $status) => web_url('careers').'?'.http_build_query(array_filter([
        'q' => $filters['q'],
        'category' => $filters['category'],
        'status' => $status === 'all' ? null : $status,
    ])).'#cr-opportunities';
    $faq = [
        ['careers.how_do_i_submit_an_application', 'careers.the_egati_recruitment_page_directs_applicants'],
        ['careers.which_documents_do_i_need', 'careers.open_the_position_page_to_find'],
        ['careers.where_can_i_find_candidate_lists', 'careers.use_the_candidate_announcements_or_selection'],
    ];
    $steps = [
        ['file', 'careers.find_your_role', 'careers.read_the_announcement_responsibilities_qualifications_and'],
        ['check', 'careers.prepare_your_documents', 'careers.download_the_role_specific_forms_and'],
        ['mail', 'careers.send_your_application', 'careers.email_your_application_and_documents_to'],
    ];
    $pillars = [['people', 'careers.pillar_1'], ['growth', 'careers.pillar_2'], ['briefcase', 'careers.pillar_3']];
@endphp

@section('title', __web('nav.careers').' | '.__web('site.title'))
@section('body_class', 'about-overview-color')

@section('content')
    <div class="cr-page">
        {{-- Hero --}}
        <section class="cr-hero">
            <div class="cr-crumb"><a href="{{ web_url('') }}">{{ __web('careers.home') }}</a><span>/</span><a href="{{ web_url('careers') }}">{{ __web('careers.careers') }}</a></div>
            <div class="cr-hero-grid">
                <div class="cr-hero-copy">
                    <span class="cr-kicker"><i></i> CAREERS AT EGATi</span>
                    <h1>{!! __web('careers.your_potential_our_energy_a_future') !!}</h1>
                    <p>{!! __web('careers.be_part_of_egati_bring_your') !!}</p>
                    <div class="cr-hero-actions">
                        <a class="cr-primary" href="#cr-opportunities">{{ __web('careers.explore_opportunities') }}@include('web.careers.icon', ['name' => 'arrow'])</a>
                        <a class="cr-text-button" href="#cr-process">{{ __web('careers.how_to_apply') }} ↓</a>
                    </div>
                </div>
                <div class="cr-hero-visual">
                    <div class="cr-hero-photo">
                        <img src="{{ web_media('careers.hero', 'news/10317-0.webp') }}" alt="{{ __web('careers.egati_team_participating_in_business_knowledge') }}">
                        <div class="cr-photo-label"><span>PEOPLE. PURPOSE. POSSIBILITIES.</span><a href="{{ web_url('news') }}">{{ __web('careers.learning_at_egati') }} ↗</a></div>
                    </div>
                    <div class="cr-opportunity-badge">
                        <span>@include('web.careers.icon', ['name' => 'briefcase'])</span>
                        <div><strong>{{ str_pad((string) $openCount, 2, '0', STR_PAD_LEFT) }}</strong><p>{{ __web('careers.open_positions') }}</p></div>
                        <b aria-hidden="true">↗</b>
                    </div>
                    <svg class="cr-hero-spark" viewBox="0 0 80 80" aria-hidden="true"><path d="M40 4v72M4 40h72M15 15l50 50m0-50L15 65" fill="none" stroke="currentColor" stroke-width="3"/></svg>
                    <span class="cr-hero-word" aria-hidden="true">TOGETHER</span>
                </div>
            </div>
        </section>

        <div class="cr-pillars">
            @foreach ($pillars as [$icon, $key])
                <div><span>0{{ $loop->iteration }}</span>@include('web.careers.icon', ['name' => $icon])<strong>{{ __web($key) }}</strong></div>
            @endforeach
        </div>

        {{-- Positions --}}
        <section class="cr-opportunities" id="cr-opportunities">
            <div class="cr-section-head">
                <div>
                    <span class="cr-kicker">FIND YOUR PLACE</span>
                    <h2>{!! __web('careers.find_the_role_that_fits_you') !!}</h2>
                </div>
                <p>{!! __web('careers.explore_roles_and_follow_selection_announcements') !!}</p>
            </div>

            <form class="cr-search" id="cr-search" role="search" method="get" action="{{ web_url('careers') }}#cr-opportunities">
                @if ($filters['status'] !== 'all')
                    <input type="hidden" name="status" value="{{ $filters['status'] }}">
                @endif
                <div>
                    <label for="cr-query">{{ __web('careers.search_opportunities') }}</label>
                    <div class="cr-input-wrap">
                        <span aria-hidden="true">⌕</span>
                        <input type="search" id="cr-query" name="q" value="{{ $filters['q'] }}" maxlength="100" placeholder="{{ __web('careers.job_title_or_department') }}" autocomplete="off">
                    </div>
                </div>
                <div>
                    <label for="cr-department">{{ __web('careers.job_family') }}</label>
                    <select id="cr-department" name="category" data-autosubmit>
                        <option value="">{{ __web('careers.all_job_families') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) $filters['category'] === $category->id)>{{ $category->labelFor($locale) }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div class="cr-status-filters" role="group" aria-label="{{ __web('careers.announcement_status') }}">
                <a href="{{ $statusUrl('all') }}" aria-pressed="{{ $filters['status'] === 'all' ? 'true' : 'false' }}">{{ __web('careers.status_all') }}<span>{{ $total }}</span></a>
                @foreach ($statuses as $status)
                    <a href="{{ $statusUrl($status) }}" aria-pressed="{{ $filters['status'] === $status ? 'true' : 'false' }}">{{ __web("careers.status_{$status}") }}<span>{{ $counts[$status] ?? 0 }}</span></a>
                @endforeach
            </div>

            <div class="cr-results-toolbar">
                <p id="cr-result-count" role="status" aria-live="polite">{{ __web('careers.positions_found', null, ['count' => $jobs->count()]) }}</p>
                <a id="cr-reset" href="{{ web_url('careers') }}#cr-opportunities">{{ __web('careers.reset_filters') }} ↻</a>
            </div>

            <div class="cr-jobs" id="cr-jobs">
                @forelse ($jobs as $job)
                    @include('web.careers.job-card', ['job' => $job])
                @empty
                    <div class="cr-empty">
                        <span aria-hidden="true">⌕</span>
                        <h3>{{ __web('careers.no_matching_positions') }}</h3>
                        <p>{{ __web('careers.try_another_search_term_job_family') }}</p>
                    </div>
                @endforelse
            </div>

            <div class="cr-source-note">
                <span>{{ __web('careers.source_reviewed') }}{{ $reviewed ? \App\Helpers\WebDate::plain($reviewed) : '' }} · {{ __web('careers.status_follows_the_application_dates_and') }}</span>
                <a href="{{ config('careers.source_url') }}" target="_blank" rel="noopener">{{ __web('careers.original_recruitment_website') }} ↗</a>
            </div>
        </section>

        {{-- How to apply --}}
        <section class="cr-process" id="cr-process">
            <div class="cr-section-head">
                <div>
                    <span class="cr-kicker">YOUR NEXT STEP</span>
                    <h2>{!! __web('careers.your_next_chapter_starts_here') !!}</h2>
                </div>
                <p>{!! __web('careers.three_steps_to_prepare_your_application') !!}</p>
            </div>
            <div class="cr-steps">
                @foreach ($steps as [$icon, $title, $text])
                    <article>
                        <span class="cr-step-number">0{{ $loop->iteration }}</span>
                        <div class="cr-step-icon">@include('web.careers.icon', ['name' => $icon])</div>
                        <h3>{{ __web($title) }}</h3>
                        <p>{{ __web($text) }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="cr-learning">
            <div class="cr-learning-image"><img src="{{ web_media('careers.learning', 'news/10317-1.webp') }}" alt="{{ __web('careers.egati_learning_activity') }}" loading="lazy"></div>
            <div class="cr-learning-copy">
                <span class="cr-kicker">GROW WITH PURPOSE</span>
                <h2>{!! __web('careers.keep_learning_keep_moving_forward') !!}</h2>
                <p>{{ __web('careers.egati_values_people_development_knowledge_sharing') }}</p>
            </div>
        </section>

        <section class="cr-faq">
            <div>
                <span class="cr-kicker">GOOD TO KNOW</span>
                <h2>{!! __web('careers.before_you_apply') !!}</h2>
            </div>
            <div>
                @foreach ($faq as [$question, $answer])
                    <details><summary>{{ __web($question) }}<span aria-hidden="true">＋</span></summary><p>{{ __web($answer) }}</p></details>
                @endforeach
            </div>
        </section>

        @include('web.careers.contact')
    </div>
@endsection
