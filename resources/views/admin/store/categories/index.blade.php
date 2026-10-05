@extends('admin.layouts.app')

@section('title', 'Product Categories')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Product Categories</h4>
        <a href="{{ route('admin.product-categories.create') }}" class="btn btn-primary">+ Add Category</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Parent</th>
                        <th>Slug</th>
                        <th>Products</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->parent?->name ?? '—' }}</td>
                            <td>{{ $category->slug }}</td>
                            <td>{{ $category->products_count }}</td>
                            <td>{{ $category->sort_order }}</td>
                            <td>
                                <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $category->is_active ? 'Active' : 'Hidden' }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.product-categories.edit', $category) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
                                <form action="{{ route('admin.product-categories.destroy', $category) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this category? Its products will become uncategorised.');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">No categories yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
