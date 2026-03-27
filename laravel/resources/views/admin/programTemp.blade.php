@extends('admin.master')

@section('title')
  Program
@stop

@section('content')
  <div id="page-wrapper">
    @if( $cmd == false)
      {!! html()->form('POST', action('Admin\DashboardController@postFinalProgram'))->open() !!}
    @else
      {!! html()->form('GET', action('Admin\DashboardController@selectedCategories', ['something']))->open() !!}
    @endif

    <div class="row">
      <div class="col-lg-12">
        @if( $cmd == false)
          <h1 class="page-header">Podgląd programu
        @else
          <h1 class="page-header">Podgląd nowego programu
        @endif
          <div class="pull-right">
            {!! html()->submit('Zatwierdź')->id('submitButton1')->class('btn btn-primary button-menu') !!}
          </div>
        </h1>
      </div>
    </div>

    <div class="row">
      <?php
        if( !empty($program) && count($program) ) 
          $maxDances = max(array_map(fn($r) => count($r->dances), $program)); 
        else
          $maxDances = 4;
      ?>
      <div class="col-lg-12">
        <div class="table-responsive">
          <table class="table table-striped table-bordered table-hover">
            <thead>
              <tr>
                <th>Lp.</th>
                <th>Runda</th>
                
                  <th colspan="{{ $maxDances }}">Tańce</th>
                
              </tr>
            </thead>
            <tbody id="sortable1" class="connectedSortable">
              @php $idx = 0; @endphp
              @foreach($program as $programKey => $programRound)
                <tr>
                  <td class="btn-circle fs-5">{{ $idx+1 }}.</td>
                  @php $idx++; @endphp

                  @if($programRound->isDance && isset($programRound->bg_color))
                    <td style="background-color: {{$programRound->bg_color}};">
                  @elseif($programRound->isDance)
                    <td>
                  @else
                    <td class="text-muted">
                  @endif
                    {{ $programRound->description }}
                    <span class="alternativeDescription" @if(empty($programRound->alt_description)) hidden @endif>
                      {{ $programRound->alt_description }}
                    </span>
                    {!! html()->hidden('roundId[]', $programKey) !!}
                    {!! html()->hidden('roundName[]', $programRound->description) !!}
                    {!! html()->hidden('isDance[]', $programRound->isDance) !!}
                    {!! html()->hidden('roundAlternativeName[]', $programRound->alt_description) !!}
                  </td>

                  @if( $cmd == false)
                    @if($programRound->isDance)
                      @foreach($programRound->dances as $danceKey => $programRoundDance)
                        <td>
                          {!! html()->hidden("{$programKey}DanceName[]", $programRoundDance) !!}
                          @php 
                            $dbRound = \App\Round::where('description', trim($programRound->description))
                                     ->where('dance', $programRoundDance)
                                     ->first();
                          @endphp
                          @if( !is_null($dbRound) && $dbRound->closed == 1)
                            &nbsp{!! html()->checkbox("{$programKey}{$programRoundDance}", 0, true) !!}
                          @else
                            &nbsp{!! html()->checkbox("{$programKey}{$programRoundDance}", 0, false) !!}
                          @endif
                          {!! html()->label($programRoundDance, "{$programKey}{$danceKey}") !!}
                        </td>
                      @endforeach
                      @for($i = count($programRound->dances); $i < $maxDances; $i++)
                        <td class="p-1">&nbsp;</td>
                      @endfor

                    @endif
                  @else
                    @if($programRound->isDance)
                      <td>
                        @foreach($programRound->dances as $danceKey => $programRoundDance)
                          {!! html()->hidden("{$programKey}DanceName[]", $programRoundDance) !!}
                          {!! html()->label($programRoundDance, "{$programKey}{$danceKey}") !!}
                        @endforeach
                      </td>
                      @for($i = count($programRound->dances); $i < $maxDances; $i++)
                        <td class="p-1">&nbsp;</td>
                      @endfor
                    @endif
                  @endif
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="pull-right">
          {!! html()->submit('Zatwierdź')->id('submitButton2')->class('btn btn-primary button-menu') !!}
        </div>
      </div>
    </div>

    {!! html()->form()->close() !!}

    {{-- Zachowanie sesji --}}
    {!! Session::put('new_program', $program) !!}
  </div>
  <!-- /#page-wrapper -->
@stop

@section('customScripts')
  <script>
    $(function() {
      var removeIntent = false;
      $("#sortable1").sortable({
        connectWith: ".connectedSortable",
        update: function(event, ui){
          $(this).find('tr').each(function(i){
            $(this).find('td:first').text(i+1);
          });
        },
        start: function(event, ui){
          ui.item.css('background-color', '#F2F5A9');
          ui.item.css('border-radius','8px');
          ui.item.css('border','2px solid #428bca');
        },
        over: function(event, ui){
          removeIntent = false;
        },
        out: function(event, ui){
          removeIntent = true;
        },
        beforeStop: function(event, ui){
          if(removeIntent) {
            ui.item.remove();
          };
        },
        stop: function(event, ui){
          ui.item.css('border','');
          ui.item.css('background-color','#E0F8E0');
        },
      }).disableSelection();

      $('td').each(function(){
        $(this).css('width', $(this).width() + 'px');
      });
    });
  </script>
@stop
