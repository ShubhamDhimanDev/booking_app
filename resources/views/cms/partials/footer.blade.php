@include('cms.partials.styles')
@if(trim((string) $country->footer_html) !== '')
    {!! $country->footer_html !!}
@else
    <footer class="cms-footer">
        <div class="cms-container">
            <nav class="cms-nav">
                @foreach($country->footerItems()->with('page.country')->get() as $item)
                    <a href="{{ $item->href() }}" @if($item->opens_new_tab) target="_blank" rel="noopener" @endif>{{ $item->label }}</a>
                @endforeach
            </nav>
            <div>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</div>
        </div>
    </footer>
@endif
