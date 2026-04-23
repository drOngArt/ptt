@extends('admin.master')

@section('title')
    Raport
@stop

@section('content')
<div id="page-wrapper" class="container-fluid">

    {{ html()->form('GET', url('admin/report'))->open() }}

    <div class="row mb-2">
        <div class="page-header-break fs-5">
          ZESTAWIENIE AKTUALNYCH KLUBÓW<br/>
        </div>
        <div class="col-12 d-flex align-items-center">
          <h1 class="page-header mb-0">Kluby Aktualne</h1>
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
                <table class="table table-striped table-bordered table-hover text-center align-middle table-pad-6px font-print-18pt">
                    <thead>
                        <tr class="font-print-18pt">
                            <th class="text-center" style="width:10%">Lp.</th>
                            <th style="width:60%">Klub - Miasto</th>
                            <th style="width:30%">Kraj / Okręg</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $idx = 0; @endphp

                        @foreach($clubs as $club)
                            <tr>
                                <td class="btn-circle font-print-18pt">{{ $idx + 1 }}.</td>
                                @php $idx++; @endphp

                                <td class="text-start font-print-18pt">{!! $club->club ?: '&nbsp;' !!}</td>
                                <td class="text-start font-print-18pt">{!! $club->country ?: '&nbsp;' !!}</td>
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
