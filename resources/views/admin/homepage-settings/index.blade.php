@extends('admin.layouts.app')

@section('title', 'Homepage Settings')

@section('content')

<div class="container-fluid">

    <h4 class="mb-4">Homepage Settings</h4>

    @if(session('alert_type'))
        <div class="alert alert-{{ session('alert_type') }} alert-dismissible fade show" role="alert">
            {{ session('alert_message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <label for="region-select" class="form-label">Page</label>
            <select id="region-select" class="form-select" style="max-width: 320px;" onchange="location.href = this.value">
                @foreach($regions as $key => $meta)
                    <option value="{{ route('admin.homepage-settings.index', ['region' => $key]) }}" {{ $region === $key ? 'selected' : '' }}>
                        {{ $meta['label'] }}
                    </option>
                @endforeach
            </select>
            <div class="form-text">
                {{ $region === 'in' ? 'Renders at /en-in' : 'Renders at /en-us' }}
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">{{ $regions[$region]['label'] }} — HTML</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-warning">
                This is raw HTML, rendered exactly as entered — no escaping, no sanitization. Only trusted admins should have access to this page.
            </div>

            <form action="{{ route('admin.homepage-settings.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="region" value="{{ $region }}">

                <textarea
                    name="html"
                    rows="24"
                    class="form-control font-monospace"
                    style="font-size: 0.9rem;"
                    placeholder="&lt;section&gt;&#10;  &lt;h1&gt;Welcome&lt;/h1&gt;&#10;  ...&#10;&lt;/section&gt;"
                >{{ old('html', $html) }}</textarea>

                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <a href="{{ $region === 'in' ? route('home.in') : route('home.us') }}" target="_blank" class="text-muted small">
                        <i class="mdi mdi-open-in-new"></i> Preview live page
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save"></i> Save {{ $regions[$region]['label'] }}
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection
