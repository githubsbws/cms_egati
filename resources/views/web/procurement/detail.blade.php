@php
    $type = $record->type;
    $docs = $record->attachments->filter(fn ($a) => $a->url);
    $rows = match ($type) {
        'plan' => [['proc.field_budget', $amountText]],
        'price' => [['proc.field_method', $record->method?->labelFor(app()->getLocale())], ['proc.field_price_date', $record->published_on ? \App\Helpers\WebDate::plain($record->published_on) : null]],
        'notice' => [['proc.field_work_type', $record->workType?->labelFor(app()->getLocale())], ['proc.field_method', $record->method?->labelFor(app()->getLocale())], ['proc.field_notice_date', $record->published_on ? \App\Helpers\WebDate::plain($record->published_on) : null]],
        default => [
            ['proc.field_vendor', $record->vendor?->name],
            ['proc.field_method', $record->method?->labelFor(app()->getLocale())],
            ['proc.field_amount', $amountText],
            ['proc.field_date', $record->published_on ? \App\Helpers\WebDate::plain($record->published_on) : null],
            [$type === 'winner_in' ? 'proc.field_reference_in' : 'proc.field_reference_out', $record->reference_no],
            ['proc.field_reason', $record->reason],
        ],
    };
@endphp
<dialog class="proc-detail-dialog" data-proc-category="{{ \Illuminate\Support\Str::camel($type) }}" id="proc-detail-{{ $record->id }}" aria-labelledby="proc-detail-{{ $record->id }}-title">
    <div class="proc-dialog-top">
        <span class="proc-type-tag proc-type-{{ \Illuminate\Support\Str::camel($type) }}">{{ __web("proc.cat_{$type}_short") }}</span>
        <button type="button" class="proc-dialog-close" data-proc-close aria-label="{{ __web('proc.close_details') }}">×</button>
    </div>
    <h2 id="proc-detail-{{ $record->id }}-title">{{ $record->title }}</h2>
    <p class="proc-dialog-sub">{{ __web('proc.year_3') }} {{ $year($record->fiscal_year) }}@if ($record->legacy_id) · {{ __web('proc.source_record') }} {{ $record->legacy_id }}@endif</p>
    <dl class="proc-detail-fields">
        @foreach ($rows as [$label, $value])
            @if (filled($value))
                <div><dt>{{ __web($label) }}</dt><dd>{{ $value }}</dd></div>
            @endif
        @endforeach
    </dl>
    <div class="proc-documents">
        <h3>{{ __web('proc.attached_documents') }} <span>{{ $docs->count() }}</span></h3>
        @forelse ($docs as $doc)
            <a class="proc-document-link" href="{{ $doc->url }}" target="_blank" rel="noopener noreferrer">
                @include('web.procurement.icon', ['name' => 'file'])
                <span><strong>{{ __web("proc.kind_{$doc->kind}") }}</strong><small lang="th">{{ urldecode(basename(parse_url($doc->url, PHP_URL_PATH) ?: $doc->url)) }}</small></span>
                <span aria-hidden="true">↗</span>
            </a>
        @empty
            <p>{{ __web('proc.the_source_lists_no_attachment') }}</p>
        @endforelse
    </div>
    <div class="proc-dialog-footer"><p>{{ __web('proc.documents_open_directly_from_the') }}</p></div>
</dialog>
