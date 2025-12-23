@extends('layouts.app')

@section('title', 'Edit Event')

@section('content')

<div class="container-fluid">

    <h4 class="mb-4">Edit Event</h4>

    <div class="card shadow-sm">
        <div class="card-body">

            <form action="{{ route('admin.events.update', $event->id) }}" method="POST">
                @csrf
                @method('PUT')

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const form = document.querySelector('form[action="{{ route('admin.events.update', $event->id) }}"]');
                        if (! form) return;
                        form.addEventListener('submit', function(e) {
                            const customContainer = document.getElementById('customTimesList');
                            if (!customContainer || customContainer.children.length === 0) {
                                e.preventDefault();
                                alert('Please add at least one custom timeslot before saving the event.');
                            }
                        });
                    });
                </script>

                {{-- Title --}}
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input
                        type="text"
                        name="title"
                        class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title', $event->title) }}"
                    >
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea id="description" name="description" rows="5"
                        class="form-control @error('description') is-invalid @enderror"
                    >{{ old('description', $event->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Duration --}}
                <div class="mb-3">
                    <label class="form-label">Duration</label>
                    <select
                        name="duration"
                        class="form-select @error('duration') is-invalid @enderror"
                    >
                        <option value="">-- Select duration --</option>

                        @foreach([15, 30, 45, 60, 90, 120] as $min)
                            <option value="{{ $min }}"
                                {{ old('duration', $event->duration) == $min ? 'selected' : '' }}>
                                {{ $min }} minutes
                            </option>
                        @endforeach
                    </select>

                    @error('duration')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Slug --}}
                <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input
                        type="text"
                        name="slug"
                        class="form-control @error('slug') is-invalid @enderror"
                        value="{{ old('slug', $event->slug) }}"
                    >
                    <div class="form-text">
                        https://booking-app.insanedev.in/e/{{ old('slug', $event->slug) }}
                    </div>

                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Color --}}
                <div class="mb-3">
                    <label class="form-label">Color</label>
                    <input
                        type="color"
                        name="color"
                        class="form-control form-control-color @error('color') is-invalid @enderror"
                        value="{{ old('color', $event->color) }}"
                        title="Choose color"
                    >
                    @error('color')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Price --}}
                <div class="mb-3">
                    <label class="form-label">Price (INR)</label>
                    <input
                        type="number"
                        name="price"
                        step="0.01"
                        class="form-control @error('price') is-invalid @enderror"
                        value="{{ old('price', $event->price) }}"
                    >
                    <div class="form-text">Amount in Indian Rupees (e.g. 500.00)</div>

                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr/>

                {{-- Available Dates --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Available From</label>
                        <input
                            type="date"
                            name="available_from_date"
                            class="form-control @error('available_from_date') is-invalid @enderror"
                            value="{{ old('available_from_date', $event->available_from_date) }}"
                        >
                        @error('available_from_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Available To</label>
                        <input
                            type="date"
                            name="available_to_date"
                            class="form-control @error('available_to_date') is-invalid @enderror"
                            value="{{ old('available_to_date', $event->available_to_date) }}"
                        >
                        @error('available_to_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Timeslots removed — use Custom Timeslots instead --}}

                {{-- Available Week Days (Mon - Sun) --}}
                <div class="mb-3">
                    <label class="form-label">Available Week Days</label>
                    <div class="d-flex gap-2 flex-wrap">
                        @php $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
                            $selectedDays = old('available_week_days', $event->available_week_days ?? []);
                        @endphp
                        @foreach($days as $d)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="available_week_days[]" value="{{ $d }}" id="day_{{ $d }}" {{ (is_array($selectedDays) && in_array($d, $selectedDays)) ? 'checked' : '' }} style="margin: 1px 5px;">
                                <label class="form-check-label" for="day_{{ $d }}">{{ ucfirst($d) }}</label>
                            </div>
                        @endforeach
                    </div>
                    <div class="form-text">Select which weekdays this event is available on. Leave empty for all days.</div>
                </div>

                    {{-- Custom Timeslots (apply to all selected dates) --}}
                    <div id="customTimesList" class="mb-2"></div>
                    <button type="button" id="addCustomTime" class="btn btn-outline-secondary btn-sm">+ Add Timeslot</button>
                    <div class="form-text">Create specific timeslots (start/end). These will be applied to every date in the range. Leave empty to use automatic slots generated from Duration.</div>

                    {{-- Exclusions: custom date/time exclusions --}}
                    <div class="mb-3">
                        <label class="form-label">Excluded Dates / Times</label>
                        <div id="exclusionsList" class="mb-2"></div>
                        <button type="button" id="addExclusion" class="btn btn-outline-secondary btn-sm">+ Add Exclusion</button>
                        <div class="form-text">Add specific dates to exclude or exclude particular timeslots on that date. Leave empty to not exclude.</div>
                    </div>

                        {{-- Reminders: admin-configurable reminders per event --}}
                        <div class="mb-3">
                            <label class="form-label">Reminders</label>
                            <input type="hidden" name="reminders_present" value="1">
                            <div id="remindersList" class="mb-2"></div>
                            <button type="button" id="addReminder" class="btn btn-outline-secondary btn-sm">+ Add Reminder</button>
                            <div class="form-text">Add reminders like "2 hours before" or "1 day before". Set a value and unit. You may add multiple reminders.</div>
                        </div>
                {{-- Submit --}}
                <button type="submit" class="btn btn-primary px-4">
                    Update Event
                </button>

            </form>
        </div>
    </div>

</div>

@endsection


@push('scripts')
<script>
    // ======================
    // DATE VALIDATION
    // ======================

    const fromDate = document.querySelector('input[name="available_from_date"]');
    const toDate   = document.querySelector('input[name="available_to_date"]');

    const today = new Date().toISOString().split("T")[0];
    fromDate.setAttribute("min", today);

    fromDate.addEventListener("change", () => {
        toDate.setAttribute("min", fromDate.value);
    });

    toDate.addEventListener("change", () => {
        if (toDate.value < fromDate.value) {
            alert("End date must be greater than Start date.");
            toDate.value = "";
        }
    });

    // ======================
    // SLUG LIVE PREVIEW
    // ======================
    const slugInput = document.querySelector('input[name="slug"]');
    const slugPreview = document.querySelector('.form-text');

    slugInput.addEventListener("input", () => {
        const cleanSlug = slugInput.value
            .trim()
            .toLowerCase()
            .replace(/[^a-z0-9-]/g, '-')
            .replace(/-+/g, '-');

        slugPreview.innerText = "https://booking-app.insanedev.in/e/" + cleanSlug;
    });

    // ======================
    // TIME VALIDATION (legacy inputs removed)
    // ======================
    // Legacy `available_from_time`/`available_to_time` inputs were removed
    // in favor of `custom_timeslots`. Guard any code that attempts to
    // read them so pages without those inputs don't throw.
    const timeFromInput = document.querySelector('input[name="available_from_time"]');
    const timeToInput   = document.querySelector('input[name="available_to_time"]');

    if (timeFromInput && timeToInput) {
        timeToInput.addEventListener("change", () => {
            if (timeFromInput.value && timeToInput.value <= timeFromInput.value) {
                alert("Timeslot TO must be greater than Timeslot FROM.");
                timeToInput.value = "";
            }
        });
    }

    // PRICE VALIDATION
    const priceInput = document.querySelector('input[name="price"]');
    priceInput.addEventListener("input", () => {
        if (priceInput.value < 0) {
            alert("Price cannot be negative.");
            priceInput.value = "";
        }
    });

    CKEDITOR.replace('description', {
        versionCheck: false
    });

    // ======================
    // Exclusions UI (edit)
    // ======================
    const exclusionsList = document.getElementById('exclusionsList');
    const addExclusion = document.getElementById('addExclusion');

    // Custom timeslots UI (edit)
    const customTimesList = document.getElementById('customTimesList');
    const addCustomTime = document.getElementById('addCustomTime');

    function createCustomTimeRowEdit(data = {}) {
        const idx = Date.now() + Math.floor(Math.random()*1000);
        const row = document.createElement('div');
        row.className = 'd-flex gap-2 align-items-center mb-2';
        row.innerHTML = `
            <input type="time" name="custom_timeslots[${idx}][start]" class="form-control form-control-sm" value="${data.start || ''}" required style="max-width:140px">
            <input type="time" name="custom_timeslots[${idx}][end]" class="form-control form-control-sm" value="${data.end || ''}" required style="max-width:140px">
            <button type="button" class="btn btn-sm btn-danger ms-auto remove-custom">Remove</button>
        `;
        customTimesList.appendChild(row);
        row.querySelector('.remove-custom').addEventListener('click', () => row.remove());
        return row;
    }

    addCustomTime.addEventListener('click', () => createCustomTimeRowEdit());

    // prefill custom timeslots from old input (validation) or server
    // helper to parse HH:MM into minutes for sorting
    function _parseMinutes(t) {
        if (!t) return 0;
        const parts = t.split(':').map(Number);
        return (parts[0] || 0) * 60 + (parts[1] || 0);
    }

    const existingCustomTimes = @json(old('custom_timeslots', $event->custom_timeslots ?? []));
    if (existingCustomTimes) {
        const list = Array.isArray(existingCustomTimes) ? existingCustomTimes : Object.values(existingCustomTimes);
        // sort early -> late before rendering rows
        list.sort((a,b) => _parseMinutes(a.start) - _parseMinutes(b.start));
        list.forEach(ts => createCustomTimeRowEdit({start: ts.start, end: ts.end}));
    }

    // Validate custom timeslots for overlaps (edit)
    function toMinutes(t) {
        const [hh, mm] = t.split(':').map(Number);
        return hh*60 + mm;
    }

    function validateCustomTimesUI() {
        const rows = Array.from(document.querySelectorAll('#customTimesList .form-control'));
        const pairs = [];
        for (let i = 0; i < rows.length; i += 2) {
            const start = rows[i]?.value || '';
            const end = rows[i+1]?.value || '';
            const rowEl = rows[i]?.closest ? rows[i].closest('.d-flex') : null;
            if (!start || !end) continue;
            const s = toMinutes(start);
            const e = toMinutes(end);
            if (rowEl) rowEl.classList.remove('is-invalid');
            pairs.push({s,e,row: rowEl});
        }
        pairs.sort((a,b) => a.s - b.s);
        let ok = true;
        for (let i=1;i<pairs.length;i++) {
            if (pairs[i].s < pairs[i-1].e) {
                ok = false;
                if (pairs[i].row) pairs[i].row.classList.add('is-invalid');
                if (pairs[i-1].row) pairs[i-1].row.classList.add('is-invalid');
            }
        }
        return ok;
    }

    document.addEventListener('input', function(e){
        if (e.target && e.target.matches('#customTimesList input[type="time"]')) {
            validateCustomTimesUI();
        }
    });

    document.addEventListener('DOMContentLoaded', function(){
        const form = document.querySelector('form[action="{{ route('admin.events.update', $event->id) }}"]');
        if (!form) return;
        form.addEventListener('submit', function(e){
            if (! validateCustomTimesUI()) {
                e.preventDefault();
                alert('Custom timeslots must not overlap and each must have end after start. Please fix highlighted rows.');
            }
        });
    });

    function computeTimeslots() {
        // If custom timeslots have been defined, use them
        const customContainer = document.getElementById('customTimesList');
        if (customContainer && customContainer.children.length) {
            const rows = Array.from(customContainer.querySelectorAll('input[name$="[start]"]'));
            const slots = rows.map(startInput => {
                const name = startInput.name.replace('[start]','[end]');
                const endInput = customContainer.querySelector(`input[name="${name}"]`);
                return { start: startInput.value, end: (endInput ? endInput.value : '') };
            }).filter(s => s.start && s.end);
            // sort slots early -> late
            slots.sort((a,b) => _parseMinutes(a.start) - _parseMinutes(b.start));
            return slots;
        }

        // Legacy fallback: guard missing inputs so pages without these fields don't break
        const fromInput = document.querySelector('input[name="available_from_time"]');
        const toInput = document.querySelector('input[name="available_to_time"]');
        const from = fromInput ? fromInput.value : '';
        const to = toInput ? toInput.value : '';
        const duration = parseInt(document.querySelector('select[name="duration"]').value || 0, 10);
        if (!from || !to || !duration) return [];
        const [fh, fm] = from.split(':').map(Number);
        const [th, tm] = to.split(':').map(Number);
        const start = new Date(); start.setHours(fh, fm, 0, 0);
        const end = new Date(); end.setHours(th, tm, 0, 0);
        const slots = [];
        while (start < end) {
            const hh = String(start.getHours()).padStart(2,'0');
            const mm = String(start.getMinutes()).padStart(2,'0');
            const next = new Date(start.getTime() + duration*60000);
            if (next > end) break;
            slots.push({start: `${hh}:${mm}`, end: `${String(next.getHours()).padStart(2,'0')}:${String(next.getMinutes()).padStart(2,'0')}`});
            start.setTime(start.getTime() + duration*60000);
        }
        return slots;
    }

    function renderTimesCheckboxes(container, selectedTimes=[]) {
        container.innerHTML = '';
        const slots = computeTimeslots();
        if (slots.length === 0) {
            container.innerHTML = '<div class="text-muted">Set duration and times to populate slots.</div>';
            return;
        }
        slots.forEach(s => {
            const id = 'ts_' + Math.random().toString(36).slice(2,8);
            const wrapper = document.createElement('div');
            wrapper.className = 'form-check form-check-inline';
            const input = document.createElement('input');
            input.type = 'checkbox';
            input.name = 'DUMMY';
            input.value = s.start;
            input.id = id;
            if (selectedTimes.includes(s.start)) input.checked = true;
            input.className = 'form-check-input slot-checkbox';
            const label = document.createElement('label');
            label.className = 'form-check-label';
            label.htmlFor = id;
            label.innerText = s.start + ' - ' + s.end;
            wrapper.appendChild(input);
            wrapper.appendChild(label);
            container.appendChild(wrapper);
        });
    }

    function createExclusionRow(data = {}) {
        const idx = Date.now() + Math.floor(Math.random()*1000);
        const row = document.createElement('div');
        row.className = 'card p-2 mb-2';
        row.innerHTML = `
            <div class="d-flex gap-2 align-items-center mb-2">
                <input type="date" name="exclusions[${idx}][date]" class="form-control form-control-sm excl-date" value="${data.date || ''}" required style="max-width:200px">
                <div class="form-check">
                    <input type="checkbox" name="exclusions[${idx}][exclude_all]" class="form-check-input excl-all" ${data.exclude_all ? 'checked' : ''} id="excl_all_${idx}">
                    <label class="form-check-label" for="excl_all_${idx}">Exclude all slots</label>
                </div>
                <button type="button" class="btn btn-sm btn-danger ms-auto remove-excl">Remove</button>
            </div>
            <div class="excl-times mb-1"></div>
        `;
        exclusionsList.appendChild(row);

        const timesContainer = row.querySelector('.excl-times');
        renderTimesCheckboxes(timesContainer, data.times || []);

        function assignNames() {
            const checkboxes = timesContainer.querySelectorAll('.slot-checkbox');
            checkboxes.forEach(cb => cb.name = `exclusions[${idx}][times][]`);
        }
        assignNames();

        row.querySelector('.excl-all').addEventListener('change', (e) => {
            if (e.target.checked) timesContainer.style.display = 'none';
            else timesContainer.style.display = '';
        });

        row.querySelector('.remove-excl').addEventListener('click', () => row.remove());

        return row;
    }

    addExclusion.addEventListener('click', () => createExclusionRow());

    // prefill existing exclusions from server
    const existingExclusions = @json($event->exclusions->map(function($e){ return ['date' => $e->date->toDateString(), 'exclude_all' => (bool)$e->exclude_all, 'times' => $e->times ?? []]; }));
    if (Array.isArray(existingExclusions)) {
        existingExclusions.forEach(ex => createExclusionRow(ex));
    }

    // ======================
    // Reminders UI (edit)
    // ======================
    const remindersList = document.getElementById('remindersList');
    const addReminder = document.getElementById('addReminder');

    function createReminderRowEdit(data = {}) {
        const idx = Date.now() + Math.floor(Math.random()*1000);
        const row = document.createElement('div');
        row.className = 'd-flex gap-2 align-items-center mb-2';
        row.innerHTML = `
            <input type="number" min="0" name="reminders[${idx}][value]" class="form-control form-control-sm" value="${data.value ?? data.offset_minutes ?? ''}" style="max-width:100px" required>
            <select name="reminders[${idx}][unit]" class="form-select form-select-sm" style="max-width:140px">
                <option value="minutes" ${(!data.unit || data.unit === 'minutes') ? 'selected' : ''}>minutes</option>
                <option value="hours" ${data.unit === 'hours' ? 'selected' : ''}>hours</option>
                <option value="days" ${data.unit === 'days' ? 'selected' : ''}>days</option>
            </select>
            <input type="text" name="reminders[${idx}][name]" class="form-control form-control-sm" placeholder="Label (optional)" value="${data.name ?? ''}" style="max-width:220px">
            <input type="hidden" name="reminders[${idx}][enabled]" value="0">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="reminders[${idx}][enabled]" value="1" ${(data.enabled === false) ? '' : 'checked'}>
                <label class="form-check-label">Enabled</label>
            </div>
            <button type="button" class="btn btn-sm btn-danger ms-auto remove-reminder">Remove</button>
        `;
        remindersList.appendChild(row);
        row.querySelector('.remove-reminder').addEventListener('click', () => row.remove());
        return row;
    }

    addReminder.addEventListener('click', () => createReminderRowEdit());

    // prefill existing reminders from server
    const existingReminders = @json($event->reminders->map(function($r){ return ['offset_minutes' => $r->offset_minutes, 'name' => $r->name, 'enabled' => (bool)$r->enabled]; }));
    if (Array.isArray(existingReminders)) {
        existingReminders.forEach(r => {
            // try to represent offset in value+unit (hours/days/minutes)
            let value = r.offset_minutes;
            let unit = 'minutes';
            if (value % 1440 === 0) { value = value / 1440; unit = 'days'; }
            else if (value % 60 === 0) { value = value / 60; unit = 'hours'; }
            createReminderRowEdit({ value: value, unit: unit, name: r.name || '', enabled: r.enabled });
        });
    }

    // update times lists when duration or times change
    ['available_from_time','available_to_time','duration'].forEach(name => {
        document.querySelectorAll(`[name="${name}"]`).forEach(el => {
            el.addEventListener('change', () => {
                document.querySelectorAll('#exclusionsList .excl-times').forEach(container => {
                    const parent = container.closest('.card');
                    const selected = Array.from(container.querySelectorAll('.slot-checkbox:checked')).map(cb => cb.value);
                    renderTimesCheckboxes(container, selected);
                    container.querySelectorAll('.slot-checkbox').forEach(cb => cb.name = container.previousElementSibling.querySelector('input[type="date"]').name.replace(/date/, 'times[]'));
                });
            });
        });
    });

</script>
@endpush
