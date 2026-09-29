{{-- Generic renderer for one SectionTypes field. Vars: $inputName, $field, $value, $uid, $events (page-level) --}}
<div class="mb-3">
    <label class="form-label">{{ $field['label'] }}</label>

    @switch($field['type'])
        @case('code')
            <textarea name="{{ $inputName }}" class="form-control font-monospace" style="font-size: .85rem; min-height: 260px;" spellcheck="false">{{ $value }}</textarea>
            @break

        @case('textarea')
            <textarea name="{{ $inputName }}" class="form-control" rows="3">{{ $value }}</textarea>
            @break

        @case('image')
            <div class="image-field d-flex gap-2 align-items-center">
                <input type="text" name="{{ $inputName }}" class="form-control image-url" value="{{ $value }}" placeholder="https://... or upload">
                <input type="file" accept="image/*" class="form-control image-upload" style="max-width: 260px;" data-url="{{ route('admin.cms.upload') }}">
            </div>
            @break

        @case('events')
            @forelse($events as $ev)
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="{{ $inputName }}[]" value="{{ $ev->id }}" id="ev-{{ $uid }}-{{ $ev->id }}" {{ in_array($ev->id, (array) $value) ? 'checked' : '' }}>
                    <label class="form-check-label" for="ev-{{ $uid }}-{{ $ev->id }}">{{ $ev->title }}</label>
                </div>
            @empty
                <div class="text-muted small">This country has no events yet.</div>
            @endforelse
            @break

        @case('repeater')
            <div class="repeater">
                <div class="repeater-rows">
                    @foreach((array) $value as $ri => $row)
                        @include('admin.cms.sections._repeater_row', ['inputName' => $inputName, 'field' => $field, 'row' => $row, 'ri' => $ri])
                    @endforeach
                </div>
                <template>
                    @include('admin.cms.sections._repeater_row', ['inputName' => $inputName, 'field' => $field, 'row' => [], 'ri' => '__INDEX__'])
                </template>
                <button type="button" class="btn btn-sm btn-outline-secondary repeater-add">+ Add item</button>
            </div>
            @break

        @default
            <input type="{{ $field['type'] === 'url' ? 'text' : 'text' }}" name="{{ $inputName }}" class="form-control" value="{{ $value }}">
    @endswitch
</div>
