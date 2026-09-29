@extends('admin.layouts.app')

@section('title', 'Edit page: ' . $page->title)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">{{ $page->title }} <small class="text-muted">{{ $page->country->name }} &middot; {{ parse_url($page->url(), PHP_URL_PATH) }}</small></h4>
        <div>
            <a href="{{ $page->url() }}?preview=1" target="_blank" class="btn btn-outline-secondary"><i class="mdi mdi-open-in-new"></i> Preview</a>
            <a href="{{ route('admin.pages.index', ['country_id' => $page->country_id]) }}" class="btn btn-light">Back</a>
        </div>
    </div>

    @include('admin.cms.partials.alert')

    <div class="card shadow-sm mb-4">
        <div class="card-header"><h5 class="mb-0">Page settings</h5></div>
        <div class="card-body">
            <form action="{{ route('admin.pages.update', $page) }}" method="POST">
                @csrf @method('PUT')
                @include('admin.cms.pages._meta_fields')
                <button class="btn btn-primary mt-3">Save page settings</button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mb-4" id="sections">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sections <small class="text-muted">(drag <i class="mdi mdi-drag"></i> to reorder, saved automatically)</small></h5>
            <form action="{{ route('admin.pages.sections.store', $page) }}" method="POST" class="d-flex gap-2">
                @csrf
                <select name="type" class="form-select form-select-sm">
                    @foreach($types as $key => $type)
                        <option value="{{ $key }}">{{ $type['label'] }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-primary text-nowrap">+ Add section</button>
            </form>
        </div>
        <div class="card-body">
            <div id="section-list" data-reorder-url="{{ route('admin.pages.sections.reorder', $page) }}">
                @forelse($page->sections as $section)
                    @include('admin.cms.sections._card', ['section' => $section, 'def' => $types[$section->type] ?? null])
                @empty
                    <p class="text-muted mb-0" id="no-sections">No sections yet. Pick a type above and click "Add section".</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
<script>
(function () {
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    var list = document.getElementById('section-list');

    // Drag-and-drop reorder -> POST the new id order.
    Sortable.create(list, {
        handle: '.drag-handle',
        animation: 150,
        onEnd: function () {
            var order = Array.prototype.map.call(list.querySelectorAll('.section-card'), function (el) { return el.dataset.id; });
            fetch(list.dataset.reorderUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ order: order })
            });
        }
    });

    // Image fields: upload a file, drop the returned URL into the text input.
    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('image-upload')) return;
        var input = e.target.closest('.image-field').querySelector('.image-url');
        var fd = new FormData(); fd.append('file', e.target.files[0]);
        fetch(e.target.dataset.url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: fd })
            .then(function (r) { return r.json(); })
            .then(function (j) { if (j.url) input.value = j.url; else alert('Upload failed'); })
            .catch(function () { alert('Upload failed'); });
    });

    // Repeater fields: add / remove rows.
    document.addEventListener('click', function (e) {
        var add = e.target.closest('.repeater-add');
        if (add) {
            var wrap = add.closest('.repeater');
            var idx = wrap.querySelectorAll('.repeater-row').length;
            var holder = document.createElement('div');
            holder.innerHTML = wrap.querySelector('template').innerHTML.replace(/__INDEX__/g, idx).trim();
            wrap.querySelector('.repeater-rows').appendChild(holder.firstElementChild);
        }
        var rm = e.target.closest('.repeater-remove');
        if (rm) rm.closest('.repeater-row').remove();
    });
})();
</script>
@endpush
