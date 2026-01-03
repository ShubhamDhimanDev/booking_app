<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title', 'MeetFlow')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="{{ asset('admins/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admins/assets/js/select.dataTables.min.css') }}">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="{{ asset('admins/assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('admins/assets/vendors/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admins/assets/vendors/select2-bootstrap-theme/select2-bootstrap.min.css') }}">
    <!-- endinject -->
    <link rel="shortcut icon" href="{{ asset('admins/assets/images/favicon.ico') }}" />

    <!-- Dark theme overrides loaded from dark-overrides.css -->
    <link rel="stylesheet" href="{{ asset('admins/assets/css/dark-overrides.css') }}">
    <link rel="stylesheet" href="{{ asset('admins/assets/css/theme-overrides.css') }}">
    <style>
        .form-check .form-check-input {
            margin: 1px 5px;
        }
    </style>

    @stack('styles')
</head>

<body class="with-welcome-text">
    <div class="container-scroller">
        <!-- partial:partials/_navbar.html -->
        @include('layouts.navbar')
        <!-- partial -->
        <div class="container-fluid page-body-wrapper">
            <!-- partial:partials/_sidebar.html -->
            @include('layouts.sidebar')
            <!-- partial -->
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="row">
                        <div class="col-sm-12">
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <strong>Error:</strong> Please fix the following issues:
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if (session('alert_message'))
                                <div class="alert alert-{{ session('alert_type', 'info') }} alert-dismissible fade show"
                                    role="alert">
                                    <strong>{{ ucfirst(session('alert_type', 'Info')) }}: </strong>
                                    {{ session('alert_message') }}

                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif
                            @yield('content')
                        </div>
                    </div>
                </div>
                <!-- content-wrapper ends -->
                <!-- partial:partials/_footer.html -->
                <footer class="footer">
                    <div class="d-sm-flex justify-content-center justify-content-sm-between">
                        <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Visit <a
                                href="#" target="_blank">Booking Portal</a></span>
                        <span class="float-none float-sm-end d-block mt-1 mt-sm-0 text-center">Copyright ©
                            {{ date('Y') }}. All rights reserved.</span>
                    </div>
                </footer>
                <!-- partial -->
            </div>
            <!-- main-panel ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="{{ asset('admins/assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('admins/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="{{ asset('admins/assets/vendors/chart.js/chart.umd.js') }}"></script>
    <script src="{{ asset('admins/assets/vendors/progressbar.js/progressbar.min.js') }}"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="{{ asset('admins/assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('admins/assets/js/template.js') }}"></script>
    <script src="{{ asset('admins/assets/js/settings.js') }}"></script>
    <script src="{{ asset('admins/assets/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('admins/assets/js/todolist.js') }}"></script>
    <!-- endinject -->
    <!-- Custom js for this page-->
    <script src="{{ asset('admins/assets/js/jquery.cookie.js') }}" type="text/javascript"></script>
    <script src="{{ asset('admins/assets/js/dashboard.js') }}"></script>
    <!-- <script src="{{ asset('admins/assets/js/Chart.roundedBarCharts.js') }}"></script> -->
    <!-- End custom js for this page-->

    <script src="{{ asset('admins/assets/vendors/select2/select2.min.js') }}"></script>
    <script src="{{ asset('admins/assets/js/select2.js') }}"></script>
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>

    @stack('scripts')
</body>

</html>
