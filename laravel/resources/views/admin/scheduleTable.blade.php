<div class="row">
  <?php
    if( !empty($compressedProgram) && count($compressedProgram) ) 
      $maxDances = max(array_map(fn($r) => count($r->dances), $compressedProgram)); 
    else
      $maxDances = 4; ?>
   <div class="col-lg-12">
      <div class="table-responsive base-rounds-wrapper mx-1 mt-1">
         <table class="table table-striped table-bordered table-hover align-middle">
            <thead>
               <tr class="font-14pt px-2">
                  <th >
                     Lp.
                  </th>
                  <th>
                     <div class="alignleft">Runda </div>
                     <div class="alignright">&nbsp&nbsp[ Prezentacje ]</div>
                  </th>
                  <th class="userTime ekran">Czas</th>
                  <th>Gr.</th>
                  <th colspan="{{ $maxDances }}">Tańce</th>
               </tr>
            </thead>
            <tbody>
               <?php $lastDescription = null; $inProgress=1 ?>
               @foreach($compressedProgram as $index => $programRound)
                  @if($programRound->isDance)
                     <tr>
                        <td class="btn-circle fs-5">
                           {{$index+1}}.
                        </td>
                        <td class="py-2">
                           {{-- EKRAN --}}
                           <div class="ekran alignleft {{ $programRound->couples == -1000 ? 'text-decoration-line-through' : '' }}" media="only screen">
                              {{$programRound->description}} <description class="alternativeDescription"> {{$programRound->alternative_description}} </description>
                           </div>
                           <div class="ekran alignright" media="only screen">
                            @if( $programRound->couples > 0 )
                              <span class="badge badge-secondary p-2">&nbsp{{$programRound->couples}}&nbsp</span>
                            @elseif( $programRound->couples > -1000 && $programRound->couples != -1000 ) 
                              <span class="p-1">[&nbsp{{-$programRound->couples}}&nbsp]</span>
                            @elseif( $programRound->couples < -1000 ) 
                              <span class="p-1">z pkt.{{-$programRound->couples-1000}} ->[ &nbsp&nbsp ]</span>
                            @endif
                           </div>
                           {{-- DRUKARKA --}}
                           <div class="drukarka font-print-18pt alignleft {{ $programRound->couples == -1000 ? 'text-decoration-line-through' : '' }}" media="only print">
                           @if( $programRound->alternative_description )
                              {{$programRound->alternative_description}} 
                           @else
                              {{$programRound->description}}
                           @endif
                           </div>
                           <div class="drukarka alignright font-arial" media="only print">
                           @if( $programRound->couples > 0 )
                              <span class="badge badge-secondary p-1 fs-5">&nbsp{{$programRound->couples}}&nbsp</span>
                            @elseif( $programRound->couples > -1000 && $programRound->couples != -1000 ) 
                              <span class="p-1">[&nbsp{{-$programRound->couples}}&nbsp]</span>
                            @elseif( $programRound->couples < -1000 ) 
                              <span class="p-1">z pkt.{{-$programRound->couples-1000}} ->[ &nbsp&nbsp ]</span>
                            @endif
                           </div>
                        </td>
                        <td class="userTime ekran ps-1">
                           @if( $times[$index] )
                           <div class="font-14pt ekran text-center align-middle" media="only screen">
                              <span class="btn-blue-gray badge badge-pill">
                                 {{$times[$index]}}
                              </span>
                           </div>
                           <div class="font-14pt drukarka text-center align-middle" media="only print">
                              <span class="userTime3 font-14pt">
                                 {{$times[$index]}}
                              </span>
                           </div>
                           @endif
                        </td>
                        <td class="font-print-18pt text-center p-1">
                        @if( $programRound->groups != 1 )
                            {{$programRound->groups}}
                        @endif
                        </td>
                           @foreach($programRound->dances as $dance)
                              <td class="font-print-18pt text-center"><tablecell>
                                 @if(!empty($dance['order']))
                                    <tc-order>{{ $dance['order'] }}&nbsp;</tc-order>
                                 @endif
                                 <tc-dance>
                                    @if($dance['closed'])
                                      <span class="dance-closed">{{ $dance['dance'] }}</span>
                                       @if($inProgress!==2)
                                          <?php $inProgress=1 ?>
                                       @endif
                                    @elseif($inProgress==1)
                                       <div class="dance-current">{{$dance['dance']}}</div>
                                       <?php $inProgress=2 ?>
                                    @else
                                      {{$dance['dance']}}
                                    @endif
                                 </tc-dance>
                              </tablecell></td>
                           @endforeach
                           {{-- 🔥 brakujące komórki --}}
                            @for($i = count($programRound->dances); $i < $maxDances; $i++)
                                <td class="p-1">&nbsp;</td>
                           @endfor
                     </tr>
                  @else
                     <tr>
                        <td class="btn-circle">
                           {{$index+1}}.
                        </td>
                        <td class="text-muted">
                           {{$programRound->description}} <description class="alternativeDescription"> {{$programRound->alternative_description}} </description>
                        </td>
                     </tr>
                  @endif
               @endforeach
            </tbody>
         </table>
      </div>
   </div>
</div>