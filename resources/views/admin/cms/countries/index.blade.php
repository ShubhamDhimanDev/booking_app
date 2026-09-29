@extends('admin.layouts.app')

@section('title', 'Countries')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Countries</h4>
        <a href="{{ route('admin.countries.create') }}" class="btn btn-primary">+ Add Country</a>
    </div>

    @include('admin.cms.partials.alert')

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Country</th>
                        <th>URL</th>
                        <th>Currency</th>
                        <th>Default timezone</th>
                        <th>Pages</th>
                        <th>Events</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($countries as $country)
                        <tr>
                            <td>
                                {{ $country->name }}
                                @if($country->is_default) <span class="badge bg-primary ms-1">Default</span> @endif
                            </td>
                            <td><a href="{{ $country->url() }}?preview=1" target="_blank">/{{ $country->slug }}</a></td>
                            <td>{{ $country->currency }} ({{ trim($country->currency_symbol) }})</td>
                            <td>{{ $country->default_timezone }}</td>
                            <td><a href="{{ route('admin.pages.index', ['country_id' => $country->id]) }}">{{ $country->pages_count }}</a></td>
                            <td>{{ $country->events_count }}</td>
                            <td>
                                <span class="badge bg-{{ $country->is_active ? 'success' : 'secondary' }}">{{ $country->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.countries.edit', $country) }}" class="btn btn-sm btn-outline-primary">Edit / Header &amp; Footer</a>
                                <form action="{{ route('admin.countries.destroy', $country) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete {{ $country->name }} and all its pages?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
