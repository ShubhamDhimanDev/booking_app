@extends('admin.layouts.app')

@section('title', $product->exists ? 'Edit Product' : 'Add Product')

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">{{ $product->exists ? 'Edit Product' : 'Add Product' }}</h4>
    @include('admin.cms.partials.alert')

    <form method="POST" enctype="multipart/form-data"
          action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
        @csrf
        @if($product->exists) @method('PUT') @endif

        <div class="card shadow-sm mb-4">
            <div class="card-header">Details</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $product->slug) }}" placeholder="Auto from name">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">Uncategorised</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (int) old('category_id', $product->category_id) === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Short description</label>
                        <input type="text" name="short_description" maxlength="500" class="form-control" value="{{ old('short_description', $product->short_description) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="6" class="form-control">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header">Images</div>
            <div class="card-body">
                @if($product->exists && count($product->images ?? []))
                    <div class="d-flex flex-wrap gap-3 mb-3">
                        @foreach($product->images as $img)
                            <label class="text-center">
                                <img src="{{ $img }}" alt="" style="width:96px;height:96px;object-fit:cover;border-radius:6px"><br>
                                <input type="checkbox" name="keep_images[]" value="{{ $img }}" checked> keep
                            </label>
                        @endforeach
                    </div>
                @endif
                <input type="file" name="new_images[]" class="form-control" accept="image/*" multiple>
                <div class="form-text">The first image is the cover. Untick "keep" to remove an image.</div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header">Pricing by country</div>
            <div class="card-body">
                <p class="text-muted small">Fill in the MRP to sell this product in a country; leave it empty to hide it there. If the sale price is empty it equals the MRP. There is no tax or shipping, so the customer pays the sale price.</p>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Country</th><th>Currency</th><th>MRP</th><th>Sale price</th></tr>
                        </thead>
                        <tbody>
                            @foreach($countries as $country)
                                @php $row = $prices[$country->id] ?? null; @endphp
                                <tr>
                                    <td>{{ $country->name }}</td>
                                    <td>{{ $country->currency }}</td>
                                    <td style="max-width:160px">
                                        <input type="number" step="0.01" min="0" name="prices[{{ $country->id }}][mrp]"
                                               class="form-control @error('prices.' . $country->id . '.mrp') is-invalid @enderror"
                                               value="{{ old('prices.' . $country->id . '.mrp', $row?->mrp) }}">
                                    </td>
                                    <td style="max-width:160px">
                                        <input type="number" step="0.01" min="0" name="prices[{{ $country->id }}][sale_price]"
                                               class="form-control @error('prices.' . $country->id . '.sale_price') is-invalid @enderror"
                                               value="{{ old('prices.' . $country->id . '.sale_price', $row && $row->sale_price < $row->mrp ? $row->sale_price : null) }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header">Stock, visibility and free session</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Stock quantity</label>
                        <input type="number" min="0" name="stock_qty" class="form-control" value="{{ old('stock_qty', $product->stock_qty ?? 0) }}">
                    </div>
                    <div class="col-md-9 d-flex flex-wrap align-items-end gap-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="track_stock" value="1" id="track_stock" {{ old('track_stock', $product->track_stock) ? 'checked' : '' }}>
                            <label class="form-check-label" for="track_stock">Track stock</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" value="1" id="is_active" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_featured" value="1" id="is_featured" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">Featured</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" name="grants_free_session" value="1" id="grants_free_session" {{ old('grants_free_session', $product->grants_free_session) ? 'checked' : '' }}>
                            <label class="form-check-label" for="grants_free_session">Buyer gets a free session slot link in the order email</label>
                        </div>
                        <select name="free_session_event_id" class="form-select">
                            <option value="">Use the country's default free-session event</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" {{ (int) old('free_session_event_id', $product->free_session_event_id) === $event->id ? 'selected' : '' }}>{{ $event->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header">SEO</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Meta title</label>
                        <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $product->meta_title) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Meta description</label>
                        <input type="text" name="meta_description" class="form-control" value="{{ old('meta_description', $product->meta_description) }}">
                    </div>
                </div>
            </div>
        </div>

        <button class="btn btn-primary">{{ $product->exists ? 'Save changes' : 'Create product' }}</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-light">Cancel</a>
    </form>
</div>
@endsection
