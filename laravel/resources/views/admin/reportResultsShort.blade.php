@extends('admin.master')

@section('title')
    Raport
@stop

@section('content')
  <div id="page-wrapper" class="container-fluid">
    {!! html()->form('GET', url('admin/report'))->open() !!}

    <div class="row mb-3">
      <div class="col-12 d-flex align-items-center">
        <h1 class="page-header mb-0">Wyniki</h1>
  
        <div class="ms-auto d-flex gap-2">
          {!! html()
              ->submit('Powrót')
              ->id('submitButton1')
              ->class('btn btn-primary button-menu') !!}
  
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
        @foreach($couples as $index => $couple)
          <div class="div-no-break text-center">
            WYNIKI<br/>
            <div class="text-center h3">
              Kategoria: {{ $index }} ( prezentacji: {{ $Numbers[$index] }} )<br/>
            </div>
              <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover text-center table-pad-2px font-print-18pt">
                    <thead>
                      <tr>
                          <th class="text-center font-14pt">Miejsce</th>
                          <th class="text-center font-14pt">Numery</th>
                      </tr>
                    </thead>
                    <tbody>
                    @foreach($couple as $position => $numbers)
                      <tr>
                          <td class="text-center font-print-18pt" style="height:42px">{{ $position }}</td>
                          <td class="text-left font-print-18pt" style="height:42px">{{ $numbers }}</td>
                      </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
          </div>
        @endforeach
        </div>
    </div>
    {!! html()->form()->close() !!}
  </div>
@stop

@section('customScripts')
    <script>
    </script>
@stop
