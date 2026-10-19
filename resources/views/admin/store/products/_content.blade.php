{{-- Product page content editor. Every block is optional; empty blocks are hidden on the storefront. --}}
@php
    $c = old('content', $product->content ?? []);

    // key => [label, help]
    $lists = [
        'benefits' => ['Key benefits (hero ticks)', 'Three short lines work best.'],
        'highlights' => ['Highlight pills', 'Short tags shown under the rating.'],
        'who_yes' => ['"This is for you if"', null],
        'who_no' => ['"This is not for you if"', null],
        'signs' => ['Signs this product may be for you', null],
        'energy_checks' => ['Energising checklist', 'Ticks shown in the dark "personally energised" box.'],
        'expect_yes' => ['"You may notice over time"', null],
        'expect_no' => ['"What not to expect"', null],
    ];
    // key => [label, [field => label], textarea field]
    $rows = [
        'benefit_cards' => ['Benefit cards', ['title' => 'Title', 'body' => 'Text'], 'body'],
        'steps' => ['How it works (steps)', ['title' => 'Step title', 'body' => 'Text'], 'body'],
        'usage' => ['Usage and care cards', ['title' => 'Title', 'body' => 'Text'], 'body'],
        'not_cards' => ['"What this is not" cards', ['title' => 'Title', 'body' => 'Text'], 'body'],
        'specs' => ['Specifications', ['key' => 'Label', 'value' => 'Value'], null],
        'faq' => ['FAQ', ['q' => 'Question', 'a' => 'Answer'], 'a'],
        'reviews' => ['Customer reviews (enter real reviews only)', ['name' => 'Name', 'location' => 'Role / city', 'text' => 'Review', 'tag' => 'Tag', 'rating' => 'Stars (1-5)'], 'text'],
    ];

    $listRow = fn ($name, $value) => '<div class="input-group mb-2 pc-item"><input type="text" class="form-control" name="' . e($name) . '[]" value="' . e($value) . '"><div class="input-group-append"><button type="button" class="btn btn-outline-danger" data-remove>&times;</button></div></div>';

    $rowFields = function ($key, $fields, $big, $i, $item) {
        $html = '<div class="border rounded p-3 mb-2 pc-item"><div class="row">';
        foreach ($fields as $f => $label) {
            $name = e("content[$key][$i][$f]");
            $val = e($item[$f] ?? ($f === 'rating' ? 5 : ''));
            $col = $f === $big ? 'col-12' : ($f === 'rating' ? 'col-md-2' : 'col-md-5');
            $html .= '<div class="' . $col . ' mb-2"><label class="small text-muted mb-1">' . e($label) . '</label>';
            $html .= $f === $big
                ? '<textarea class="form-control" rows="3" name="' . $name . '">' . $val . '</textarea>'
                : '<input type="' . ($f === 'rating' ? 'number" min="1" max="5' : 'text') . '" class="form-control" name="' . $name . '" value="' . $val . '">';
            $html .= '</div>';
        }

        return $html . '</div><button type="button" class="btn btn-sm btn-outline-danger" data-remove>Remove</button></div>';
    };
@endphp

<div class="card shadow-sm mb-4" id="pc-editor">
    <div class="card-header">Product page content</div>
    <div class="card-body">
        <p class="text-muted small">Fills the sections of the product page. Leave a block empty to hide it. Plain text only; separate paragraphs with a blank line.</p>

        <div class="row g-3 mb-3">
            <div class="col-12">
                <label class="form-label">Subtitle</label>
                <input type="text" name="content[subtitle]" class="form-control" value="{{ $c['subtitle'] ?? '' }}" placeholder="Personally energised by Chaitanya for you based on your energy">
            </div>
            <div class="col-12">
                <label class="form-label">Purpose box (who it is for)</label>
                <textarea name="content[purpose]" rows="2" class="form-control">{{ $c['purpose'] ?? '' }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Consultation button link</label>
                <input type="text" name="content[consult_url]" class="form-control @error('content.consult_url') is-invalid @enderror" value="{{ $c['consult_url'] ?? '' }}" placeholder="https://... or /in/e/consultation (leave empty to hide the button)">
                @error('content.consult_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        @foreach(['benefits', 'highlights'] as $key)
            @include('admin.store.products._content-list', ['key' => $key, 'meta' => $lists[$key], 'items' => $c[$key] ?? [], 'listRow' => $listRow])
        @endforeach

        <hr>
        <div class="row g-3 mb-3">
            <div class="col-12"><label class="form-label">Benefits section intro</label><textarea name="content[benefits_intro]" rows="2" class="form-control">{{ $c['benefits_intro'] ?? '' }}</textarea></div>
        </div>
        @include('admin.store.products._content-rows', ['key' => 'benefit_cards', 'meta' => $rows['benefit_cards'], 'items' => $c['benefit_cards'] ?? [], 'rowFields' => $rowFields])

        <hr>
        @foreach(['who_yes', 'who_no'] as $key)
            @include('admin.store.products._content-list', ['key' => $key, 'meta' => $lists[$key], 'items' => $c[$key] ?? [], 'listRow' => $listRow])
        @endforeach

        <hr>
        <div class="row g-3 mb-3">
            <div class="col-md-6"><label class="form-label">"About" heading</label><input type="text" name="content[about_heading]" class="form-control" value="{{ $c['about_heading'] ?? '' }}" placeholder="What is the {{ $product->name ?: 'product' }}?"></div>
            <div class="col-12"><label class="form-label">"About" text</label><textarea name="content[about]" rows="6" class="form-control">{{ $c['about'] ?? '' }}</textarea></div>
        </div>
        @include('admin.store.products._content-list', ['key' => 'signs', 'meta' => $lists['signs'], 'items' => $c['signs'] ?? [], 'listRow' => $listRow])

        <hr>
        <div class="row g-3 mb-3">
            <div class="col-md-6"><label class="form-label">Steps heading</label><input type="text" name="content[steps_heading]" class="form-control" value="{{ $c['steps_heading'] ?? '' }}" placeholder="How the {{ $product->name ?: 'product' }} works"></div>
        </div>
        @include('admin.store.products._content-rows', ['key' => 'steps', 'meta' => $rows['steps'], 'items' => $c['steps'] ?? [], 'rowFields' => $rowFields])

        <hr>
        <div class="row g-3 mb-3">
            <div class="col-12"><label class="form-label">Energising quote</label><textarea name="content[energy_quote]" rows="3" class="form-control">{{ $c['energy_quote'] ?? '' }}</textarea></div>
        </div>
        @include('admin.store.products._content-list', ['key' => 'energy_checks', 'meta' => $lists['energy_checks'], 'items' => $c['energy_checks'] ?? [], 'listRow' => $listRow])

        <hr>
        @include('admin.store.products._content-rows', ['key' => 'usage', 'meta' => $rows['usage'], 'items' => $c['usage'] ?? [], 'rowFields' => $rowFields])

        <hr>
        @foreach(['expect_yes', 'expect_no'] as $key)
            @include('admin.store.products._content-list', ['key' => $key, 'meta' => $lists[$key], 'items' => $c[$key] ?? [], 'listRow' => $listRow])
        @endforeach

        <hr>
        <div class="row g-3 mb-3">
            <div class="col-12"><label class="form-label">Practitioner's note (quote)</label><textarea name="content[note_quote]" rows="3" class="form-control">{{ $c['note_quote'] ?? '' }}</textarea></div>
        </div>
        @include('admin.store.products._content-rows', ['key' => 'specs', 'meta' => $rows['specs'], 'items' => $c['specs'] ?? [], 'rowFields' => $rowFields])

        <hr>
        <div class="row g-3 mb-3">
            <div class="col-12"><label class="form-label">"What this is not" intro</label><textarea name="content[not_intro]" rows="2" class="form-control">{{ $c['not_intro'] ?? '' }}</textarea></div>
        </div>
        @include('admin.store.products._content-rows', ['key' => 'not_cards', 'meta' => $rows['not_cards'], 'items' => $c['not_cards'] ?? [], 'rowFields' => $rowFields])

        <hr>
        @include('admin.store.products._content-rows', ['key' => 'reviews', 'meta' => $rows['reviews'], 'items' => $c['reviews'] ?? [], 'rowFields' => $rowFields])

        <hr>
        @include('admin.store.products._content-rows', ['key' => 'faq', 'meta' => $rows['faq'], 'items' => $c['faq'] ?? [], 'rowFields' => $rowFields])
    </div>
</div>

@push('scripts')
<script>
(function () {
    var editor = document.getElementById('pc-editor');
    if (!editor) return;
    var seq = Date.now();
    editor.addEventListener('click', function (e) {
        var add = e.target.closest('[data-add]');
        if (add) {
            var box = add.closest('.pc-block');
            var tpl = box.querySelector('template').innerHTML.replace(/__i__/g, ++seq);
            box.querySelector('.pc-items').insertAdjacentHTML('beforeend', tpl);
            return;
        }
        var rm = e.target.closest('[data-remove]');
        if (rm) rm.closest('.pc-item').remove();
    });
})();
</script>
@endpush
