@extends('admin.master')

@section('title')
    Raport
@stop

@section('content')
    <div id="page-wrapper" class="container-fluid">
        {!! html()->form('GET', url('admin/report'))->open() !!}
        <div class="row">
            <div class="col-lg-12">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="page-header mb-0">Wyniki</h1>
                  {!! html()
                      ->submit('Powrót')
                      ->id('submitButton1')
                      ->class('btn btn-primary button-menu') !!}
                </div>
            </div>
        </div>
    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="d-flex justify-content-end">
              <button type="button"
                  class="btn btn-brown button-menu btn-icon-left my-2"
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
                   Kategoria: {{ $index }} ( par: {{ $Numbers[$index] }} )<br/>
                 </div>
                  <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover text-center table-pad-2px font-print-18pt">
                        <thead>
                           <tr>
                              <th class="text-center font-14pt">Miejsce</th>
                              <th class="text-center font-14pt">Numery par</th>
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

        <div class="row">
            <div class="col-lg-12">
                <div class="pull-right">
                  {!! html()->submit('Powrót')->id('submitButton1')->class('btn btn-primary button-menu') !!}
                </div>
            </div>
        </div>

        {!! html()->form()->close() !!}

        <div class="row">
            <div class="col-lg-12">
               <div class="pull-right">
                  {!! html()->button('Drukuj', 'button')
                        ->class('btn button-menu btn-brown')
                        ->attribute('onclick','window.print()') !!}
               </div>
            </div>
        </div>
    </div>
@stop

@section('customScripts')
    <script>
    </script>
@stop
