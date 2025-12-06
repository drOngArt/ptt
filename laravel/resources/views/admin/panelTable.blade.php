@extends('admin.master')

@section('title')
  Panel
@stop

@section('content')
<div id="page-wrapper" class="container-fluid">

  <div class="page-header-break text-center mb-3">
    LISTA SĘDZIÓW&nbsp;{{ $parts }}<br>
  </div>

  {{ html()->form('POST', url('admin/postPanel'))->open() }}

  <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="page-header mb-0">Panel sędziowski&nbsp;&nbsp;{{ $parts }}</h1>
        <div class="d-flex gap-2">
          {{ html()->submit('Zapisz...')->name('save')->class('btn btn-cyan button-menu') }}
          {{ html()->submit('Powrót')->id('submitButton1')->class('btn btn-primary button-menu') }}
        </div>
      </div>
    </div>
  </div>

  @if(session('status') === 'error')
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      Brak pliku 'Listy_{{ $eventId }}.csv' w katalogu turnieju lub nieprawidłowy format.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
    </div>
  @endif

  <div class="row g-3">
    <div class="col-lg-12 d-flex justify-content-between align-items-start">
      <div class="me-3 ekran">
        {{ html()->label('Sędzia główny:')->class('form-label fw-semibold mb-1') }}
        {{ html()->select('MainJudge', $judgelist, null)->id('mainJudge')->class('form-select') }}
        <br>
        <div class="row g-2">
          <div class="col-sm-6 col-md-4">
            {{ html()->text('main_judge_l')->placeholder('Nazwisko')->maxlength(25)->class('form-control my_main_judge') }}
          </div>
          <div class="col-sm-6 col-md-4">
            {{ html()->text('main_judge_f')->placeholder('Imię')->maxlength(15)->class('form-control my_main_judge') }}
          </div>
          <div class="col-sm-6 col-md-4">
            {{ html()->text('main_judge_c')->placeholder('Miasto')->maxlength(15)->class('form-control my_main_judge') }}
          </div>
        </div>
      </div>

      <div class="ms-auto">
        <div class="d-flex justify-content-end align-items-center gap-2">
          <button id="selectAll" type="button" class="btn btn-deep-orange button-menu">Zaznacz</button>

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
                <a class="dropdown-item print-dd-item" href="#" id="printFormatV">
                  <i class="fa fa-ellipsis-v me-2 px-1"></i>
                  <span class="dd-sep"></span>
                  Pionowo
                </a>
              </li>
              <li>
                <a class="dropdown-item print-dd-item" href="#" id="printFormatH">
                  <i class="fa fa-ellipsis-h me-2"></i>
                  <span class="dd-sep"></span>
                  Poziomo
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row mt-1">
    <div class="col-12">
      <div class="table-scroll">
        <table id="my_table" class="table table-striped table-hover table-panel">
          <thead class="font-14pt">
            <tr class="header-row">
              {{-- 1. kolumna – Kategoria --}}
              <th class="headcol sticky-col-1 text-end align-bottom fs-4">
                Kategoria<br>Klasa<br>Styl
              </th>
              {{-- 2. kolumna – pionowe "Liczba sędziów" --}}
              <th class="sticky-col-2 align-bottom sum-header p-2 fs-2">
                &Sigma;
              </th>
              {{-- dalej: SĘDZIOWIE w kolumnach --}}
              @foreach($judges as $pl_id => $judge)
                <th class="text-center fixed-col judge-vertical" data-judge="{{ $pl_id }}">
                  <div class="judge-vertical-text">
                    <span class="lname">{{ $judge->lastName }}</span><br>
                    <span class="fname">{{ $judge->firstName }}</span>
                  </div>
                  <i class="fa fa-chevron-right rowToggle d-block mt-1 py-1"
                    data-judge="{{ $pl_id }}"
                    style="cursor:pointer;"></i>
          
                  {{ html()->hidden('judgeId[]',   $pl_id) }}
                  {{ html()->hidden('judgeName[]', $judge->firstName.' '.$judge->lastName) }}
                </th>
              @endforeach
            </tr>
          </thead>

          <tbody id="sortable">
            @foreach($program as $roundIndex => $category)
              <tr data-round="{{ $roundIndex }}">
                {{-- 1. kolumna – Kategoria --}}
                <th class="headcol sticky-col-1 text-start align-middle">
                  <div class="fw-semibold">{{ $category->categoryName }} {{ $category->className }}</div>
                  <div class="text-muted px-2">{{ $category->styleName }}</div>
                </th>
                {{-- 2. kolumna – licznik (tutaj JS wpisuje 9 (OK), 9 (-2), 9 (+1) ) --}}
                <td class="sticky-col-2 text-center align-middle requirement-cell fs-6"
                    data-judge-no="{{ $category->judgesNo }}">
                  <span class="judge-counter small text-muted"></span>
                </td>
          
                {{-- 3+ kolumny – sędziowie --}}
                @foreach($judges as $pl_id => $judge)
                  @php
                    $checked = isset($judge->sign[$roundIndex]) && $judge->sign[$roundIndex] !== ' ';
                  @endphp
                  <td class="text-center fixed-col">
                    {{ html()->checkbox($roundIndex.'-'.$pl_id, $checked, 1)
                        ->class('form-check-input judgeCheckbox')
                        ->data('round', $roundIndex)
                        ->data('judge', $pl_id) }}
                  </td>
                @endforeach
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        <div class="d-flex flex-wrap align-items-center gap-2">
          {{ html()->select('dodaj', [
              '0' => 'Dodaj sędziego',
              '1' => 'Dodaj sędziego z Bazy:',
              '2' => 'Dodaj sędziego własnego:'
            ], '0')->id('add_judge')->class('form-select w-auto') }}

          {{ html()->text('term')->placeholder('Nazwisko i imię')->id('from_base')->class('form-control ekran w-auto') }}
          {{ html()->text('judgeadd_l')->placeholder('Nazwisko')->id('judgeadd_l')->maxlength(25)->class('form-control ekran my_add w-auto') }}
          {{ html()->text('judgeadd_f')->placeholder('Imię')->id('judgeadd_f')->maxlength(15)->class('form-control ekran my_add w-auto') }}
          {{ html()->text('judgeadd_c')->placeholder('Miasto')->id('judgeadd_c')->maxlength(15)->class('form-control ekran my_add w-auto') }}

          {{ html()->button('Zatwierdź')->id('after_add')->type('button')->class('btn btn-primary') }}
          {{ html()->button('Zatwierdź')->id('after_add_manual')->type('button')->class('btn btn-primary') }}
        </div>
      </div>
    </div>
  </div>

  {{ html()->form()->close() }}
</div>
@stop

@section('customScripts')
  <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
  <script src="{{ asset('js/jquery.dragtable.min.js') }}"></script>

  <script>
  $(function() {
    let selectAllState = false;
    const $table     = $('#my_table');
    const $selectAll = $('#selectAll');

    // === 1. Kolorowanie rundy + licznik ===
    function updateColorForRound(roundIdx) {
      const $row  = $('#my_table tbody tr[data-round="' + roundIdx + '"]');
      const $cell = $row.find('td.requirement-cell');
      if ($cell.length === 0) return;
    
      const needed = parseInt($cell.data('judgeNo'), 10) || 0;
      const taken  = $row.find('.judgeCheckbox:checked').length;
      const $cnt   = $cell.find('.judge-counter');
    
      // wyczyść stare klasy
      $cell.removeClass('table-ok table-low table-high');
      $cnt.removeClass('text-success text-warning text-danger');
    
      if (!needed) {
        $cnt.text(taken);
        return;
      }
    
      const diff = taken - needed;
      let label;
    
      if (diff === 0) {
        label = needed;
        $cell.addClass('table-ok');
        $cnt.addClass('text-success');
      } else if (diff < 0) {
        label = needed + '</br>[' + diff + ']';   // np. 9 (-2)
        $cell.addClass('table-low');
        $cnt.addClass('text-warning');
      } else {
        label = needed + '</br>[+' + diff + ']';  // np. 9 (+1)
        $cell.addClass('table-high');
        $cnt.addClass('text-danger');
      }
      $cnt.html(label);
    }

    function recalcAllRounds() {
      $table.find('tbody tr[data-round]').each(function() {
        updateColorForRound($(this).data('round'));
      });
    }

    // === 2. Kolumna (sędzia) – stan ikonki rowToggle ===
    function updateColumnToggle(judgeId) {
      const $checks = $table.find('.judgeCheckbox[data-judge="'+judgeId+'"]');
      const $icon   = $table.find('.rowToggle[data-judge="'+judgeId+'"]');
      const total   = $checks.length;
      const checked = $checks.filter(':checked').length;
      const allOn   = total > 0 && checked === total;

      $icon
        .toggleClass('fa-chevron-down', allOn)
        .toggleClass('fa-chevron-right', !allOn);
    }

    function recalcAllColumns() {
      $table.find('.rowToggle').each(function() {
        updateColumnToggle($(this).data('judge'));
      });
    }

    // startowo
    recalcAllRounds();
    recalcAllColumns();

    // === 3. klik na pojedynczy checkbox ===
    $(document).on('change', '.judgeCheckbox', function() {
      const roundIdx = $(this).data('round');
      const judgeId  = $(this).data('judge');
      updateColorForRound(roundIdx);
      updateColumnToggle(judgeId);
    });

    // === 4. Globalny „Zaznacz / Odznacz” ===
    $selectAll.on('click', function() {
      const markAll = !selectAllState;
      selectAllState = markAll;

      $table.find('.judgeCheckbox').prop('checked', markAll);

      $selectAll
        .text(markAll ? 'Odznacz' : 'Zaznacz')
        .toggleClass('btn-light-blue', markAll)
        .toggleClass('btn-deep-orange', !markAll);

      recalcAllRounds();
      recalcAllColumns();
    });

    // === 5. rowToggle – przełącznik całej kolumny (jednego sędziego) ===
    $(document).on('click', '.rowToggle', function() {
      const judgeId = $(this).data('judge');
      const $checks = $table.find('.judgeCheckbox[data-judge="'+judgeId+'"]');
      const total   = $checks.length;
      const checked = $checks.filter(':checked').length;
      const allOn   = total > 0 && checked === total;
      const newState = !allOn;

      $checks.prop('checked', newState);

      // przelicz tylko dotknięte rundy
      const touched = {};
      $checks.each(function() {
        const rIdx = $(this).data('round');
        if (!touched[rIdx]) {
          updateColorForRound(rIdx);
          touched[rIdx] = true;
        }
      });

      updateColumnToggle(judgeId);
    });

    // === 6. Druk: portret/landscape (bez zmian) ===
    const css_h = '@page { size: landscape; }';
    const css_v = '@page { size: portrait; }';
    const head  = document.head || document.getElementsByTagName('head')[0];
    const style = document.createElement('style');
    style.type  = 'text/css';
    style.media = 'print';

    $('#printFormatV').on('click', function(e) {
      e.preventDefault();
      style.textContent = css_v;
      head.appendChild(style);
      window.print();
    });
    $('#printFormatH').on('click', function(e) {
      e.preventDefault();
      style.textContent = css_h;
      head.appendChild(style);
      window.print();
    });

    // === 7. Sortowanie rund (wierszy) ===
    $('#sortable').sortable({
      axis: 'y',
      items: 'tr:not(.ui-state-disabled)',
      start: function (_e, ui) {
        ui.item.css({
          'background-color':'#A9F5F2',
          'border-radius':'8px',
          'border':'2px solid #428bca'
        });
      },
      stop: function (_e, ui) {
        ui.item.css({ 'border':'', 'background-color':'#F8ECE0' });
      }
    }).disableSelection();

    // zapamiętanie szerokości komórek (jak wcześniej)
    $table.find('td').each(function(){
      $(this).css('width', $(this).width() +'px');
    });
    // $('#my_table').dragtable(); // przy pivot raczej niepotrzebne

    // === 8. Pokaz/ukryj pola głównego sędziego ===
    $('.my_main_judge, .my_add, #after_add, #after_add_manual, #judgeadd_l, #judgeadd_f, #judgeadd_c, #from_base')
      .addClass('d-none');

    $('#mainJudge').on('change', function () {
      if (this.value == '000000') $('.my_main_judge').removeClass('d-none');
      else                        $('.my_main_judge').addClass('d-none');
    });

    // === 9. Tryby „Dodaj sędziego” ===
    $('#add_judge').on('change', function () {
      const v = this.value;
      if (v == 1) { // z bazy
        $('#from_base').removeClass('d-none').val('');
        $('.my_add, #after_add_manual').addClass('d-none');
        $('#after_add').removeClass('d-none');
      } else if (v == 2) { // ręcznie
        $('#from_base, #after_add').addClass('d-none').val('');
        $('.my_add, #after_add_manual, #judgeadd_l, #judgeadd_f, #judgeadd_c').removeClass('d-none');
      } else {
        $('#from_base, #after_add, #after_add_manual, .my_add, #judgeadd_l, #judgeadd_f, #judgeadd_c')
          .addClass('d-none').val('');
      }
    });

    // === 10. Funkcja: dodanie nowej kolumny (sędziego) ===
    function addJudgeColumn(judgeId, firstname, lastname) {
      // nagłówek
      const thHtml = `
        <th class="text-center fixed-col judge-vertical" data-judge="${judgeId}">
          <div class="judge-vertical-text">
            <span class="lname">${lastname}</span><br>
            <span class="fname">${firstname}</span>
          </div>
          <i class="fa fa-chevron-right rowToggle d-block mt-1"
             data-judge="${judgeId}"
             style="cursor:pointer;"></i>
          <input type="hidden" name="judgeId[]"   value="${judgeId}">
          <input type="hidden" name="judgeName[]" value="${firstname} ${lastname}">
        </th>
      `;
      $table.find('thead tr.header-row').append(thHtml);

      // dla każdej rundy dodaj komórkę z checkboxem
      $table.find('tbody tr[data-round]').each(function() {
        const roundIdx = $(this).data('round');
        const tdHtml = `
          <td class="text-center fixed-col">
            <input type="checkbox"
                   class="form-check-input judgeCheckbox p-1"
                   name="${roundIdx}-${judgeId}"
                   value="1"
                   data-round="${roundIdx}"
                   data-judge="${judgeId}">
          </td>
        `;
        $(this).append(tdHtml);
      });

      updateColumnToggle(judgeId);
    }

    // === 11. Dodaj z bazy ===
    $('#after_add').on('click', function () {
      const val       = $('#from_base').val();
      const lastname  = $.trim((val.split(',')[0] || ''));
      const firstname = $.trim((val.split(',')[1] || ''));
      const judgeId   = $.trim((val.split(',')[3] || '')); // jak wcześniej

      if (!judgeId) return;

      addJudgeColumn(judgeId, firstname, lastname);

      $(this).addClass('d-none');
      $('#from_base').addClass('d-none');
    });

    // === 12. Dodaj ręcznie ===
    $('#after_add_manual').on('click', function () {
      const lastname  = $.trim($('#judgeadd_l').val());
      const firstname = $.trim($('#judgeadd_f').val());
      const city      = $.trim($('#judgeadd_c').val());
      const judgeId   = `${lastname};${firstname};${city};`; // jak u Ciebie

      if (!lastname || !firstname) return;

      addJudgeColumn(judgeId, firstname, lastname);

      $(this).addClass('d-none');
      $('#judgeadd_l, #judgeadd_f, #judgeadd_c, #from_base').addClass('d-none');
    });

    // === 13. Autocomplete z bazy ===
    $('#from_base').autocomplete({
      source: 'autocomplete',
      minLength: 1,
      autofocus: true,
      scroll: true,
      close: function() { $('#after_add').removeClass('d-none'); },
      select: function(_event, ui) {
        $('#from_base').val(ui.item.value);
        return false;
      },
      open: function() { $('.ui-autocomplete').css('z-index', 5000); }
    });

  });
  </script>
@stop
