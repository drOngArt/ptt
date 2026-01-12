<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Wybór turnieju</title>

    {{-- Bootstrap 5 + Twoje style --}}
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sb-admin-2.css') }}">
    <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">

    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    {{-- jeśli masz bundle dla BS5, lepiej użyć tego pliku --}}
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/sb-admin-2.js') }}"></script>
</head>

<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">

            {{-- formularz --}}
            {!! html()
                ->form('POST', url('admin/chooseTournament'))
                ->attribute('enctype', 'multipart/form-data')
                ->open() !!}

            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title h5 mb-0">Wybierz turniej</h3>
                </div>

                <div class="card-body">

                    {{-- komunikat błędu --}}
                    @if($errors->first('message'))
                        <div class="alert alert-danger py-2 mb-3">
                            {{ $errors->first('message') }}
                        </div>
                    @endif

                    {{-- wybór pliku TurniejDir.txt --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Wybierz plik <code>TurniejDir.txt</code> z katalogu programu „Turniej”
                        </label>
                        {!! html()->file('tournamentDirectoryFile')
                               ->class('form-control') !!}
                    </div>

                    {{-- ścieżka do katalogu --}}
                    <div class="mb-3">
                        <label for="tournamentDirectoryPath" class="form-label fw-semibold">
                            Lub wpisz ścieżkę do katalogu z bazą turnieju
                        </label>
                        {!! html()->text('tournamentDirectoryPath')
                               ->id('tournamentDirectoryPath')
                               ->class('form-control')
                               ->placeholder('ścieżka do katalogu') !!}
                    </div>

                </div>

                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between">
                        <a href="{{ url()->previous() }}"
                           class="btn btn-warning button-menu">
                            Anuluj
                        </a>

                        {!! html()->submit('Wybierz')
                               ->class('btn btn-primary button-menu') !!}
                    </div>
                </div>
            </div>

            {!! html()->form()->close() !!}

        </div>
    </div>
</div>

</body>
</html>
