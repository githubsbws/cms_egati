@use('App\Helpers\HomeContent')
@php
  $storyCodes = ['story_1', 'story_2', 'story_3'];
@endphp
{{-- Home: about EGATi scroll story (3 slides with background video). Text, videos and links are managed in the back office (menu id 2) --}}
<section class="about-story" id="about-story" aria-label="{{ __web('about.about_egati') }}">
  <div class="about-stage">
    <div class="about-slides">
      @foreach ($storyCodes as $index => $code)
      <article class="about-slide {{ $index === 0 ? 'is-active' : '' }}" data-about-slide="{{ $index }}" aria-hidden="{{ $index === 0 ? 'false' : 'true' }}" @if ($index > 0) inert @endif aria-labelledby="about-title-{{ $index }}">
        <video class="about-film" data-src="{{ HomeContent::video($code) }}" poster="{{ HomeContent::poster($code) }}" muted loop playsinline preload="none" aria-hidden="true"></video>
        <div class="about-shade"></div>
        <div class="about-copy">
          <span class="about-kicker">{{ HomeContent::text($code, 'kicker') }}</span>
          <h2 id="about-title-{{ $index }}">{!! HomeContent::html($code, 'title') !!}</h2>
          <p>{{ HomeContent::text($code, 'text') }}</p>
          <a class="about-cta" href="{{ HomeContent::url($code, 'cta_url') }}" >{{ HomeContent::text($code, 'cta_label') }}<span aria-hidden="true">↗</span></a>
        </div>
        <span class="about-giant" aria-hidden="true">{{ sprintf('%02d', $index + 1) }}</span>
      </article>
      @endforeach
    </div>
    <div class="about-topline"><span class="about-top-note">ENERGY BEYOND BORDERS</span></div>
    <div class="about-bottom">
      <div class="about-chapters" role="group" aria-label="{{ __web('about.choose_a_story') }}">
        @foreach ($storyCodes as $index => $code)
        <button class="about-chapter" data-about-go="{{ $index }}" aria-pressed="{{ $index === 0 ? 'true' : 'false' }}"><span>{{ sprintf('%02d', $index + 1) }}</span><strong>{{ HomeContent::text($code, 'chapter_label') }}</strong><i aria-hidden="true">↗</i></button>
        @endforeach
      </div>
      <div class="about-tools">
        <span class="about-scroll-hint"><span aria-hidden="true">↓</span> {{ __web('about.scroll_to_discover_the_next') }}</span>
        <span class="about-count" aria-hidden="true"><b>01</b> / 03</span>
        <button class="about-pause" aria-label="{{ __web('about.pause_background_video') }}" aria-pressed="false">Ⅱ</button>
      </div>
      <span class="about-stock-note">{{ __web('about.footage_from_the_nam_ngiep') }}</span>
    </div>
    <div class="about-progress" aria-hidden="true"><span></span></div>
  </div>
</section>
