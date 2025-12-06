@extends('admin.master')

@section('title')
    Aktualna runda
@stop

@section('content')
    <div id="page-wrapper" class="container-fluid">

        {{-- Nagłówek rundy --}}
        <div class="row">
            <div class="col-lg-12">

                @if($round != null)
                    <div class="d-flex justify-content-between align-items-center mt-2 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            @if($prevRoundIdFromDB > 0)
                                <a href="{{ $baseURI }}/admin/roundFromDb/{{ $prevRoundIdFromDB }}"
                                   class="btn btn-secondary btn-sm"
                                   role="button">
                                    <i class="fa fa-step-backward"></i>
                                </a>
                            @endif
                            <h1 class="h3 mb-0">
                                {{ $roundDescription }}, {{ $danceName }}
                            </h1>
                        </div>
                        @if($nextRoundIdFromDB > 0)
                            <a href="{{ $baseURI }}/admin/roundFromDb/{{ $nextRoundIdFromDB }}"
                               class="btn btn-secondary btn-sm"
                               role="button">
                                <i class="fa fa-step-forward"></i>
                            </a>
                        @endif
                    </div>

                    <div class="mb-2 d-flex flex-wrap align-items-center gap-2">
                        @if($roundDescription[0] != 'F')
                            <button class="btn btn-primary d-inline-flex align-items-center"
                                    type="button"
                                    data-bs-toggle="modal"
                                    data-bs-target=".showGroupsModal">
                                Grupy
                                <span class="badge rounded-pill bg-light text-dark ms-2 fs-6">
                                    @if($groups == false)
                                        ---
                                    @else
                                        {{ $groups }}
                                    @endif
                                </span>
                            </button>
                        @endif
                        <button class="btn btn-primary d-inline-flex align-items-center"
                                type="button"
                                data-bs-toggle="modal"
                                data-bs-target=".showCouplesModal">
                            <i class="fa fa-female fa-lg me-1" aria-hidden="true"></i>
                            <i class="fa fa-male fa-lg me-2" aria-hidden="true"></i>
                            <span class="badge rounded-pill bg-light text-dark fs-6">
                                @if($couples == false)
                                    ---
                                @else
                                    {{ $couples }}
                                @endif
                            </span>
                        </button>

                        @if($roundDescription[0] != 'F')
                            <span class="ms-2">
                                <i class="fa fa-lg fa-sign-out fa-fw" aria-hidden="true"></i>
                            </span>
                            <button class="btn btn-deep-orange d-inline-flex align-items-center" type="button">
                                Awans
                                <span class="badge rounded-pill bg-primary ms-2 fs-6">
                                    {{ $votes }}
                                </span>
                            </button>
                        @endif
                    </div>

                    <div class="alternativeDescription mb-3">
                        {{ $roundAlternativeDescription }}
                    </div>

                @else
                    <div class="d-flex justify-content-between align-items-center mt-2 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            @if($prevRoundIdFromDB > 0)
                                <a href="{{ $baseURI }}/admin/roundFromDb/{{ $prevRoundIdFromDB }}"
                                   class="btn btn-secondary btn-sm"
                                   role="button">
                                    <i class="fa fa-step-backward"></i>
                                </a>
                            @endif

                            <h1 class="h3 mb-0">
                                {{ $roundDescription }} - {{ $danceName }}
                            </h1>
                        </div>
                    </div>

                    <div class="alternativeDescription mb-3">
                        {{ $roundAlternativeDescription }}
                    </div>
                @endif

            </div>
        </div>

        {{-- ALERTY --}}
        @if(Session::has('success'))
            <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                <strong>ZAPISANE</strong> {{ Session::get('success', '') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
            </div>
        @endif

        @if(Session::has('alert'))
            <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                <h3 class="mb-0">
                    <strong>BŁĄD!</strong> {{ Session::get('alert', '') }}
                </h3>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
            </div>
        @endif

        <div class="table-responsive mt-3 w-50">
            <table class="table table-striped align-middle">
                <tbody>
                @foreach($judges as $judge)
                    <tr>
                        <td>
                            <div id="status{{ $judge->sign }}" class="d-flex align-items-center gap-3 p-1">
                                <button class="btn-circle">{{ $judge->sign }}</button>
                                @if($judge->without_pass == true)
                                    <a href="{{ $baseURI }}/admin/password/{{ $judge->id }}/false"
                                       class="btn btn-dorange"
                                       role="button">
                                        USTAW HASŁO
                                    </a>
                                @endif
                                <span class="font-14pt">
                                    {{ $judge->lastName }} {{ $judge->firstName }}
                                </span>
                            </div>
                        </td>
                        <td class="text-end">
                            <button id="completed{{ $judge->sign }}"
                                    type="button"
                                    class="btn btn-outline-secondary hidden fa fa-check-square-o fa-lg judgeResultsButton"
                                    data-bs-toggle="modal"
                                    data-bs-target=".judgeResults"
                                    data-judge-sign="{{ $judge->sign }}"
                                    data-judge-name="{{ $judge->firstName }} {{ $judge->lastName }}">
                            </button>
                            <div id="completedText{{ $judge->sign }}" class="d-none"></div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        @if($roundIdFromDB == 0)
            <div class="row mt-3">
                <div class="col-lg-12">
                    <div class="d-flex justify-content-end gap-2">

                        @if(count($roundsToUndo) > 0)
                            <button type="button"
                                    class="btn btn-danger button-menu"
                                    data-bs-toggle="modal"
                                    data-bs-target=".roundUndoModal">
                                Powtórz taniec
                            </button>
                        @else
                            <button type="button"
                                    class="btn btn-danger button-menu"
                                    data-bs-toggle="modal"
                                    data-bs-target=".roundUndoModal"
                                    disabled>
                                Powtórz taniec
                            </button>
                        @endif

                        <a class="confirmation"
                           href="{{ $baseURI }}/admin/round/forceCloseDance/{{ $localRoundId }}">
                            <button type="button" class="btn btn-warning button-menu">
                                Zakończ taniec
                            </button>
                        </a>

                        @if(count($roundsToClose) > 0)
                            <button type="button"
                                    class="btn btn-success button-menu"
                                    data-bs-toggle="modal"
                                    data-bs-target=".roundCloseModal">
                                Zapisz rundę
                            </button>
                        @else
                            <button type="button"
                                    class="btn btn-success button-menu"
                                    data-bs-toggle="modal"
                                    data-bs-target=".roundCloseModal"
                                    disabled>
                                Zapisz rundę
                            </button>
                        @endif

                    </div>
                </div>
            </div>
        @endif

    </div> {{-- /#page-wrapper --}}

    <div class="modal fade roundCloseModal" tabindex="-1" aria-labelledby="roundCloseModalLabel" aria-hidden="true">
        {{ html()->form('POST', url('admin/round'))->open() }}
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="roundCloseModalLabel">Zapisz rundę</h5>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="roundToClose" class="form-label">Wybierz rundę do zapisania</label>
                        <select name="roundToClose" id="roundToClose" class="form-select">
                            @foreach($roundsToClose->reverse() as $roundToClose)
                                <option value="{{ $roundToClose->id }}">
                                    {{ $roundToClose->description }}
                                </option>
                            @endforeach
                        </select>
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

    <div class="modal fade roundUndoModal" tabindex="-1" aria-labelledby="roundUndoModalLabel" aria-hidden="true">
        {{ html()->form('POST', url('admin/round/undoRound'))->open() }}
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="roundUndoModalLabel">Powtórz taniec</h5>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="roundToUndo" class="form-label">Wybierz kategorię i taniec do powtórzenia:</label>
                        <select name="roundToUndo" id="roundToUndo" class="form-select">
                            <option selected disabled hidden>— wybierz —</option>
                            @foreach($roundsToUndo->reverse() as $roundToUndo)
                                <option value="{{ $roundToUndo->id }}">
                                    {{ $roundToUndo->description }}, {{ $roundToUndo->dance }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-warning button-menu ms-start"
                            data-bs-dismiss="modal">
                        Anuluj
                    </button>
                    {{ html()->submit('Powtórz taniec')->class('btn btn-primary button-menu ms-auto') }}
                </div>

            </div>
        </div>
        {{ html()->form()->close() }}
    </div>

    <div class="modal fade judgeResults" tabindex="-1" aria-labelledby="judgeResultsLabel" aria-hidden="true">
        {{ html()->form('POST', url('admin/round/undoRound'))->id('judgeUndoForm')->open() }}
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="judgeResultsLabel">Typowania sędziego</h5>
                </div>

                <div class="modal-body">
                    <div><label id="judgeResultsSign" class="fw-bold"></label></div>
                    <div><label id="judgeResultsRound">{{ $roundDescription }}, {{ $danceName }}</label></div>
                    <div id="judgeResultsVotes" class="mt-2"></div>

                    <input type="hidden" name="roundToUndo" value="{{ $localRoundId }}">
                    <input type="hidden" id="judgeToUndo" name="judgeToUndo" value="">
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-warning button-menu ms-start"
                            data-bs-dismiss="modal">
                        Anuluj
                    </button>
                    {{ html()->submit('Powtórz taniec')
                          ->id('judgeUndoButton')
                          ->class('btn btn-danger button-menu ms-auto') }}
                </div>

            </div>
        </div>
        {{ html()->form()->close() }}
    </div>

   <!-- show couples -->
   <div class="modal fade showCouplesModal" tabindex="-1" role="dialog" aria-labelledby="showCouples" aria-hidden="true">
      <div class="modal-dialog modal-lg">
         <div class="modal-content">
            <div class="modal-header">
               <h4 class="modal-title" align="center"><b>Lista par w {{$roundDescription}}</b></h4>
            </div>
            <div class="modal-body">
               <div class="row">
                  <div class="col-lg-12">
                     <table class="table table-bordered">
                        <tbody class="btn-lblue-gray">
                        @if( $names )
                           <tr>
                              <th >Numer</th>
                              <th >Nazwisko i imię</th>
                              <th >Klub /Kraj</th>
                           </tr>
                           @foreach ($names as $couple)
                              
                           <tr>
                              <td class="text-center p-1">{{$couple->number}}</td>
                              <td class="p-1">
                                 {{$couple->firstNameA}} {{$couple->lastNameA}}</br>
                                 {{$couple->firstNameB}} {{$couple->lastNameB}} 
                              </td>
                              <td class="p-1">
                                 {{$couple->club}} </br>
                                 {{$couple->country}}
                              </td>
                           </tr> 
                           @endforeach
                        @endif
                     </tbody>
                     </table>
                  </div>
               </div>
            </div>
         </div>
      </div>
    </div> 

    <div class="modal fade showGroupsModal" tabindex="-1" aria-labelledby="showGroupsLabel" aria-hidden="true">
       <div class="modal-dialog modal-lg">
          <div class="modal-content">
             <div class="modal-header">
                <h5 class="modal-title" id="showGroupsLabel">
                    {{ $danceName }} - grupy {{ $roundDescription }}
                </h5>
             </div>
             <div class="modal-body">
                <div class="row">
                   <div class="col-lg-12">
                      <table class="table table-bordered">
                         <tbody class="btn-lblue-gray">
                         @if($dance)
                            @php $idx = 1; @endphp
                            @foreach($dance->couples as $groups)
                                <tr class="font-14pt">
                                    <td>
                                        @if(count($dance->couples) > 1)
                                            Grupa {{ $idx }}:&nbsp;
                                        @else
                                            Numery:
                                        @endif
                                    </td>
                                    <td>
                                        @foreach ($groups as $couple)
                                            {{ $couple->number }}&nbsp;
                                        @endforeach
                                    </td>
                                    @php $idx++; @endphp
                                </tr>
                            @endforeach
                         @endif
                         </tbody>
                      </table>
                   </div>
                </div>
             </div>

          </div>
       </div>
    </div>

@stop

@section('customScripts')
    <script src="{{ asset('js/adminRound.js') }}"></script>
    <script>
        var adminRefreshTimer = "{{ Config::get('ptt.adminRefreshTimer') }}";
        var roundIdFromDB     = {{ $roundIdFromDB }};
        var baseURI           = "{{ $baseURI }}";

        @if($danceName != null || $roundDescription != null)
            var roundName = "{{ $roundDescription }}, {{ $danceName }}";
        @endif
    </script>
@stop
