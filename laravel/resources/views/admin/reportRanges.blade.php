@extends('admin.master')

@section('title')
    Panel
@stop

@section('content')
<div id="page-wrapper" class="container-fluid">

  <div class="page-header-break">
    ZAKRES NUMERÓW STARTOWYCH<br>
  </div>

  {{ html()->form('POST', url('admin/postRanges'))->open() }}

  <div class="row mb-3">
    <div class="col-lg-12">
      <div class="d-flex justify-content-between align-items-center">
        <h1 class="page-header mb-0">Numery startowe</h1>

        <div class="d-flex gap-2">
          @if(count($lists) != 0)
            {{ html()->submit('Zapisz…')
                  ->name('save')
                  ->class('btn btn-info button-menu') }}
          @endif

          {{ html()->a(url()->previous(), 'Anuluj')
                ->class('btn btn-warning button-menu') }}
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

<div class="row mb-3">
  <div class="col-lg-12">

    {{-- Wiersz 1 — Zakres numerów --}}
    <div class="row mb-2">
      <div class="col-lg-12">
        <div class="range-box">
          <div class="input-group">
    
            <span class="input-group-text btn-blue1 ekran width_200px">
              Zakres numerów:
            </span>
    
            {{ html()->input('text', 'main_start_no', $start)
                  ->placeholder('Od')
                  ->maxlength(4)
                  ->class('form-control text-end ekran') }}
    
            {{ html()->input('text', 'main_end_no', $finish)
                  ->placeholder('Do')
                  ->maxlength(4)
                  ->class('form-control text-end ekran') }}
          </div>
        </div>
      </div>
    </div>

    {{-- Wiersz 2 — Brak numerów --}}
    <div class="row mb-2">
      <div class="col-lg-12">
        <div class="range-box">
          <div class="input-group">
    
            <span class="input-group-text btn-blue2 ekran width_200px">
              Brak numerów:
            </span>
    
            {{ html()->input('text', 'lack_no', '')
                  ->placeholder('brakujące numery')
                  ->maxlength(128)
                  ->class('form-control text-start ekran') }}
          </div>
          <small class="text-muted ps-3">(wpisz numery oddzielone przecinkiem)</small>
        </div>
      </div>
    </div>

    {{-- Wiersz 3 — Ten sam numer... --}}
    <div class="row mb-2">
      <div class="col-lg-12">
        <div class="d-flex align-items-center flex-wrap gap-2">
          {{ html()->label('Ten sam numer dla startujących w różnych blokach?')
                ->class('btn btn-blue3 ekran width_450px mb-0') }}

          <div class="form-check ms-2">
            {{ html()->checkbox('agree', 'yes')
                  ->class('form-check-input p-2') }}
          </div>
        </div>
      </div>
    </div>

    {{-- Wiersz 4 — Liczba dodatkowych numerów --}}
    <div class="row mb-3">
      <div class="col-lg-12">
        <div class="range-box">
          <div class="input-group">
    
            <span class="input-group-text btn-blue4 ekran width_450px">
              Liczba dodatkowych wolnych numerów:
            </span>
    
            {{ html()->input('number', 'free_places', 2)
                  ->id('bt_free_numbers')
                  ->class('form-control text-center ekran')
                  ->attribute('min', 0)
                  ->attribute('max', 19)
                  ->required() }}
          </div>
        </div>
      </div>
    </div>


  </div>
</div>


  {{-- Tabela z kategoriami / blokami --}}
  <div class="row">
    <div class="col-lg-12">
      <table class="table table-striped table-bordered table-hover ekran text-center align-middle">
        <thead>
          <tr>
            <th class="text-end">Kategoria</th>
            <th class="text-center">Zdefiniowany zakres</th>
            <th class="text-center">Prezentacji</th>
            <th class="text-center">Numer początkowy</th>
          </tr>
        </thead>
        <tbody>
          @php $idx = 10; @endphp
          @foreach($lists as $category)
            <tr>
              @if($idx != $category->positionW)
                @php $idx = $category->positionW; @endphp

                {{-- Wiersz BLOK-u --}}
                <td colspan="3" class="text-start fw-semibold">
                  {{ html()->hidden('blockId[]', $category->positionW) }}
                  &nbsp;BLOK&nbsp;{{ $category->positionW }}
                </td>
                <td class="text-center">
                  {{ html()->input('text', 'block_no[]', '')
                        ->placeholder('start')
                        ->size(5)
                        ->maxlength(4)
                        ->class('btn btn-cyan ekran') }}
                </td>
            </tr>
            {{-- kolejny wiersz z kategorią --}}
            <tr>
              <td class="text-end">
                {{ html()->hidden('roundId[]',   $category->baseRoundId) }}
                {{ html()->hidden('roundName[]', $category->description) }}
                {{ $category->description }}&nbsp;
              </td>
              <td class="text-center">
                {{ $category->startNo }} - {{ $category->endNo }}
              </td>
              <td class="text-center">
                {{ $category->baseNumberOfCouples }}
              </td>
              <td class="text-center">
                {{ html()->input('text', 'start_no[]', '')
                      ->placeholder('start')
                      ->size(5)
                      ->maxlength(4)
                      ->class('text-center ekran') }}
              </td>
              @else
              {{-- kolejne kategorie w tym samym bloku --}}
              <td class="text-end">
                {{ html()->hidden('roundName[]', $category->description) }}
                {{ html()->hidden('roundId[]',   $category->baseRoundId) }}
                {{ $category->description }}&nbsp;
              </td>
              <td class="text-center">
                {{ $category->startNo }} - {{ $category->endNo }}
              </td>
              <td class="text-center">
                {{ $category->baseNumberOfCouples }}
              </td>
              <td class="text-center">
                {{ html()->input('text', 'start_no[]', '')
                      ->placeholder('start')
                      ->size(5)
                      ->maxlength(4)
                      ->class('text-center ekran') }}
              </td>
              @endif
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  {{ html()->form()->close() }}
</div>
<!-- /#page-wrapper -->
@stop

@section('customScripts')
  <script>
    // miejsce na ewentualne JS dla tego widoku
  </script>
@stop
