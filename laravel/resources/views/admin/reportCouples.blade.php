@extends('admin.master')

@section('title')
    Raport
@stop

@section('content')
<div id="page-wrapper" class="container-fluid">

    {{ html()->form('GET', url('admin/report'))->open() }}

    {{-- Nagłówek --}}
    <div class="row">
        <div class="col-lg-12">

            <div class="page-header-break">ZESTAW PAR<br/></div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="page-header mb-0">Numery par w rundach</h1>

                {{ html()
                    ->submit('Powrót')
                    ->id('submitButton1')
                    ->class('btn btn-primary button-menu') }}
            </div>

        </div>
    </div>

    {{-- Przycisk Drukuj --}}
    <div class="row mb-2">
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

    {{-- Tabela --}}
    <div class="row">
        <div class="col-lg-12">

            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover text-center">

                    <thead>
                        <tr>
                            <th style="width: 5%">Lp.</th>
                            <th class="text-start" style="width: 40%">Kategoria / Klasa</th>
                            <th class="text-center" style="width: 10%">Liczba par</th>
                            <th class="text-start">Numery par</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php $idx = 0; @endphp

                        @foreach($program as $index => $programRound)
                            @if($programRound->baseNumberOfCouples > 0)
                                <tr>
                                    {{-- Lp --}}
                                    <td class="btn-circle">{{ ++$idx }}.</td>

                                    {{-- Opis rundy --}}
                                    <td class="text-start font-14pt">
                                        {{ $programRound->description }}
                                    </td>

                                    {{-- Liczba par --}}
                                    <td class="font-print-24pt">
                                        {{ $programRound->baseNumberOfCouples }}
                                    </td>

                                    {{-- Numery par --}}
                                    <td class="text-start font-print-18pt">
                                        @foreach($couples[$index] as $i => $couple)
                                            {{ $couple->number }}@if($i < count($couples[$index]) - 1),@endif
                                        @endforeach
                                    </td>
                                </tr>
                            @endif
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
<script>
</script>
@stop
