@extends('admin.master')

@section('title')
    Raport
@stop

@section('content')
<div id="page-wrapper" class="container-fluid">

    {{ html()->form('GET', url('admin/report'))->open() }}

    {{-- Nagłówek + Powrót --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="page-header mb-0">Wyniki</h1>

                {{ html()
                    ->submit('Powrót')
                    ->id('submitButton1')
                    ->class('btn btn-primary button-menu') }}
            </div>
        </div>
    </div>

    {{-- Przycisk Drukuj --}}
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

    {{-- Tabele wyników dla każdej kategorii --}}
    <div class="row">
        <div class="col-lg-12">
            @foreach($couples as $index => $couple)
                <div class="page-header-break">WYNIKI<br/></div>

                <div class="text-center h3 mb-3">
                    Kategoria: {{ $index }} ( par: {{ $Numbers[$index] }} )
                    <br/>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-striped table-bordered table-hover text-center table-pad-2px">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 10%">Miejsce</th>
                                <th class="text-center" style="width: 10%">Numer</th>
                                <th style="width: 40%">Imię i nazwisko</th>
                                <th class="text-center" style="width: 40%">Klub / Kraj</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($couple as $position)
                                <tr>
                                    <td class="text-center font-14pt">
                                        {{ $position->manualPosition }}
                                    </td>
                                    <td class="text-center font-print-18pt">
                                        {{ $position->number }}
                                    </td>
                                    <td class="text-start font-12pt">
                                        {{ $position->lastNameA }}&nbsp;{{ $position->firstNameA }}<br/>
                                        {{ $position->lastNameB }}&nbsp;{{ $position->firstNameB }}
                                    </td>
                                    <td class="text-center font-12pt">
                                        {{ $position->club }}<br/>
                                        {{ $position->country }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    </div>

    {{ html()->form()->close() }}

</div>
@stop

@section('customScripts')
<script>
</script>
@stop
