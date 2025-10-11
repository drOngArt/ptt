<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Host/Prezentacja Login</title>
    {!! HTML::style('css/bootstrap.min.css') !!}
    {!! HTML::style('css/sb-admin-2.css') !!}
    {!! HTML::style('css/metisMenu.min.css') !!}
    {!! HTML::style('css/font-awesome.min.css') !!}

    {!! HTML::script('js/jquery-2.1.3.min.js') !!}
    {!! HTML::script('js/bootstrap.min.js') !!}
    {!! HTML::script('js/metisMenu.min.js') !!}
    {!! HTML::script('js/sb-admin-2.js') !!}

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
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
                        <h3 class="panel-title">PTT Host/Prezentacja</h3>
                    </div>
                    <div class="panel-body">
                        {!! Form::open(array('url' => 'wall/login')) !!}
                            <p class="bg-danger">{{ $errors->first('message') }}</p>
                            @if (Session::has('flash_message'))
                                <p id="message" class="bg-success">{{ Session::get('flash_message') }}</p>
                            @endif
                            <div class="form-group">
                            {!! Form::label('username', 'Login',  array('class' => 'sr-only')) !!}
                            {!! Form::text('username', '', array('class' => 'form-control', 'placeholder' => 'Login', 'required' => 'true', 'autofocus' => 'true')) !!}
                            </div>
                            <div class="form-group">
                            {!! Form::label('password', 'Hasło',  array('class' => 'sr-only')) !!}
                            {!! Form::password('password', array('class' => 'form-control', 'placeholder' => 'Hasło', 'required' => 'true')) !!}
                            </div>
                            {!! Form::submit('Login', array('class' => 'btn btn-lg btn-success btn-block')) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>