@extends('web.layouts.mainlayout')
@use('App\Helpers\HomeContent')

@section('title', __web('news.corporate_video').' | '.__web('site.title'))
@section('body_class', 'about-overview-color')

{{-- Corporate video: the same video as the home page presentation (managed in the back office, menu id 2) --}}
@section('content')
    <div class="nr-page nr-video-page">
        <section class="nr-cinema ac-page-header">
            @include('web.news.crumb', ['label' => __web('news.corporate_video')])
            @include('web.news.tabs', ['video' => true])

            <div class="nr-video-title">
                <div>
                    <span class="nr-kicker"><i></i> EGATi ON SCREEN</span>
                    <h1>{!! __web('news.our_story_powered_by_possibility') !!}</h1>
                </div>
                <p>{!! __web('news.the_corporate_presentation_of_egat') !!}</p>
            </div>

            <div class="nr-cinema-frame">
                <video id="nr-video" controls autoplay muted playsinline preload="auto" poster="{{ HomeContent::poster('presentation') }}" aria-label="{{ __web('presentation.egati_corporate_presentation_in_english') }}">
                    <source src="{{ HomeContent::video('presentation') }}" type="video/mp4">
                    {{ __web('news.this_browser_does_not_support') }}
                </video>
                <button class="nr-video-fallback" id="nr-video-fallback" type="button" hidden><span>▶</span>{{ __web('news.play_presentation') }}</button>
            </div>

            <div class="nr-video-controls">
                <div>
                    <span class="nr-video-live"><i></i> CORPORATE PRESENTATION</span>
                    <h2>EGATi’s Video Presentation</h2>
                    <p>{{ HomeContent::text('presentation', 'language_note') }}</p>
                </div>
                <button type="button" id="nr-sound" aria-pressed="false">{{ __web('news.turn_sound_on') }} <span aria-hidden="true">♫</span></button>
            </div>
            <p id="nr-video-status" class="nr-video-status" role="status">{{ __web('news.the_video_starts_muted_select') }}</p>
            <a class="nr-video-original" href="{{ HomeContent::video('presentation') }}" target="_blank" rel="noopener">{{ __web('news.video_from_egati_s_website') }} ↗</a>
        </section>

        <section class="nr-video-next">
            <div>
                <span class="nr-kicker">CONTINUE YOUR JOURNEY</span>
                <h2>{!! __web('news.there_s_more_to_discover') !!}</h2>
            </div>
            <a href="{{ web_url('news') }}"><span>01 / NEWSROOM</span><h3>{{ __web('news.news_activities') }}</h3><span aria-hidden="true">↗</span></a>
            <a href="{{ web_url('business') }}"><span>02 / OUR BUSINESS</span><h3>{{ __web('news.our_business') }}</h3><span aria-hidden="true">↗</span></a>
        </section>
    </div>
@endsection
