@extends('admin.layouts.app')

@section('title', 'Products')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Products</h4>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">+ Add Product</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="q" class="form-control" placeholder="Search name or SKU" value="{{ request('q') }}">
        </div>
        <div class="col-md-3">
            <select name="category_id" class="form-select">
                <option value="">All categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ (int) request('category_id') === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-primary">Filter</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-light">Reset</a>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:64px"></th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Prices</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                @if($product->first_image)
                                    <img src="{{ $product->first_image }}" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:6px">
                                @endif
                            </td>
                            <td>
                                <strong>{{ $product->name }}</strong>
                                @if($product->is_featured) <span class="badge bg-warning text-dark ms-1">Featured</span> @endif
                                @if($product->grants_free_session) <span class="badge bg-info ms-1">Free session</span> @endif
                                @if($product->sku)<br><small class="text-muted">{{ $product->sku }}</small>@endif
                            </td>
                            <td>{{ $product->category?->name ?? '—' }}</td>
                            <td>
                                @forelse($product->prices as $price)
                                    <div>
                                        <small class="text-muted">{{ $price->country->name }}:</small>
                                        {{ trim(config('cms.currencies.' . $price->currency, $price->currency)) }}{{ number_format($price->sale_price, 2) }}
                                        @if($price->sale_price < $price->mrp)
                                            <small class="text-muted text-decoration-line-through">{{ number_format($price->mrp, 2) }}</small>
                                        @endif
                                    </div>
                                @empty
                                    <small class="text-muted">Not sold anywhere</small>
                                @endforelse
                            </td>
                            <td>
                                @if($product->track_stock)
                                    <span class="{{ $product->stock_qty === 0 ? 'text-danger' : '' }}">{{ $product->stock_qty }}</span>
                                @else
                                    <small class="text-muted">Untracked</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $product->is_active ? 'Active' : 'Hidden' }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this product? Past orders keep their details.');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($products->hasPages())
        <div class="mt-4">{{ $products->links() }}</div>
    @endif
</div>
@endsection
