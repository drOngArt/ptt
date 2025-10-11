<!DOCTYPE html>
<html lang="pl">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>@yield('title')</title>
    {!! HTML::style('css/PTT.css') !!}
    {!! HTML::style('css/bootstrap.min.css') !!}
    {!! HTML::style('css/font-awesome.min.css') !!}
    {!! HTML::style('css/sb-admin-2.css') !!}
    {!! HTML::style('css/dragtable.min.css') !!}
    
    <header class="onlyprint">{{$tournamentName}}</header>
</head>

<body>

<div id="wrapper">

    <!-- Navigation -->

    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{$baseURI}}/admin">{{$tournamentName}}</a>
        </div>
        <!-- /.navbar-header -->

        <ul class="nav navbar-top-links navbar-right">
            </li>
            <!-- /.dropdown -->
            <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                    <i class="fa fa-cog fa-spin fa-fw"></i>  <i class="fa fa-caret-down"></i>
                </a>
                <ul class="dropdown-menu dropdown-user">
                    <li><a href="{{$baseURI}}/admin/password/{{$adminId}}"><i class="fa fa-user fa-fw"></i> Zmiana hasła</a>
                    </li>
                    <li><a href="{{$baseURI}}/admin/utils/{{$adminId}}"><i class="fa fa-info-circle fa-fw"></i> Informacje</a>
                    </li>
                    <li class="divider"></li>
                    <li><a href="{{$baseURI}}/admin/logout"><i class="fa fa-sign-out fa-fw"></i> Wyloguj</a>
                    </li>
                </ul>
                <!-- /.dropdown-user -->
            </li>
            <!-- /.dropdown -->
        </ul>
        <!-- /.navbar-top-links -->

        <div class="navbar-default sidebar" role="navigation">
            <div class="sidebar-nav navbar-collapse">
                <ul class="nav" id="side-menu">
                    <li>
                        <a href="{{$baseURI}}/admin"><i class="fa fa-user-times fa-fw"></i> Sędziowie</a>
                    </li>
                </ul>
                <ul class="nav" id="side-menu">
                    <li>
                        <a href="{{$baseURI}}/admin/program"><i class="fa fa-calendar fa-fw"></i> Program turnieju</a>
                    </li>
                </ul>
                <ul class="nav" id="side-menu">
                    <li>
                        <a href="{{$baseURI}}/admin/round"><i class="fa fa-check fa-fw"></i> Aktualna runda</a>
                    </li>
                </ul>
                <ul class="nav" id="side-menu">
                    <li>
                        <a href="{{$baseURI}}/admin/report"><i class="fa fa-bar-chart fa-fw"></i> Raporty/Wyniki</a>
                    </li>
                </ul>
                <ul class="nav" id="side-menu">
                    <li>
                        <a href="{{$baseURI}}/admin/chooseTournament"><i class="fa fa-database fa-fw"></i> Zmiana turnieju</a>
                    </li>
                </ul>
            </div>
            <!-- /.sidebar-collapse -->
        </div>
        <!-- /.navbar-static-side -->
    </nav>

    @yield('content')

</div>
<!-- /#wrapper -->

{!! HTML::script('js/jquery-2.1.3.min.js') !!}
{!! HTML::script('js/bootstrap.min.js') !!}
{!! HTML::script('js/metisMenu.min.js') !!}
{!! HTML::script('js/sb-admin-2.js') !!}
{!! HTML::script('js/bootbox.min.js') !!}
@yield('customScripts')

</body>

</html> 