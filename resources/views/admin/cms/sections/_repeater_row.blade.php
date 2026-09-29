<div class="repeater-row border rounded p-2 mb-2">
    @foreach($field['fields'] as $sub => $subField)
        <label class="form-label small mb-0">{{ $subField['label'] }}</label>
        @if($subField['type'] === 'textarea')
            <textarea name="{{ $inputName }}[{{ $ri }}][{{ $sub }}]" class="form-control mb-2" rows="2">{{ $row[$sub] ?? '' }}</textarea>
        @else
            <input type="text" name="{{ $inputName }}[{{ $ri }}][{{ $sub }}]" class="form-control mb-2" value="{{ $row[$sub] ?? '' }}">
        @endif
    @endforeach
    <button type="button" class="btn btn-sm btn-outline-danger repeater-remove">Remove</button>
</div>
