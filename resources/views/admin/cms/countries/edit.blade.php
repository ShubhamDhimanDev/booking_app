@extends('admin.layouts.app')

@section('title', 'Edit ' . $country->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">{{ $country->name }} <small class="text-muted">/{{ $country->slug }}</small></h4>
        <a href="{{ route('admin.countries.index') }}" class="btn btn-light">Back</a>
    </div>

    @include('admin.cms.partials.alert')

    {{-- Details + header/footer HTML --}}
    <form action="{{ route('admin.countries.update', $country) }}" method="POST">
        @csrf @method('PUT')

        <div class="card shadow-sm mb-4">
            <div class="card-header"><h5 class="mb-0">Details</h5></div>
            <div class="card-body">
                @include('admin.cms.countries._fields')
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header"><h5 class="mb-0">Custom header / footer HTML <small class="text-muted">(optional)</small></h5></div>
            <div class="card-body">
                <div class="alert alert-warning">
                    Raw HTML, CSS and JS, rendered as-is (no escaping or sanitising). If left empty, the default header/footer is built from the menus below.
                </div>
                <label class="form-label">Header HTML</label>
                <textarea name="header_html" class="form-control font-monospace mb-3" rows="8">{{ old('header_html', $country->header_html) }}</textarea>
                <label class="form-label">Footer HTML</label>
                <textarea name="footer_html" class="form-control font-monospace" rows="8">{{ old('footer_html', $country->footer_html) }}</textarea>
            </div>
        </div>

        <button class="btn btn-primary mb-4">Save details &amp; HTML</button>
    </form>

    {{-- Menus --}}
    <form action="{{ route('admin.countries.navigation', $country) }}" method="POST">
        @csrf @method('PUT')
        <div class="card shadow-sm mb-4">
            <div class="card-header"><h5 class="mb-0">Menus <small class="text-muted">(drag to reorder)</small></h5></div>
            <div class="card-body">
                <div class="row">
                    @foreach(['header' => 'Header menu', 'footer' => 'Footer menu'] as $loc => $label)
                        <div class="col-lg-6 mb-4">
                            <h6>{{ $label }}</h6>
                            <div class="nav-list" data-location="{{ $loc }}">
                                @foreach($country->navItems->where('location', $loc) as $item)
                                    @include('admin.cms.countries._nav_row', ['loc' => $loc, 'item' => $item, 'i' => $loop->index])
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary add-nav" data-location="{{ $loc }}">+ Add link</button>
                        </div>
                    @endforeach
                </div>
                <button class="btn btn-primary">Save menus</button>
            </div>
        </div>
    </form>

    @foreach(['header', 'footer'] as $loc)
        <template id="nav-template-{{ $loc }}">
            @include('admin.cms.countries._nav_row', ['loc' => $loc, 'item' => null, 'i' => '__INDEX__'])
        </template>
    @endforeach
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
<script>
(function () {
    // Rows are named header[i][...] / footer[i][...]; array order on submit = menu order,
    // so after a drag we just renumber the inputs in DOM order.
    function renumber(list) {
        var loc = list.dataset.location;
        list.querySelectorAll('.nav-row').forEach(function (row, idx) {
            row.querySelectorAll('[name]').forEach(function (el) {
                el.name = el.name.replace(/^(header|footer)\[[^\]]*\]/, loc + '[' + idx + ']');
            });
        });
    }
    function toggleType(row) {
        var type = row.querySelector('.nav-type').value;
        row.querySelector('.nav-page').style.display = type === 'page' ? '' : 'none';
        row.querySelector('.nav-url').style.display = type === 'page' ? 'none' : '';
    }
    function wire(row) {
        row.querySelector('.nav-type').addEventListener('change', function () { toggleType(row); });
        row.querySelector('.remove-nav').addEventListener('click', function () {
            var list = row.parentNode; row.remove(); renumber(list);
        });
        toggleType(row);
    }
    document.querySelectorAll('.nav-list').forEach(function (list) {
        list.querySelectorAll('.nav-row').forEach(wire);
        Sortable.create(list, { handle: '.drag-handle', animation: 150, onEnd: function () { renumber(list); } });
    });
    document.querySelectorAll('.add-nav').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var loc = btn.dataset.location;
            var list = document.querySelector('.nav-list[data-location="' + loc + '"]');
            var html = document.getElementById('nav-template-' + loc).innerHTML.replace(/__INDEX__/g, list.children.length);
            var holder = document.createElement('div'); holder.innerHTML = html.trim();
            var row = holder.firstElementChild; list.appendChild(row); wire(row); renumber(list);
        });
    });
})();
</script>
@endpush
