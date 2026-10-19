<div class="pc-block mb-4">
    <label class="form-label font-weight-bold">{{ $meta[0] }}</label>
    <div class="pc-items">
        @foreach($items as $i => $item){!! $rowFields($key, $meta[1], $meta[2], $i, $item) !!}@endforeach
    </div>
    <template>{!! $rowFields($key, $meta[1], $meta[2], '__i__', []) !!}</template>
    <button type="button" class="btn btn-sm btn-outline-primary" data-add>+ Add</button>
</div>
