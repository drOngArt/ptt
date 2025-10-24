<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Wybór turnieju</title>
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
        /*$(document).ready(function(){
            $('#tournamentDirectoryFile').change(function(){
                this.form.submit();
            });
        });*/
    </script>
</head>

<body>

<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            {!! Form::open(array('url' => 'admin/chooseTournament', 'enctype' => 'multipart/form-data')) !!}
            <div class="login-panel panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Wybierz turniej</h3>
                </div>
                <div class="panel-body">
                    <p class="bg-danger">{{ $errors->first('message') }}</p>
                    <div class="form-group">
                    Wybierz plik TurniejDir.txt z katalogu programu Turniej
                    {!! Form::file('tournamentDirectoryFile') !!} {!! csrf_field() !!}
                    </div>
                    <div class="form-group">
                    lub wpisz ścieżkę do katalogu z bazą turnieju
                    {!! Form::text('tournamentDirectoryPath', '', array('class' => 'form-control', 'placeholder' => 'scieżka do katalogu')) !!}
                    </div>
                    <div class="form-group">                        
                        <div class="pull-left">
                           {!! link_to(URL::previous(), 'Anuluj', ['class' => 'btn btn-lg btn-warning button-menu']) !!}
                        </div>
                        <div class="pull-right">
                           {!! Form::submit('Wybierz', array('class' => 'btn btn-lg btn-primary button-menu')) !!}
                        </div>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>

</body>

</html>