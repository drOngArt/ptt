@extends('wall.master')

@section('title')
  WALL - Konfiguracja
@stop

@php
  // aktualny wybór (z URL)
  $cs = (string) request()->input('colorSet', '3');
  $df = (string) request()->input('divideFactor', '36'); // % lewego panelu

  // Nazwy zestawów (bez cyferek w UI)
  $colorSetLabels = [
    '1' => 'Czarno-biały',
    '2' => 'Biało-czarny',
    '3' => 'Żółto-niebieski',
    '4' => 'Złoto-brąz',
    '5' => 'ZW Arial',
  ];

  // Szerokości (Twoje wartości) – pokazujemy ładny opis
  // Jeśli $divideFactor masz z kontrolera jako np. ['35'=>'35', '40'=>'40'...]
  // to i tak wyświetlimy jako "35% / 65%".
  $widthOptions = [];
  foreach(($divideFactor ?? []) as $k => $v){
    $key = (string) $k;
    if (!is_numeric($key)) $key = (string) $v;
    if (is_numeric($key)) {
      $left = (int) $key;
      $right = 100 - $left;
      $widthOptions[(string)$left] = "{$left}% / {$right}%";
    }
  }
  if (empty($widthOptions)) {
    // fallback gdyby lista przyszła pusta
    $widthOptions = [
      '35' => '35% / 65%',
      '40' => '40% / 60%',
      '45' => '45% / 55%',
      '50' => '50% / 50%',
    ];
  }
@endphp

@section('content')
<div class="container-fluid px-3 py-3">

  <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
    <div>
      <h1 class="h3 mb-1">Aktualna konfiguracja</h1>
      <div class="text-muted">Wybierz kolory i podział paneli. Podgląd zmienia się od razu.</div>
    </div>

    {{-- FORM: wysyłamy dopiero po kliknięciu Zatwierdź --}}
    {!! html()->form('GET', action('Wall\DashboardController@showDashboard'))
          ->id('wallConfigForm')
          ->class('d-flex flex-wrap align-items-end gap-2')
          ->open() !!}

      {{-- trzymamy wartości w hiddenach (żeby dropdown buttony wyglądały jak w Twojej appce) --}}
      {!! html()->hidden('colorSet', $cs)->id('colorSetHidden') !!}
      {!! html()->hidden('divideFactor', $df)->id('divideFactorHidden') !!}

      {{-- Dropdown: Kolory --}}
      <div class="dropdown">
        <button class="btn btn-deep-orange button-menu dropdown-toggle"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                id="btnColors">
          Kolory: <span class="ms-1" id="labelColors">{{ $colorSetLabels[$cs] ?? '—' }}</span>
        </button>
        <ul class="dropdown-menu">
          @foreach($colorSetLabels as $val => $label)
            <li>
              <a class="dropdown-item js-color" href="#"
                 data-value="{{ $val }}"
                 data-label="{{ $label }}">
                {{ $label }}
              </a>
            </li>
          @endforeach
        </ul>
      </div>

      {{-- Dropdown: Szerokość --}}
      <div class="dropdown">
        <button class="btn btn-indigo button-menu dropdown-toggle"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                id="btnWidth">
          Podział: <span class="ms-1" id="labelWidth">{{ $widthOptions[$df] ?? ($df.'% / '.(100-(int)$df).'%') }}</span>
        </button>
        <ul class="dropdown-menu">
          @foreach($widthOptions as $val => $label)
            <li>
              <a class="dropdown-item js-width" href="#"
                 data-value="{{ $val }}"
                 data-label="{{ $label }}">
                {{ $label }}
              </a>
            </li>
          @endforeach
        </ul>
      </div>

      {{-- Zatwierdź --}}
      <button type="submit" class="btn btn-primary button-menu">
        Zatwierdź
      </button>

    {!! html()->form()->close() !!}
  </div>

  {{-- PODGLĄD: tu NIE wychodzimy ze strony, tylko zmieniamy klasę theme-X i --wall-left --}}
  <div class="mt-3 theme-{{ $cs }}" id="wallPreviewTheme"
       style="--wall-left: {{ is_numeric($df) ? $df.'%' : '36%' }};">
    <div class="wall-layout wall-layout-preview">

      {{-- LEWA: schedule (przykład jak wcześniej: opisy + tańce + numerki) --}}
      <aside class="wall-left wall-schedule">
        <h2 class="h4 mb-2">PROGRAM TURNIEJU</h2>

        <div class="table-responsive">
          <table class="table table-striped mb-0">
            <tbody>
              <tr>
                <td class="wall-schedule-desc">
                  1/2 Finału 10-11F Kombinacja 6T
                </td>
                <td class="wall-schedule-dance">
                  <span class="wall-pill btn-deep-orange">
                    WA <span class="badge ms-1">1</span>
                    <i class="fa fa-pulse fa-spinner fa-lg ms-2"></i>
                  </span>
                </td>
                <td class="wall-schedule-dance">
                  <span class="wall-pill btn-indigo">VW <span class="badge ms-1">2</span></span>
                </td>
                <td class="wall-schedule-dance">
                  <span class="wall-pill btn-indigo">Q</span>
                </td>
              </tr>

              <tr>
                <td class="wall-schedule-desc">
                  1/2 Finału 12-13E Kombinacja 8T
                </td>
                <td class="wall-schedule-dance">
                  <span class="wall-pill btn-indigo">WA</span>
                </td>
                <td class="wall-schedule-dance">
                  <span class="wall-pill btn-indigo">T <span class="badge ms-1">3</span></span>
                </td>
                <td class="wall-schedule-dance">
                  <span class="wall-pill btn-indigo">WW</span>
                </td>
              </tr>

              <tr>
                <td class="text-muted wall-schedule-note" colspan="4">
                  Przerwa techniczna / dekoracja
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </aside>

      {{-- PRAWA: przykładowa runda + grupy + numery --}}
      <main class="wall-right">
        <h2 class="w_page-header">
          1/2 Finału 10-11F Kombinacja 6T
          (<i class="fa fa-female" aria-hidden="true"></i><i class="fa fa-male" aria-hidden="true"></i> 12)
        </h2>

        <h4 class="w_page-header-dance">&nbsp;WA VW Q&nbsp;</h4>

        <div class="table-responsive">
          <table class="table table-striped mb-0">
            <tbody>
              <tr>
                <td style="white-space:nowrap;">
                  <div class="table-couples-main">Grupa&nbsp;stała&nbsp;1:</div>
                </td>
                <td>
                  <div class="table-couples-main">31, 32, 34, 36, 41, 44</div>
                </td>
              </tr>
              <tr>
                <td style="white-space:nowrap;">
                  <div class="table-couples-main">Grupa&nbsp;stała&nbsp;2:</div>
                </td>
                <td>
                  <div class="table-couples-main">33, 35, 37, 38, 42, 45</div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </main>

    </div>
  </div>

</div>
@stop

@section('customScripts')
<script>
  (function () {
    const preview = document.getElementById('wallPreviewTheme');

    const colorHidden  = document.getElementById('colorSetHidden');
    const widthHidden  = document.getElementById('divideFactorHidden');

    const labelColors = document.getElementById('labelColors');
    const labelWidth  = document.getElementById('labelWidth');

    function setTheme(cs){
      // usuń stare theme-X
      preview.className = preview.className.replace(/\btheme-\d+\b/g, '').trim();
      preview.classList.add('theme-' + cs);
    }

    function setWidth(df){
      preview.style.setProperty('--wall-left', df + '%');
    }

    // Kolory (dropdown click)
    document.querySelectorAll('.js-color').forEach(a => {
      a.addEventListener('click', function(e){
        e.preventDefault();
        const val = this.getAttribute('data-value');
        const lab = this.getAttribute('data-label');

        colorHidden.value = val;
        labelColors.textContent = lab;
        setTheme(val);
      });
    });

    // Szerokość (dropdown click)
    document.querySelectorAll('.js-width').forEach(a => {
      a.addEventListener('click', function(e){
        e.preventDefault();
        const val = this.getAttribute('data-value');
        const lab = this.getAttribute('data-label');

        widthHidden.value = val;
        labelWidth.textContent = lab;
        setWidth(val);
      });
    });

    // start: ustaw podgląd zgodnie z URL
    setTheme(colorHidden.value || '3');
    const df = widthHidden.value || '36';
    if (!isNaN(parseInt(df, 10))) setWidth(parseInt(df, 10));
  })();
</script>
@stop
