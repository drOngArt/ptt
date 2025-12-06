@extends('admin.master')

@section('title')
    Edycja Programu Turnieju
@stop

@section('content')
   <div id="page-wrapper" class="container-fluid">

      {{ html()->form('GET', action('Admin\DashboardController@selectedCategories', ['something']))->open() }}
      <div class="row mb-3">
         <div class="col-lg-12">
            <div class="page-header-break">PROGRAM TURNIEJU</div>

            <div class="d-flex justify-content-between align-items-center mt-2">
               <h1 class="h3 mb-0">Nowy program turnieju</h1>

               <div class="d-flex align-items-center gap-2">
                <div class="dropdown">
                    <button class="btn btn-success button-menu dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                        Dodaj
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end button-menu-dropdown">
                        <li>
                            <a class="dropdown-item button-menu-item"
                              href="#"
                              data-bs-toggle="modal"
                              data-bs-target=".programAddRoundModal"
                              id="addRound">
                                <i class="fa fa-reply"></i>
                                <span class="button-menu-sep"></span>
                                <span>Rundę</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item button-menu-item"
                              href="#"
                              data-bs-toggle="modal"
                              data-bs-target=".programAddShowModal"
                              id="addShow">
                                <i class="fa fa-music"></i>
                                <span class="button-menu-sep"></span>
                                <span>Pokaz</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item button-menu-item"
                              href="#"
                              data-bs-toggle="modal"
                              data-bs-target=".programAddBreakModal"
                              id="addBreak">
                                <i class="fa fa-bell"></i>
                                <span class="button-menu-sep"></span>
                                <span>Przerwę</span>
                            </a>
                        </li>
                    </ul>
                </div>
                 {!! html()
                      ->button('Zapisz jako…', 'button')
                      ->class('btn btn-light-blue button-menu')
                      ->attribute('data-bs-toggle', 'modal')
                      ->attribute('data-bs-target', '.programSelectorModal2')
                      ->id('save_as') !!}

                  {!! html()
                      ->submit('Zatwierdź')
                      ->id('submitButton1')
                      ->class('btn btn-primary button-menu') !!}
               </div>
            </div>

            @if(session('status'))
               @if(session('status') === 'success')
                  <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                     Nowy program turnieju został zapisany :)
                     <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
                  </div>
               @else
                  <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                     Błąd dostępu do pliku ;(
                     <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
                  </div>
               @endif
               {!! Session::forget('status') !!}
            @endif
         </div>
      </div>

      <div class="d-flex justify-content-end">
          <button type="button"
                  class="btn btn-brown button-menu btn-icon-left my-2"
                  onclick="window.print()">
              <i class="fa fa-print"></i>
              <span class="button-menu-sep"></span>
              <span>Drukuj</span>
          </button>
      </div>

      <div class="row">
         <div class="col-lg-12">
            <div class="table-responsive" style="border: 2px solid orange;">
               <table class="table table-striped table-bordered table-hover">
                  <thead>
                     <tr>
                        <th style="width: 60px;">Lp.</th>
                        <th>Runda</th>
                        <th>Tańce</th>
                     </tr>
                  </thead>
                  <tbody id="sortable" class="connectedSortable">
                     @foreach($program as $index => $programRound)
                        <tr class="ui-state-default">
                           <td class="btn-circle">{{ $index + 1 }}.</td>
                           <td style="background-color: {{ $programRound->bg_color }};">
                              {!! html()->hidden('roundId[]',   $index) !!}
                              {!! html()->hidden('roundName[]', $programRound->description) !!}
                              {!! html()->hidden('isDance[]',   $programRound->isDance) !!}

                              <div class="ekran" media="only screen">
                                 <span class="description">{{ $programRound->description }}</span>
                              </div>
                              <div class="drukarka" media="only print">
                                 <span class="description">{{ $programRound->description }}</span>
                              </div>
                           </td>
                           @if($programRound->isDance)
                              <td>
                                 @foreach($programRound->dances as $danceKey => $programRoundDance)
                                    {{ $programRoundDance }}&nbsp;
                                 @endforeach
                              </td>
                           @else
                              <td></td>
                           @endif
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

   <div class="modal fade programSelectorModal2" tabindex="-1" aria-labelledby="programSelector" aria-hidden="true">
      {{ html()->form('GET', action('Admin\DashboardController@selectedCategories', ['saveFile']))->open() }}
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="programSelectorLabel">Zapisz program turnieju</h5>
            </div>
            <div class="modal-body">
               <div class="mb-3">
                  <label class="form-label">Wpisz nazwę nowego programu</label>
                  {{ html()->text('fileName', 'Program Turnieju ')
                      ->placeholder('nazwa pliku')
                      ->maxlength(32)
                      ->class('form-control d-inline-block w-auto text-start') }}
                  <strong>.csv</strong>
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

   <div class="modal fade programAddRoundModal" tabindex="-1" aria-labelledby="programAddRound" aria-hidden="true">
      {{ html()->form('POST', action('Admin\DashboardController@postSelectedCategories', ['addRound']))
           ->acceptsFiles(false)
           ->open() }}
      <div class="modal-dialog modal-lg">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="programAddRoundLabel">Dodaj rundę do programu turnieju</h5>
            </div>
            <div class="modal-body">
               <div class="mb-3">
                  <label for="round" class="form-label fw-semibold">Runda:</label>
                  {{ html()->select('round')->options($roundNames)->class('form-select mb-2') }}
                  <label for="category" class="form-label fw-semibold">Kategoria:</label>
                  {{ html()->select('category')->options($categoriesNames)->class('form-select mb-2') }}
                  <label for="additional" class="form-label fw-semibold">Typ rundy:</label>
                  {{ html()->select('additional')->options($additNames)->class('form-select') }}
               </div>
            </div>
            <div class="modal-footer">
               {{ html()->button('Anuluj')
                   ->type('button')
                   ->class('btn btn-warning button-menu ms-start')
                   ->attribute('data-bs-dismiss','modal') }}
               {{ html()->submit('Dodaj')
                   ->class('btn btn-primary button-menu ms-auto') }}
            </div>
         </div>
      </div>
      {{ html()->form()->close() }}
   </div>

   <div class="modal fade programAddShowModal" tabindex="-1" aria-labelledby="programAddShow" aria-hidden="true">
      {{ html()->form('POST', action('Admin\DashboardController@postSelectedCategories', ['addShow']))->open() }}
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="programAddShowLabel">Dodaj pokaz do programu turnieju</h5>
            </div>
            <div class="modal-body">
               <div class="mb-3">
                  {{ html()->text('showName', 'Pokazy')
                      ->placeholder('nazwa pokazu')
                      ->maxlength(40)
                      ->class('form-control mb-2') }}
                  {{ html()->text('showNameDance', '')
                      ->placeholder('co tańczą?? S R JV')
                      ->maxlength(30)
                      ->class('form-control') }}
               </div>
            </div>
            <div class="modal-footer">
               {{ html()->button('Anuluj')
                   ->type('button')
                   ->class('btn btn-warning button-menu ms-start')
                   ->attribute('data-bs-dismiss','modal') }}
               {{ html()->submit('Dodaj')
                   ->class('btn btn-primary button-menu ms-auto') }}
            </div>
         </div>
      </div>
      {{ html()->form()->close() }}
   </div>

   <div class="modal fade programAddBreakModal" tabindex="-1" aria-labelledby="programAddBreak" aria-hidden="true">
      {{ html()->form('POST', action('Admin\DashboardController@postSelectedCategories', ['addBreak']))->open() }}
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="programAddBreakLabel">Dodaj przerwę do programu turnieju</h5>
            </div>
            <div class="modal-body">
               <div class="mb-3">
                  {{ html()->text('BreakName', 'Przerwa')
                      ->placeholder('nazwa przerwy')
                      ->maxlength(40)
                      ->class('form-control mb-2') }}
                  {{ html()->text('breakTime', '')
                      ->placeholder('minut?')
                      ->maxlength(3)
                      ->class('form-control') }}
               </div>
            </div>
            <div class="modal-footer">
               {{ html()->button('Anuluj')
                   ->type('button')
                   ->class('btn btn-warning button-menu')
                   ->attribute('data-bs-dismiss','modal') }}
               {{ html()->submit('Dodaj')
                   ->class('btn btn-primary button-menu ms-start') }}
            </div>
         </div>
      </div>
      {{ html()->form()->close() }}
   </div>

@stop

@section('customScripts')
   <script src="{{ asset('js/adminProgramEdit.js') }}"></script>
   <script>
      var submit1 = document.getElementById('submitButton1');
      var save_as = document.getElementById('save_as');
      var add_sth = document.getElementById('main-menu');

      $(submit1).prop('disabled', true);
      submit1.classList.add('btn-primary');

      $(function() {
         var removeIntent = false;

         $('td').each(function(){
           $(this).css('width', $(this).width() +'px');
         });

         $(".connectedSortable").sortable({
            connectWith: ".connectedSortable",
            revert: 100,
            start: function(event, ui){
              ui.item.css('border-radius','8px');
              ui.item.css('border','2px solid #428bca');
              ui.item.data('start_pos', ui.item.index());
            },
            over: function(event,ui){
              removeIntent = false;
            },
            out: function(event,ui){
              removeIntent = true;
            },
            beforeStop: function(event,ui){
              if (removeIntent) {
                ui.item.remove();
              }
            },
            update: function(event,ui){
              $(this).find('tr').each(function(i){
                 $(this).find('td:first').text((i+1) + '.');
              });
            },
            stop: function(event,ui){
              ui.item.css('border','');
              $(submit1).prop('disabled', false);
              submit1.classList.add('btn-danger');
              submit1.classList.remove('btn-primary');
              $(save_as).prop('disabled', true);
              $(add_sth).prop('disabled', true);
            },
         }).disableSelection();
      });
   </script>
@stop
