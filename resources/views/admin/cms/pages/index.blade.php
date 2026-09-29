@extends('admin.layouts.app')

@section('title', 'Pages')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Pages</h4>
        <a href="{{ route('admin.pages.create', ['country_id' => $countryId]) }}" class="btn btn-primary">+ Add Page</a>
    </div>

    @include('admin.cms.partials.alert')

    <form method="GET" class="mb-3 d-flex align-items-center gap-2">
        <label class="form-label mb-0">Country</label>
        <select name="country_id" class="form-select" style="max-width: 240px;" onchange="this.form.submit()">
            @foreach($countries as $c)
                <option value="{{ $c->id }}" {{ (string) $countryId === (string) $c->id ? 'selected' : '' }}>{{ $c->name }} (/{{ $c->slug }})</option>
            @endforeach
        </select>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>URL</th>
                        <th>Sections</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pages as $page)
                        <tr>
                            <td>
                                {{ $page->title }}
                                @if($page->is_home) <span class="badge bg-primary ms-1">Home</span> @endif
                            </td>
                            <td><a href="{{ $page->url() }}?preview=1" target="_blank">{{ parse_url($page->url(), PHP_URL_PATH) }}</a></td>
                            <td>{{ $page->sections_count }}</td>
                            <td><span class="badge bg-{{ $page->status === 'published' ? 'success' : 'secondary' }}">{{ ucfirst($page->status) }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('admin.pages.duplicate', $page) }}" method="POST" class="d-inline-flex gap-1">
                                    @csrf
                                    <select name="country_id" class="form-select form-select-sm" style="width: 120px;" title="Copy to country">
                                        @foreach($countries as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-sm btn-outline-secondary">Copy to</button>
                                </form>
                                <form action="{{ route('admin.pages.destroy', $page) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this page?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No pages for this country yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
