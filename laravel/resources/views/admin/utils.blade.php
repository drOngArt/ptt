@extends('admin.master')

@section('title')
    Narzędzia
@stop

@section('content')
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Informacje</h1>
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!-- /.row -->
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                    Użytkownik: {{$user->username}} / adres IP:
                    <b><?php echo getHostByName(getHostName()); ?></b>
                    </div>
                    <div class="panel-heading">
                    Wersja: {{$version->version}} ({{$version->date}})
                    </div>
                    <div class="panel-heading">
                    Folder turnieju: {{$tournamentDirectory}} <br>Numer turnieju: {{$eventId}}
                    </div>
                    <div class="panel-heading">
                    Oceny sędziów: 
                    @if($votes) 
                        niemodyfikowane 
                    @else
                    <div class="alert-warning">
                        modyfikowane lub brak ocen
                    </div>
                    @endif
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Lista zamkniętych struktur z bazy turnieju
                    </div>
                    <div class="panel-body">
                        @foreach($rounds as $round)
                            {{$round->roundName}} {{$round->categoryName}} {{$round->className}} {{$round->styleName}}
                            @if($round->isAdditional)
                                {{$round->matchType}}
                            @endif
                            ( 
                            @foreach($round->dances as $dance)
                                {{$dance}}
                            @endforeach
                            )
                            @if($round->isClosed)
                            [zamknięta]
                            @endif
                            <!--{{$round->roundId}}-->
                            <br>
                        @endforeach
                    </div>
                </div>
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /#page-wrapper -->
@stop