@extends('admin.master')

@section('title')
    Program Turnieju
@stop

@section('content')
  <div id="page-wrapper">
    <div class="row">
      <div class="page-header-break">PROGRAM TURNIEJU &nbsp;{{ $parts }}</div>
  
      <div class="col-lg-12">
          <div class="d-flex justify-content-between align-items-center mb-2">
  
              <!-- LEWA STRONA -->
              <h1 class="page-header m-0">
                  Program turnieju &nbsp;{{ $parts }}
              </h1>
              <div class="d-flex align-items-center gap-2">
                  @if(count($additionalRounds) > 0 && count($program) > 0)
                  <button type="button"
                          class="btn btn-amber button-menu"
                          data-bs-toggle="modal"
                          data-bs-target=".additionalRoundModal">
                      Dodatkowa / Baraż
                  </button>
                  @endif
                  @if(count($program) > 0)
                  <a href="{{ url('admin/program/editProgram') }}"
                      class="btn btn-teal button-menu"
                      role="button">
                      Modyfikuj
                  </a>
                  <button type="button"
                          class="btn btn-deep-orange button-menu"
                          data-bs-toggle="modal"
                          data-bs-target=".programAddRoundModal"
                          role="button">
                      Dodaj rundę
                  </button>
                  @endif
                <div class="dropdown">
                  <button class="btn btn-primary button-menu dropdown-toggle"
                          type="button"
                          data-bs-toggle="dropdown"
                          aria-expanded="false">
                      Program
                  </button>
                  <ul class="dropdown-menu program-dropdown-menu">
                      <li>
                          <a class="dropdown-item d-flex align-items-center"
                            href="#"
                            data-bs-toggle="modal"
                            data-bs-target=".programSelectorModal">
                              <i class="fa fa-folder-open"></i>
                              <span class="dropdown-separator"></span>
                              <span class="dropdown-text">Pobierz</span>
                          </a>
                      </li>
                      @if(count($program) > 0)
                      <li>
                          <a class="dropdown-item d-flex align-items-center"
                            href="#"
                            data-bs-toggle="modal"
                            data-bs-target=".additionalProgramModal">
                              <i class="fa fa-plus-square"></i>
                              <span class="dropdown-separator"></span>
                              <span class="dropdown-text">Dołącz</span>
                          </a>
                      </li>
                      <li>
                          <a class="dropdown-item d-flex align-items-center"
                            href="#"
                            data-bs-toggle="modal"
                            data-bs-target=".saveProgramModal">
                              <i class="fa fa-file-text"></i>
                              <span class="dropdown-separator"></span>
                              <span class="dropdown-text">Zapisz</span>
                          </a>
                      </li>
                      @endif
                      <li>
                          <a class="dropdown-item d-flex align-items-center"
                            href="{{ url('admin/program/newProgram') }}">
                              <i class="fa fa-pencil-square-o"></i>
                              <span class="dropdown-separator"></span>
                              <span class="dropdown-text">Nowy</span>
                          </a>
                      </li>
                  </ul>
                </div>
              </div>
          </div>
      </div>
    </div>  <!-- /.row -->

    @if(session('status'))
       @if(session('status') == 'success')
          <div class="alert alert-success">
             Program został zapisany do pliku.
             <a href="#" class="close" data-bs-dismiss="alert" aria-label="close">&times;</a>
          </div>
       @else
          <div class="alert alert-danger">
             Błąd dostępu do pliku. Program nie został zapisany.
             <a href="#" class="close" data-bs-dismiss="alert" aria-label="close">&times;</a>
          </div>
       @endif
    @endif

    @if(count($program) > 0)
      <div class="row mt-2">
          <div class="col-lg-12">
              <div class="d-flex justify-content-between align-items-center">
              <!-- LEWA STRONA -->
              <div class="font-14pt userTime ekran">
                  Czas rozpoczęcia:
                  <button class="btn-light-blue font-14pt">{{ $layout->startTime }}</button>
                  &nbsp;zakończenia:
                  <button class="btn-light-blue font-14pt">{{ $times[count($compressedProgram)] }}</button>
              </div>
              <!-- PRAWA STRONA -->
              <div class="dropdown print-dropdown ms-auto">
                  <button type="button"
                          class="btn btn-brown button-menu dropdown-toggle"
                          data-bs-toggle="dropdown">
                      <i class="fa fa-print me-2"></i>
                      <span class="border-start border-1 border-light px-2 ms-1">
                          Drukuj
                      </span>
                  </button>
                  <ul class="dropdown-menu print-dropdown-menu dropdown-menu-end">
                      <li>
                          <a class="dropdown-item print-dd-item" href="#" id="withTimes">
                              <i class="fa fa-plus-square me-2"></i>
                              <span class="dd-sep"></span>
                              z czasami
                          </a>
                      </li>
                      <li>
                          <a class="dropdown-item print-dd-item" href="#" id="withoutTimes">
                              <i class="fa fa-minus-square me-2"></i>
                              <span class="dd-sep"></span>
                              bez czasów
                          </a>
                      </li>
                  </ul>
                </div>
              </div>
          </div>
      </div>
    @endif

    <!-- schedeuler -->
    @include('admin.scheduleTable')

    @if(count($program) > 0)
      <div class="row mt-2">
          <div class="col-lg-12">
              <div class="d-flex justify-content-between align-items-center">
                  <div class="dropdown print-dropdown ms-auto">
                      <button type="button"
                              class="btn btn-brown button-menu dropdown-toggle"
                              data-bs-toggle="dropdown">
                          <i class="fa fa-print me-2"></i>
                          <span class="border-start border-1 border-light px-2 ms-1">
                              Drukuj
                          </span>
                      </button>
                      <ul class="dropdown-menu print-dropdown-menu dropdown-menu-end">
                          <li>
                              <a class="dropdown-item print-dd-item" href="#" id="withTimes">
                                  <i class="fa fa-plus-square me-2"></i>
                                  <span class="dd-sep"></span>
                                  z czasami
                              </a>
                          </li>
                          <li>
                              <a class="dropdown-item print-dd-item" href="#" id="withoutTimes">
                                  <i class="fa fa-minus-square me-2"></i>
                                  <span class="dd-sep"></span>
                                  bez czasów
                              </a>
                          </li>
                      </ul>
                  </div>
      
              </div>
          </div>
      </div>
    @endif
  </div>  <!-- /#page-wrapper -->

  {{-- Modal “File input selector” --}}
  <div class="modal fade programSelectorModal"
      tabindex="-1"
      aria-labelledby="programSelectorLabel"
      aria-hidden="true">
    {{ html()->form('POST', url('admin/program'))->acceptsFiles()->open() }}
      <div class="modal-dialog">
        <div class="modal-content">
  
          <div class="modal-header">
            <h5 class="modal-title" id="programSelectorLabel">Program turnieju</h5>
          </div>
  
          <div class="modal-body">
            <p class="mb-2">Wybierz plik z programem turnieju</p>
            <input type="file"
                  name="program"
                  accept=".csv,.dbf"
                  class="form-control">
          </div>
  
          <div class="modal-footer">
            <button type="button"
                    class="btn btn-warning button-menu ms-start"
                    data-bs-dismiss="modal">
              Anuluj
            </button>
            {{ html()->submit('Wczytaj')->class('btn btn-primary button-menu ms-auto') }}
          </div>
  
        </div>
      </div>
    {{ html()->form()->close() }}
  </div>
  
  {{-- Modal “Add round selector” --}}
  <div class="modal fade programAddRoundModal"
      tabindex="-1"
      aria-labelledby="programAddRoundLabel"
      aria-hidden="true">
    {{ html()->form('POST', action('Admin\DashboardController@postAddedRound'))->open() }}
  
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
  
        <div class="modal-header">
          <h5 class="modal-title" id="programAddRoundLabel">Dodaj rundę/pokaz/przerwę do programu turnieju</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zamknij"></button>
        </div>
  
        <div class="modal-body">
          <div class="row g-3 mb-3">
            <div class="col-12 col-lg-4" id="wrap_round">
              <label for="my_round" class="form-label mb-1">Runda</label>
              {{ html()->select('round', $roundNames, null)
                  ->id('my_round')
                  ->class('form-select') }}
            </div>
            @if($categoriesNames !== false)
              <div class="col-12 col-lg-4" id="wrap_category">
                <label for="my_category" class="form-label mb-1">Kategoria</label>
                {{ html()->select('category', $categoriesNames, null)
                    ->id('my_category')
                    ->class('form-select') }}
              </div>
            @endif
  
            <div class="col-12 col-lg-4" id="wrap_additional">
              <label for="my_additional" class="form-label mb-1">Typ rundy</label>
              {{ html()->select('additional', $additNames, null)
                  ->id('my_additional')
                  ->class('form-select') }}
            </div>
          </div>

          <div class="row g-3">
            <div class="col-12" id="wrap_myround">
              <label for="myround" class="form-label">Nazwa własna rundy (opcjonalnie)</label>
              {{ html()->text('myround')
                  ->id('myround')
                  ->placeholder('Nazwa własna?')
                  ->maxlength(20)
                  ->class('form-control text-start') }}
            </div>
  
            <div class="col-12 col-lg-6" id="wrap_breakshow_name">
              <label for="mybreakshow_name" class="form-label">Nazwa przerwy/pokazu (opcjonalnie)</label>
              {{ html()->text('mybreakshow_name')
                  ->id('mybreakshow_name')
                  ->placeholder('nazwa?')
                  ->maxlength(20)
                  ->class('form-control text-start') }}
            </div>
  
            <div class="col-12 col-lg-6" id="wrap_breakshow_dance">
              <label for="mybreakshow_dance" class="form-label">Tańce / minuty (opcjonalnie)</label>
              {{ html()->text('mybreakshow_dance')
                  ->id('mybreakshow_dance')
                  ->placeholder('tańce/minuty?')
                  ->maxlength(20)
                  ->class('form-control text-start') }}
            </div>
          </div>
  
        </div>
  
        <div class="modal-footer">
          <button type="button"
                  class="btn btn-warning button-menu"
                  data-bs-dismiss="modal">
            Anuluj
          </button>
  
          {{ html()->submit('Dodaj')->class('btn btn-primary button-menu ms-auto') }}
        </div>
  
      </div>
    </div>
  
    {{ html()->form()->close() }}
  </div>

  {{-- Modal “Additional Program” --}}
  <div class="modal fade additionalProgramModal"
      tabindex="-1"
      aria-labelledby="additionalProgramLabel"
      aria-hidden="true">
    {{ html()->form('POST', url('admin/program/linkProgram'))->acceptsFiles()->open() }}
      <div class="modal-dialog">
        <div class="modal-content">
  
          <div class="modal-header">
            <h5 class="modal-title" id="additionalProgramLabel">Dodatkowy program turnieju</h5>
          </div>
  
          <div class="modal-body">
            <p class="mb-2">Wybierz dodatkowy plik z programem turnieju</p>
            <input type="file"
                  name="program_add"
                  accept=".csv,.dbf"
                  class="form-control">
          </div>
  
          <div class="modal-footer">
            <button type="button"
                    class="btn btn-warning button-menu ms-start"
                    data-bs-dismiss="modal">
              Anuluj
            </button>
  
            {{ html()->submit('Dodaj do programu')->class('btn btn-primary button-menu ms-auto') }}
          </div>
  
        </div>
      </div>
    {{ html()->form()->close() }}
  </div>
  
  
  {{-- Modal “Save Program” --}}
  <div class="modal fade saveProgramModal"
      tabindex="-1"
      aria-labelledby="saveProgramLabel"
      aria-hidden="true">
    {{ html()->form('GET', action('Admin\DashboardController@saveCurrentProgram'))->open() }}
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="saveProgramLabel">Zapisz program turnieju</h5>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="fileName" class="form-label">Zapisz nazwę programu</label>
              <div class="d-flex align-items-center gap-2">
                {{ html()->text('fileName', 'Program Turnieju ')
                    ->id('fileName')
                    ->placeholder('nazwa pliku')
                    ->maxlength(32)
                    ->class('form-control text-start') }}
                <strong>.csv</strong>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button"
                    class="btn btn-warning button-menu ms-start"
                    data-bs-dismiss="modal">
              Anuluj
            </button>
            {{ html()->submit('Zapisz')->class('btn btn-primary button-menu ms-auto') }}
          </div>
        </div>
      </div>
    {{ html()->form()->close() }}
  </div>
  
  
  {{-- Modal “Additional Round” --}}
  <div class="modal fade additionalRoundModal"
      tabindex="-1"
      aria-labelledby="additionalRoundLabel"
      aria-hidden="true">
    {{ html()->form('POST', url('admin/program/postAdditionalRound'))->open() }}
      <div class="modal-dialog">
        <div class="modal-content">
  
          <div class="modal-header">
            <h5 class="modal-title" id="additionalRoundLabel">Wybierz dodatkową rundę</h5>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="additionalRoundId" class="form-label">Dodatkowa runda</label>
              {{ html()->select('additionalRoundId')
                  ->options($additionalRounds
                      ->mapWithKeys(fn($r) => [$r->roundId => $r->roundName])
                      ->toArray())
                  ->id('additionalRoundId')
                  ->class('form-select') }}
            </div>
          </div>
          <div class="modal-footer">
            <button type="button"
                    class="btn btn-warning button-menu ms-start"
                    data-bs-dismiss="modal">
              Anuluj
            </button>
             {{ html()->submit('Dodaj do programu')->class('btn btn-primary button-menu ms-auto') }}
          </div>
         </div>
      </div>
    {{ html()->form()->close() }}
  </div>

@stop

@section('customScripts')
    <script src="{{ asset('js/jquery.multisortable.js') }}"></script>

    <script>
    $(function(){
      function showOnly(mode){
        $('#wrap_myround, #wrap_breakshow_name, #wrap_breakshow_dance').addClass('d-none');
        // domyślnie pokazuj selecty (kategoria/typ)
        $('#wrap_category, #wrap_additional').removeClass('d-none');

        if (mode === 'custom') {
          $('#wrap_myround').removeClass('d-none');
        }
        if (mode === 'breakshow') {
          $('#wrap_category, #wrap_additional').addClass('d-none');
          $('#wrap_breakshow_name, #wrap_breakshow_dance').removeClass('d-none');
        }
      }

      showOnly('round');
      $('#my_round').on('change', function () {
        const v = this.value;
        // schowaj wszystko (wrappery!)
        $('#wrap_category, #wrap_additional, #wrap_myround, #wrap_breakshow_name, #wrap_breakshow_dance')
          .addClass('d-none');

        if (v === 'sh_br') { // Pokaz/Przerwa
          $('#wrap_breakshow_name, #wrap_breakshow_dance').removeClass('d-none');
        } else if (v === 'my') { // Zdefiniuj własną:
          $('#wrap_myround, #wrap_category, #wrap_additional').removeClass('d-none');
        } else { // normalna runda
          $('#wrap_category, #wrap_additional').removeClass('d-none');
        }
      }).trigger('change'); // ustawia od razu po otwarciu

/*        $('#my_round').on('change', function () {
            var v = this.value;
            if( v == 'my' ) {
               //console.log('my_round - my ',this.value);
               $('#my_name').show();
               $('#my_category').show();
               $('#my_additional').show();
               $('#my_sb_name').hide();
               $('#my_sb_dance').hide();
            }
            else if( v == 'sh_br' ){
               //console.log('my_round - break ',this.value);
               $('#my_sb_name').show();
               $('#my_sb_dance').show();
               $('#my_category').hide();
               $('#my_additional').hide();
               $('#my_name').hide();
            }
            else {
               //console.log('my_round - inny ',this.value);
               $('#my_category').show();
               $('#my_additional').show();
               $('#my_sb_name').hide();
               $('#my_sb_dance').hide();
            }
        });*/
        $('#withTimes').on('click', function () {
            $('.userTime').addClass('drukarka');
            $('.userTime').removeClass('ekran');
            window.print();
            $('.userTime').addClass('ekran');
            $('.userTime').removeClass('drukarka');
        });
        $('#withoutTimes').on('click', function () {
            window.print();
        });
        $('#withTimes2').on('click', function () {
            $('.userTime').addClass('drukarka');
            $('.userTime').removeClass('ekran');
            window.print();
            $('.userTime').addClass('ekran');
            $('.userTime').removeClass('drukarka');
        });
        $('#withoutTimes2').on('click', function () {
            window.print();
        });

        var printFormat = false;
        var css_h = '@page { size: landscape; }',
            css_v = '@page { size: portrait; }',
            head = document.head || document.getElementsByTagName('head')[0],
            style = document.createElement('style');
        style.type = 'text/css';
        style.media = 'print';
        $('#printFormat').on('click', function() {
           if(printFormat){
              $('#printFormat').text('Pionowo');
              if (style.styleSheet){
                 style.styleSheet.cssText = css_v;
              } else {
                 style.appendChild(document.createTextNode(css_v));
              }
              head.appendChild(style);
           }
           else{
              $('#printFormat').text('Poziomo');
              if (style.styleSheet){
                 style.styleSheet.cssText = css_h;
              } else {
                 style.appendChild(document.createTextNode(css_h));
              }
              head.appendChild(style);
           }
           printFormat = !printFormat;
        });
    });
  </script>
@stop