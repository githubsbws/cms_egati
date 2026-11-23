{{-- Appointment order of a committee (the PDF uploaded in the back office). Param: $group --}}
@php $orderTitle = __web('about.appointment_order', null, ['name' => $group->nameFor(app()->getLocale())]); @endphp
<aside class="ac-committee-document" aria-labelledby="ac-notice-title">
    <span class="ac-document-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h8l4 4v16H6z"/><path d="M14 2v5h5M9 12h6M9 16h6"/></svg>
    </span>
    <div class="ac-document-copy">
        <span class="ac-kicker">OFFICIAL DOCUMENT · PDF</span>
        <h3 id="ac-notice-title">{{ $orderTitle }}</h3>
    </div>
    <a class="ac-document-open" href="{{ web_file_url($group->document_path) }}" target="_blank" rel="noopener" aria-label="{{ $orderTitle }}">
        <span>{{ __web('about.read_document') }}</span><b aria-hidden="true">↗</b>
    </a>
</aside>