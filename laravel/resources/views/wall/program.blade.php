@extends('wall.master')

@section('title')
  Program Turnieju
@stop

@php
  $rounds     = is_array($rounds ?? null) ? array_values($rounds) : [];
  $danceNames = is_array($danceNames ?? null) ? array_values($danceNames) : [];
  $times      = is_array($times ?? null) ? array_values($times) : [];
  $couples    = is_array($couples ?? null) ? array_values($couples) : [];
  $couplesNo  = is_array($couplesNo ?? null) ? array_values($couplesNo) : [];
  $groupConst = is_array($groupConst ?? null) ? array_values($groupConst) : [];

  $lastDescription = null;
@endphp

@section('content')
  @php
  $cs = (string) request()->input('colorSet', '3');
  $df = (string) request()->input('divideFactor', '36');
@endphp

    <div class="theme-{{ $cs }}" style="--wall-left: {{ is_numeric($df) ? $df.'%' : '36%' }};">
      <div class="wall-layout">
        <aside class="wall-left">
          <h1 class="mb-2">PROGRAM TURNIEJU</h1>
    
          <h3 class="mb-3">
            @if(!empty($times[0]))
              Aktualny czas: {{ $times[0] }}
            @endif
            @if(!empty($compressedProgram))
              <br>Koniec: ~ {{ $times[count($compressedProgram)+1] ?? '' }}
            @endif
          </h3>

          @include('wall.scheduleTable')
        </aside>

        <main class="wall-right">
        <div>

          @if(empty($rounds))
            <h3 class="w_page-header">...trwa przygotowanie danych turnieju ... :))</h3>
          @else
            @foreach($rounds as $pos => $round)
              @php
                $desc = $roundDescriptions[$pos] ?? null;
                $alt  = $roundAlternativeDescriptions[$pos] ?? '';
              @endphp

              @if($desc && $lastDescription !== $desc)
                <h2 class="w_page-header mb-1">
                  {{ $alt !== '' ? $alt : $desc }}

                  @if(!empty($couplesNo[$pos]))
                    (<i class="fa fa-female" aria-hidden="true"></i><i class="fa fa-male" aria-hidden="true"></i>
                    {{ $couplesNo[$pos] }})
                  @endif
                </h2>
                @php $lastDescription = $desc; @endphp
              @endif

              @if(!empty($danceNames[$pos]))
                <h4 class="w_page-header-dance mb-1">&nbsp;{{ $danceNames[$pos] }}&nbsp;</h4>
              @endif

              @if(!empty($round))
               <table class="table mb-1 couples-table">
                <tbody>
                  @if(!empty($couples[$pos]))
                    @foreach($couples[$pos] as $index => $group)
                      <tr>
                        {{-- KOLUMNA 1 – STAŁA --}}
                        <td class="couples-col-label">
                          <div class="table-couples-main">
                            @if(!empty($groupConst[$pos]))
                              Grupa&nbsp;stała&nbsp;{{ $index+1 }}
                            @elseif(count($couples[$pos]) > 1)
                              Grupa&nbsp;{{ $index+1 }}
                            @else
                              Numery:
                            @endif
                          </div>
                        </td>
                        {{-- KOLUMNA 2 – NUMERY --}}
                        <td class="couples-col-numbers">
                          <div class="table-couples-main">
                            @foreach($group as $i => $couple)
                              {{ $couple->number }}@if($i < count($group)-1), @endif
                            @endforeach
                          </div>
                        </td>
                      </tr>
                    @endforeach
                  @endif
                </tbody>
              </table>
              @endif
            @endforeach
          @endif

        </div>
      </main>

    </div>
  </div>
@stop

@section('customScripts')
  <script src="{{ asset('js/wallRound.js') }}"></script>
  <script>
    window.wallRefreshTimer = @json(config('ptt.wallRefreshTimer'));
    window.colorSet  = @json(request()->input('colorSet'));
    window.divideFactor = @json(request()->input('divideFactor'));

    @if(!empty($rounds) && !empty($roundDescriptions[0]) && !empty($danceNames[0]))
      window.roundName = @json(($roundDescriptions[0] ?? '').', '.($danceNames[0] ?? ''));
    @endif
  </script>
@stop
