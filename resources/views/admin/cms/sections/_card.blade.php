@php $content = $section->content ?? []; @endphp
<div class="section-card border rounded mb-3" data-id="{{ $section->id }}" id="section-{{ $section->id }}">
    <div class="d-flex align-items-center gap-2 p-2 border-bottom bg-transparent">
        <span class="drag-handle mdi mdi-drag" style="cursor: grab; font-size: 1.5rem;" title="Drag to reorder"></span>
        <i class="mdi {{ $def['icon'] ?? 'mdi-view-dashboard' }}"></i>
        <strong>{{ $def['label'] ?? $section->type }}</strong>
        @unless($section->is_visible) <span class="badge bg-secondary">Hidden</span> @endunless
        <button class="btn btn-sm btn-outline-secondary ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#section-body-{{ $section->id }}">Edit</button>
        <button class="btn btn-sm btn-outline-danger" form="delete-section-{{ $section->id }}" onclick="return confirm('Delete this section?')">Delete</button>
    </div>
    <div class="collapse {{ session('open_section') == $section->id ? 'show' : '' }}" id="section-body-{{ $section->id }}">
        <form action="{{ route('admin.sections.update', $section) }}" method="POST" class="p-3">
            @csrf @method('PUT')
            @foreach($def['fields'] ?? [] as $name => $field)
                @include('admin.cms.sections._field', [
                    'inputName' => "content[$name]",
                    'field' => $field,
                    'value' => $content[$name] ?? null,
                    'uid' => $section->id . '-' . $name,
                ])
            @endforeach
            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" name="is_visible" value="1" id="vis-{{ $section->id }}" {{ $section->is_visible ? 'checked' : '' }}>
                <label class="form-check-label" for="vis-{{ $section->id }}">Visible on the public page</label>
            </div>
            <button class="btn btn-primary btn-sm">Save section</button>
        </form>
        <form id="delete-section-{{ $section->id }}" action="{{ route('admin.sections.destroy', $section) }}" method="POST" class="d-none">
            @csrf @method('DELETE')
        </form>
    </div>
</div>
