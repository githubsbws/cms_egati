<footer id="site-footer">
  <div class="footer-bottom">
    <div>
      <img src="{{ web_media('footer.logo', 'logo.png') }}" alt="EGATi">
      <h3>{{ __web('footer.egat_international_company_limited') }}</h3>
      <p>{!! __web('footer.53_moo_2_building_t') !!}</p>
    </div>
    <div class="contact-details">
      <a href="tel:024366905">02-436-6905</a>
      <a href="mailto:info@egati.co.th">info@egati.co.th</a>
    </div>
    <div class="footer-links">
      <a href="{{ web_url('about') }}">{{ __web('footer.about_egati') }}</a>
      <a href="{{ web_url('procurement') }}">{{ __web('footer.procurement') }}</a>
      <a href="{{ web_url('careers') }}">{{ __web('footer.careers') }}</a>
      <a href="{{ web_url('contact') }}">{{ __web('footer.contact_us') }}</a>
      <a href="{{ web_url('sitemap') }}">{{ __web('footer.sitemap') }}</a>
    </div>
  </div>
  <small>
    © 2026 EGAT International Co., Ltd.
    <a href="#site-header">{{ __web('footer.back_to_top') }}</a>
  </small>
</footer>
