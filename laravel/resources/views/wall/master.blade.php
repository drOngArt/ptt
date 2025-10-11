<!DOCTYPE html>
<html lang="pl">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="program">
    <meta name="author" content="Ar2r.D">

    <title>@yield('title')</title>
    {!! HTML::style('css/bootstrap.min.css') !!}
    {!! HTML::style('css/sb-wall.css') !!}
    {!! HTML::style('css/font-awesome.min.css') !!}
    
    
    {!! HTML::script('js/jquery-2.1.3.min.js') !!}
    {!! HTML::script('js/bootstrap.min.js') !!}    
    {!! HTML::script('js/metisMenu.min.js') !!}    
    
    {!! HTML::script('js/underscore-min.js') !!}
    {!! HTML::script('js/wallCss.js') !!}
    
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