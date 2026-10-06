@extends('admin.layouts.app')

@section('title', 'Submission #' . $submission->id)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">{{ $submission->form_name }} <small class="text-muted">#{{ $submission->id }}</small></h4>
        <a href="{{ route('admin.submissions.index') }}" class="btn btn-light">Back</a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <dl class="row mb-0">
                @foreach($submission->data as $d)
                    <dt class="col-sm-3">{{ $d['label'] }}</dt>
                    <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $d['value'] !== '' ? $d['value'] : '—' }}</dd>
                @endforeach
            </dl>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body small text-muted">
            Submitted {{ $submission->created_at->format('d M Y H:i') }}
            · {{ optional($submission->country)->name }} / {{ optional($submission->page)->title }}
            · IP {{ $submission->ip }}
        </div>
    </div>
</div>
@endsection
