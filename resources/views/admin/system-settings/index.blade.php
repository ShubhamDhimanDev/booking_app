@extends('admin.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">System Settings</h4>
                <p class="card-description">Configure user interface theme and appearance</p>

                @if(session('alert_type'))
                    <div class="alert alert-{{ session('alert_type') }} alert-dismissible fade show" role="alert">
                        {{ session('alert_message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.system-settings.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label fw-bold">User Theme Layout</label>
                        <select name="theme_layout" class="form-select" required>
                            <option value="modern" {{ $settings->theme_layout === 'modern' ? 'selected' : '' }}>
                                Modern Theme (Clean & Minimalist)
                            </option>
                            <option value="classic" {{ $settings->theme_layout === 'classic' ? 'selected' : '' }}>
                                Classic Theme (Traditional & Professional)
                            </option>
                        </select>
                        <small class="form-text text-muted">Select the theme layout for user-facing pages</small>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="darkModeSwitch" name="dark_mode"
                                   {{ $settings->dark_mode ? 'checked' : '' }}>
                            <label class="form-check-label" for="darkModeSwitch">
                                <strong>Enable Dark Mode</strong>
                            </label>
                        </div>
                        <small class="form-text text-muted">Allow users to view pages in dark mode</small>
                    </div>

                    <div class="alert alert-info">
                        <i class="mdi mdi-information"></i>
                        <strong>Note:</strong> These settings affect user-facing pages only. Admin panel theme remains unchanged.
                    </div>

                    <button type="submit" class="btn btn-primary me-2">
                        <i class="mdi mdi-content-save"></i> Save Changes
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-light">
                        Cancel
                    </a>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Theme Preview</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="border rounded p-3 mb-3">
                            <h6 class="text-center mb-3">Modern Theme</h6>
                            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 100px; border-radius: 8px; margin-bottom: 10px;"></div>
                            <p class="small text-muted">Clean, modern design with gradient accents and smooth animations</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 mb-3">
                            <h6 class="text-center mb-3">Classic Theme</h6>
                            <div style="background: #2c3e50; height: 100px; border-radius: 4px; margin-bottom: 10px;"></div>
                            <p class="small text-muted">Traditional, professional design with solid colors and structured layout</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
