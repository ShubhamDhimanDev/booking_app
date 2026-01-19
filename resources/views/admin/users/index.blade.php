@extends('admin.layouts.app')

@section('title', 'Users')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Users</h4>

        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            + Add New User
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($users as $user)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>{{ $user->name }}</td>

                            <td>{{ $user->username ?? '-' }}</td>

                            <td>{{ $user->email }}</td>

                            <td>
                                @foreach($user->roles as $r)
                                    <span class="badge bg-secondary">{{ $r->name }}</span>
                                @endforeach
                            </td>

                            <td class="text-end">

                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                   class="btn btn-sm btn-warning">
                                    Edit
                                </a>

                                <form action="{{ route('admin.users.destroy', $user->id) }}"
                                      method="POST"
                                      class="d-inline-block"
                                      onsubmit="return confirm('Are you sure you want to delete this user?');">

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
                            <td colspan="6" class="text-center py-4 text-muted">
                                No users found.
                            </td>
                        </tr>
                        @endforelse

                    </tbody>

                </table>
            </div>
        </div>
    </div>

    @if(method_exists($users, 'links'))
    <div class="mt-3">
        {{ $users->links() }}
    </div>
    @endif

</div>

@endsection

@push('scripts')
@endpush
