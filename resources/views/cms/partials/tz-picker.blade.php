{{-- Lets a visitor override the detected timezone. Stored in localStorage, read by partials.timezone-convert. --}}
@include('cms.partials.styles')
<div class="cms-container">
    <div class="cms-tz">
        <span>Times shown in</span>
        <select id="cms-tz-select" aria-label="Timezone"></select>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var sel = document.getElementById('cms-tz-select');
    if (!sel) return;
    var current = window.__visitorTz || @json($country->default_timezone);
    var zones = [];
    try { zones = Intl.supportedValuesOf('timeZone'); } catch (e) {}
    if (zones.indexOf(current) === -1) zones.unshift(current);
    zones.forEach(function (z) {
        var o = document.createElement('option');
        o.value = z; o.textContent = z.replace(/_/g, ' ');
        if (z === current) o.selected = true;
        sel.appendChild(o);
    });
    sel.addEventListener('change', function () {
        try { localStorage.setItem('visitor_tz', sel.value); } catch (e) {}
        location.reload();
    });
});
</script>
