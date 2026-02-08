@extends('admin.layouts.app')

@section('title', 'Events List')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Events List</h4>

        <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
            + Add New Event
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Duration</th>
                            <th>Price</th>
                            <th>Bookings</th>
                            <th>Available Dates</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($events as $event)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>{{ $event->title }}</td>

                            <td>
                              <a target="_blank" href="{{ route('events.show.public', ['event' => $event->slug]) }}">
                                <i class="fa fa-external-link-square"></i>
                              </a>
                            </td>

                            <td>{{ $event->duration }} min</td>

                            <td>₹{{ number_format($event->price, 2) }}</td>

                            <td class="text-center">
                              {{ $event->bookings_count }}
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($event->available_from_date)->format('d M Y') }}
                                →
                                {{ \Carbon\Carbon::parse($event->available_to_date)->format('d M Y') }}
                            </td>



                            <td class="text-end">

                                <a href="{{ route('admin.events.edit', $event->id) }}"
                                   class="btn btn-sm btn-warning">
                                    Edit
                                </a>

                                <form action="{{ route('admin.events.destroy', $event->id) }}"
                                      method="POST"
                                      class="d-inline-block"
                                      onsubmit="return confirm('Are you sure you want to delete this event?');">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-danger">
                                        Delete
                                    </button>
                                </form>

                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                No events found.
                            </td>
                        </tr>
                        @endforelse

                    </tbody>

                </table>
            </div>
        </div>
    </div>

    @if(method_exists($events, 'links'))
    <div class="mt-3">
        {{ $events->links() }}
    </div>
    @endif

</div>

@endsection

@push('scripts')
@endpush
