<div class="row">
  <?php
    if( !empty($compressedProgram) && count($compressedProgram) ) 
      $maxDances = max(array_map(fn($r) => count($r->dances), $compressedProgram)); 
    else
      $maxDances = 4; ?>
   <div class="col-lg-12">
      <div class="table-responsive">
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
                           <div class="ekran alignleft" media="only screen">
                              {{$programRound->description}} <description class="alternativeDescription"> {{$programRound->alternative_description}} </description>
                           </div>
                           <div class="ekran alignright" media="only screen">
                            @if( $programRound->couples > 0 )
                              <span class="badge badge-secondary p-2">&nbsp{{$programRound->couples}}&nbsp</span>
                            @elseif( $programRound->couples < 0 ) 
                              <span class="p-1">[&nbsp{{-$programRound->couples}}&nbsp]</span>
                            @endif
                           </div>
                           <div class="drukarka font-print-18pt alignleft" media="only print">
                           @if( $programRound->alternative_description )
                              {{$programRound->alternative_description}} 
                           @else
                              {{$programRound->description}}
                           @endif
                           </div>
                           <div class="drukarka font-print-18pt alignright" media="only print">
                           @if( $programRound->couples > 0 )
                              &nbsp[ {{$programRound->couples}} ]
                           @elseif( $programRound->couples < 0 )
                              &nbsp[ {{-$programRound->couples}} ]
                           @endif
                           </div>
                        </td>
                        <td class="userTime ekran ps-1">
                           @if( $times[$index] )
                           <div class="font-14pt ekran text-center" media="only screen">
                              <span class="btn-blue-gray badge badge-pill">
                                 {{$times[$index]}}
                              </span>
                           </div>
                           <div class="font-14pt drukarka text-center" media="only print">
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
                              <td class="fs-5"><tablecell>
                                 <tc-order>
                                    {{$dance['order']}}&nbsp
                                 </tc-order>
                                 <tc-dance>
                                    {{$dance['dance']}}
                                    @if($dance['closed'])
                                       <div class="fa fa-check"></div>
                                       @if($inProgress!==2)
                                          <?php $inProgress=1 ?>
                                       @endif
                                    @elseif($inProgress==1)
                                       <div class="fa fa-spinner fa-pulse fa-lg"></div>
                                       <?php $inProgress=2 ?>
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