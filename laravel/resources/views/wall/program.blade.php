@extends('wall.master')

@section('title')
    Program Turnieju
@stop

@php
    use Illuminate\Support\Arr;

    // normalizacja wejścia do pustych tablic
    $rounds     = is_array($rounds ?? null) ? array_values($rounds) : [];
    $danceNames = is_array($danceNames ?? null) ? array_values($danceNames) : [];
    $times      = is_array($times ?? null) ? array_values($times) : [];
    $couples    = is_array($couples ?? null) ? array_values($couples) : [];
    $couplesNo  = is_array($couplesNo ?? null) ? array_values($couplesNo) : [];
    $groupConst = is_array($groupConst ?? null) ? array_values($groupConst) : [];

    // pomocnicze „safe get”
    $get = function ($array, $index, $default = '') {
        return \Illuminate\Support\Arr::get($array, $index, $default);
    };
@endphp


@section('content')

   <?php $lastDescription = null; ?>
   <div id="page-wrapper-left">
      <div class="row col-lg-12">
            <h1 class="page-header">PROGRAM&nbsp; TURNIEJU</br></h1>
            <h3>@if( $times[0] )
                    Aktualny czas: {{$times[0]}}
                @endif    
                @if( count($compressedProgram) )
                    Koniec: ~ {{$times[count($compressedProgram)+1]}}
                @endif
           </h3>
        </div>
        <!-- /.row -->
      @include('wall.scheduleTable')
   </div>
   

   <div id="page-wrapper-right">
      @if($rounds == null )
         <div class="row col-lg-12">
            <h3 class="w_page-header">...trwa przygotowanie danych turnieju ... :))</h3>
         </div>
      @else
         <?php $pos=0; ?>
         @foreach($rounds as $round)
         <div class="row col-lg-12">
            @if( $roundDescriptions[$pos] != null && $lastDescription != $roundDescriptions[$pos] )
               <h2 class="w_page-header">
                  @if($roundAlternativeDescriptions[$pos] != "")
                     {{$roundAlternativeDescriptions[$pos]}}
                  @else
                     {{$roundDescriptions[$pos]}}
                  @endif
                  @if( $couplesNo[$pos] )
                      (<i class="fa fa-female" aria-hidden="true"></i><i class="fa fa-male" aria-hidden="true"></i>
                      {{$couplesNo[$pos]}})
                  @endif
               </h2></br>
               <?php $lastDescription = $roundDescriptions[$pos]; ?>
            @endif
            @if($round != null && $danceNames[$pos] != null )
               <h4 class="w_page-header-dance">&nbsp;{{$danceNames[$pos]}}&nbsp;</h4>
            @elseif( $danceNames[$pos] != null )
               <h4 class="w_page-header-dance">&nbsp;{{$danceNames[$pos]}}&nbsp;</h4>
            @endif
         </div>
            
         <div class="col-lg-12">
            @if($round != null)
            <table class="table table-responsive">
               <tbody>
                  @if($couples[$pos] != null)
                     @foreach($couples[$pos] as $index => $group)
                     <tr>
                        <td>
                        <div class="table-couples-main">
                        @if( $groupConst[$pos] == true )
                           Grupa&nbsp;stała&nbsp;{{$index+1}}:
                        @elseif( count($couples[$pos]) > 1 )
                           Grupa&nbsp;{{$index+1}}:
                        @else
                           Pary:
                        @endif
                        </div></td>
                        <td>
                           <div class="table-couples-main">
                              <?php $idx = 0; ?>
                              @foreach($group as $couple)
                                 {{$couple->number}}
                                 <?php $idx += 1; ?>
                                 @if( $idx < count($group) )
                                       ,
                                 @endif
                              @endforeach
                           </div>
                        </td>
                     </tr>
                     @endforeach
                  @endif
               </tbody>
            </table>
            @endif
            <?php $pos = $pos+1; ?>
         </div> 
         @endforeach
      @endif
    </div>

    <!-- /#page-wrapper -->
@stop


@section('customScripts')
    {!! HTML::script('js/wallRound.js') !!}
    <script>
        var wallRefreshTimer = "{{Config::get('ptt.wallRefreshTimer')}}";
        var color = "{{Input::get('colorSet')}}";
        var factor = "{{Input::get('divideFactor')}}";

        @if($rounds[0] != null)
            var roundName = "{{$roundDescriptions[0]}}" + ", " + "{{$danceNames[0]}}";
        @endif
    </script>
@stop

