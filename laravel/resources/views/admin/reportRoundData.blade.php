@extends('admin.master')

@section('title')
    Raport
@stop

@section('content')
    <div id="page-wrapper" class="container-fluid">
        {{ html()->form('GET', url('admin/report'))->open() }}

        <div class="row">
            <div class="col-lg-12">

                <div class="page-header-break">ZESTAWIENIE RUND<br/></div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="page-header mb-0">
                        Zestaw rund (liczby par, grup)
                    </h1>

                    {{ html()
                        ->submit('Powrót')
                        ->id('submitButton1')
                        ->class('btn btn-primary button-menu') }}
                </div>
            </div>
        </div>

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

        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover text-center">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%">Lp.</th>
                                <th style="width: 45%">Runda</th>
                                <th class="text-center">Liczba par</th>
                                <th class="text-center">Typowań</th>
                                <th class="text-center">Grup</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $idx = 0; @endphp
                            @foreach($program as $programRound)
                                <tr>
                                    <td class="btn-circle">
                                      @if($programRound->baseNumberOfCouples > 0)
                                        {{ $idx + 1 }}.
                                        @php $idx++; @endphp
                                      @endif
                                    </td>

                                    <td class="text-start font-14pt">
                                        {{ $programRound->description }}
                                    </td>

                                    @if($programRound->baseNumberOfCouples > 0)
                                        <td class="font-print-24pt">
                                            {{ $programRound->baseNumberOfCouples }}
                                        </td>
                                    @else
                                        <td></td>
                                    @endif

                                    <td></td>
                                    <td></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{ html()->form()->close() }}
    </div>
    <!-- /#page-wrapper -->
@stop

@section('customScripts')
    <script>
        // miejsce na ewentualny JS
    </script>
@stop
