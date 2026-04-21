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
        <div class="table-responsive base-rounds-wrapper mx-3">
          @foreach($tables as $table)
            @php
              $isWide = count(array_slice($table['headers'], 3)) > 4;
              $blocks = array_unique(array_values($table['styleParts']));
              sort($blocks);
            @endphp
            <div class="table-wide {{ $isWide ? 'landscape' : '' }}">
            <div class=" page-header-break fs-4">LISTA STARTOWA [ BLOK {{ implode(', ', $blocks) }} ]</div>
              <table class="table table-striped table-bordered table-hover text-center table-pad-2px align-middle">
                <colgroup>
                  <col style="width: 13mm;">
                  <col style="width: 47mm;">
                  <col style="width: 60mm;">
                    @foreach(array_slice($table['headers'], 3) as $style)
                      <col style="width: 22mm;">
                    @endforeach
                </colgroup>
                 <thead>
                  <tr>
                    <th colspan="3" style="width: 120mm">
                      <p class="alignleft font-print-18pt">
                        &nbsp;Kategoria:&nbsp;{{ $table['category'] }} {{ $table['class'] }}
                      </p>
                    </th>
                    @foreach(array_slice($table['headers'], 3) as $style)
                      <th class="style-col text-center align-middle fs-6" style="padding: 0">
                       {{ $table['displayStyles'][$style] ?? $style }}
                      </th>
                    @endforeach
                  </tr>
                  <tr>
                    <th class="text-center col-lp">Lp</th>
                      <th class="text-start col-name">Nazwisko i imię</th>
                      <th class="col-club">
                        <div style="display:flex; justify-content:space-between;">
                          <span class="text-start">Klub</span>
                          <span class="alignright">Zgłoszeń</span>
                          </div>
                      </th>
                      @foreach(array_slice($table['headers'], 3) as $style)
                        <th class="text-center font-print-24pt">
                        {{ $table['styleCounts'][$style] ?? 0 }}
                        </th>
                      @endforeach
                  </tr>
                </thead>
 
                <tbody>
                  @foreach($table['rows'] as $row)
                      <tr class="fs-5">
                          <td class="btn-circle  col-lp font-print-18pt">{{ $row['lp'] }}</td>
                          <td class="text-start col-name py-1">
                            @foreach($row['couple_names'] as $name)
                              <div class="name-line">{{ $name }}</div>
                            @endforeach
                          </td>
                          <td class="text-center col-club py-1">
                            <div class="cell">
                              <span class="club">{{ $row['club'] }}</span>
                              <span class="country"> {{ $row['country'] }}</span>
                            </div>
                          </td>
                          @foreach(array_slice($table['headers'], 3) as $style)
                              <td class="h3 col-style media-middle">
                                  {{ $row[$style] ?? '' }}
                              </td>
                          @endforeach
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
  <!-- /#page-wrapper -->
@stop

@section('customScripts')
    <script>
    </script>
@stop
