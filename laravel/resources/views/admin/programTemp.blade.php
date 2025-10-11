@extends('admin.master')

@section('title')
    Program
@stop

@section('content')
    <div id="page-wrapper">
        @if( $cmd == false)
            {!! Form::open(array('method' => 'post', 'action' => array('Admin\DashboardController@postFinalProgram'))) !!}
        @else
            {!! Form::open(array('method' => 'get','action' => array('Admin\DashboardController@selectedCategories', 'something'))) !!}
        @endif
        <div class="row">
            <div class="col-lg-12">
                @if( $cmd == false)
                    <h1 class="page-header">Podgląd programu
                @else
                    <h1 class="page-header">Podgląd nowego programu
                @endif
                <div class="pull-right">
                    {!! Form::submit('Zatwierdź', array('id'=>'submitButton1', 'class' => 'btn btn-primary button-menu')) !!}
                </div>
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
                                @if( $cmd != false)
                                <th  colspan="10">
                                    Tańce
                                </th>
                                @endif
                            </tr>
                        </thead>
                        <tbody id="sortable1" class="connectedSortable">
                            <?php $idx = 0 ?>
                            @foreach($program as $programKey => $programRound)
                                <tr>
                                    <td class="btn-circle">
                                        {{$idx+1}}.
                                        <?php $idx = $idx+1 ?>
                                    </td>
                                @if($programRound->isDance && isset($programRound->bg_color))
                                    <td style="background-color: {{$programRound->bg_color}};">
                                @elseif( $programRound->isDance )
                                   <td >
                                @else
                                    <td class="text-muted">
                                @endif
                                    {{$programRound->description}}
                                    <input hidden name="roundId[]" value="{{$programKey}}">
                                    <input hidden name="roundName[]" value="{{$programRound->description}}">
                                    <input hidden name="isDance[]" value="{{$programRound->isDance}}">
                                    </td>
                                @if( $cmd == false)
                                    @if($programRound->isDance)
                                        @foreach($programRound->dances as $danceKey => $programRoundDance)
                                        <td>
                                            <input hidden name="{{$programKey}}DanceName[]" value="{{$programRoundDance}}">
                                            <?php $dbRound = \App\Round::where('description', '=', trim($programRound->description))->where('dance', '=', $programRoundDance)->first()?>
                                            @if( !is_null($dbRound) and $dbRound->closed == 1)
                                                &nbsp<input type="checkbox" id="{{$programKey}}{{$danceKey}}" name="{{$programKey}}{{$programRoundDance}}" checked>
                                            @else
                                                &nbsp<input type="checkbox" id="{{$programKey}}{{$danceKey}}" name="{{$programKey}}{{$programRoundDance}}">
                                            @endif
                                            <label for="{{$programKey}}{{$danceKey}}">
                                            &nbsp&nbsp{{$programRoundDance}}
                                            </label>
                                        </td>
                                        @endforeach
                                    @endif
                                 @else
                                    @if($programRound->isDance)
                                        <td>
                                        @foreach($programRound->dances as $danceKey => $programRoundDance)
                                            <input hidden name="{{$programKey}}DanceName[]" value="{{$programRoundDance}}">
                                            <label for="{{$programKey}}{{$danceKey}}">
                                            {{$programRoundDance}}
                                            </label>
                                        @endforeach
                                        </td>
                                    @endif
                                @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="pull-right">
                    {!! Form::submit('Zatwierdź', array('id'=>'submitButton2', 'class' => 'btn btn-primary button-menu')) !!}
                </div>
            </div>
        </div>
        {!! Form::close() !!}
        {!! Session::put('new_program', $program) !!}
    </div>
    <!-- /#page-wrapper -->
@stop

@section('customScripts')
   {!! HTML::script('js/jquery-ui.min.js') !!}
   <script>
      $(function() {
         var removeIntent = false;
         $( "#sortable1" ).sortable({
            connectWith: ".connectedSortable",
            update:
               function(event,ui){
                  console.log("update", event);
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
                  //console.groupCollapsed("Sorting stopped");
                  //console.log("this", this);
                  //console.log("event", event);
                  //console.log("ui", ui);
                  //console.groupEnd();
                  ui.item.css('border', '');
                  ui.item.css('background-color', '#E0F8E0');
               },
         });
         $( 'td' ).each(function(){
             $(this).css('width', $(this).width() +'px');
         });
      }).disableSelection();
    </script>
@stop