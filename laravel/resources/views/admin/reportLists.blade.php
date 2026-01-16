@extends('admin.master')

@section('title')
    Raport
@stop

@section('content')
    <div id="page-wrapper" class="container-fluid">
      {!! html()->form('GET', url('admin/report'))->open() !!}
      <div class="row mb-3">
        <div class="col-12 d-flex align-items-center">
          <h1 class="page-header mb-0">Listy Startowe</h1>
      
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
                    <div class="page-header-break">LISTA STARTOWA {{$couples[$index][0]->section}}</div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover text-center table-pad-2px">
                            <thead>
                                <tr>
                                    @if($couples[$index][0]->marker[0] == 'a')
                                        <th colspan="5" class="text-center">
                                            <p class="alignleft font-print-18pt">&nbsp;Kategoria: {{$index}}</p>
                                            <p class="alignright">[ Zgłoszonych:<a class="font-print-24pt">&nbsp;{{$couples[$index][0]->NoCpl1}}</a>&nbsp;]</p>
                                        </th>
                                    @else
                                        <th colspan="5" class="text-center">
                                            <p class="alignleft font-print-18pt">&nbsp;Kategoria: {{$index}}</p>
                                            <p class="alignright">[ Zgłoszonych:<a class="font-print-24pt">&nbsp;{{$couples[$index][0]->NoCpl1}}&nbsp;/&nbsp;{{$couples[$index][0]->NoCpl2}}</a>&nbsp;]</p>
                                        </th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th class="text-center">Lp.</th>
                                    <th>Imię i nazwisko</th>
                                    <th class="text-center">Klub / Kraj</th>
                                    @if($couples[$index][0]->marker[0] == 'a')
                                        <th class="text-center">Numer</br></th>
                                    @else
                                        <th class="text-center">Standard</th>
                                        <th class="text-center">Latin</th>
                                    @endif
                                </tr>
                                @php $idx = 0; @endphp
                                @foreach($couple as $position)
                                    <tr>
                                        <td class="btn-circle fs-5">{{ $idx + 1 }}.</td>
                                        @php $idx++; @endphp
                                        <td class="text-left py-1">
                                            {{ $position->lastNameA }}&nbsp;{{ $position->firstNameA }}<br/>
                                            {{ $position->lastNameB }}&nbsp;{{ $position->firstNameB }}
                                        </td>
                                        <td class="text-center py-1">
                                            {{ $position->club }}<br/>
                                            {{ $position->country }}
                                        </td>
                                        @if($position->marker[0] == 'a')
                                            <td class="h3 media-middle">{{ $position->number }}</td>
                                        @else
                                            @if($position->marker[0] == '1')
                                                <td class="h3 media-middle">{{ $position->number }}</td><td></td>
                                            @elseif($position->marker[0] == '2')
                                                <td></td><td class="h3 media-middle">{{ $position->number }}</td>
                                            @else
                                                <td class="h3 media-middle">{{ $position->number }}</td>
                                                <td class="h3 media-middle">{{ $position->number2 }}</td>
                                            @endif
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        </div>

        {!! html()->form()->close() !!}
    </div>
    <!-- /#page-wrapper -->
@stop

@section('customScripts')
    <script>
    </script>
@stop
