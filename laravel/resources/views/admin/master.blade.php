<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>

    <link href="{{ asset('css/PTT.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.css') }}" rel="stylesheet">
    <link href="{{ asset('css/dragtable.min.css') }}" rel="stylesheet">

    <header class="onlyprint">{{ $tournamentName }}</header>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light rounded-3" role="navigation" style="margin-bottom: 1; background-color: #F0F8FF;">
    <div class="container-fluid">
        <a class="navbar-brand fst-italic" href="{{ $baseURI }}/admin">{{ $tournamentName }}</a>
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarTopLinks"
                aria-controls="navbarTopLinks"
                aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarTopLinks">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-cog fa-spin"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ $baseURI }}/admin/password/{{ $adminId }}">
                              <i class="fa fa-user fa-fw"></i> Zmiana hasła</a>
                        </li>
                        <li><a class="dropdown-item" href="{{ $baseURI }}/admin/utils/{{ $adminId }}">
                              <i class="fa fa-info-circle fa-fw"></i> Informacje</a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                          <a class="dropdown-item" href="{{ url($baseURI.'/admin/logout') }}"
                             onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fa fa-sign-out fa-fw"></i> Wyloguj</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="d-flex">
    <nav class="sidebar flex-shrink-0 rounded-3" role="navigation">
        <div class="sidebar-sticky">
            <ul class="nav flex-column pt-4">
                <li class="nav-item">
                    <a class="nav-link mx-3 my-2" href="{{ $baseURI }}/admin"><span class="icon-wrap"><i class="fa fa-user-times fa-fw"></i><span class="border-left fs-6">Sędziowie</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link mx-3 my-2" href="{{ $baseURI }}/admin/program"><span class="icon-wrap"><i class="fa fa-calendar fa-fw"></i><span class="border-left fs-6">Program turnieju</span></a>
                    <!--<a class="nav-link m-3 my-2" href="{{ $baseURI }}/admin/program"><i class="fa fa-calendar fa-fw"></i>Program turnieju</a> -->
                </li>
                <li class="nav-item">
                    <a class="nav-link mx-3 my-2" href="{{ $baseURI }}/admin/round"><span class="icon-wrap"><i class="fa fa-check fa-fw"></i><span class="border-left fs-6">Aktualna runda</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link mx-3 my-2" href="{{ $baseURI }}/admin/report"><span class="icon-wrap"><i class="fa fa-bar-chart fa-fw"></i><span class="border-left fs-6">Raporty / Wyniki</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link mx-3 my-2" href="{{ $baseURI }}/admin/chooseTournament"><span class="icon-wrap"><i class="fa fa-database fa-fw"></i><span class="border-left fs-6">Zmiana turnieju</span></a>
                </li>
            </ul>
        </div>
    </nav>

    <main class="flex-grow-1 p-3" id="page-wrapper">
        @yield('content')
    </main>
</div>

<!-- Skrypty -->
<script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>

<script src="{{ asset('js/sb-admin-2.js') }}"></script>
<script src="{{ asset('js/bootbox.min.js') }}"></script>
<script src="{{ asset('js/jquery-ui.min.js') }}"></script>
@yield('customScripts')
</body>
</html>
