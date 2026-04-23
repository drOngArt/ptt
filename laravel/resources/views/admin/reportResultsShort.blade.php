@extends('admin.master')

@section('title')
    Raport
@stop

@section('content')
  <div id="page-wrapper" class="container-fluid">
    {!! html()->form('GET', url('admin/report'))->open() !!}

    <div class="row mb-3">
      <div class="col-12 d-flex align-items-center">
        <h1 class="page-header mb-1">Wyniki</h1>
  
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
        <div class="text-center fs-4">
            WYNIKI SKRÓCONE<br/>
        </div>

        <div class="base-rounds-wrapper mx-2">
        @foreach($couples as $index => $couple)
          <div class="table-responsive div-no-break mb-3">
            <table class="table table-striped table-bordered table-hover text-center table-pad-4px align-middle">
              <thead>
                <tr>
                  <th class="text-center fs-4" colspan="5">Kategoria: {{ $index }} ( Uczestników: {{ $Numbers[$index] }} )</th>
                </tr>
                <tr>
                    <th class="text-center font-print-18pt">Miejsce</th>
                    <th class="text-center font-print-18pt">Numery</th>
                </tr>
              </thead>
              <tbody>
                @foreach($couple as $position => $numbers)
                  <tr>
                    <td class="text-center font-print-18pt">{{ $position }}</td>
                    <td class="text-start font-print-18pt px-3">{{ $numbers }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
            </div>
        @endforeach
        </div>
      </div>
    </div>
    {!! html()->form()->close() !!}
  </div>
@stop

@section('customScripts')
    <script>
    </script>
@stop
