@extends('admin.master')

@section('title')
    Program dodany
@stop

@section('content')
    <div id="page-wrapper">
        {!! Form::open(array('method' => 'post', 'action' => array('Admin\DashboardController@postFinalProgram'))) !!}
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Aktualny program + dodane rundy
                <div class="pull-right">{!! Form::submit('Zatwierdź', array('class' => 'btn btn-primary ')) !!}</div>
                </h1>
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!-- /.row -->
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">

                     <table class="table table-striped table-bordered table-hover">
                        <thead>
                           <tr>
                              <th>
                                 Lp.
                              </th>
                              <th>
                                 Runda
                              </th>
                           </tr>
                        </thead>
                        <tbody id="sortable2" class="connectedSortable">
                           @foreach($program as $index => $programRound)
                              <tr>
                                 <td class="btn-circle">
                                    {{$index+1}}.
                                    <?php $idx = $index+1 ?>
                                 </td>

                                 @if($programRound->isDance)
                                    <td>
                                 @else
                                    <td class="text-muted">
                                 @endif
                                 <input hidden name="roundName[]" value="{{$programRound->description}}">
                                 <input hidden name="roundId[]" value="{{$programRound->id}}">
                                 <input hidden name="isDance[]" value="{{$programRound->isDance}}">
                                 <description class="description">{{$programRound->description}}</description>
                                 <div>
                                    <description class="alternativeDescription">{{$programRound->alternative_description}}</description>
                                    <input hidden class="alternativeInput" name="roundAlternativeName[]" value='{{$programRound->alternative_description}}'>
                                 </div>
                                 </td>
                                    
                                 @if($programRound->isDance)
                                    @foreach($programRound->dances as $programRoundDance)
                                       <td><tablecell>
                                          <input hidden name="{{$programRound->id}}DanceName[]" value="{{$programRoundDance['dance']}}">
                                          <tc-dance>
                                             <label for="{{$programRound->id}}{{$programRoundDance['dance']}}">
                                                {{$programRoundDance['dance']}}
                                             </label>
                                             <?php $dbRound = \App\Round::where('description', '=', trim($programRound->description))->where('dance', '=', $programRoundDance['dance'])->first()?>
                                             @if( !is_null($dbRound) and $dbRound->closed == 1)
                                                &nbsp<input class="danceCheckbox" type="checkbox" id="{{$programRound->id}}{{$programRoundDance['dance']}}" name="{{$programRound->id}}{{$programRoundDance['dance']}}" checked>
                                             @else
                                                &nbsp<input class="danceCheckbox" type="checkbox" id="{{$programRound->id}}{{$programRoundDance['dance']}}" name="{{$programRound->id}}{{$programRoundDance['dance']}}">
                                             @endif
                                          </tc-dance>
                                          <tc-order>
                                             {{$programRoundDance['order']}}
                                          </tc-order>
                                          <input hidden name="order{{$programRound->id}}{{$programRoundDance['dance']}}" value = "{{$programRoundDance['order']}}">
                                        </tablecell></td>
                                    @endforeach
                                 @endif
                              </tr>
                           @endforeach
                           @if( $programAdd )
                              @foreach($programAdd as $programRound)
                                 <tr style="color:IndianRed; background-color: Lavender;">
                                    <td class="btn-circle">
                                        {{$idx+1}}.
                                        <?php $idx = $idx+1 ?>
                                    </td>
                                    <td>
                                    <input hidden name="roundId[]" value="{{$programRound->id}}">
                                    <input hidden name="roundName[]" value="{{$programRound->description}}">
                                    <input hidden name="isDance[]" value="{{$programRound->isDance}}">
                                    <description class="description">{{$programRound->description}}</description>
                                    <div>
                                       <input hidden class="alternativeInput" name="roundAlternativeName[]" value=''>
                                    </div>
                                    </td>
                                 @if($programRound->isDance)
                                    @foreach($programRound->dances as $danceKey => $programRoundDance)
                                    <td><tablecell>
                                       <input hidden name="{{$programRound->id}}DanceName[]" value="{{$programRoundDance}}">
                                       <tc-dance>
                                          <input class="danceCheckbox" type="checkbox" id="{{$programRound->id}}{{$programRoundDance}}" name="{{$programRound->id}}{{$programRoundDance}}">
                                          <label for="{{$programRound->id}}{{$programRoundDance}}">
                                          &nbsp&nbsp{{$programRoundDance}}
                                          </label>
                                       </tc-dance>
                                    </tablecell></td>
                                    @endforeach
                                 @endif
                                 </tr>
                              @endforeach
                           @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="pull-right">
                    {!! Form::submit('Zatwierdź', array('class' => 'btn btn-primary ')) !!}
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
    <!-- /#page-wrapper -->
@stop

@section('customScripts')
    {!! HTML::script('js/jquery-ui.min.js') !!}
    <script>
        $(function() {
            $( "#sortable2" ).sortable({
                connectWith: ".connectedSortable",
                update:
                    function(event,ui){
                        console.log("update", event);
                        $(this).find('tr').each(function(i){
                            $(this).find('td:first').text(i+1);
                        });
                    },
            });
            $( 'td' ).each(function(){
                $(this).css('width', $(this).width() +'px');
            });
        });
    </script>
@stop