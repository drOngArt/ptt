@extends('admin.master')

@section('title')
    Raport
@stop

@section('content')
<div id="page-wrapper" class="container-fluid">

    {{ html()->form('GET', url('admin/report'))->open() }}

    <div class="row mb-3">
        <div class="page-header-break">
           RAPORT TAŃCZĄCYCH W RÓŻNYCH KLASACH<br/>
        </div>
        <div class="col-12 d-flex align-items-center">
          <h1 class="page-header mb-0">Startujący w różnych klasach.</h1>
          <div class="ms-auto d-flex gap-2">
            {{ html()
              ->submit('Powrót')
              ->id('submitButton1')
              ->class('btn btn-primary button-menu') }}
            <button type="button"
                  class="btn btn-brown button-menu btn-icon-left"
                  onclick="window.print()">
                <i class="fa fa-print"></i>
                <span class="button-menu-sep"></span>
                <span>Drukuj</span>
            </button>
          </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">

            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover text-center table-pad-2px">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:10%">Lp.</th>
                            <th class="text-center" style="width:15%">Para</th>
                            <th style="width:75%">Style</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php $idx = 0; @endphp

                        @foreach($couples as $number => $description)
                            <tr>
                                <td class="btn-circle">{{ $idx + 1 }}.</td>
                                @php $idx++; @endphp

                                <td class="text-center font-12pt">{{ $number }}</td>
                                <td class="text-start font-12pt">{{ $description }}</td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

        </div>
    </div>

    {{ html()->form()->close() }}

</div>
@stop

@section('customScripts')
<script></script>
@stop
