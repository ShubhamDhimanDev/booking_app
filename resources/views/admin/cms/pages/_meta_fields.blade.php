@php $editing = $page->exists; @endphp
<div class="row g-3">
    @unless($editing)
        <div class="col-md-4">
            <label class="form-label">Country</label>
            <select name="country_id" class="form-select">
                @foreach($countries as $c)
                    <option value="{{ $c->id }}" {{ (string) old('country_id', $countryId) === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
    @endunless
    <div class="col-md-4">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $page->title) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Slug</label>
        <input type="text" name="slug" class="form-control" value="{{ old('slug', $page->slug) }}" placeholder="about" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="draft" {{ old('status', $page->status ?: 'draft') === 'draft' ? 'selected' : '' }}>Draft (admins can preview)</option>
            <option value="published" {{ old('status', $page->status) === 'published' ? 'selected' : '' }}>Published</option>
        </select>
    </div>
    <div class="col-md-8 d-flex align-items-end">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="is_home" value="1" id="is_home" {{ old('is_home', $page->is_home) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_home">This is the country's home page (served at the country URL)</label>
        </div>
    </div>
    <div class="col-md-6">
        <label class="form-label">SEO title</label>
        <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $page->meta_title) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">SEO description</label>
        <input type="text" name="meta_description" class="form-control" value="{{ old('meta_description', $page->meta_description) }}">
    </div>
</div>
