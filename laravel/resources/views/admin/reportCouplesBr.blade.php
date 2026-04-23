@extends('admin.master')

@section('title')
    Raport
@stop

@section('content')
<div id="page-wrapper" class="container-fluid">

    {{ html()->form('GET', url('admin/report'))->open() }}

    <div class="row mb-3">
        <div class="page-header-break fs-5">
           RAPORT TAŃCZĄCYCH W RÓŻNYCH KATEGORIACH / KLASACH<br/>
        </div>
        <div class="col-12 d-flex align-items-center">
          <h1 class="page-header mb-1">Startujący w różnych klasach.</h1>
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
                <table class="table table-striped table-bordered table-hover text-center table-pad-6px font-print-18pt align-middle">
                    <thead>
                        <tr class="font-print-18pt">
                            <th class="text-center" style="width:6%">Lp.</th>
                            <th class="text-center" style="width:34%">Uczestnik</th>
                            <th style="width:60%">Style</th>
                        </tr>
                    </thead>

                    <tbody>
                      <?php $idx = 1; ?>
                      @foreach($couples as $person)
                      <tr>
                          <td class="btn-circlet">{{$idx}}.</td>
                          <?php $idx++; ?>
                          <td class="text-start">{{ $person['lastName'] }} {{ $person['firstName'] }} {{ $person['club'] }}</td>
                          <td class="text-start">
                              @foreach($person['entries'] as $entry)
                                  <div>
                                    [{{ $entry['number'] }}] -> {{ $entry['description'] }} 
                                  </div>
                              @endforeach
                          </td>
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
