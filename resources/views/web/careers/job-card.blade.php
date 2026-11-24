@php
    $translation = $job->translationFor($locale, $defaultLocale);
    $status = $job->current_status ?? $job->status();
@endphp
<a class="cr-job-card" href="{{ web_url('careers/'.$job->slug) }}">
    <div class="cr-card-top">
        <span class="cr-job-category">{{ $job->category?->labelFor($locale) }}</span>
        <span class="cr-status cr-status--{{ $status }}"><i></i>{{ __web("careers.status_{$status}") }}</span>
    </div>
    <h3>{{ $translation?->title }}</h3>
    <p>{{ __web('careers.department') }} {{ $translation?->department }}</p>
    <div class="cr-job-meta">
        <span>@include('web.careers.icon', ['name' => 'people']){{ $job->vacancies }} {{ __web('careers.vacancy') }}</span>
        <span>@include('web.careers.icon', ['name' => 'calendar']){{ __web('careers.closes') }}{{ \App\Helpers\WebDate::plain($job->deadline_on) }}</span>
    </div>
    <div class="cr-card-bottom"><span>{{ __web('careers.view_details_documents') }}</span><b>@include('web.careers.icon', ['name' => 'arrow'])</b></div>
</a>
