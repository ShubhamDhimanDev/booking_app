{{-- Decorative yantra, shown when the product has no photo. --}}
<svg class="yp-yantra" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <g fill="none" stroke="#C9973A" stroke-width=".8">
        <circle cx="100" cy="100" r="92" stroke-width=".5" opacity=".4"/>
        <circle cx="100" cy="100" r="82" stroke-width=".4" opacity=".3"/>
        @for($a = 0; $a < 360; $a += 30)
            <ellipse cx="100" cy="20" rx="7" ry="18" opacity=".5" transform="rotate({{ $a }} 100 100)"/>
        @endfor
        <polygon points="100,30 165,145 35,145" fill="rgba(201,151,58,.08)"/>
        <polygon points="100,170 165,55 35,55" fill="rgba(201,151,58,.08)"/>
        <circle cx="100" cy="100" r="44"/>
        <circle cx="100" cy="100" r="32" stroke-width=".5"/>
        @for($a = 0; $a < 360; $a += 45)
            <ellipse cx="100" cy="72" rx="4" ry="10" opacity=".7" transform="rotate({{ $a }} 100 100)"/>
        @endfor
    </g>
    <circle cx="100" cy="100" r="5" fill="#C9973A" opacity=".9"/>
    <circle cx="100" cy="100" r="2" fill="#E8B85A"/>
    <rect x="6" y="6" width="188" height="188" rx="2" fill="none" stroke="#C9973A" stroke-width=".5" opacity=".3"/>
</svg>
