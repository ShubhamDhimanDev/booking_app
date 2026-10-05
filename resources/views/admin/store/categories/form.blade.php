@extends('admin.layouts.app')

@section('title', $category->exists ? 'Edit Category' : 'Add Category')

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">{{ $category->exists ? 'Edit Category' : 'Add Category' }}</h4>
    @include('admin.cms.partials.alert')

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ $category->exists ? route('admin.product-categories.update', $category) : route('admin.product-categories.store') }}">
                @csrf
                @if($category->exists) @method('PUT') @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $category->slug) }}" placeholder="Auto from name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Parent category</label>
                        <select name="parent_id" class="form-select">
                            <option value="">None (top level)</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" {{ (int) old('parent_id', $category->parent_id) === $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sort order</label>
                        <input type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" value="1" id="is_active" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary">{{ $category->exists ? 'Save changes' : 'Create category' }}</button>
                    <a href="{{ route('admin.product-categories.index') }}" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
