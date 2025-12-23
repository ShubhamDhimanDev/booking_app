@extends('admin.layout.app')

@section('title', 'Edit Profile')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <h4>Edit Profile</h4>

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                            class="form-control" />
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                            class="form-control" />
                    </div>

                   {{--  <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control">
                    </div> --}}

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>

                    <button class="btn btn-primary">Save</button>
                </form>

                <hr />
                <form method="POST" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger mt-3">Delete Account</button>
                </form>
            </div>
        </div>
    </div>
@endsection
