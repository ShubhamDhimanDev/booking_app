@extends('admin.layouts.app')

@section('title', 'Add Page')

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">Add Page</h4>
    @include('admin.cms.partials.alert')

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.pages.store') }}" method="POST">
                @csrf
                @include('admin.cms.pages._meta_fields', ['page' => new \App\Models\Page()])
                <div class="mt-4">
                    <button class="btn btn-primary">Create &amp; add sections</button>
                    <a href="{{ route('admin.pages.index', ['country_id' => $countryId]) }}" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
