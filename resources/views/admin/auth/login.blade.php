<!-- resources/views/admin/auth/login.blade.php -->
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Star Admin2 — Admin Login</title>

    <!-- plugins:css -->
    <link rel="stylesheet" href="{{ asset('admins/assets/vendors/feather/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('admins/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admins/assets/vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('admins/assets/vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admins/assets/vendors/typicons/typicons.css') }}">
    <link rel="stylesheet" href="{{ asset('admins/assets/vendors/simple-line-icons/css/simple-line-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('admins/assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('admins/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
    <!-- endinject -->
    <!-- inject:css -->
    <link rel="stylesheet" href="{{ asset('admins/assets/css/style.css') }}">
    <!-- endinject -->
    <link rel="shortcut icon" href="{{ asset('admins/assets/images/favicon.ico') }}" />
    <link rel="stylesheet" href="{{ asset('admins/assets/css/dark-overrides.css') }}">
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
                    <img src="{{ asset('admins/assets/images/AC-Logo_1-8.png') }}" alt="logo" style="max-height: 60px;">
                  </div>

                  <h4 class="text-center mb-1">Hello! let's get started</h4>
                  <p class="text-center text-muted mb-4">Sign in to continue.</p>

                  {{-- global status / errors --}}
                  @if(session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                  @endif

                  @if($errors->any())
                    <div class="alert alert-danger">
                      <ul class="mb-0">
                        @foreach($errors->all() as $error)
                          <li>{{ $error }}</li>
                        @endforeach
                      </ul>
                    </div>
                  @endif

                  {{-- main form --}}
                  <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                      <label for="login" class="form-label">Email or Username</label>
                      <input
                        type="text"
                        name="login"
                        id="login"
                        class="form-control @error('login') is-invalid @enderror"
                        value="{{ old('login') }}"
                        required
                        autofocus
                      >
                      @error('login')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>

                    <div class="mb-3">
                      <label for="password" class="form-label">Password</label>
                      <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control @error('password') is-invalid @enderror"
                        required
                      >
                      @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <div class="form-check">
                        <input
                          type="checkbox"
                          name="remember"
                          id="remember"
                          class="form-check-input"
                          value="1"
                          {{ old('remember') ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="remember">Keep me signed in</label>
                      </div>
                    </div>

                    <div class="d-grid mb-2">
                      <button type="submit" class="btn btn-primary px-4">SIGN IN</button>
                    </div>

                    <div class="d-grid">
                      <a href="{{ route('admin.google.redirect') }}" class="btn btn-outline-danger">
                        <i class="fa fa-google" aria-hidden="true"></i> Sign in with Google
                      </a>
                    </div>

                  </form>

                  <div class="text-center mt-3">
                    <a href="{{ route('password.request') }}">Forgot password?</a>
                    <span class="text-muted mx-2">|</span>
                    <a href="{{ route('register') }}">Create an account</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- content-wrapper ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->

    <!-- plugins:js -->
    <script src="{{ asset('admins/assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('admins/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
    <!-- endinject -->

    <!-- inject:js -->
    <script src="{{ asset('admins/assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('admins/assets/js/template.js') }}"></script>
    <script src="{{ asset('admins/assets/js/settings.js') }}"></script>
    <script src="{{ asset('admins/assets/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('admins/assets/js/todolist.js') }}"></script>
    <!-- endinject -->
  </body>
</html>
