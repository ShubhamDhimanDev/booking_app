@php $tzList = \DateTimeZone::listIdentifiers(); @endphp
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $country->name) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">URL slug</label>
        <div class="input-group">
            <span class="input-group-text">{{ url('/') }}/</span>
            <input type="text" name="slug" class="form-control" value="{{ old('slug', $country->slug) }}" placeholder="en-gb" required>
        </div>
        <div class="form-text">Lowercase letters, numbers and dashes. All of this country's pages live under it.</div>
    </div>
    <div class="col-md-3">
        <label class="form-label">ISO code</label>
        <input type="text" name="iso_code" maxlength="2" class="form-control" value="{{ old('iso_code', $country->iso_code) }}" placeholder="GB">
        <div class="form-text">Used to detect visitors (Cloudflare header / browser locale).</div>
    </div>
    <div class="col-md-3">
        <label class="form-label">Currency</label>
        <select name="currency" class="form-select">
            @foreach(config('cms.currencies') as $code => $symbol)
                <option value="{{ $code }}" {{ old('currency', $country->currency) === $code ? 'selected' : '' }}>{{ $code }} ({{ trim($symbol) }})</option>
            @endforeach
        </select>
        <div class="form-text">Default for new events in this country.</div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Default timezone</label>
        <select name="default_timezone" class="form-select">
            @foreach($tzList as $tz)
                <option value="{{ $tz }}" {{ old('default_timezone', $country->default_timezone) === $tz ? 'selected' : '' }}>{{ $tz }}</option>
            @endforeach
        </select>
        <div class="form-text">Shown when the visitor's own timezone can't be detected. Admin always stays IST.</div>
    </div>
    <div class="col-md-6">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="is_active" value="1" id="is_active" {{ old('is_active', $country->is_active) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Active (visible to the public)</label>
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="is_default" value="1" id="is_default" {{ old('is_default', $country->is_default) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_default">Default country (used when a visitor's country can't be detected)</label>
        </div>
    </div>
</div>
