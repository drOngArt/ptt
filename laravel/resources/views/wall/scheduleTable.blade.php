<div class="row">

   <div class="col-lg-12"> 
      <div class="table-responsive">
         <table class="table table-striped">
            <tbody>
               <?php $lastDescription = null; $inProgress=1; ?>
               @foreach($compressedProgram as $index => $programRound)
                  @if($programRound->isDance)
                     <tr>
                        <td>
                           @if($programRound->alternative_description != "")
                              {{$programRound->alternative_description}}
                           @else
                              {{$programRound->description}}
                           @endif
                        </td>
                        @foreach($programRound->dances as $dance)
                           <td><tablecell>
                              @if( $inProgress == 1 )
                                 @if( $dance['order'] )
                                       <button class="btn-deep-orange badge" type="button">{{$dance['dance']}}
                                       <span class="badge badge-pill">{{$dance['order']}}</span><br>
                                       <span class="fa fa-pulse fa-spinner fa-lg"> </span></button>
                                 @else
                                    <button class="btn-deep-orange" type="button">{{$dance['dance']}}</br>
                                    <span class="fa fa-pulse fa-spinner fa-lg"> </span></button>                                    
                                 @endif
                                 <?php $inProgress = 2; ?>
                              @else
                                 <div class="class_next">
                                    @if( $dance['order'] )
                                       <button class="btn btn-indigo badge" type="button">{{$dance['dance']}}
                                       <span class="badge badge-pill">{{$dance['order']}}</span></button>
                                    @else
                                       <tc-dance>
                                          {{$dance['dance']}}                              
                                       </tc-dance>
                                    @endif
                              @endif
                              </div>
                           </tablecell></td>
                        @endforeach
                     </tr>
                     @else
                        <tr>
                           <td class="text-muted">
                              @if($programRound->alternative_description != "")
                                 {{$programRound->alternative_description}}
                              @else
                                 {{$programRound->description}}
                              @endif
                           </td>
                        </tr>
                     @endif
               @endforeach
               </tbody>
            </table>
        </div>
    </div>
</div>