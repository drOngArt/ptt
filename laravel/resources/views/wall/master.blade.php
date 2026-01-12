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
    <link href="{{ asset('css/sb-wall.css') }}" rel="stylesheet">
    
    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/bootbox.min.js') }}"></script>
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    
    <script src="{{ asset('js/underscore-min.js') }}"></script>
    <script src="{{ asset('js/wallCss.js') }}"></script>
    
</head>

<body class="wall-body">

  <div id="wrapper">
    <nav class="navbar navbar-expand navbar-dark bg-dark fixed-top">
      <div class="container-fluid">
        <a class="navbar-brand" href="{{ $baseURI }}/{{ $wallPrefix }}">
          <b>{{ $tournamentName }}</b>
        </a>
      </div>
    </nav>
    {{-- treść pod fixed navbar --}}
    <main class="container-fluid wall-content">
      @yield('content')
    </main>
    @yield('customScripts')
  </div>
  @yield('additionalResources')
</body>

</html> 