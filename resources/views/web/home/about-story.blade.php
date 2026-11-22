{{-- Home: about EGATi scroll story (3 slides with background video) --}}
<section class="about-story" id="about-story" aria-label="{{ __web('about.about_egati') }}">
  <div class="about-stage">
    <div class="about-slides">
      <article class="about-slide is-active" data-about-slide="0" aria-hidden="false"  aria-labelledby="about-title-0">
        <video class="about-film" data-src="{{ web_media('about.about_hydro', 'about-hydro.mp4') }}" poster="{{ web_media('about.about_hydro', 'about-hydro.jpg') }}" muted loop playsinline preload="none" aria-hidden="true"></video>
        <div class="about-shade"></div>
        <div class="about-copy">
          <span class="about-kicker">INTERNATIONAL ENERGY</span>
          <h2 id="about-title-0">{!! __web('about.beyond_borders_new_possibilities') !!}</h2>
          <p>{{ __web('about.egat_international_company_limited_conducts') }}</p>
          <a class="about-cta" href="{{ web_url('about') }}" >{{ __web('about.discover_egati') }}<span aria-hidden="true">↗</span></a>
        </div>
        <span class="about-giant" aria-hidden="true">01</span>
      </article>
      <article class="about-slide " data-about-slide="1" aria-hidden="true" inert aria-labelledby="about-title-1">
        <video class="about-film" data-src="{{ web_media('about.about_solar', 'about-solar.mp4') }}" poster="{{ web_media('about.about_solar', 'about-solar.jpg') }}" muted loop playsinline preload="none" aria-hidden="true"></video>
        <div class="about-shade"></div>
        <div class="about-copy">
          <span class="about-kicker">ENERGY & OPPORTUNITY</span>
          <h2 id="about-title-1">{!! __web('about.different_energies_shared_possibilities') !!}</h2>
          <p>{{ __web('about.from_hydropower_to_solar_energy') }}</p>
          <a class="about-cta" href="{{ web_url('business') }}" >{{ __web('about.explore_our_business') }}<span aria-hidden="true">↗</span></a>
        </div>
        <span class="about-giant" aria-hidden="true">02</span>
      </article>
      <article class="about-slide " data-about-slide="2" aria-hidden="true" inert aria-labelledby="about-title-2">
        <video class="about-film" data-src="{{ web_media('about.about_forest', 'about-forest.mp4') }}" poster="{{ web_media('about.about_forest', 'about-forest.jpg') }}" muted loop playsinline preload="none" aria-hidden="true"></video>
        <div class="about-shade"></div>
        <div class="about-copy">
          <span class="about-kicker">GOVERNANCE & RESPONSIBILITY</span>
          <h2 id="about-title-2">{!! __web('about.building_trust_growing_together') !!}</h2>
          <p>{{ __web('about.corporate_governance_and_social_responsibility') }}</p>
          <a class="about-cta" href="{{ web_url('about/governance') }}" >{{ __web('about.corporate_governance') }}<span aria-hidden="true">↗</span></a>
        </div>
        <span class="about-giant" aria-hidden="true">03</span>
      </article>
    </div>
    <div class="about-topline"><span class="about-top-note">ENERGY BEYOND BORDERS</span></div>
    <div class="about-bottom">
      <div class="about-chapters" role="group" aria-label="{{ __web('about.choose_a_story') }}">
        <button class="about-chapter" data-about-go="0" aria-pressed="true"><span>01</span><strong>{{ __web('about.beyond_borders') }}</strong><i aria-hidden="true">↗</i></button>
        <button class="about-chapter" data-about-go="1" aria-pressed="false"><span>02</span><strong>{{ __web('about.energy_opportunities') }}</strong><i aria-hidden="true">↗</i></button>
        <button class="about-chapter" data-about-go="2" aria-pressed="false"><span>03</span><strong>{{ __web('about.responsible_growth') }}</strong><i aria-hidden="true">↗</i></button>
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
