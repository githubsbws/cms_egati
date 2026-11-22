{{-- Home: quick access cards --}}
<section class="quick-access" id="quick-access" aria-labelledby="quick-access-title">
  <div class="quick-access-heading">
    <div>
      <div class="quick-access-label"><span aria-hidden="true">✦</span> QUICK ACCESS</div>
      <h2 id="quick-access-title">{!! __web('quick.your_next_step_just_one') !!}</h2>
    </div>
    <span class="quick-heading-orbit" aria-hidden="true">↗</span>
  </div>
  <div class="quick-access-grid">
    <a class="quick-card quick-card--violet" href="#presentation" style="--reveal-delay:0ms">
      <div class="quick-card-top">
        <span class="quick-card-icon"><svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="26" height="22" rx="5"/><path d="m13 11 9 5-9 5z"/></svg></span>
        <span class="quick-card-index" aria-hidden="true">01</span>
      </div>
      <div class="quick-card-copy">
        <h3>{{ __web('quick.video_presentation') }}</h3>
        <p>{{ __web('quick.see_the_energy_behind_our') }}</p>
      </div>
      <span class="quick-card-action"><span>{{ __web('quick.discover_our_story') }}</span><span class="quick-card-arrow" aria-hidden="true">↗</span></span>
      <span class="quick-card-spark" aria-hidden="true">✦</span>
    </a>
    <a class="quick-card quick-card--sun" href="{{ web_url('documents') }}" style="--reveal-delay:80ms">
      <div class="quick-card-top">
        <span class="quick-card-icon"><svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3h12l6 6v20H8zM20 3v7h7M12 15h10M12 20h7M4 8v18"/></svg></span>
        <span class="quick-card-index" aria-hidden="true">02</span>
      </div>
      <div class="quick-card-copy">
        <h3>{{ __web('quick.publications') }}</h3>
        <p>{{ __web('quick.company_reports_all_in_one') }}</p>
      </div>
      <span class="quick-card-action"><span>{{ __web('quick.explore_publications') }}</span><span class="quick-card-arrow" aria-hidden="true">↗</span></span>
      <span class="quick-card-spark" aria-hidden="true">✦</span>
    </a>
    <a class="quick-card quick-card--blue" href="{{ web_url('procurement') }}" style="--reveal-delay:160ms">
      <div class="quick-card-top">
        <span class="quick-card-icon"><svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 5 5-2 9 9-2 5zM17 7l8 8M18 16 7 27l-3-3 11-11M17 27h12M20 23h6v4"/></svg></span>
        <span class="quick-card-index" aria-hidden="true">03</span>
      </div>
      <div class="quick-card-copy">
        <h3>{{ __web('quick.procurement') }}</h3>
        <p>{{ __web('quick.plans_notices_and_partner_opportunities') }}</p>
      </div>
      <span class="quick-card-action"><span>{{ __web('quick.explore_opportunities') }}</span><span class="quick-card-arrow" aria-hidden="true">↗</span></span>
      <span class="quick-card-spark" aria-hidden="true">✦</span>
    </a>
    <a class="quick-card quick-card--mint" href="{{ web_url('news') }}" style="--reveal-delay:240ms">
      <div class="quick-card-top">
        <span class="quick-card-icon"><svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="26" height="23" rx="4"/><path d="M8 10h7v7H8zM19 10h5M19 15h5M8 22h16"/></svg></span>
        <span class="quick-card-index" aria-hidden="true">04</span>
      </div>
      <div class="quick-card-copy">
        <h3>{{ __web('quick.news_activities') }}</h3>
        <p>{{ __web('quick.stay_connected_with_the_latest') }}</p>
      </div>
      <span class="quick-card-action"><span>{{ __web('quick.catch_up_on_our_news') }}</span><span class="quick-card-arrow" aria-hidden="true">↗</span></span>
      <span class="quick-card-spark" aria-hidden="true">✦</span>
    </a>
  </div>
</section>
