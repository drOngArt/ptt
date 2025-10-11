@extends('admin.master')

@section('title')
    Aktualna runda
@stop

@section('content')
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Pomoc</h1>
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!-- /.row -->
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Lista rund z bazy turnieju
                    </div>
                    <div class="panel-body">
                        @foreach($rounds as $round)
                            {{$round->roundName}} {{$round->categoryName}} {{$round->className}} {{$round->styleName}}<br>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
