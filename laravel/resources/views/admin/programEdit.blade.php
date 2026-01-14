@extends('admin.master')

@section('title')
    Edycja Programu Turnieju
@stop

@section('content')
<div id="page-wrapper" class="container-fluid">

    {{ html()->form('POST', action('Admin\DashboardController@postFinalProgram'))->open() }}

    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="page-header-break">PROGRAM TURNIEJU</div>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <h1 class="h3 mb-0">Program turnieju</h1>
                <div class="d-flex align-items-center gap-2">
                    {{ html()->button('Zmień kolejność')
                        ->type('button')
                        ->id('startStop')
                        ->class('btn btn-warning button-menu') }}
                    {{ html()->button('Zmień nazwy')
                        ->type('button')
                        ->id('altNames')
                        ->class('btn btn-warning button-menu') }}
                    {{ html()->submit('Zatwierdź')
                        ->id('submitButton1')
                        ->class('btn btn-primary button-menu') }}
                </div>
            </div>

            <div class="mt-3">
                <h4 class="h5 mb-0">
                    <div class="d-inline-flex align-items-center flex-wrap gap-2">
                        <span>Czas rozpoczęcia:</span>
                        {{ html()->input('time', 'stTime', $layout->startTime)
                            ->id('inpStTime')
                            ->class('form-control d-inline-block w-auto btn-light-blue font-14pt')
                            ->attribute('step', '300') }}
                        {{ html()->button('Parametry')
                            ->type('button')
                            ->class('btn btn-light-blue button-menu ms-2')
                            ->attribute('data-bs-toggle','modal')
                            ->attribute('data-bs-target','.programParametersModal') }}
                    </div>
                </h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th style="width:50px;"  class="p-1">Lp.</th>
                            <th  class="p-1">
                                <div class="d-flex justify-content-between">
                                    <div class="alignleft">
                                        Runda
                                        @if($additionalRounds)
                                            + dodatkowe rundy
                                        @endif
                                    </div>
                                    <div class="alignright">
                                        [ Prezentacji ]
                                    </div>
                                </div>
                            </th>
                            <th  class="p-1">Grup</th>
                            <th colspan="10"  class="p-1">Tańce</th>
                        </tr>
                    </thead>

                    <tbody id="sortable1" class="connectedSortable">
                        @foreach($program as $index => $programRound)
                            <tr>
                                <td class="btn-circle fs-5 p-1">{{ $index + 1 }}.</td>

                                @if($programRound->isDance)
                                    <td  class="p-1">
                                @else
                                    <td class="text-muted p-1">
                                @endif
                                    {{ html()->hidden('roundId[]',   $programRound->id) }}
                                    {{ html()->hidden('roundName[]', $programRound->description) }}
                                    {{ html()->hidden('isDance[]',   $programRound->isDance) }}

                                    <div class="d-flex justify-content-between">
                                      <div class="alignleft">
                                        <span class="description">{{ $programRound->description }}</span>
                                        <div class="mt-1">
                                          <span class="alternativeDescription" @if(empty($programRound->alternative_description)) hidden @endif>
                                            {{ $programRound->alternative_description }}
                                          </span>
                                          {{ html()->text('roundAlternativeName[]', $programRound->alternative_description)
                                              ->class('form-control form-control-sm alternativeInput')
                                              ->attribute('hidden', true)   {{-- startowo ukryte --}}
                                              ->attribute('maxlength', 40) }}
                                        </div>
                                      </div>

                                      <div class="alignright">
                                        @if($programRound->couples)
                                          [ {{ $programRound->couples }} ]
                                        @endif
                                      </div>
                                    </div>
                                </td>
                                <td  class="p-1">
                                    {{ html()->input('number', 'groupId[]', $programRound->groups)
                                        ->class('groups btn-blue-gray text-center font-12pt form-control d-inline-block w-auto')
                                        ->attribute('min',1)
                                        ->attribute('max',99)
                                        ->required() }}
                                </td>

                                @if($programRound->isDance)
                                    @foreach($programRound->dances as $programRoundDance)
                                        <td  class="p-1">
                                            <tablecell>
                                                {{ html()->hidden($programRound->id.'DanceName[]', $programRoundDance['dance']) }}

                                                <tc-dance>
                                                    @php
                                                        $dbRound = \App\Round::where('description', trim($programRound->description))
                                                            ->where('dance', $programRoundDance['dance'])
                                                            ->first();
                                                    @endphp

                                                    @if(!is_null($dbRound) && $dbRound->closed == 1)
                                                        {{ html()->checkbox($programRound->id.$programRoundDance['dance'], true)
                                                            ->class('danceCheckbox')
                                                            ->id($programRound->id.$programRoundDance['dance']) }}
                                                    @else
                                                        {{ html()->checkbox($programRound->id.$programRoundDance['dance'], false)
                                                            ->class('danceCheckbox')
                                                            ->id($programRound->id.$programRoundDance['dance']) }}
                                                    @endif

                                                    <label for="{{ $programRound->id }}{{ $programRoundDance['dance'] }}">
                                                        &nbsp;&nbsp;{{ $programRoundDance['dance'] }}
                                                    </label>
                                                </tc-dance>

                                                <tc-order>{{ $programRoundDance['order'] }}</tc-order>
                                                {{ html()->hidden('order'.$programRound->id.$programRoundDance['dance'], $programRoundDance['order']) }}
                                            </tablecell>
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                        @endforeach

                        @if($additionalRounds)
                            @foreach($additionalRounds as $additionalRound)
                                <tr>
                                    <td class="btn-circle fs-5 p-1">{{ ++$index+1 }}.</td>

                                    @if($additionalRound->isDance)
                                        <td class="p-1">
                                        {{ html()->hidden('isDance[]', 1) }}
                                    @else
                                        <td class="text-muted p-1">
                                        {{ html()->hidden('isDance[]', 0) }}
                                    @endif

                                        {{ html()->hidden('roundId[]',   $additionalRound->id) }}
                                        {{ html()->hidden('roundName[]', $additionalRound->description) }}

                                        <div>
                                            <span class="description text-primary">{{ $additionalRound->description }}</span>
                                        </div>
                                        <div>
                                            <span class="alternativeDescription text-primary fst-italic">{{ $additionalRound->alternative_description }}</span>
                                            {{ html()->hidden('roundAlternativeName[]', '')
                                                ->class('alternativeInput') }}
                                        </div>
                                    </td>

                                    <td class="p-1">
                                        {{ html()->input('number', 'groupId[]', $additionalRound->groups)
                                            ->class('groups btn-blue-gray text-center font-12pt form-control d-inline-block w-auto')
                                            ->attribute('min',1)
                                            ->attribute('max',99)
                                            ->required() }}
                                    </td>

                                    @if($additionalRound->isDance)
                                        @foreach($additionalRound->dances as $additionalRoundDance)
                                            <td class="p-1">
                                                <tablecell>
                                                    {{ html()->hidden($additionalRound->id.'DanceName[]', $additionalRoundDance['dance']) }}

                                                    <tc-dance>
                                                        {{ html()->checkbox($additionalRound->id.$additionalRoundDance['dance'], false)
                                                            ->class('danceCheckbox')
                                                            ->id($additionalRound->id.$additionalRoundDance['dance']) }}

                                                        <label for="{{ $additionalRound->id }}{{ $additionalRoundDance['dance'] }}">
                                                            {{ $additionalRoundDance['dance'] }}&nbsp;&nbsp;
                                                        </label>
                                                    </tc-dance>

                                                    <tc-order>{{ $additionalRoundDance['order'] }}</tc-order>
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

    <div class="row mt-3">
        <div class="col-lg-12">
            <div class="d-flex justify-content-end">
                {{ html()->submit('Zatwierdź')
                    ->id('submitButton2')
                    ->class('btn btn-primary button-menu') }}
            </div>
        </div>
    </div>

    {{ html()->form()->close() }}
  </div>

  <div class="modal fade programParametersModal"
      tabindex="-1"
      aria-labelledby="saveParametersLabel"
      aria-hidden="true">
  
    {{ html()->form('GET', action('Admin\DashboardController@saveParameters'))->open() }}
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
  
        <div class="modal-header">
          <h5 class="modal-title" id="saveParametersLabel">
            Parametry czasowe programu turnieju:
          </h5>
        </div>
  
        <div class="modal-body">
          <div class="mb-3 ekran">
  
            <div class="mb-2">
              {{ html()->input('number', 'intDurationElm', $layout->durationRound)
                  ->id('intDurationElm')
                  ->class('form-control d-inline-block text-center width_100px btn-light-blue ekran')
                  ->attribute('min', 0)
                  ->attribute('max', 300)
                  ->required() }}
              <span class="ms-2">[sec] – czas trwania tańca w eliminacjach</span>
            </div>
  
            <div class="mb-2">
              {{ html()->input('number', 'intDurationFin', $layout->durationFinal)
                  ->id('intDurationFin')
                  ->class('form-control d-inline-block text-center width_100px btn-light-blue ekran')
                  ->attribute('min', 0)
                  ->attribute('max', 300)
                  ->required() }}
              <span class="ms-2">[sec] – czas trwania tańca w rundzie finałowej</span>
            </div>
  
            <div class="mb-2">
              {{ html()->input('number', 'intDurationStart', $layout->parameter1)
                  ->id('intDurationStart')
                  ->class('form-control d-inline-block text-center width_100px btn-light-blue ekran')
                  ->attribute('min', 0)
                  ->attribute('max', 60)
                  ->required() }}
              <span class="ms-2">[min] – otwarcie turnieju</span>
            </div>
  
            <div class="mb-2">
              {{ html()->input('number', 'intDurationEnd', $layout->parameter2)
                  ->id('intDurationEnd')
                  ->class('form-control d-inline-block text-center width_100px btn-light-blue ekran')
                  ->attribute('min', 0)
                  ->attribute('max', 80)
                  ->required() }}
              <span class="ms-2">[min] – ogłoszenie wyników turnieju</span>
            </div>
  
          </div>
        </div>
  
        <div class="modal-footer">
          {{ html()->button('Anuluj')
              ->type('button')
              ->class('btn btn-warning button-menu ms-start')
              ->attribute('data-bs-dismiss','modal') }}
  
          {{ html()->submit('Zapisz')
              ->class('btn btn-primary button-menu ms-auto') }}
        </div>
  
      </div>
    </div>
    {{ html()->form()->close() }}
  </div>
@stop

@section('customScripts')
   <script src="{{ asset('js/adminProgramEdit.js') }}"></script>
   <script src="{{ asset('js/jquery.multisortable.js') }}"></script>
   <script>
    $(function () {
      const $tbody = $("#sortable1");
      const $table = $tbody.closest("table");
      let lastMouse = { x: 0, y: 0 };
      let tracking = false;
    
      function renumber() {
        $tbody.find("tr").each(function(i){
          $(this).find("td:first").text((i+1) + ".");
        });
      }
    
      function isOutsideTable(x, y) {
        const o = $table.offset();
        const left = o.left, top = o.top;
        const right = left + $table.outerWidth();
        const bottom = top + $table.outerHeight();
        const margin = 12;
        return (x < left - margin || x > right + margin || y < top - margin || y > bottom + margin);
      }
    
      $(document).on("mousemove.sortDelete", function(e){
        if (!tracking) return;
        lastMouse.x = e.pageX;
        lastMouse.y = e.pageY;
      });
    
      // helper, który zachowuje szerokości kolumn (bez clone + bez body)
      const fixHelper = function(e, ui) {
        ui.children().each(function() {
          $(this).width($(this).width()); // “zamraża” szerokość komórki na czas drag
        });
        return ui;
      };
    
      $tbody.sortable({
        axis: "y",
        items: "> tr",
        tolerance: "pointer",
        helper: fixHelper,
        forcePlaceholderSize: true,
        placeholder: "sortable-placeholder",
    
        start: function(_e, ui) {
          tracking = true;
    
          // placeholder ma mieć wysokość jak wiersz
          ui.placeholder.height(ui.item.outerHeight());
    
          // delikatny styl dragowanego wiersza
          ui.item.addClass("dragging-row");
        },
    
        stop: function(_e, ui) {
          tracking = false;

          ui.item.removeClass("dragging-row");
          ui.item.css('border', '');
          ui.item.css('background-color', '#E0ECF8');

          // ODBLOKUJ szerokości komórek po upuszczeniu (ważne!)
          ui.item.children().css("width", "");
    
          if (isOutsideTable(lastMouse.x, lastMouse.y)) {
            ui.item.fadeOut(120, function(){
              $(this).remove();
              renumber();
            });
          } else {
            renumber();
          }
        },
    
        update: function(){
          renumber();
        }
      }).disableSelection();
    });
    </script>
    
    <style>
    /* placeholder w tabeli */
    .sortable-placeholder td{
      background: rgba(0,0,0,.04);
      border: 2px dashed rgba(0,0,0,.2);
    }
    
    /* wygląd podczas przeciągania */
    .dragging-row{
      background: #F2F5A9 !important;
      border-radius: 8px;
      outline: 2px solid #428bca;
    }
    </style>

@stop
