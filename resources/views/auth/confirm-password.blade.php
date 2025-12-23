<!-- resources/views/auth/confirm-password.blade.php -->
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Confirm Password</title>
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
                    <img src="{{ asset('admins/assets/images/AC-Logo_1-8.png') }}" alt="logo" style="max-height: 60px;">
                  </div>
                  <h4 class="text-center mb-1">Confirm your password</h4>
                  <p class="text-center text-muted mb-4">For security, please confirm your password to continue.</p>

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

                  <form method="POST" action="{{ route('password.confirm') }}">
                    @csrf

                    <div class="mb-3">
                      <label for="password" class="form-label">Password</label>
                      <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required autofocus>
                      @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>

                    <div class="d-grid mb-2">
                      <button type="submit" class="btn btn-primary px-4">Confirm</button>
                    </div>
                  </form>

                  <div class="text-center mt-3">
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
