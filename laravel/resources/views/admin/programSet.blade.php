@extends('admin.master')

@section('title')
    Edycja Programu Turnieju
@stop

@section('content')
  <div id="page-wrapper" class="container-fluid">
  
    <div class="row mb-3">
      <div class="col-lg-12">
        <div class="page-header-break">PROGRAM TURNIEJU</div>

        @php
          $urlNormal = action('Admin\DashboardController@selectedCategories', ['something']);
          $urlSave   = action('Admin\DashboardController@selectedCategories', ['saveFile']);
        @endphp

        {{ html()->form('GET', $urlNormal)
            ->attribute('id','programForm')
            ->attribute('data-action-normal', $urlNormal)
            ->attribute('data-action-save', $urlSave)
            ->open() }}
        {!! html()->hidden('saveFile', 0)->id('saveFileFlag') !!}
        {!! html()->hidden('fileName', '')->id('fileNameHiddenForm') !!}

        {{-- TU BĘDZIEMY DYNAMICZNIE DODAWAĆ roundId[] --}}
        <div id="submitPayload"></div>
          <div class="d-flex justify-content-between align-items-center mt-2">
            <h1 class="h3 mb-0 ms-2">Nowy program turnieju</h1>
            <div class="ms-auto d-flex align-items-center gap-2">
              <button type="button" id="undoAll" class="btn btn-secondary button-menu">
                <i class="fa fa-undo me-2"></i>Cofnij wszystko
              </button>
              <button type="button"
                      class="btn btn-light-blue button-menu"
                      data-bs-toggle="modal"
                      data-bs-target="#programSelectorModal2"
                      id="save_as">
                Zapisz jako…
              </button>
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
  
    <div class="row g-3">
      {{-- LEWA tabela --}}
      <div class="col-lg-6">
        <div class="table-responsive border border-warning rounded">
          <div class="px-2 py-1 fw-semibold">Dostępne</div>
  
          <table class="table table-striped table-bordered table-hover mb-0 program-tables">
            <thead>
              <tr>
                <th style="width:60px">Lp.</th>
                <th>Runda (Tańce)</th>
                <th style="width:56px" class="text-center"></th>
              </tr>
            </thead>
  
            <tbody id="sortable-left" class="connectedSortable">
              @foreach($program as $index => $programRound)
                @php
                  $dances = '';
                  if($programRound->isDance && !empty($programRound->dances)) {
                    $dances = ' (' . implode(', ', $programRound->dances) . ')';
                  }
                @endphp
  
                <tr class="ui-state-default program-row" data-round-id="{{ $index }}" data-bg="{{ $programRound->bg_color }}">
                  <td class="lp text-center">{{ $index + 1 }}.</td>
  
                  <td class="round-cell" style="background-color: {{ $programRound->bg_color }};">
                    <span class="description">{{ $programRound->description }}{{ $dances }}</span>
  
                    {{-- hiddeny: startowo w LEWEJ tabeli disabled --}}
                    {!! html()->hidden('roundId[]',   $index)->class('row-field')->attribute('disabled', true) !!}
                    {!! html()->hidden('roundName[]', $programRound->description)->class('row-field')->attribute('disabled', true) !!}
                    {!! html()->hidden('isDance[]',   $programRound->isDance)->class('row-field')->attribute('disabled', true) !!}
                    {!! html()->hidden('dances[]', trim($dances))->class('row-field')->attribute('disabled', true) !!}
                  </td>
  
                  <td class="text-center">
                    <button type="button"
                            class="btn btn-sm btn-outline-primary move-right p-2"
                            title="Przenieś do programu">
                      <i class="fa fa-hand-o-right"></i>
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
  
      {{-- PRAWA tabela --}}
      <div class="col-lg-6">
        <div class="table-responsive border border-success rounded">
          <div class="px-2 py-1 fw-semibold">Wybrane</div>
  
          <table class="table table-striped table-bordered table-hover mb-0 program-tables">
            <thead>
              <tr>
                <th style="width:56px"></th>
                <th style="width:60px">Lp.</th>
                <th>Runda (Tańce)</th>
              </tr>
            </thead>
  
            <tbody id="sortable-right" class="connectedSortable">
              {{-- startowo pusto --}}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  
    {{ html()->form()->close() }}
  </div>
<!-- /#page-wrapper -->

  {{-- MODAL: Zapisz jako --}}
  <div class="modal fade" id="programSelectorModal2" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Zapisz program turnieju</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zamknij"></button>
        </div>
  
        <div class="modal-body">
          <label for="fileNameInput" class="form-label">Wpisz nazwę nowego programu</label>
          <div class="d-flex align-items-center gap-2">
            <input id="fileNameInput" type="text" class="form-control" maxlength="32" value="Program Turnieju ">
            <strong>.csv</strong>
          </div>
        </div>
  
        <div class="modal-footer">
          <button type="button" class="btn btn-warning button-menu" data-bs-dismiss="modal">Anuluj</button>
          <button type="button" class="btn btn-primary button-menu" id="saveProgramBtn">Zapisz</button>
        </div>
      </div>
    </div>
  </div>
@stop

@section('customScripts')
  <script src="{{ asset('js/adminProgramEdit.js') }}"></script>
  <script>
  $(function () {
    const $saveAsBtn    = $('#save_as');
    const $saveModalBtn = $('#saveProgramBtn');
    const $form         = $('#programForm');
  
    let removeIntent = false;
  
    function updateSaveButtonsState() {
      const hasRows = $('#sortable-right tr').length > 0;
      $saveAsBtn.prop('disabled', !hasRows);
      $saveModalBtn.prop('disabled', !hasRows);
    }
  
    function ensureButtons() {
      // LEFT: akcja na końcu
      $('#sortable-left tr').each(function() {
        const $tr = $(this);
        if ($tr.find('.move-right').length === 0) {
          if ($tr.children('td').length === 2) $tr.append('<td class="text-center"></td>');
          $tr.children('td').last().html(
            '<button type="button" class="btn btn-sm btn-outline-primary move-right p-2" title="Przenieś do programu">' +
              '<i class="fa fa-hand-o-right"></i>' +
            '</button>'
          );
        }
      });
  
      // RIGHT: akcja na początku
      $('#sortable-right tr').each(function() {
        const $tr = $(this);
        if ($tr.find('.move-left').length === 0) {
          if ($tr.children('td').length === 2) $tr.prepend('<td class="text-center"></td>');
          $tr.children('td').first().html(
            '<button type="button" class="btn btn-sm btn-outline-secondary move-left" title="Przenieś do dostępnych">' +
              '<i class="fa fa-angle-double-left"></i>' +
            '</button>'
          );
        }
      });
    }
  
    function renumber() {
      $('#sortable-left tr').each(function(i){
        $(this).find('td.lp').text((i + 1) + '.');
      });
      $('#sortable-right tr').each(function(i){
        $(this).find('td.lp').text((i + 1) + '.');
      });
    }

    function syncSubmitState() {
      document.querySelectorAll('#sortable-left .row-field').forEach(el => {
        el.disabled = true;
      });

      document.querySelectorAll('#sortable-right .row-field').forEach(el => {
        el.disabled = false;     // to jest kluczowe
      });
    }

    function refreshAll() {
      ensureButtons();
      renumber();
      syncSubmitState();
      updateSaveButtonsState();
    }

    function moveRow($tr, toRight) {
      if (toRight) {
        // usuń akcję z końca
        $tr.find('.move-right').closest('td').remove();
        // dodaj akcję na początek
        $tr.prepend('<td class="text-center"></td>');
        $tr.children('td').first().html(
          '<button type="button" class="btn btn-sm btn-outline-secondary move-left" title="Przenieś do dostępnych">' +
            '<i class="fa fa-angle-double-left"></i>' +
          '</button>'
        );
        $('#sortable-right').append($tr);
      } else {
        // usuń akcję z początku
        $tr.find('.move-left').closest('td').remove();
        // dodaj akcję na koniec
        $tr.append('<td class="text-center"></td>');
        $tr.children('td').last().html(
          '<button type="button" class="btn btn-sm btn-outline-primary move-right p-2" title="Przenieś do programu">' +
            '<i class="fa fa-hand-o-right"></i>' +
          '</button>'
        );
        $('#sortable-left').append($tr);
      }
      $tr.addClass('row-moved');
      const bg = $tr.attr('data-bg');
      if (bg) $tr.find('.round-cell').css('background-color', bg);
      refreshAll();
    }

    // Cofnij wszystko: prawa -> lewa
    $('#undoAll').on('click', function() {
      $('#sortable-right tr').toArray().forEach(tr => moveRow($(tr), false));
    });
  
    // Sortable między tabelami
    $(".connectedSortable").sortable({
      connectWith: ".connectedSortable",
      placeholder: "ui-state-highlight",
      tolerance: "pointer",
      revert: 100,
  
      start: function(_event, ui){
        removeIntent = false;
        ui.item.addClass('dragging');
        ui.item.css({'border-radius':'8px','border':'2px solid #428bca'});
      },
      out: function(){ removeIntent = true; },
      over: function(){ removeIntent = false; },
      beforeStop: function(_event, ui){
        if(removeIntent) ui.item.remove();
      },
      stop: function(_event, ui){
        ui.item.removeClass('dragging').css({'border':''});
        ui.item.addClass('row-moved');
        refreshAll();
      },
      update: function(){ refreshAll(); }
    }).disableSelection();

    // Klik: lewa -> prawa
    $(document).on('click', '.move-right', function() {
      moveRow($(this).closest('tr'), true);
    });

    // Klik: prawa -> lewa
    $(document).on('click', '.move-left', function() {
      moveRow($(this).closest('tr'), false);
    });

    $('#saveProgramBtn').on('click', function () {
      const name = ($('#fileNameInput').val() || '').trim();
      if (!name) { $('#fileNameInput').focus(); return; }

      const $rightRows = $('#sortable-right tr');
      if ($rightRows.length === 0) {
        alert('Program jest pusty – przenieś rundy do prawej tabeli.');
        return;
      }

      // ustaw pola w FORMIE
      $('#fileNameHiddenForm').val(name);
      $('#saveFileFlag').val(1);

      // zbuduj payload w jednym miejscu (pewne wysyłanie)
      const $payload = $('#submitPayload');
      $payload.empty();

      $rightRows.each(function () {
        const roundId = $(this).attr('data-round-id'); // najpewniejsze źródło
        if (roundId !== undefined && roundId !== null && String(roundId).trim() !== '') {
          $payload.append(
            $('<input>', { type: 'hidden', name: 'roundId[]', value: roundId })
          );
        }
      });

      const form = document.getElementById('programForm');
      form.action = form.getAttribute('data-action-save');
      form.submit();
    });

    // start
    refreshAll();
  });
  </script>
@stop
