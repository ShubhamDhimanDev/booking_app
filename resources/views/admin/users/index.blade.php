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

    {{-- Filters --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Name, username, or email" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" id="role" class="form-select">
                        <option value="">All Roles</option>
                        @foreach($roles as $roleName)
                            <option value="{{ $roleName }}" {{ request('role') == $roleName ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('-', ' ', $roleName)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'role']))
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
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

                        @php $i = ($users->currentPage() - 1) * $users->perPage() + 1; @endphp

                        @forelse ($users as $user)
                        <tr>
                            <td>{{ $i++ }}</td>

                            <td>{{ $user->name }}</td>

                            <td>{{ $user->username ?? '-' }}</td>

                            <td>{{ $user->email }}</td>

                            <td>
                                @foreach($user->roles as $r)
                                    <span class="badge bg-secondary text-white">{{ $r->name }}</span>
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
