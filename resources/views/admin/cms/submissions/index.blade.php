@extends('admin.layouts.app')

@section('title', 'Form Submissions')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Form Submissions</h4>
        <a href="{{ route('admin.submissions.export', request()->query()) }}" class="btn btn-outline-primary">Export CSV</a>
    </div>

    @include('admin.cms.partials.alert')

    <form method="GET" class="mb-3 d-flex align-items-center gap-2 flex-wrap">
        <select name="form" class="form-select" style="max-width: 260px;" onchange="this.form.submit()">
            <option value="">All forms</option>
            @foreach($forms as $f)
                <option value="{{ $f->page_section_id }}" {{ (string) request('form') === (string) $f->page_section_id ? 'selected' : '' }}>{{ $f->form_name }}</option>
            @endforeach
        </select>
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" style="max-width: 260px;" placeholder="Search answers...">
        <button class="btn btn-primary">Search</button>
        <a href="{{ route('admin.submissions.index') }}" class="btn btn-light">Reset</a>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Form</th>
                        <th>Country / Page</th>
                        <th>Answers</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $s)
                        <tr>
                            <td class="text-nowrap">
                                {{ $s->created_at->format('d M Y H:i') }}
                                @unless($s->read_at) <span class="badge bg-primary ms-1">New</span> @endunless
                            </td>
                            <td>{{ $s->form_name }}</td>
                            <td>{{ optional($s->country)->name }} / {{ optional($s->page)->title }}</td>
                            <td class="text-muted small">
                                {{ \Illuminate\Support\Str::limit(collect($s->data)->map(fn ($d) => $d['value'])->filter()->implode(' · '), 90) }}
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.submissions.show', $s) }}" class="btn btn-sm btn-outline-primary">View</a>
                                <form action="{{ route('admin.submissions.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this submission?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No submissions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $submissions->links() }}</div>
</div>
@endsection
