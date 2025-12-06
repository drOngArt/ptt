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
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
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


<body>

    <div class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">PTT Admin Login</h3>
                    </div>
                    <div class="panel-body">
                        {{ html()->form('POST', url('admin/login'))->open() }}
                        <p class="bg-danger">{{ $errors->first('message') }}</p>

                        @if (Session::has('flash_message'))
                            <p id="message" class="bg-success">{{ Session::get('flash_message') }}</p>
                        @endif

                        <div class="form-group">
                            {{ html()->label('username', 'Login')->class('sr-only') }}
                            {{ html()->text('username')
                            ->value(old('username'))
                            ->class('form-control')
                            ->placeholder('Login')
                            ->required()
                            ->autofocus() }}
                        </div>
                        <div class="form-group">
                            {{ html()->label('password', 'Hasło')->class('sr-only') }}
                            {{ html()->password('password')
                                ->class('form-control')
                                ->placeholder('Hasło')
                                ->required() }}
                        </div>
                        {{ html()->submit('Login')->class('btn btn-lg btn-success btn-block') }}
                        {{ html()->form()->close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>