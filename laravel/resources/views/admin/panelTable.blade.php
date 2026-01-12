@extends('admin.master')

@section('title')
  Panel
@stop

@section('printStyles')
  <style media="print">
    @page { size: A4 {{ (($printMode ?? 'V') === 'H') ? 'landscape' : 'portrait' }}; }
  </style>
@endsection


@section('content')
<div class="d-print-none">
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
          <div class="dropdown print-dropdown">
            <button type="button"
                    class="btn btn-brown button-menu dropdown-toggle"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
              <i class="fa fa-print me-2"></i>
              <span class="border-start border-1 border-light px-2 ms-1">Drukuj</span>
            </button>

            @php $basePrintUrl = url('admin/panelSet'); @endphp

            <ul class="dropdown-menu dropdown-menu-end print-menu"
              <li>
                <a class="dropdown-item d-flex align-items-center gap-2"
                  target="_blank" rel="noopener"
                  href="{{ $basePrintUrl.'?'.http_build_query(['print'=>'V','autoprint'=>1]) }}">
                  <i class="fa fa-file-text-o"></i> Pionowo
                </a>
              </li>
              <li>
                <a class="dropdown-item d-flex align-items-center gap-2"
                  target="_blank" rel="noopener"
                  href="{{ $basePrintUrl.'?'.http_build_query(['print'=>'H','autoprint'=>1]) }}">
                  <i class="fa fa-file-o"></i> Poziomo
                </a>
              </li>
            </ul>
          </div>
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
  <div class="col-12">
    <div class="d-flex align-items-center gap-3 ekran flex-wrap">

      {{-- LABEL + SELECT W JEDNEJ LINII --}}
      <div class="d-flex align-items-center gap-2" style="min-width: 320px;">
        {{ html()->label('Sędzia główny:')
              ->class('form-label fw-semibold mb-0 text-nowrap') }}

        {{ html()->select('MainJudge', $judgelist, null)
              ->id('mainJudge')
              ->class('form-select') }}
      </div>

      {{-- Nazwisko --}}
      <div style="width:160px;">
        {{ html()->text('main_judge_l')
              ->placeholder('Nazwisko')
              ->maxlength(25)
              ->class('form-control my_main_judge') }}
      </div>

      {{-- Imię --}}
      <div style="width:120px;">
        {{ html()->text('main_judge_f')
              ->placeholder('Imię')
              ->maxlength(15)
              ->class('form-control my_main_judge') }}
      </div>

      {{-- Miasto --}}
      <div style="width:140px;">
        {{ html()->text('main_judge_c')
              ->placeholder('Miasto')
              ->maxlength(15)
              ->class('form-control my_main_judge') }}
      </div>

    </div>
  </div>
</div>


  <div class="row mt-1">
    <div class="col-12">
      <div class="table-scroll">
        <table id="my_table" class="table table-striped table-hover table-panel">
          <thead class="font-12pt">
            <tr class="header-row">
              <th class="headcol sticky-col-1 text-end align-bottom fs-4">
                Kategoria<br>Klasa<br>Styl
              </th>
              <th class="sticky-col-2 align-bottom sum-header p-2 fs-2">
                &Sigma;
              </th>
              @foreach($judges as $pl_id => $judge)
                <th class="text-center fixed-col judge-vertical rotate-20" data-judge="{{ $pl_id }}">
                  <div class="judge-vertical-text">
                    <span class="judge-two-lines">
                      <span class="lname">{{ $judge->lastName }}</span>
                      <span class="fname">{{ $judge->firstName }}</span>
                    </span>
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
              @php $pos = $loop->index; @endphp
              <tr data-round="{{ $roundIndex }}">
                {{-- 1. kolumna – Kategoria --}}
                {{ html()->hidden('roundBaseId[]', $category->baseRoundId) }}
                {{ html()->hidden('roundName[]', $category->description) }}
                <th class="headcol sticky-col-1 category-cell text-start align-middle">
                  <div class="category-two-lines">
                    <span class="category-name">
                      {{ $category->categoryName }} {{ $category->className }}
                    </span>
                    <span class="category-style">
                      {{ $category->styleName }}
                    </span>
                  </div>
                </th>
                </th>
                <td class="sticky-col-2 text-center align-middle requirement-cell fs-6"
                    data-judge-no="{{ $category->judgesNo }}">
                    {{ html()->hidden('judgeNo[]', $category->judgesNo) }}
                  <span class="judge-counter small text-muted font-print-18pt"></span>
                </td>
                @foreach($judges as $pl_id => $judge)
                  <td class="text-center fixed-col align-middle">
                    @php
                      $checked = isset($judge->sign[$pos]) && $judge->sign[$pos] !== ' ';
                    @endphp

                    {{ html()
                        ->checkbox($roundIndex.'-'.$pl_id)
                        ->value(1)
                        ->checked($checked)
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
  <script>
    //console.log('judges:', @json($judges));
  </script>
 </div>
</div>
@php
  // tryb druku
  $pm = ($printMode ?? request('print') ?? 'V');     // 'V' albo 'H'
  $perPage = ($pm === 'H') ? 24 : 15;

  // $judges to Twoja mapa pl_id => Judge
  $judgeChunks = array_chunk($judges, $perPage, true);
@endphp

<div class="d-none d-print-block">
  @foreach($judgeChunks as $chunkIndex => $judgesPage)

    {{-- proste “łamanie strony” między porcjami --}}
    @if($chunkIndex > 0)
      <!--<div class="page-break"></div>-->
    @endif

    <div class="print-page">

      <div class="mb-2 fw-bold print-title">
        Panel sędziowski — {{ $parts }}
        (strona {{ $chunkIndex+1 }} / {{ count($judgeChunks) }})
      </div>

      <table class="table table-bordered table-sm print-table">
        <thead class="font-12pt">
          <tr class="header-row">
            {{-- 1. kolumna --}}
            <th class="headcol sticky-col-1 text-end align-bottom fs-4">
              Kategoria<br>Klasa<br>Styl
            </th>

            {{-- 2. kolumna --}}
            <th class="sticky-col-2 align-bottom sum-header p-2 fs-2 text-center">
              &Sigma;
            </th>

            {{-- sędziowie (tylko porcja na tę stronę) --}}
            @foreach($judgesPage as $pl_id => $judge)
              <th class="text-center fixed-col judge-vertical print-judge-col" data-judge="{{ $pl_id }}">
                <div class="judge-rot-wrap">
                <div class="judge-vertical-text">
                  <span class="judge-two-lines">
                    <span class="lname">{{ $judge->lastName }}</span>
                    <span class="fname">{{ $judge->firstName }}</span>
                  </span>
                </div>
                </div>
              </th>
            @endforeach
          </tr>
        </thead>

        <tbody>
          @php $rowPos = 0; @endphp
          @foreach($program as $roundIndex => $category)
            <tr data-round="{{ $roundIndex }}">
              <th class="headcol sticky-col-1 text-start align-middle category-cell">
                <span class="cat-two-lines">
                  <span class="cat-title">{{ $category->categoryName }} {{ $category->className }}</span>
                  <span class="cat-style"> {{ $category->styleName }}</span>
                </span>
              </th>
              <td class="sticky-col-2 text-center align-middle requirement-cell print-sum"
                  data-judge-no="{{ $category->judgesNo }}">
                {{-- w druku możesz zostawić puste albo wstawić samą liczbę --}}
                <span class="judge-counter">{{ $category->judgesNo }}</span>
              </td>

              @foreach($judgesPage as $pl_id => $judge)
                @php
                  // UWAGA: tu używasz już “kolejnego idx”, a nie baseRoundId klucza.
                  // Jeśli u Ciebie sign jest już wypełnione po idx = 0..N, to roundIndex w foreach($program as ...) OK.
                  $checked = isset($judge->sign[$roundIndex]) && $judge->sign[$roundIndex] !== ' ';
                @endphp
                <td class="print-check">
                  <div class="check-wrap">
                    <span class="tick">
                      {!! $checked ? '&#10003;' : '&nbsp;' !!}
                    </span>
                  </div>
                </td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>

    </div>
  @endforeach
</div>

@stop

@section('customScripts')
  <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
  <script src="{{ asset('js/jquery.dragtable.min.js') }}"></script>

  @parent
  @if(request()->boolean('autoprint'))
    <script>
      // Debug: zobaczysz w konsoli, czy w ogóle trafiasz do tego widoku
      console.log('AUTOPRINT on panelSet, print=', @json(request('print')));

      window.addEventListener('load', function () {
        setTimeout(() => window.print(), 200);
      });

      // opcjonalnie zamknij po druku
      window.addEventListener('afterprint', function(){ window.close(); });
    </script>
  @endif

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

    $('#printFormatV').on('click', function(e){
      e.preventDefault();
      const url = new URL(window.location.href);
      url.searchParams.set('print', 'V');
      url.searchParams.set('autoprint', '1');
      window.open(url.toString(), '_blank', 'noopener');   // ✅ ważne
    });
    
    $('#printFormatH').on('click', function(e){
      e.preventDefault();
      const url = new URL(window.location.href);
      url.searchParams.set('print', 'H');
      url.searchParams.set('autoprint', '1');
      window.open(url.toString(), '_blank', 'noopener');
    });

    /*$('#printFormatV').on('click', function(e) {
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
    });*/

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
