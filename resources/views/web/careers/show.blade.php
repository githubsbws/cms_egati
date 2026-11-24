@extends('web.layouts.mainlayout')

@php
    $translation = $job->translationFor($locale, $defaultLocale);
    $title = (string) $translation?->title;
    $notice = $job->noticeDocument();
    $latest = $job->documents->first();
    $qualifications = $translation?->qualifications ?? [];
    $email = config('careers.hr_email');
    $mailto = 'mailto:'.$email.'?subject='.rawurlencode(__web('careers.application_for').$title);
    $date = fn ($d) => \App\Helpers\WebDate::plain($d);
@endphp

@section('title', $title.' | '.__web('site.title'))
@section('body_class', 'about-overview-color')

@section('content')
    <div class="cr-page cr-detail">
        <section class="cr-detail-header">
            <div class="cr-crumb"><a href="{{ web_url('') }}">{{ __web('careers.home') }}</a><span>/</span><a href="{{ web_url('careers') }}">{{ __web('careers.careers') }}</a><span>/</span><span>{{ __web('careers.position_details') }}</span></div>
            <a class="cr-back" href="{{ web_url('careers') }}">← {{ __web('careers.all_opportunities') }}</a>
            <div class="cr-detail-heading">
                <div>
                    <div class="cr-detail-tags">
                        <span class="cr-job-category">{{ $job->category?->labelFor($locale) }}</span>
                        <span class="cr-status cr-status--{{ $status }}"><i></i>{{ __web("careers.status_{$status}") }}</span>
                    </div>
                    <h1>{{ $title }}</h1>
                    <p>{{ __web('careers.department') }} {{ $translation?->department }}</p>
                </div>
                <span class="cr-detail-symbol">@include('web.careers.icon', ['name' => 'briefcase'])</span>
            </div>
            <div class="cr-detail-facts">
                <span>@include('web.careers.icon', ['name' => 'people']){{ $job->vacancies }} {{ __web('careers.vacancy') }}</span>
                <span>@include('web.careers.icon', ['name' => 'calendar']){{ __web('careers.notice_updated') }}{{ $date($job->updated_on) }}</span>
            </div>
        </section>

        <div class="cr-detail-layout">
            <div class="cr-detail-main">
                <div class="cr-date-banner">
                    <span>@include('web.careers.icon', ['name' => 'calendar'])</span>
                    <div>
                        <small>{{ __web('careers.application_window') }}</small>
                        <strong>{{ $date($job->start_on) }} – {{ $date($job->deadline_on) }}</strong>
                        <p>{{ __web('careers.business_days_08_00_17_00') }}</p>
                    </div>
                </div>

                @if ($qualifications)
                    <section class="cr-detail-section">
                        <span class="cr-kicker">AT A GLANCE</span>
                        <h2>{{ __web('careers.key_qualifications') }}</h2>
                        <ul class="cr-qualification-list">
                            @foreach ($qualifications as $qualification)
                                <li><span>@include('web.careers.icon', ['name' => 'check'])</span>{{ $qualification }}</li>
                            @endforeach
                        </ul>
                        <p class="cr-detail-note">{{ __web('careers.this_is_a_summary_of_selected') }}</p>
                        @if ($notice?->url)
                            <a class="cr-outline-button" href="{{ $notice->url }}" target="_blank" rel="noopener">{{ __web('careers.read_the_full_recruitment_notice') }} ↗</a>
                        @endif
                    </section>
                @else
                    <section class="cr-detail-section">
                        <span class="cr-kicker">POSITION INFORMATION</span>
                        <h2>{{ __web('careers.position_requirements') }}</h2>
                        <p>{{ __web('careers.read_the_full_recruitment_notice_below') }}</p>
                        @if ($notice?->url)
                            <a class="cr-outline-button" href="{{ $notice->url }}" target="_blank" rel="noopener">{{ __web('careers.open_recruitment_notice') }} ↗</a>
                        @endif
                    </section>
                @endif

                <section class="cr-detail-section" id="cr-job-documents">
                    <div class="cr-documents-heading">
                        <div>
                            <span class="cr-kicker">DOCUMENTS &amp; ANNOUNCEMENTS</span>
                            <h2>{{ __web('careers.documents_notices') }}</h2>
                        </div>
                        <span>{{ $job->documents->count() }} {{ __web('careers.files') }}</span>
                    </div>
                    <div class="cr-document-list">
                        @foreach ($job->documents as $document)
                            @continue(! $document->url)
                            <a class="cr-document-link" href="{{ $document->url }}" target="_blank" rel="noopener">
                                <span class="cr-pdf-icon">PDF</span>
                                <div>
                                    <strong>{{ $document->labelFor($locale, $defaultLocale) }}</strong>
                                    <small>PDF{{ $document->size_mb ? ' · '.$document->size_mb.' MB' : '' }}{{ $loop->first && $job->stage !== 'application' ? ' · '.__web('careers.latest_notice') : '' }}</small>
                                </div>
                                <span class="cr-file-arrow" aria-hidden="true">↗</span>
                            </a>
                        @endforeach
                    </div>
                    <p class="cr-detail-note">{{ __web('careers.original_documents_are_in_thai_and') }}</p>
                </section>

                <section class="cr-detail-section">
                    <h2>{{ __web('careers.submitting_your_application') }}</h2>
                    <p>{{ match ($status) {
                        'open' => __web('careers.after_reviewing_the_notice_and_preparing'),
                        'upcoming' => __web('careers.applications_have_not_opened_yet_prepare'),
                        default => __web('careers.the_application_window_for_this_notice'),
                    } }}</p>
                    @if ($status === 'open')
                        <a class="cr-email-line" href="{{ $mailto }}">@include('web.careers.icon', ['name' => 'mail']){{ $email }} ↗</a>
                    @else
                        <a class="cr-outline-button" href="{{ web_url('careers') }}">{{ __web('careers.explore_other_positions') }} →</a>
                    @endif
                </section>
            </div>

            <aside class="cr-apply-card">
                <span class="cr-kicker">YOUR NEXT CHAPTER</span>
                <h2>{!! $status === 'open' ? __web('careers.ready_for_your_next_chapter') : __web('careers.follow_the_latest_position_notices') !!}</h2>
                <span class="cr-status cr-status--{{ $status }}"><i></i>{{ __web("careers.status_{$status}") }}</span>
                <dl>
                    <div><dt>{{ __web('careers.department_2') }}</dt><dd>{{ $translation?->department }}</dd></div>
                    <div><dt>{{ __web('careers.vacancies') }}</dt><dd>{{ $job->vacancies }} {{ __web('careers.position') }}</dd></div>
                    <div><dt>{{ __web('careers.application_deadline') }}</dt><dd>{{ $date($job->deadline_on) }}<small>17:00 · {{ __web('careers.thailand_time') }}</small></dd></div>
                </dl>
                @if ($status === 'open')
                    <a class="cr-primary" href="{{ $mailto }}">{{ __web('careers.prepare_application_email') }}@include('web.careers.icon', ['name' => 'mail'])</a>
                    <p class="cr-apply-hint">{{ __web('careers.opens_your_email_app_attach_the') }}</p>
                @elseif ($latest?->url)
                    <a class="cr-primary" href="{{ $latest->url }}" target="_blank" rel="noopener">{{ __web('careers.open_latest_notice') }} ↗</a>
                @endif
                <a class="cr-text-button" href="#cr-job-documents">{{ __web('careers.all_documents') }} ↓</a>
                <div class="cr-apply-help"><span>{{ __web('careers.contact_human_resources') }}</span><a href="tel:024366944">02-436-6944</a></div>
            </aside>
        </div>

        <div class="cr-detail-source">
            <a href="{{ config('careers.source_url') }}" target="_blank" rel="noopener">{{ __web('careers.original_recruitment_website') }} ↗</a>
            <span>{{ __web('careers.reviewed') }}{{ $date($job->updated_on) }}</span>
        </div>

        @include('web.careers.contact')
    </div>
@endsection
