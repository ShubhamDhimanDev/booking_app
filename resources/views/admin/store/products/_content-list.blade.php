<div class="pc-block mb-4">
    <label class="form-label font-weight-bold">{{ $meta[0] }}</label>
    @if($meta[1])<div class="form-text mb-2">{{ $meta[1] }}</div>@endif
    <div class="pc-items">
        @foreach($items as $value){!! $listRow("content[$key]", $value) !!}@endforeach
    </div>
    <template>{!! $listRow("content[$key]", '') !!}</template>
    <button type="button" class="btn btn-sm btn-outline-primary" data-add>+ Add</button>
</div>
