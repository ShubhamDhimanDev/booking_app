@php $type = $item && $item->page_id ? 'page' : 'url'; @endphp
<div class="nav-row border rounded p-2 mb-2 bg-light">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="drag-handle mdi mdi-drag" style="cursor: grab; font-size: 1.4rem;" title="Drag to reorder"></span>
        <input type="text" name="{{ $loc }}[{{ $i }}][label]" class="form-control form-control-sm" style="width: 140px;" placeholder="Label" value="{{ $item->label ?? '' }}">
        <select name="{{ $loc }}[{{ $i }}][type]" class="form-select form-select-sm nav-type" style="width: 100px;">
            <option value="page" {{ $type === 'page' ? 'selected' : '' }}>Page</option>
            <option value="url" {{ $type === 'url' ? 'selected' : '' }}>URL</option>
        </select>
        <select name="{{ $loc }}[{{ $i }}][page_id]" class="form-select form-select-sm nav-page" style="width: 160px;">
            @foreach($country->pages as $p)
                <option value="{{ $p->id }}" {{ $item && $item->page_id === $p->id ? 'selected' : '' }}>{{ $p->title }}</option>
            @endforeach
        </select>
        <input type="text" name="{{ $loc }}[{{ $i }}][url]" class="form-control form-control-sm nav-url" style="width: 200px;" placeholder="https://..." value="{{ $item->url ?? '' }}">
        <label class="form-check-label small">
            <input type="checkbox" class="form-check-input" name="{{ $loc }}[{{ $i }}][opens_new_tab]" value="1" {{ $item && $item->opens_new_tab ? 'checked' : '' }}> new tab
        </label>
        <button type="button" class="btn btn-sm btn-outline-danger remove-nav ms-auto">&times;</button>
    </div>
</div>
