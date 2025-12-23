<!-- resources/views/auth/register.blade.php -->
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Register</title>
    <link rel="stylesheet" href="{{ asset('admins/assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('admins/assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('admins/assets/css/dark-overrides.css') }}">
    <link rel="shortcut icon" href="{{ asset('admins/assets/images/favicon.ico') }}" />
  </head>
  <body>
    <div class="container-scroller">
      <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-center auth px-0">
          <div class="row w-100 mx-0 justify-content-center">
            <div class="col-lg-5 col-md-6">
              <div class="card shadow-sm">
                <div class="card-body">
                  <div class="text-center mb-3">
                    <img src="{{ asset('admins/assets/images/logo.png') }}" alt="logo" style="max-height: 60px;">
                  </div>
                  <h4 class="text-center mb-1">Create your account</h4>
                  <p class="text-center text-muted mb-4">Register to continue.</p>

                  @if($errors->any())
                    <div class="alert alert-danger">
                      <ul class="mb-0">
                        @foreach($errors->all() as $error)
                          <li>{{ $error }}</li>
                        @endforeach
                      </ul>
                    </div>
                  @endif

                  <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="mb-3">
                      <label for="name" class="form-label">Name</label>
                      <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-3">
                      <label for="email" class="form-label">Email</label>
                      <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>

                    <div class="mb-3">
                      <label for="phone" class="form-label">Phone (optional)</label>
                      <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>

                    <div class="mb-3">
                      <label for="password" class="form-label">Password</label>
                      <input type="password" id="password" name="password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                      <label for="password_confirmation" class="form-label">Confirm Password</label>
                      <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                    </div>

                    <div class="d-grid">
                      <button type="submit" class="btn btn-primary px-4">Register</button>
                    </div>
                  </form>

                  <div class="text-center mt-3">
                    <a href="{{ route('login') }}">Already have an account? Sign in</a>
                    <span class="text-muted mx-2">|</span>
                    <a href="{{ route('password.request') }}">Forgot password?</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="{{ asset('admins/assets/vendors/js/vendor.bundle.base.js') }}"></script>
  </body>
</html>
