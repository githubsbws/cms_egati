{{-- Date chip (day, month, year). Thai shows the Buddhist year. Params: $date (Carbon), optional $class --}}
@php $chip = \App\Helpers\WebDate::chip($date); @endphp
<time class="news-date-chip {{ $class ?? '' }}" datetime="{{ $chip['iso'] }}" aria-label="{{ $chip['label'] }}"><strong>{{ $chip['day'] }}</strong><span><b>{{ $chip['month'] }}</b><small>{{ $chip['year'] }}</small></span></time>