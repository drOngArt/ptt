<!DOCTYPE html>
<html lang="pl">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="program">
    <meta name="author" content="Ar2r.D">

    <title>@yield('title')</title>
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/css/sb-wall.css') }}" rel="stylesheet">
    
    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/bootbox.min.js') }}"></script>
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    
    <script src="{{ asset('js/underscore-min.js') }}"></script>
    <script src="{{ asset('js/wallCss.js') }}"></script>
    
</head>

<body>

<div id="wrapper">

    <!-- Navigation -->
    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
            <a class="navbar-brand" href="{{$baseURI}}/{{$wallPrefix}}"><b>{{$tournamentName}}</b></a>
        </div>
        <!-- /.navbar-header -->

    </nav>

    @yield('content')
    @yield('customScripts')
</div>
<!-- /#wrapper -->
<!--[if lt IE 8]>
<script src="js/jquery-1.11.2.min.js">
<![endif]-->

@yield('additionalResources')



</body>

</html> 