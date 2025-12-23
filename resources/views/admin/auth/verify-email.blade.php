<!-- resources/views/admin/auth/verify-email.blade.php -->
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Verify Email</title>
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
                  <h4 class="text-center mb-2">Verify your email</h4>
                  <p class="text-center text-muted">We've sent a verification link to your email. Please click the link to verify your account.</p>

                  @if(session('status') === 'verification-link-sent')
                    <div class="alert alert-success">A new verification link has been sent to your email address.</div>
                  @endif

                  <form method="POST" action="{{ route('verification.send') }}" class="d-grid gap-2">
                    @csrf
                    <button type="submit" class="btn btn-primary">Resend Verification Email</button>
                  </form>

                  <hr>
                  <form method="POST" action="{{ route('logout') }}" class="d-grid gap-2 mt-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary">Logout</button>
                  </form>

                  <div class="text-center mt-3">
                    <a href="{{ route('login') }}">Back to login</a>
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
