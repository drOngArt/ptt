@extends('admin.master')

@section('title')
    Aktualna runda
@stop

@section('content')
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                @if($round != null)
                    <h1 class="page-header">
                        @if($prevRoundIdFromDB > 0)
                            <small><a href="{{$baseURI}}/admin/roundFromDb/{{$prevRoundIdFromDB}}" class="btn btn-secondary btn-sm fa fa-step-backward" role="button"></a></small>
                        @endif
                        {{$roundDescription}}, {{$danceName}}
                        @if($nextRoundIdFromDB > 0)
                            <div class="pull-right">
                            <small><a href="{{$baseURI}}/admin/roundFromDb/{{$nextRoundIdFromDB}}" class="btn btn-secondary btn-sm fa fa-step-forward" role="button"></a></small>
                            </div>
                        @endif
                    </h1>
                    <h4>
                        @if( $roundDescription[0] != 'F')
                           <button class="btn btn-primary" type="button" data-toggle="modal" data-target=".showGroupsModal">Grupy
                           <span class="badge badge-pill">
                              @if( $groups == false )
                                 ---
                              @else
                                 {{$groups}}
                              @endif
                           </span></button>
                        @endif
                        <button class="btn btn-primary" type="button" data-toggle="modal" data-target=".showCouplesModal">
                           <i class="fa fa-female fa-lg" aria-hidden="true"></i><i class="fa fa-male fa-lg" aria-hidden="true"></i>
                           <span class="badge badge-pill-danger">
                           @if( $couples == false )
                              ---
                           @else
                              {{$couples}} 
                           @endif
                        </span></button>
                        @if( $roundDescription[0] != 'F')
                           <i class="fa fa-lg fa-sign-out fa-fw" aria-hidden="true"></i> 
                           <button class="btn btn-deep-orange" type="button">Awans
                           <span class="badge green badge-pill">{{$votes}}</span></button>
                        @endif
                    </h4>
                    <div class="alternativeDescription"> {{$roundAlternativeDescription}} </div>
                @else
                    <h1 class="page-header">
                        @if($prevRoundIdFromDB > 0)
                            <small><a href="{{$baseURI}}/admin/roundFromDb/{{$prevRoundIdFromDB}}" class="btn btn-secondary btn-sm fa fa-step-backward" role="button"></a></small>
                        @endif
                        {{$roundDescription}} - {{$danceName}}
                    </h1>
                    <div class="alternativeDescription"> {{$roundAlternativeDescription}} </div>
                @endif
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!-- /.row -->

    <!--<div class="modal" id="myModal" tabindex="-1" role="dialog">
         <div class="modal-dialog"  role="document">
          <div class="modal-content">
           <div class="modal-header">
            <h5 class="modal-title">Modal title</h5>
             <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
             </button>
           </div>
          <div class="modal-body">
           <p>{{ Session::get('success', '') }}</p>
          </div>
         <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Zamknij</button>
         </div>
        </div>
       </div>
      </div> -->

   @if(Session::has('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <strong>BRAWO !!</strong> {{ Session::get('success', '') }}
    </div>
    @endif
    
    @if(Session::has('alert'))
    <div class="alert alert-danger .alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <h3><strong>BŁĄD !!!</strong> {{ Session::get('alert', '') }}</h3>
    </div>

@endif

        <div class="table-responsive">
            <table class="table table-striped">
                <tbody>
                    @foreach($judges as $judge)
                    <tr>
                        <td>
                            <div id="status{{$judge->sign}}">
                                <button class="btn-circle">{{$judge->sign}}</button>
                                @if( $judge->without_pass == true )
                                    <a href="{{$baseURI}}/admin/password/{{$judge->id}}/false" class="btn btn-orange" role="button"> USTAW HASŁO </a>
                                @endif
                                <span class="font-14pt"> {{$judge->lastName}} {{$judge->firstName}}</span>
                            </div>
                        </td>
                        <td>
                            <button id="completed{{$judge->sign}}" type="button" class="btn btn-outline hidden fa fa-check-square-o fa-lg judgeResultsButton" 
                                data-toggle="modal" data-target=".judgeResults" data-judge-sign="{{$judge->sign}}" data-judge-name="{{$judge->firstName}} {{$judge->lastName}}">
                            </button>
                            <div id="completedText{{$judge->sign}}" class="hidden"></div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($roundIdFromDB == 0)
            <div class="row">
                <div class="col-lg-12">
                    <div class="pull-right">
                        @if(count($roundsToUndo) > 0)
                            <button type="button" class="btn btn-danger button-menu" data-toggle="modal" data-target=".roundUndoModal">Powtórz taniec</button>
                        @else
                            <button disabled type="button" class="btn btn-danger" data-toggle="modal" data-target=".roundUndoModal">Powtórz taniec</button>
                        @endif
                        <a class="confirmation" href="{{$baseURI}}/admin/round/forceCloseDance/{{$localRoundId}}"><button type="button" class="btn btn-warning button-menu">Zakończ taniec</button></a>
                        @if(count($roundsToClose) > 0)
                            <button type="button" class="btn btn-success button-menu" data-toggle="modal" data-target=".roundCloseModal">Zapisz rundę</button>
                        @else
                            <button disabled type="button" class="btn btn-success" data-toggle="modal" data-target=".roundCloseModal">Zapisz rundę</button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
    <!-- /#page-wrapper -->

    <div class="modal fade roundCloseModal" tabindex="-1" role="dialog" aria-labelledby="roundClose" aria-hidden="true">
        {!! Form::open(array('url' => 'admin/round', 'method' => 'POST')) !!} {!! csrf_field() !!}
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Zapisz rundę</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <select name="roundToClose" class="form-control">
                            @foreach($roundsToClose->reverse() as $roundToClose)
                                <option value="{{$roundToClose->id}}">{{$roundToClose->description}}</option>
                            @endforeach
                        </select>
                    </div>
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
        {!! Form::close() !!}
    </div>


    <div class="modal fade roundUndoModal" tabindex="-1" role="dialog" aria-labelledby="roundUndo" aria-hidden="true">
        {!! Form::open(array('url' => 'admin/round/undoRound', 'method' => 'POST')) !!} {!! csrf_field() !!}
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Powtórz taniec</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <select name="roundToUndo" class="form-control">
                            <option selected disabled hidden>Wybierz kategorię i taniec do powtórzenia:</option>
                            @foreach($roundsToUndo->reverse() as $roundToUndo)
                                <option value="{{$roundToUndo->id}}">{{$roundToUndo->description}}, {{$roundToUndo->dance}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                  <div class="pull-left">
                    <button type="button" class="btn btn-lg btn-warning button-menu" data-dismiss="modal">Anuluj</button>
                  </div>
                  <div class="pull-right"> 
                    {!! Form::submit('Powtórz taniec', array('class' => 'btn btn-primary button-menu')) !!}
                  </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    <div class="modal fade judgeResults" tabindex="-1" role="dialog" aria-labelledby="judgeResults" aria-hidden="true">
        {!! Form::open(array('url' => 'admin/round/undoRound', 'method' => 'POST', 'id'=>'judgeUndoForm')) !!} {!! csrf_field() !!}
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Typowania sędziego</h4>
                </div>
                <div class="modal-body">
                        <div><label id="judgeResultsSign" value=""></label></div>
                        <div><label id="judgeResultsRound" value = "{{$localRoundId}}">{{$roundDescription}}, {{$danceName}}</label></div>
                        <div id="judgeResultsVotes" value = ""></div>
                        
                        <input hidden name="roundToUndo" value = "{{$localRoundId}}"></input>
                        <input hidden id="judgeToUndo" name="judgeToUndo" value =""></input>
                </div>
                <div class="modal-footer">
                  <div class="pull-left">
                    <button type="button" class="btn btn-lg btn-warning button-menu" data-dismiss="modal">Anuluj</button>
                  </div>
                  <div class="pull-right">  
                    {!! Form::submit('Powtórz taniec', array('class' => 'btn btn-danger button-menu', 'id'=>'judgeUndoButton')) !!}
                  </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>

  	<!-- show couples -->
   <div class="modal fade showCouplesModal" tabindex="-1" role="dialog" aria-labelledby="showCouples" aria-hidden="true">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
               <h4 class="modal-title" align="center"><b>Lista par w {{$roundDescription}}</b></h4>
            </div>
            <div class="modal-body">
               <div class="row">
                  <div class="col-lg-12">
                     <table class="table table-bordered">
                        <tbody class="btn-lblue-gray">
                        @if( $names )
                           <tr>
                              <th >Numer</th>
                              <th >Nazwisko i imię</th>
                              <th >Klub /Kraj</th>
                           </tr>
                           @foreach ($names as $couple)
                              
                           <tr>
                              <td class="text-center">{{$couple->number}}</td>
                              <td>
                                 {{$couple->firstNameA}} {{$couple->lastNameA}}</br>
                                 {{$couple->firstNameB}} {{$couple->lastNameB}} 
                              </td>
                              <td>
                                 {{$couple->club}} </br>
                                 {{$couple->country}}
                              </td>
                           </tr> 
                           @endforeach
                        @endif
                     </tbody>
                     </table>
                  </div>
               </div>
            </div>
         </div>
      </div>
    </div>

   <!-- show groups-->
   <div class="modal fade showGroupsModal" tabindex="-1" role="dialog" aria-labelledby="showGroups" aria-hidden="true">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
               <h3 class="modal-title" align="left">{{$danceName}} - grupy {{$roundDescription}}</h3>
            </div>
            <div class="modal-body">
               <div class="row">
                  <div class="col-lg-12">
                     <table class="table table-bordered">
                        <tbody class="btn-lblue-gray" >
                        @if( $dance )
                           <?php $idx = 1; ?>
                           @foreach($dance->couples as $groups)
                            <tr class='font-14pt'>
                              <td >
                              @if( count($dance->couples) > 1 )
                                 Grupa {{$idx}}:&nbsp 
                              @else
                                 Numery:
                              @endif
                              </td>
                              <td>
                                @foreach ($groups as $couple)
                                    {{$couple->number}}&nbsp
                                @endforeach
                              </td>
                              <?php $idx++; ?>
                           </tr> 
                           @endforeach
                        @endif
                     </tbody>
                     </table>
                  </div>
               </div>
            </div>
         </div>
      </div>
    </div>
    
@stop

@section('customScripts')
    {!! HTML::script('js/adminRound.js') !!}
    <script>
        var adminRefreshTimer = "{{Config::get('ptt.adminRefreshTimer')}}";
        var roundIdFromDB = {{$roundIdFromDB}};
        var baseURI = "{{$baseURI}}";
        @if($danceName != null || $roundDescription!=null)
            var roundName = "{{$roundDescription}}" + ", " + "{{$danceName}}";
        @endif
        /*var msg = '{{Session::get('alert')}}';
        var exist = '{{Session::has('alert')}}';
        if(exist){
            $('#myModal').modal("show");
        }*/
    </script>
@stop
