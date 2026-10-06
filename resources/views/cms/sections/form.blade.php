@php
    $successKey = 'form_success_' . $section->id;
    $submitUrl = route('form.submit', ['cmsCountry' => $country->slug, 'section' => $section->id]);
@endphp
<section class="cms-section" id="form-{{ $section->id }}">
    <div class="cms-container cms-form-wrap">
        @if(!empty($data['heading']))<h2>{{ $data['heading'] }}</h2>@endif
        @if(!empty($data['intro']))<p class="cms-text">{{ $data['intro'] }}</p>@endif

        @if(session($successKey))
            <div class="cms-form-success">{{ session($successKey) }}</div>
        @else
            <form method="POST" action="{{ $submitUrl }}" class="cms-form">
                @csrf
                <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;" aria-hidden="true">
                @foreach(array_values($data['fields'] ?? []) as $i => $f)
                    @php
                        $name = "f{$i}";
                        $type = $f['type'] ?? 'text';
                        $req = ($f['required'] ?? '') === '1';
                        $old = old($name);
                    @endphp
                    <div class="cms-form-row">
                        @if($type === 'checkbox')
                            <label class="cms-form-check">
                                <input type="checkbox" name="{{ $name }}" value="1" {{ $old ? 'checked' : '' }} {{ $req ? 'required' : '' }}>
                                {{ $f['label'] ?? '' }}@if($req) *@endif
                            </label>
                        @else
                            <label for="{{ $name }}-{{ $section->id }}">{{ $f['label'] ?? '' }}@if($req) *@endif</label>
                            @if($type === 'textarea')
                                <textarea id="{{ $name }}-{{ $section->id }}" name="{{ $name }}" rows="4" {{ $req ? 'required' : '' }}>{{ $old }}</textarea>
                            @elseif($type === 'select')
                                <select id="{{ $name }}-{{ $section->id }}" name="{{ $name }}" {{ $req ? 'required' : '' }}>
                                    <option value="">Select...</option>
                                    @foreach(array_filter(array_map('trim', preg_split('/\r?\n/', (string) ($f['options'] ?? '')))) as $opt)
                                        <option value="{{ $opt }}" {{ $old === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input id="{{ $name }}-{{ $section->id }}" type="{{ in_array($type, ['email', 'tel', 'number', 'date']) ? $type : 'text' }}" name="{{ $name }}" value="{{ $old }}" {{ $req ? 'required' : '' }}>
                            @endif
                        @endif
                        @error($name)<div class="cms-form-error">{{ $message }}</div>@enderror
                    </div>
                @endforeach
                <button type="submit" class="cms-btn" style="border:0;cursor:pointer;">{{ $data['button_label'] ?? '' ?: 'Submit' }}</button>
            </form>
        @endif
    </div>
</section>
