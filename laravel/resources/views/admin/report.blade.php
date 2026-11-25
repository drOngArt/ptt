@extends('admin.master')
@section('title')
    Raporty / Wyniki
@stop

@section('content')
<div id="page-wrapper">

  {{ html()->form('POST', route('admin.report.submit'))->open() }}
  @csrf

  <div class="row">
    <div class="col-lg-12 d-flex justify-content-between align-items-center mb-1">
      <h1 class="page-header mb-0">Raporty / Wyniki</h1>
        {{-- ZAZNACZ WSZYSTKO --}}
        <button id="selectAll"
            type="button"
            class="btn btn-primary button-menu">
            Zaznacz wszystko
        </button>
      <div class="d-flex justify-content-between align-items-center gap-2">
        <ul class="nav navbar-nav d-flex flex-row gap-2 mb-1">
          {{-- LISTY --}}
          <li class="dropdown">
              {!! html()->button('LISTY', 'button')
                  ->class('btn btn-tangerine button-menu dropdown-toggle')
                  ->attribute('data-bs-toggle', 'dropdown') !!}

              <ul class="dropdown-menu">
                  <li>{!! html()->submit('- przydział numerów')->name('ranges')->class('btn-coral button-menu text-left') !!}</li>
                  <li>{!! html()->submit('- startowe')->name('lists')->class('btn-coral button-menu text-left') !!}</li>
                  <li>{!! html()->submit('- klubów')->name('clubs')->class('btn-coral button-menu text-left') !!}</li>
                  <li>{!! html()->submit('- aktualnych klubów')->name('clubsOpen')->class('btn-coral button-menu text-left') !!}</li>
                  <li>{!! html()->submit('- startów w wielu kl./kat.')->name('couplesBr')->class('btn-coral button-menu text-left') !!}</li>
              </ul>
          </li>
          {{-- RAPORTY --}}
          <li class="dropdown">
              {!! html()->button('RAPORTY', 'button')
                  ->class('btn btn-lsalmon button-menu dropdown-toggle')
                  ->attribute('data-bs-toggle', 'dropdown') !!}

              <ul class="dropdown-menu">
                  <li>{!! html()->submit('- wykaz rund')->name('rounds')->class('btn-coral button-menu text-left') !!}</li>
                  <li>{!! html()->submit('- numery startowe')->name('couples')->class('btn-coral button-menu text-left') !!}</li>
                  <li>{!! html()->submit('- stażysta')->name('trainee')->class('btn-coral button-menu text-left') !!}</li>
              </ul>
          </li>
          {{-- WYNIKI --}}
          <li class="dropdown">
              {!! html()->button('WYNIKI', 'button')
                  ->class('btn btn-coral button-menu dropdown-toggle')
                  ->attribute('data-bs-toggle', 'dropdown') !!}

              <ul class="dropdown-menu">
                  <li>{!! html()->submit('- imienne')->name('results_f')->class('btn-coral button-menu text-left') !!}</li>
                  <li>{!! html()->submit('- skrócone')->name('results_s')->class('btn-coral button-menu text-left') !!}</li>
              </ul>
          </li>
          {{-- IMPREZA --}}
          <li>
              {!! html()->submit('IMPREZA')
                  ->name('impreza')
                  ->class('btn btn-dorange button-menu') !!}
          </li>
        </ul>
      </div>
    </div>
  </div>

  {{-- KOMUNIKATY --}}

  @if($eventId)
      <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <h3><strong>UWAGA !!</strong> {{ $eventId }}</h3>
      </div>
  @endif

  @if($listyCSV)
      <div class="alert alert-warning alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <h3><strong>UWAGA !!</strong> {{ $listyCSV }}</h3>
      </div>
  @endif

  @if(session('status'))
      @if(session('status') === 'success')
          <div class="alert alert-success">
              Wyniki zostały zapisane do pliku.
              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
          </div>
      @else
          <div class="alert alert-danger">
              Błąd dostępu do pliku. Wyniki nie zostały zapisane.
              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
          </div>
      @endif
  @endif

  @if(session()->has('conflict'))
      <div class="alert alert-success">
          {{ session('conflict') }}
          <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
      </div>
  @endif

  {{-- TABELA KATEGORII --}}

  <div class="row">
    <div class="col-lg-12">
      <div class="table-responsive base-rounds-wrapper mx-1">

        <table class="table table-striped table-bordered table-hover p-2">
          <tbody id="baseRounds">
            @php $idx = null; @endphp
            @foreach($baseRounds as $index => $round)
              {{-- NAGŁÓWEK BLOKU --}}
              @if($idx !== $round->positionW)
                @php $idx = $round->positionW; @endphp
                <tr>
                  <td class="text-center" style="width: 15%">
                    <button type="button"
                            id="select_{{ $round->positionW }}"
                            class="btn btn-primary select-block"
                            data-block="{{ $round->positionW }}">
                        Zaznacz
                    </button>
                  </td>
                  <td class="text-center align-middle">
                    &nbsp;BLOK&nbsp;{{ $round->positionW }}
                    [ kategorii - {{ $blockStats[$round->positionW]['categories'] ?? 0 }} ]
                  </td>
                  <td class="text-center align-middle">
                    UCZESTNIKÓW
                    [ {{ $blockStats[$round->positionW]['participants'] ?? 0 }} ]
                  </td>
                  <td class="text-center">&nbsp;</td>
                </tr>
              @endif
              {{-- KATEGORIA --}}
              <tr>
                <td class="text-center check-cell" style="width: 15%">
                  <div class="check-wrapper">
                    <input class="form-check-input round-checkbox roundCheckbox_{{ $round->positionW }}"
                           type="checkbox"
                           id="{{ $round->roundId }}"
                           name="{{ $round->roundId }}"
                           data-block="{{ $round->positionW }}">
                  </div>
                </td>
                <td>
                  {!! html()->hidden('roundId[]', $round->roundId) !!}
                  {{ $round->categoryName }}
                  {{ $round->className }}
                  {{ $round->styleName }}
                </td>
                <td class="text-center">
                    {{ $round->baseNumberOfCouples }}
                </td>
                @if($isManual[$index])
                  <td class="text-center bg-body p-1" style="width: 20%">
                    <a href="reportSet/{{ $round->roundId }}"
                       class="btn btn-dbisque"
                       role="button">
                        Ustal miejsca
                    </a>
                  </td>
                @else
                  <td class="text-center bg-body p-1" style="width: 20%">
                      &nbsp;
                  </td>
                @endif
              </tr>
            @endforeach
          </tbody>
        </table>

      </div>
    </div>
  </div>

  {!! html()->form()->close() !!}

</div>
@stop

@section('customScripts')
<script>
$(function() {

    const allCheckboxes = $('#baseRounds .round-checkbox');

    // Synchronizacja przycisku głównego
    function updateSelectAllButton() {

        const allChecked = allCheckboxes.length > 0 &&
                           allCheckboxes.filter(':checked').length === allCheckboxes.length;

        $('#selectAll')
            .text(allChecked ? 'Odznacz wszystko' : 'Zaznacz wszystko')
            .toggleClass('btn-info', allChecked)
            .toggleClass('btn-primary', !allChecked);
    }

    // Synchronizacja przycisków bloków
    function updateBlockButtons() {

        $('.select-block').each(function() {

            const block = $(this).attr('data-block');

            const checkboxes = allCheckboxes.filter(function() {
                return $(this).attr('data-block') === block;
            });

            const allChecked = checkboxes.length > 0 &&
                               checkboxes.filter(':checked').length === checkboxes.length;

            $(this)
                .text(allChecked ? 'Odznacz' : 'Zaznacz')
                .toggleClass('btn-info', allChecked)
                .toggleClass('btn-primary', !allChecked);
        });
    }

    function updateButtons() {
        updateSelectAllButton();
        updateBlockButtons();
    }

    // ZAZNACZ / ODZNACZ WSZYSTKO
    $('#selectAll').on('click', function() {

        const allChecked = allCheckboxes.length > 0 &&
                           allCheckboxes.filter(':checked').length === allCheckboxes.length;

        allCheckboxes.prop('checked', !allChecked);

        updateButtons();
    });

    // ZAZNACZ / ODZNACZ BLOK
    $('.select-block').on('click', function() {

        const block = $(this).attr('data-block');

        const checkboxes = allCheckboxes.filter(function() {
            return $(this).attr('data-block') === block;
        });

        const allChecked = checkboxes.length > 0 &&
                           checkboxes.filter(':checked').length === checkboxes.length;

        checkboxes.prop('checked', !allChecked);

        updateButtons();
    });

    // RĘCZNA ZMIANA CHECKBOXA
    allCheckboxes.on('change', updateButtons);

    // STAN POCZĄTKOWY
    updateButtons();

});
</script>
@stop
