<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>@yield('title', 'Admin Login')</title>

    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.css') }}" rel="stylesheet">
    <link href="{{ asset('css/metisMenu.min.css') }}" rel="stylesheet">
    
    <link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet">

    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/metisMenu.min.js') }}"></script>
    <script src="{{ asset('js/sb-admin-2.js') }}"></script>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn’t work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script type="text/javascript">
        $("#message").show();
        setTimeout(function() { $("#message").fadeOut(); }, 5000);
    </script>
</head>


<body class="d-flex align-items-center justify-content-center vh-100">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header text-center">
                        <h3 class="mb-0">PTT Admin Login</h3>
                    </div>
                    <div class="card-body">

                        {{ html()->form('POST', url('admin/login'))->open() }}

                        <p class="text-danger">{{ $errors->first('message') }}</p>

                        @if (Session::has('flash_message'))
                            <p class="text-success">{{ Session::get('flash_message') }}</p>
                        @endif

                        <div class="mb-3">
                            {{ html()->text('username')
                                ->value(old('username'))
                                ->class('form-control')
                                ->placeholder('Login')
                                ->required()
                                ->autofocus() }}
                        </div>

                        <div class="mb-3">
                            {{ html()->password('password')
                                ->class('form-control')
                                ->placeholder('Hasło')
                                ->required() }}
                        </div>

                        {{ html()->submit('Login')->class('btn btn-success w-100') }}

                        {{ html()->form()->close() }}

                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>