@extends('admin.master')

@section('title')
    Wyniki
@stop

@section('content')
    <div id="page-wrapper">
        {!! html()->form('POST', url('admin/reportSet'))->open() !!}
            {!! html()->hidden('roundId', $round->roundId) !!}
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Ustalanie miejsc
                    <div class="pull-right">
                        {!! html()->a(url()->previous(), 'Anuluj')->class('btn btn-lg btn-warning button-menu') !!}
                        {!! html()->submit('Zapamiętaj')->class('btn btn-primary button-menu') !!}
                    </div>
                </h1>
                Kategoria: {{ $round->categoryName }} {{ $round->className }} {{ $round->styleName }}
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <table id="table" class="table">
                <tbody><tr>

                @for($i = 0; $i < $numberOfPositions + 1; $i++)
                    <td>
                      <div class="col-lg-12">
                        <table class="table table-bordered">
                          <thead>
                            <tr><th>
                              @if($i == 0)
                                Numer (Miejsce)
                              @else
                                {!! html()->hidden('coupleNumber[]', 'position'.$i) !!}
                                Miejsce {{ $i }}
                              @endif
                            </th></tr>
                          </thead>
                          <tbody class="connectedMultisortable" 
                                 style="height:auto; min-height:300px; display:block;">
                              @foreach($couples as $couple)
                                @if($couple->manualPosition == $i
                                    || ($i == 0 && $couple->manualPosition > $numberOfPositions))
                                  <tr class="multirow">
                                    <td>
                                      {!! html()->hidden('coupleNumber[]', $couple->number) !!}
                                      {{ $couple->number }} ({{ $couple->resultPosition }})
                                    </td>
                                  </tr>
                                @endif
                              @endforeach
                          </tbody>
                        </table>
                      </div>
                    </td>
                @endfor

                </tr></tbody>
                </table>
            </div>
        </div>

        {!! html()->form()->close() !!}
    </div>
@stop

@section('customScripts')
    <script>
       $(function() {
            $('.connectedMultisortable').multisortable();
            $('.connectedMultisortable')
              .sortable('option', 'connectWith', '.connectedMultisortable');
        });
        $('table tbody').on('click', 'table', function () {
            if($('.multirow:hover').length != 0) {
                return;
            }
            $('table > tbody > tr').each(function() { $(this).removeClass('selected'); });
        });
    </script>
@stop
