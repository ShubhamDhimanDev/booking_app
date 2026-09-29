@extends('admin.layouts.app')

@section('title', 'Add Country')

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">Add Country</h4>
    @include('admin.cms.partials.alert')

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.countries.store') }}" method="POST">
                @csrf
                @include('admin.cms.countries._fields')
                <div class="mt-4">
                    <button class="btn btn-primary">Create country</button>
                    <a href="{{ route('admin.countries.index') }}" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
