@extends('admin.master')

@section('title')
    Program dodany
@stop

@section('content')
  <div id="page-wrapper">
    {{ html()->form('POST', action('Admin\DashboardController@postFinalProgram'))->open() }}

    <div class="row">
      <div class="col-lg-12">
        <h1 class="page-header">
          Aktualny program + dodane rundy
          <div class="pull-right">
              {{ html()->submit('Zatwierdź')->class('btn btn-primary') }}
          </div>
        </h1>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="table-responsive">
          <table class="table table-striped table-bordered table-hover">
            <thead>
              <tr>
                <th>Lp.</th>
                <th>Runda</th>
              </tr>
            </thead>
            <tbody id="sortable2" class="connectedSortable">
              @foreach($program as $index => $programRound)
                <tr>
                  <td class="btn-circle">{{ $index + 1 }}.</td>
                  @if($programRound->isDance)
                    <td>
                  @else
                    <td class="text-muted">
                  @endif
                      {{ html()->hidden('roundName[]', $programRound->description) }}
                      {{ html()->hidden('roundId[]', $programRound->id) }}
                      {{ html()->hidden('isDance[]', $programRound->isDance) }}
                      <description class="description">{{ $programRound->description }}</description>
                      <div>
                        <description class="alternativeDescription">{{ $programRound->alternative_description }}</description>
                        {{ html()->hidden('roundAlternativeName[]', $programRound->alternative_description)->class('alternativeInput') }}
                      </div>
                    </td>

                  @if($programRound->isDance)
                    @foreach($programRound->dances as $programRoundDance)
                      <td>
                        <tablecell>
                          {{ html()->hidden($programRound->id.'DanceName[]', $programRoundDance['dance']) }}
                          <tc-dance>
                            <label for="{{ $programRound->id }}{{ $programRoundDance['dance'] }}">
                              {{ $programRoundDance['dance'] }}
                            </label>
                            <?php
                              $dbRound = \App\Round::where('description', '=', trim($programRound->description))
                                ->where('dance', '=', $programRoundDance['dance'])
                                ->first();
                            ?>
                            @if(!is_null($dbRound) && $dbRound->closed == 1)
                                &nbsp;{{ html()->checkbox($programRound->id.$programRoundDance['dance'], true)->class('danceCheckbox')->id($programRound->id.$programRoundDance['dance']) }}
                            @else
                                &nbsp;{{ html()->checkbox($programRound->id.$programRoundDance['dance'], false)->class('danceCheckbox')->id($programRound->id.$programRoundDance['dance']) }}
                            @endif
                          </tc-dance>
                          <tc-order>{{ $programRoundDance['order'] }}</tc-order>
                            {{ html()->hidden('order'.$programRound->id.$programRoundDance['dance'], $programRoundDance['order']) }}
                        </tablecell>
                      </td>
                    @endforeach
                  @endif
                </tr>
                @endforeach

                @if($programAdd)
                  @foreach($programAdd as $programRound)
                    <tr style="color: IndianRed; background-color: Lavender;">
                      <td class="btn-circle">{{ ++$index+1 }}.</td>
                      <td>
                        {{ html()->hidden('roundId[]', $programRound->id) }}
                        {{ html()->hidden('roundName[]', $programRound->description) }}
                        {{ html()->hidden('isDance[]', $programRound->isDance) }}
                        <description class="description">{{ $programRound->description }}</description>
                        <div>
                          {{ html()->hidden('roundAlternativeName[]', '')->class('alternativeInput') }}
                        </div>
                      </td>
                      @if($programRound->isDance)
                        @foreach($programRound->dances as $danceKey => $programRoundDance)
                          <td>
                            <tablecell>
                              {{ html()->hidden($programRound->id.'DanceName[]', $programRoundDance) }}
                              <tc-dance>
                              {{ html()->checkbox($programRound->id.$programRoundDance, false)
                                       ->class('danceCheckbox')
                                       ->id($programRound->id.$programRoundDance) }}
                                <label for="{{ $programRound->id }}{{ $programRoundDance }}">
                                  &nbsp;&nbsp;{{ $programRoundDance }}
                                </label>
                              </tc-dance>
                            </tablecell>
                          </td>
                        @endforeach
                      @endif
                    </tr>
                  @endforeach
                @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="pull-right">
          {{ html()->submit('Zatwierdź')->class('btn btn-primary') }}
        </div>
      </div>
    </div>

    {{ html()->form()->close() }}
  </div>
@stop

@section('customScripts')
  <script>
    $(function() {
      $("#sortable2").sortable({
        connectWith: ".connectedSortable",
        update: function(event, ui) {
           //console.log("update", event);
          $(this).find('tr').each(function(i) {
            $(this).find('td:first').text(i + 1);
          });
        },
      });
      $('td').each(function(){
          $(this).css('width', $(this).width() + 'px');
      });
    });
  </script>
@stop
