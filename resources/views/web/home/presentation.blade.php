@use('App\Helpers\HomeContent')
{{-- Home: company video presentation (managed in the back office, menu id 2) --}}
<section class="presentation-section" id="presentation">
  <div class="presentation-artwork" aria-hidden="true">
    <svg viewBox="0 0 1600 800" preserveAspectRatio="xMidYMid slice" fill="none" focusable="false">
      <defs>
        <linearGradient id="presentation-wave-gradient" x1="0" y1="800" x2="1600" y2="0" gradientUnits="userSpaceOnUse">
          <stop stop-color="#37b7d4" stop-opacity=".08"/>
          <stop offset=".46" stop-color="#6adce7" stop-opacity=".65"/>
          <stop offset="1" stop-color="#bdddfb" stop-opacity=".12"/>
        </linearGradient>
        <linearGradient id="presentation-gold-gradient" x1="0" y1="400" x2="1600" y2="400" gradientUnits="userSpaceOnUse">
          <stop stop-color="#ffdc60" stop-opacity="0"/>
          <stop offset=".58" stop-color="#ffdc60" stop-opacity=".65"/>
          <stop offset="1" stop-color="#ffdc60" stop-opacity="0"/>
        </linearGradient>
        <pattern id="presentation-dots" width="54" height="54" patternUnits="userSpaceOnUse">
          <circle cx="2" cy="2" r="1.1" fill="#c5ebff" opacity=".15"/>
        </pattern>
      </defs>
      <rect width="1600" height="800" fill="url(#presentation-dots)"/>
      <g class="presentation-wave-field" stroke="url(#presentation-wave-gradient)" stroke-width="1.6">
        <path d="M-200 580C200 820 480 40 900 210S1400 760 1800 340"/>
        <path d="M-200 610C200 850 480 70 900 240S1400 790 1800 370"/>
        <path d="M-200 640C200 880 480 100 900 270S1400 820 1800 400"/>
        <path d="M-200 670C200 910 480 130 900 300S1400 850 1800 430"/>
        <path d="M-200 700C200 940 480 160 900 330S1400 880 1800 460"/>
        <path d="M-200 730C200 970 480 190 900 360S1400 910 1800 490"/>
        <path class="presentation-energy-trail" d="M-200 640C200 880 480 100 900 270S1400 820 1800 400" stroke="#89e5ef" stroke-width="2.6" stroke-dasharray="80 1000"/>
        <path class="presentation-energy-trail presentation-energy-trail--gold" d="M-200 700C200 940 480 160 900 330S1400 880 1800 460" stroke="url(#presentation-gold-gradient)" stroke-width="3" stroke-dasharray="110 1100"/>
      </g>
      <g class="presentation-orbit-field">
        <circle cx="1110" cy="390" r="330" stroke="#9bdbff" stroke-opacity=".18"/>
        <circle cx="1110" cy="390" r="370" stroke="#9bdbff" stroke-opacity=".1"/>
        <circle cx="1110" cy="390" r="410" stroke="#9bdbff" stroke-opacity=".08"/>
        <circle cx="1110" cy="390" r="330" stroke="#83dbe7" stroke-opacity=".5" stroke-width="2" stroke-dasharray="120 1953"/>
        <circle cx="1440" cy="390" r="5" fill="#ffdc60"/>
        <circle cx="780" cy="390" r="4" fill="#8be6f0"/>
      </g>
      <g fill="#9ae7f2">
        <circle class="presentation-signal" cx="160" cy="160" r="4"/>
        <circle class="presentation-signal" cx="740" cy="670" r="5" style="animation-delay:-2s"/>
        <circle class="presentation-signal" cx="1500" cy="130" r="4" style="animation-delay:-4s"/>
      </g>
    </svg>
  </div>
  <div class="presentation-copy">
    <div class="eyebrow">{{ HomeContent::text('presentation', 'eyebrow') }}</div>
    <h2>{!! HomeContent::html('presentation', 'title') !!}</h2>
    <p>{{ HomeContent::text('presentation', 'text') }}</p>
    <div class="presentation-language"><span>EN</span>{{ HomeContent::text('presentation', 'language_note') }}</div>
    <button class="presentation-watch" data-play-presentation>{{ HomeContent::text('presentation', 'button_label') }}<span aria-hidden="true">↗</span></button>
  </div>
  <div class="presentation-frame">
    <div class="presentation-player" id="presentation-player">
      <video id="company-presentation" aria-hidden="true" playsinline preload="none" poster="{{ HomeContent::poster('presentation') }}" aria-label="{{ __web('presentation.egati_corporate_presentation_in_english') }}"></video>
      <button class="presentation-cover" data-play-presentation aria-label="{{ __web('presentation.play_egati_corporate_presentation') }}"><span class="presentation-play-icon" aria-hidden="true">▶</span><span class="presentation-cover-caption">EGAT INTERNATIONAL<span>OFFICIAL CORPORATE PRESENTATION</span></span></button>
    </div>
    <div class="presentation-caption"><span>EGATi’s Video Presentation <strong class="presentation-duration">{{ HomeContent::plain('presentation', 'duration') }}</strong></span><a href="{{ HomeContent::video('presentation') }}" target="_blank" rel="noopener">{{ __web('presentation.open_original_video') }} ↗</a></div>
    <p class="presentation-status" id="presentation-status" role="status" hidden></p>
  </div>
  <button class="presentation-motion-toggle" type="button" aria-pressed="false"><span aria-hidden="true">Ⅱ</span>{{ __web('presentation.pause_background_motion') }}</button>
</section>
