@use('App\Helpers\HomeContent')
{{-- Home: related agencies (logo grid). Managed in the back office (menu id 2) --}}
@php
  $agencies = HomeContent::agencies();
  $defaultLocale = \App\Models\Language::active()->where('is_default', 'y')->value('code');
@endphp
@if ($agencies->isNotEmpty())
<section class="stakeholder-section stakeholder-showcase" id="stakeholders" aria-labelledby="stakeholder-title">
  <div class="stakeholder-intro">
    <h2 id="stakeholder-title">{{ __web('stakeholders.stakeholder') }}</h2>
    <span aria-hidden="true"></span>
  </div>
  <nav class="stakeholder-logo-grid" aria-label="{{ __web('stakeholders.related_agencies_and_organizations') }}">
    @foreach ($agencies as $agency)
    @php $name = $agency->nameFor(app()->getLocale(), $defaultLocale); @endphp
    <a class="stakeholder-link" href="{{ $agency->url }}" target="_blank" rel="noopener" aria-label="{{ $name }}" aria-describedby="stakeholder-tip-{{ $agency->id }}" style="--reveal-delay:{{ $loop->index * 60 }}ms">
      <span class="stakeholder-mark"><img loading="lazy" src="{{ $agency->logo_url }}" alt="" width="512" height="512"></span>
      <span class="stakeholder-tooltip" role="tooltip" id="stakeholder-tip-{{ $agency->id }}">{{ $name }}</span>
      <span class="stakeholder-hover-dot" aria-hidden="true"></span>
    </a>
    @endforeach
  </nav>
</section>
@endif
