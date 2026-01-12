<div class="row">
   <div class="col-lg-12">
      <div class="table-responsive">
         <table class="table table-striped table-bordered table-hover">
            <thead>
               <tr class="font-14pt px-2">
                  <th >
                     Lp.
                  </th>
                  <th>
                     <div class="alignleft">Runda </div>
                     <div class="alignright">&nbsp&nbsp[ Tańczących ]</div>
                  </th>
                  <th class="userTime ekran">Czas</th>
                  <th>Grup</th>
                  <th colspan="10">Tańce</th>
               </tr>
            </thead>
            <tbody>
               <?php $lastDescription = null; $inProgress=1 ?>
               @foreach($compressedProgram as $index => $programRound)
                  @if($programRound->isDance)
                     <tr>
                        <td class="btn-circle fs-6 py-2">
                           {{$index+1}}.
                        </td>
                        <td class="p-1">
                           <div class="ekran alignleft" media="only screen">
                              {{$programRound->description}} <description class="alternativeDescription"> {{$programRound->alternative_description}} </description>
                           </div>
                           <div class="ekran alignright" media="only screen">
                           @if( $programRound->couples )
                              &nbsp[ {{$programRound->couples}} ]
                           @endif
                           </div>
                           <div class="drukarka alignleft" media="only print">
                           @if( $programRound->alternative_description )
                              {{$programRound->alternative_description}} 
                           @else
                              {{$programRound->description}}
                           @endif
                           </div>
                           <div class="drukarka alignright" media="only print">
                           @if( $programRound->couples )
                              &nbsp[ {{$programRound->couples}} ]
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
                              <td class="p-1"><tablecell>
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