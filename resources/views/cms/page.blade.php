@extends('layouts.country')

@section('content')
    @foreach($page->sections->where('is_visible', true) as $section)
        @php $sectionView = 'cms.sections.' . $section->type; @endphp
        @if(View::exists($sectionView))
            @include($sectionView, ['section' => $section, 'data' => $section->content ?? [], 'country' => $country])
        @endif
    @endforeach
@endsection
