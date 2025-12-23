<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Link Google Account</title>

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
    <link rel="shortcut icon" href="{{ asset('admins/assets/images/favicon.png') }}" />
  </head>

  <body>
    <div class="container-scroller">
      <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-center auth px-0">
          <div class="row w-100 mx-0">
            <div class="col-lg-5 mx-auto">

              <div class="auth-form-light text-left py-5 px-4 px-sm-5">

                <div class="brand-logo text-center mb-2">
                  <img src="{{ asset('admins/assets/images/logo.svg') }}" alt="logo">
                </div>

                <h3 class="text-center text-primary fw-bold">Just one more step!</h3>
                <h1 class="text-center fw-bold mt-1" style="font-size: 26px;">
                  Link Your Google Calendar
                </h1>

                <p class="text-center mt-2">
                  This will allow us to generate Google Meet links and automatically add
                  your bookings to your calendar.
                </p>

                <div class="text-center mt-3">
                  <img
                    src="/images/google-calendar.svg"
                    alt="Google Calendar"
                    style="width: 96px; height: 96px;"
                  >
                </div>

                <div class="mt-4 d-grid gap-2">
                  <a
                    href="{{ route('admin.google.redirect') }}"
                    class="btn btn-primary btn-lg d-flex justify-content-center align-items-center fw-semibold"
                    style="font-size: 16px;"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke-width="1.5"
                         stroke="currentColor"
                         class="me-2"
                         style="width: 20px; height: 20px;"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/>
                    </svg>
                    Link Google Account
                  </a>
                </div>

              </div>

            </div>
          </div>
        </div>
        <!-- content-wrapper ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>

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
