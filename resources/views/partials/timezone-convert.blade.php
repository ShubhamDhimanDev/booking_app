<script>
(function () {
    // Detect once, reuse everywhere on this page.
    function validTz(z) {
        try { new Intl.DateTimeFormat('en-US', { timeZone: z }); return true; } catch (e) { return false; }
    }
    // Order: visitor's manual choice (timezone picker) -> browser timezone -> country default -> IST.
    let visitorTz = null;
    try { visitorTz = localStorage.getItem('visitor_tz'); } catch (e) { /* storage blocked */ }
    if (!visitorTz || !validTz(visitorTz)) {
        try { visitorTz = Intl.DateTimeFormat().resolvedOptions().timeZone; } catch (e) { visitorTz = null; }
    }
    if (!visitorTz || !validTz(visitorTz)) {
        visitorTz = (window.__countryTz && validTz(window.__countryTz)) ? window.__countryTz : 'Asia/Kolkata';
    }
    window.__visitorTz = visitorTz;

    // Given an IST-wall-clock date ("YYYY-MM-DD") and time ("HH:mm"), return
    // { local: "8:00 AM PDT", ist: "6:30 PM IST", sameDay: bool } for display.
    window.convertIstToVisitorTz = function (dateStr, timeStr) {
        const istDate = new Date(`${dateStr}T${timeStr}:00+05:30`);
        const localFmt = new Intl.DateTimeFormat('en-US', {
            hour: 'numeric', minute: '2-digit', hour12: true,
            timeZoneName: 'short', timeZone: visitorTz,
        });
        const istFmt = new Intl.DateTimeFormat('en-US', {
            hour: 'numeric', minute: '2-digit', hour12: true,
            timeZoneName: 'short', timeZone: 'Asia/Kolkata',
        });
        const localDayFmt = new Intl.DateTimeFormat('en-CA', { timeZone: visitorTz }); // YYYY-MM-DD
        return {
            local: localFmt.format(istDate),
            ist: istFmt.format(istDate),
            localDate: localDayFmt.format(istDate),
            isSameDayAsIst: localDayFmt.format(istDate) === dateStr,
        };
    };

    // Progressive enhancement: rewrite any element carrying data-ist-date/data-ist-time
    // (used on server-rendered pages — details/thankyou/reschedule/bookings-grid).
    document.querySelectorAll('[data-ist-date][data-ist-time]').forEach(function (el) {
        const conv = window.convertIstToVisitorTz(el.dataset.istDate, el.dataset.istTime);
        const dayNote = conv.isSameDayAsIst ? '' : ' *';
        el.textContent = `${conv.local}${dayNote} (${conv.ist})`;
        if (dayNote) el.title = 'Date shown is in your local timezone and may differ from the IST date.';
    });
})();
</script>
