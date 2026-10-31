@extends('admin.layouts.app')

@section('title', 'Media Manager')

@push('styles')
<style>
    .media-card { border: 1px solid #e9ecef; border-radius: 6px; overflow: hidden; background: #fff; height: 100%; display: flex; flex-direction: column; }
    .media-thumb { position: relative; aspect-ratio: 4 / 3; background: #f1f3f5 repeating-conic-gradient(#e9ecef 0% 25%, #f8f9fa 0% 50%) 50% / 16px 16px; cursor: pointer; }
    .media-thumb img, .media-thumb video { width: 100%; height: 100%; object-fit: cover; display: block; }
    .media-thumb .media-badge { position: absolute; top: 6px; left: 6px; }
    .media-thumb .media-play { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 40px; text-shadow: 0 1px 6px rgba(0,0,0,.6); pointer-events: none; }
    .media-meta { padding: 8px 10px; font-size: 12px; flex: 1; }
    .media-meta .media-name { font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .media-actions { display: flex; gap: 4px; padding: 0 10px 10px; }
    .media-actions .btn { flex: 1; padding: 4px 6px; font-size: 12px; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Media Manager</h4>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Upload --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form id="media-upload-form" action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="media-files" class="form-label">Upload images / videos</label>
                        <input type="file" id="media-files" name="files[]" class="form-control" multiple required
                               accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/webm,video/quicktime">
                        <small class="text-muted">JPG, PNG, GIF, WebP, MP4, WebM, MOV &middot; up to 100 MB each</small>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="convert_webp" id="convert_webp" value="1"
                                   {{ $webpAvailable ? 'checked' : 'disabled' }}>
                            <label class="form-check-label" for="convert_webp">Convert images to WebP</label>
                        </div>
                        <small class="text-muted">
                            @if($webpAvailable)
                                Smaller files, transparency kept. Animated GIFs and videos are stored as-is.
                            @else
                                WebP is not supported by this server's GD extension.
                            @endif
                        </small>
                    </div>
                    <div class="col-md-2 text-md-end">
                        <button type="submit" class="btn btn-primary" id="media-upload-btn">Upload</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.media.index') }}" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Search by file name">
        </div>
        <div class="col-md-2">
            <select name="type" class="form-select" onchange="this.form.submit()">
                <option value="">All types</option>
                <option value="image" @selected($type === 'image')>Images</option>
                <option value="video" @selected($type === 'video')>Videos</option>
            </select>
        </div>
        <div class="col-md-auto">
            <button class="btn btn-outline-primary" type="submit">Filter</button>
            @if($type || $search !== '')
                <a href="{{ route('admin.media.index') }}" class="btn btn-link">Reset</a>
            @endif
        </div>
        <div class="col-md text-md-end align-self-center text-muted small">{{ $media->total() }} {{ \Illuminate\Support\Str::plural('item', $media->total()) }}</div>
    </form>

    {{-- Grid --}}
    <div class="row g-3">
        @forelse($media as $item)
            <div class="col-6 col-md-4 col-lg-3 col-xxl-2">
                <div class="media-card">
                    <div class="media-thumb" data-view data-type="{{ $item->type }}" data-url="{{ $item->url }}"
                         data-name="{{ $item->original_name }}" data-meta="{{ $item->human_size }}{{ $item->width ? ' · ' . $item->width . '×' . $item->height : '' }} · {{ $item->mime_type }}">
                        @if($item->isImage())
                            <img src="{{ $item->url }}" alt="{{ $item->original_name }}" loading="lazy">
                        @else
                            <video src="{{ $item->url }}#t=0.1" preload="metadata" muted></video>
                            <span class="media-play"><i class="mdi mdi-play-circle-outline"></i></span>
                        @endif
                        <span class="badge bg-dark media-badge">{{ strtoupper(pathinfo($item->path, PATHINFO_EXTENSION)) }}</span>
                    </div>
                    <div class="media-meta">
                        <div class="media-name" title="{{ $item->original_name }}">{{ $item->original_name }}</div>
                        <div class="text-muted">{{ $item->human_size }}@if($item->width) &middot; {{ $item->width }}&times;{{ $item->height }}@endif</div>
                        <div class="text-muted">{{ $item->created_at->format('d M Y') }}</div>
                    </div>
                    <div class="media-actions">
                        <button type="button" class="btn btn-outline-primary" data-view
                                data-type="{{ $item->type }}" data-url="{{ $item->url }}" data-name="{{ $item->original_name }}"
                                data-meta="{{ $item->human_size }}{{ $item->width ? ' · ' . $item->width . '×' . $item->height : '' }} · {{ $item->mime_type }}">View</button>
                        <button type="button" class="btn btn-outline-secondary" data-copy="{{ $item->url }}">Copy URL</button>
                        <form action="{{ route('admin.media.destroy', $item) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this file permanently? Pages using its URL will show a broken file.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="mdi mdi-delete"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card"><div class="card-body text-center text-muted py-5">No media found.</div></div>
            </div>
        @endforelse
    </div>

    @if($media->hasPages())
        <div class="mt-4">
            {{ $media->links() }}
        </div>
    @endif
</div>

{{-- Viewer modal --}}
<div class="modal fade" id="media-viewer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-truncate" id="media-viewer-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="media-viewer-body"></div>
                <small class="text-muted d-block mt-2" id="media-viewer-meta"></small>
            </div>
            <div class="modal-footer justify-content-between">
                <div class="input-group">
                    <input type="text" class="form-control" id="media-viewer-url" readonly>
                    <button type="button" class="btn btn-primary" id="media-viewer-copy">Copy URL</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }
        return new Promise(function (resolve, reject) {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy') ? resolve() : reject(); } catch (e) { reject(e); }
            document.body.removeChild(ta);
        });
    }

    function flash(btn, label) {
        var original = btn.dataset.label || (btn.dataset.label = btn.textContent);
        btn.textContent = label;
        clearTimeout(btn._t);
        btn._t = setTimeout(function () { btn.textContent = original; }, 1500);
    }

    function copyWithFeedback(btn, url) {
        copyText(url).then(function () { flash(btn, 'Copied!'); }, function () { flash(btn, 'Failed'); });
    }

    document.querySelectorAll('[data-copy]').forEach(function (btn) {
        btn.addEventListener('click', function () { copyWithFeedback(btn, btn.dataset.copy); });
    });

    // Viewer modal
    var modalEl = document.getElementById('media-viewer');
    var modal = new bootstrap.Modal(modalEl);
    var body = document.getElementById('media-viewer-body');
    var urlInput = document.getElementById('media-viewer-url');

    document.querySelectorAll('[data-view]').forEach(function (el) {
        el.addEventListener('click', function () {
            var d = el.dataset, node;
            if (d.type === 'video') {
                node = document.createElement('video');
                node.controls = true;
                node.autoplay = true;
            } else {
                node = document.createElement('img');
            }
            node.src = d.url;
            node.style.maxWidth = '100%';
            node.style.maxHeight = '65vh';
            body.replaceChildren(node);
            document.getElementById('media-viewer-title').textContent = d.name;
            document.getElementById('media-viewer-meta').textContent = d.meta;
            urlInput.value = d.url;
            modal.show();
        });
    });

    modalEl.addEventListener('hidden.bs.modal', function () { body.replaceChildren(); }); // stops video playback
    urlInput.addEventListener('focus', function () { urlInput.select(); });
    document.getElementById('media-viewer-copy').addEventListener('click', function () {
        copyWithFeedback(this, urlInput.value);
    });

    // Upload: normal form post (keeps flash messages / validation errors), just block double-submits
    var form = document.getElementById('media-upload-form');
    var submitBtn = document.getElementById('media-upload-btn');
    form.addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Uploading…';
    });
})();
</script>
@endpush
