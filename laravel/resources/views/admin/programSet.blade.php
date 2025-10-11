@extends('admin.master')


@section('title')
    Edycja Programu Turnieju
@stop

@section('content')
   
   <div id="page-wrapper">
      {!! Form::open(array('method' => 'get','action' => array('Admin\DashboardController@selectedCategories', 'something'))) !!}
         <div class="row">
            <div class="col-lg-12">
               <div class="page-header-break">PROGRAM TURNIEJU</div>
               <h1 class="page-header">Nowy program turnieju
               <div class="pull-right">
                  <nav id="navbar-darkgreen" class="navbar navbar-default">
                     <div class="container-fluid">
                        <ul class="nav navbar-nav">
                           <li class="dropdown"><button type="button" class="btn btn-success" class="dropdown-toggle" data-toggle="dropdown" href="#" id="main-menu">Dodaj <i class="fa fa-caret-down"></i></button>
                           <ul class="dropdown-menu">
                              <li><a href="#" data-toggle="modal" data-target=".programAddRoundModal" id="addRound"><i class="fa fa-reply">&emsp;Rundę</i></a></li>
                              <li><a href="#" data-toggle="modal" data-target=".programAddShowModal" id="addShow"><i class="fa fa-music">&emsp;Pokaz</i></a></li>
                              <li><a href="#" data-toggle="modal" data-target=".programAddBreakModal" id="addBreak"><i class="fa fa-bell">&emsp;Przerwę</i></a></li>
                           </ul>
                           </li>
                           <li><button type="button" class="btn btn-special button-menu" data-toggle="modal" data-target=".programSelectorModal2" id="save_as">Zapisz jako...</button></li>
                           <li>{!! Form::submit('Zatwierdź', array('id'=>'submitButton1','class' => 'btn btn-primary button-menu')) !!}</li>
                        </ul>
                     </div>
                  </nav>
               @if(session('status'))
                  @if(session('status') == 'success')
                     <div class="alert alert-success"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        Nowy program turnieju został zapisany :)
                     </div>
                  @else
                     <div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        Błąd dostępu do pliku ;(
                     </div>
                  @endif
                  {!! Session::forget('status') !!}
               @endif
                
               </div>
               </h1>
            </div>
         </div>
         <div class="row">
            <div class="col-lg-12">
                <div class="pull-right">
                    <a href="javascript:window.print()" type="button" class="btn button-menu btn-brown" >Drukuj</a>
                </div>
            </div>
         </div>

         <!-- /.row -->
         <div class="row">
            <div class="col-lg-12">
               <div class="table-responsive" style="border: 2px solid orange;">
                  <table class="table table-striped table-bordered table-hover">
                     <thead>
                        <tr>
                           <th>
                              Lp.
                           </th>
                           <th>
                              Runda
                           </th>
                           <th>
                              Tańce
                           </th>
                        </tr>
                     </thead>
                     <tbody id="sortable" class="connectedSortable" >
                        @foreach($program as $index => $programRound)
                           <tr class="ui-state-default" >
                              <td class="btn-circle">
                                 {{$index+1}}.
                              </td>
                              <td style="background-color: {{$programRound->bg_color}};">
                                 <input hidden name="roundId[]" value="{{$index}}">
                                 <input hidden name="roundName[]" value="{{$programRound->description}}">
                                 <input hidden name="isDance[]" value="{{$programRound->isDance}}">
                                 <div class="ekran" media="only screen">
                                 <description class="description">{{$programRound->description}}</description>
                                 </div>
                                 <div class="drukarka" media="only print">
                                 <description class="description">{{$programRound->description}}</description>
                                 </div>
                              </td>
                              @if($programRound->isDance)
                                 <td>
                                    @foreach($programRound->dances as $danceKey => $programRoundDance)
                                       {{$programRoundDance}}&nbsp
                                    @endforeach
                                 </td>
                              @endif
                           </tr>
                        @endforeach
                     </tbody>
                  </table>
               </div>
            </div>
         </div>

         {!! Form::close() !!}
   </div>
   <!-- /#page-wrapper -->
    
   <!-- File save selector -->
   <div class="modal fade programSelectorModal2" tabindex="-1" role="dialog" aria-labelledby="programSelector" aria-hidden="true">
      {!! Form::open(array('method' => 'get','action' => array('Admin\DashboardController@selectedCategories', 'saveFile'))) !!}
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
               <h4 class="modal-title" id="myModalLabel">Zapisz program turnieju</h4>
            </div>
            <div class="modal-body">
               <div class="form-group">
                  Wpisz nazwę nowego programu
                  {!! Form::text('fileName', 'Program Turnieju ', array('placeholder' => 'nazwa pliku','maxlength' => '32','class' => 'text-left')) !!}
                  <strong>.csv</strong>
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
    
    <!-- Add round selector -->
   <div class="modal fade programAddRoundModal" tabindex="-1" role="dialog" aria-labelledby="programAddRound" aria-hidden="true">
      {!! Form::open(array('method' => 'post','action' => array('Admin\DashboardController@postSelectedCategories', 'addRound', 'size'=>'50%'))) !!}
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
               <h4 class="modal-title" id="myModalLabel">Dodaj rundę do program turnieju</h4>
            </div>
            <div class="modal-body">
               <div class="form-group">
                  {!! Form::select('round', $roundNames, '1/2 Finału' ); !!}
                  {!! Form::select('category', $categoriesNames ); !!}
                  {!! Form::select('additional', $additNames ); !!}
               </div>
               <div class="modal-footer">
                  <div class="pull-left">
                     <button type="button" class="btn btn-lg btn-warning button-menu" data-dismiss="modal">Anuluj</button>
                  </div>
                  <div class="pull-right">
                     {!! Form::submit('Dodaj', array('class' => 'btn btn-primary button-menu')) !!}
                  </div>
               </div>
            </div>
         </div>
      </div>
      {!! Form::close() !!}
   </div>
    
   <!-- Add show selector -->
   <div class="modal fade programAddShowModal" tabindex="-1" role="dialog" aria-labelledby="programAddShow" aria-hidden="true">
      {!! Form::open(array('method' => 'post','action' => array('Admin\DashboardController@postSelectedCategories', 'addShow'))) !!}
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title" id="myModalLabel">Dodaj pokaz do program turnieju</h4>
            </div>
            <div class="modal-body">
               <div class="form-group">
                  Wpisz nazwę pokazu
                  {!! Form::text('showName', 'Pokazy', array('placeholder' => 'nazwa pokazu', 'maxlength' => '40')) !!}
                  {!! Form::text('showNameDance', '', array('placeholder' => 'co tańczą?? S R JV', 'maxlength' => '30')) !!}
               </div>
               <div class="modal-footer">
                  <div class="pull-left">
                     <button type="button" class="btn btn-lg btn-warning button-menu" data-dismiss="modal">Anuluj</button>
                  </div>
                  <div class="pull-right">
                     {!! Form::submit('Dodaj', array('class' => 'btn btn-primary button-menu')) !!}
                  </div>
               </div>
            </div>
         </div>
      </div>
      {!! Form::close() !!}
   </div>

   <!-- Add break selector -->
   <div class="modal fade programAddBreakModal" tabindex="-1" role="dialog" aria-labelledby="programAddBreak" aria-hidden="true">
      {!! Form::open(array('method' => 'post','action' => array('Admin\DashboardController@postSelectedCategories', 'addBreak'))) !!}
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel">Dodaj przerwę do program turnieju</h4>
            </div>
            <div class="modal-body">
               <div class="form-group">
                  {!! Form::text('BreakName', 'Przerwa', array('placeholder' => 'nazwa przerwy', 'maxlength' => '40')) !!}
                  {!! Form::text('breakTime', '', array('placeholder' => 'minut?', 'maxlength' => '3')) !!}
               </div>
               <div class="modal-footer">
                  <div class="pull-left">
                     <button type="button" class="btn btn-lg btn-warning button-menu" data-dismiss="modal">Anuluj</button>
                  </div>
                  <div class="pull-right">
                     {!! Form::submit('Dodaj', array('class' => 'btn btn-primary button-menu')) !!}
                  </div>
               </div>
            </div>
         </div>
      </div>
      {!! Form::close() !!}
   </div>
    
@stop

@section('customScripts')
   {!! HTML::script('js/jquery-ui.min.js') !!}
   {!! HTML::script('js/adminProgramEdit.js') !!}
   <script>

      var submit1 = document.getElementById('submitButton1');
      var save_as = document.getElementById('save_as');
      var add_sth = document.getElementById('main-menu');
      $(submit1).prop('disabled', true);
      submit1.classList.add('btn-primary');

      $(function() {  
         var removeIntent = false;
         $( 'td' )
            .each(function(){
               $(this).css('width', $(this).width() +'px');
         });
         $( ".connectedSortable" )
            .sortable({
               connectWith: ".connectedSortable",
               revert: 100,
               start: 
                  function (event, ui){
                     //ui.item.css('background-color', '#F2F5A9');
                     ui.item.css('border-radius','8px');
                     ui.item.css('border', '2px solid #428bca');
                     var start_pos = ui.item.index();
                     ui.item.data('start_pos', start_pos); 
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
               update:
                  function(event,ui){
                     $(this).find('tr').each(function(i){
                         $(this).find('td:first').text(i+1);
                     });
                  },
               stop: 
                  function(event,ui){
                     ui.item.css('border', '');
                     //ui.item.css('background-color', '');
                     //ui.item.css('background-color', '#E0F8E0');
                     $(submit1).prop('disabled', false);
                     submit1.classList.add('btn-danger');
                     submit1.classList.remove('btn-primary');
                     $(save_as).prop('disabled', true);
                     $(add_sth).prop('disabled', true);
                  },
            }).disableSelection(); 
      });
   </script>
@stop
