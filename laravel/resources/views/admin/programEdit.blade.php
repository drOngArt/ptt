@extends('admin.master')


@section('title')
    Edycja Programu Turnieju
@stop

@section('content')
   <div id="page-wrapper">
      {!! Form::open(array('method' => 'post', 'action' => array('Admin\DashboardController@postFinalProgram'))) !!}
         <div class="row">
            <div class="col-lg-12">
               <h1 class="page-header">Program turnieju
                  <div class="pull-right">
                     <nav id="navbar-darkgreen" class="navbar navbar-default">
                     <div class="container-fluid">
                        <ul class="nav navbar-nav">
                           <li><button type="button" id="startStop" class="btn btn-warning button-menu">Zmień kolejność</button></li>
                           <li><button type="button" id="altNames" class="btn btn-warning button-menu">Zmień nazwy</button></li>
                           <li>{!! Form::submit('Zatwierdź', array('id'=>'submitButton1', 'class' => 'btn btn-primary button-menu')) !!}</li>
                        </ul>
                     </div>
                  </div>
               </h1>
               <h4>
                  <div class="pull-left">
                  Czas rozpoczęcia: {!! Form::input('time', 'stTime', $value = $layout->startTime, $options = array('class'=>'btn-light-blue font-14pt','id' => 'inpStTime','format'=>'HH:mm','step'=>'300')) !!}&nbsp&nbsp
                  <button type="button" class="btn btn-light-blue button-menu" data-toggle="modal" data-target=".programParametersModal">Parametry</button>
                  </div>
               </h4>
            </div>
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
                              <div class="alignleft">Runda 
                                 @if($additionalRounds)
                                    + dodatkowe rundy
                                 @endif
                              </div>
                              <div class="alignright">&nbsp&nbsp[ Liczba par ]</div>
                           </th>
                           <th>
                              Grup
                           </th>
                           <th colspan="10">
                              Tańce
                           </th>
                        </tr>
                     </thead>
                     <tbody id="sortable1" class="connectedSortable">
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
                              <input hidden name="roundId[]" value="{{$programRound->id}}">
                              <input hidden name="roundName[]" value="{{$programRound->description}}">
                              <input hidden name="isDance[]" value="{{$programRound->isDance}}">
                              <description class="description alignleft">{{$programRound->description}}</description>
                              <div>
                              <description class="alternativeDescription alignleft">{{$programRound->alternative_description}}</description>
                              <input hidden class="alternativeInput" name="roundAlternativeName[]" value='{{$programRound->alternative_description}}'>
                              </div>
                              <div class="alignright">
                              @if( $programRound->couples )
                                 &nbsp[ {{$programRound->couples}} ]
                              @endif
                              </div>
                              </td>
                              <td>
                                 {!! Form::input('number', 'groupId[]', $programRound->groups, ['class' => 'groups btn-indigo text-center font-12pt', 'min' => 1, 'max' => 99, 'required' => 'required']) !!}
                              </td>
                              @if($programRound->isDance)
                                 @foreach($programRound->dances as $programRoundDance)
                                    <td><tablecell>
                                       <input hidden name="{{$programRound->id}}DanceName[]" value="{{$programRoundDance['dance']}}">
                                       <tc-dance>
                                       <?php $dbRound = \App\Round::where('description', '=', trim($programRound->description))->where('dance', '=', $programRoundDance['dance'])->first()?>
                                       @if( !is_null($dbRound) and $dbRound->closed == 1)
                                          <input class="danceCheckbox" type="checkbox" id="{{$programRound->id}}{{$programRoundDance['dance']}}" name="{{$programRound->id}}{{$programRoundDance['dance']}}" checked>
                                       @else
                                          <input class="danceCheckbox" type="checkbox" id="{{$programRound->id}}{{$programRoundDance['dance']}}" name="{{$programRound->id}}{{$programRoundDance['dance']}}">
                                       @endif
                                       <label for="{{$programRound->id}}{{$programRoundDance['dance']}}">
                                          &nbsp&nbsp{{$programRoundDance['dance']}}
                                       </label>
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
                        @if($additionalRounds)
                           @foreach($additionalRounds as $additionalRound)
                              <tr>
                                 <td class="btn-circle">
                                    {{$idx+1}}.
                                    <?php $idx = $idx+1 ?>
                                 </td>
                                 @if($additionalRound->isDance)
                                    <td>
                                    <input hidden name="isDance[]" value="1">
                                 @else
                                    <td class="text-muted">
                                    <input hidden name="isDance[]" value="0">
                                 @endif
                                 <input hidden name="roundId[]" value="{{$additionalRound->id}}">
                                 <input hidden name="roundName[]" value="{{$additionalRound->description}}">
                                 <description class="description">{{$additionalRound->description}}</description>
                                 <div>
                                 <description class="alternativeDescription">{{$additionalRound->alternative_description}}</description>
                                 <input hidden class="alternativeInput" name="roundAlternativeName[]" value='{{$additionalRound->alternative_description}}'>
                                 </div>
                                 </td>
                                 <td>
                                    {!! Form::input('number', 'groupId[]', $additionalRound->groups, ['class' => 'groups btn-indigo text-center font-12pt', 'min' => 1, 'max' => 99, 'required' => 'required']) !!}
                                 </td>
                                 
                                 @if($additionalRound->isDance)
                                    @foreach($additionalRound->dances as $additionalRoundDance)
                                       <td><tablecell>
                                          <input hidden name="{{$additionalRound->id}}DanceName[]" value="{{$additionalRoundDance['dance']}}">
                                          <tc-dance>
                                             <label for="{{$additionalRound->id}}{{$additionalRoundDance['dance']}}">
                                                {{$additionalRoundDance['dance']}}&nbsp&nbsp
                                             </label>
                                             <input class="danceCheckbox" type="checkbox" id="{{$additionalRound->id}}{{$additionalRoundDance['dance']}}" name="{{$additionalRound->id}}{{$additionalRoundDance['dance']}}">
                                          </tc-dance>
                                          <tc-order>
                                             {{$additionalRoundDance['order']}}
                                          </tc-order>
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
                    {!! Form::submit('Zatwierdź', array('id'=>'submitButton2', 'class' => 'btn button-menu btn-primary ')) !!}
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
    
    <!-- Save parameters selector -->
   <div class="modal fade programParametersModal" tabindex="-1" role="dialog" aria-labelledby="saveParameters" aria-hidden="true">
      {!! Form::open(array('method' => 'get','action' => array('Admin\DashboardController@saveParameters'))) !!}
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
               <h4 class="modal-title" id="myModalLabel">Parametry czasowe programu turnieju:</h4>
            </div>
            <div class="modal-body">
               <div class="form-group ekran">
                  {!! Form::input('number', 'intDurationElm', $layout->durationRound, ['class' => 'btn-light-blue text-center ekran width_100px', 'min' => 0, 'max' => 300, 'required' => 'required']) !!} [sec] - czas trwania tańca w eliminacjach</br>
                  {!! Form::input('number', 'intDurationFin', $layout->durationFinal, ['class' => 'btn-light-blue text-center ekran width_100px', 'min' => 0, 'max' => 300, 'required' => 'required']) !!} [sec] - czas trwania tańca w rundzie finałowej</br>
                  {!! Form::input('number', 'intDurationStart', $layout->parameter1, ['class' => 'btn-light-blue text-center ekran width_100px', 'min' => 0, 'max' => 60, 'required' => 'required']) !!} [min] - otwarcie turnieju</br>
                  {!! Form::input('number', 'intDurationEnd', $layout->parameter2, ['class' => 'btn-light-blue text-center ekran width_100px', 'min' => 0, 'max' => 60, 'required' => 'required']) !!} [min] - ogłoszenie wyników turnieju</br>
               </div>
               <div class="modal-footer">
                  <div class="pull-left">
                     <button type="button" class="btn btn-lg btn-warning button-menu" data-dismiss="modal">Anuluj</button>                     
                  </div>
                  <div class="pull-right">
                     {!! Form::submit('Zapisz', array('class' => 'btn btn-primary button-menu')) !!}
                  </div>
               </div>
            </div>
         </div>
      </div>
      {!! Form::close() !!}
   </div>

    <!-- /#page-wrapper -->
@stop

@section('customScripts')
   {!! HTML::script('js/jquery-ui.min.js') !!}
   {!! HTML::script('js/adminProgramEdit.js') !!}
   <script>
      $(function() {
         var removeIntent = false;
         $( "#sortable1" ).sortable({
            connectWith: ".connectedSortable",
            update:
                function(event,ui){
                    $(this).find('tr').each(function(i){
                        $(this).find('td:first').text(i+1);
                    });
                },
            start: 
              function (event, ui){
                 ui.item.css('background-color', '#F2F5A9');
                 ui.item.css('border-radius','8px');
                 ui.item.css('border', '2px solid #428bca');
              },
            over: 
               function (event,ui) {
                  removeIntent = false;
               },
            out: 
               function (event,ui) {
                  removeIntent = true;
               },
            beforeStop:  
               function(event,ui){
                  if(removeIntent == true){
                     ui.item.remove();
                  };
               },
            stop:
              function(event,ui){
                 ui.item.css('border', '');
                 ui.item.css('background-color', '#E0ECF8');
              },
         });
         $( 'td' ).each(function(){
             $(this).css('width', $(this).width() +'px');
         });
      });
   </script>
@stop