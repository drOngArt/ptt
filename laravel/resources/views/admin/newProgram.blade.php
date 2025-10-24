@extends('admin.master')

@section('title')
    Utwórz nowy program
@stop

@section('content')
    <div id="page-wrapper">
      {!! Form::open(array('method' => 'get', 'action' => array('Admin\DashboardController@selectedCategories'))) !!} {!! csrf_field() !!}
      <div class="row">
         <div class="col-lg-12">
            <h1 class="page-header">Wybór kategorii
               <div class="pull-right">
                  {!! Form::submit('Zatwierdź', array('id'=>'submitButton1', 'class' => 'btn btn-primary button-menu')) !!}
               </div>
            </h1>
         </div>
      </div>
      <div class="row">
         <div class="col-lg-12">
            <table class="table table-striped table-bordered table-hover">
            <?php $idx=10; ?>
            @foreach($baseRounds as $index => $round)
               @if( $round->isClosed == 0 )
                  <tr>
                     @if( $idx != $round->positionW )
                        <?php $idx = $round->positionW; ?>
                        <td class="text-center">
                           <button id="select_{{$round->positionW}}" type="button" class="btn btn-primary btn-xs">&nbspZaznacz&nbsp</button>
                        </td>
                        <td class="text-center">
                           &nbspBLOK&nbsp{{$round->positionW}} 
                        </td>
                  </tr>
                  <tr>
                        <td class="text-center">
                           <input class="roundCheckbox_{{$round->positionW}}" name="selected[]" type="checkbox" value="{{$round->roundId}}">
                        </td>
                        <td>
                           <description class="description">{{$round->roundName}} {{$round->categoryName}} {{$round->className}} {{$round->styleName}}</description>
                           @if($round->isAdditional)
                              {{$round->matchType}}
                           @endif
                           @foreach($round->dances as $dance)
                              {{$dance}}
                           @endforeach
                           <br>
                        </td>
                     @else
                        <td class="text-center">
                           <input class="roundCheckbox_{{$round->positionW}}" name="selected[]" type="checkbox" value="{{$round->roundId}}">
                        </td>
                        <td>
                           <description class="description">{{$round->roundName}} {{$round->categoryName}} {{$round->className}} {{$round->styleName}}</description>
                           @if($round->isAdditional)
                              {{$round->matchType}}
                           @endif
                           @foreach($round->dances as $dance)
                              {{$dance}}
                           @endforeach
                           <br>
                        </td>
                     @endif
                  </tr>
               @endif
            @endforeach
            </table>
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
    </div>   
    <!-- /#page-wrapper -->
@stop

@section('customScripts')
   {!! HTML::script('js/jquery-ui.min.js') !!}
   <script>
   var selectState_0 = false;
   var selectState_I = false;
   var selectState_II = false;
   var selectState_III = false;
   var selectState_IV = false;
   var selectState_V = false;
   var selectState_VI = false;
   var selectState_VII = false;
   var selectState_VIII = false;
   var selectState_IX = false;
   var bSelect_0 = document.getElementById('select_0');
   var bSelect_I = document.getElementById('select_I');
   var bSelect_II = document.getElementById('select_II');
   var bSelect_III = document.getElementById('select_III');
   var bSelect_IV = document.getElementById('select_IV');
   var bSelect_V = document.getElementById('select_V');
   var bSelect_VI = document.getElementById('select_VI');
   var bSelect_VII = document.getElementById('select_VII');
   var bSelect_VIII = document.getElementById('select_VIII');
   var bSelect_IX = document.getElementById('select_IX');

   if( bSelect_0 )
      bSelect_0.classList.add('btn-primary'); 
   if( bSelect_I )
      bSelect_I.classList.add('btn-primary'); 
   if( bSelect_II )
      bSelect_II.classList.add('btn-primary'); 
   if( bSelect_III )
      bSelect_III.classList.add('btn-primary'); 
   if( bSelect_IV )
      bSelect_IV.classList.add('btn-primary'); 
   if( bSelect_V )
      bSelect_V.classList.add('btn-primary'); 
   if( bSelect_VI )
      bSelect_VI.classList.add('btn-primary'); 
   if( bSelect_VII )
      bSelect_VII.classList.add('btn-primary'); 
   if( bSelect_VIII )
      bSelect_VIII.classList.add('btn-primary'); 
   if( bSelect_IX )
      bSelect_IX.classList.add('btn-primary'); 

   $('#select_0').on('click', function() {
      if(selectState_0){
         $(".roundCheckbox_0").prop('checked', false);
         $(bSelect_0).text('Zaznacz');
         $(bSelect_0).removeClass('btn-info');
         $(bSelect_0).addClass('btn-primary');
      }
      else{
         $(".roundCheckbox_0").prop('checked', true);
         $(bSelect_0).text('Odznacz');
         $(bSelect_0).removeClass('btn-primary');
         $(bSelect_0).addClass('btn-info');
      }
      selectState_0 = !selectState_0;
    });   
   $('#select_I').on('click', function() {
      if(selectState_I){
         console.log('press #select_I') ;
         $(".roundCheckbox_I").prop('checked', false);
         $(bSelect_I).text('Zaznacz');
         $(bSelect_I).removeClass('btn-info');
         $(bSelect_I).addClass('btn-primary');
      }
      else{
         console.log('--unpress #select_I') ;
         $(".roundCheckbox_I").prop('checked', true);
         $(bSelect_I).text('Odznacz');
         $(bSelect_I).removeClass('btn-primary');
         $(bSelect_I).addClass('btn-info');
      }
      selectState_I = !selectState_I;
    });
    $('#select_II').on('click', function() {
      if(selectState_II){
         console.log('press #select_II') ;
         $(".roundCheckbox_II").prop('checked', false);
         $(bSelect_II).text('Zaznacz');
         $(bSelect_II).removeClass('btn-info');
         $(bSelect_II).addClass('btn-primary');
      }
      else{
         console.log('--unpress #select_II') ;
         $(".roundCheckbox_II").prop('checked', true);
         $(bSelect_II).text('Odznacz');
         $(bSelect_II).removeClass('btn-primary');
         $(bSelect_II).addClass('btn-info');
      }
      selectState_II = !selectState_II;
    });
    $('#select_III').on('click', function() {
      if(selectState_III){
         $(".roundCheckbox_III").prop('checked', false);
         $(bSelect_III).text('Zaznacz');
         $(bSelect_III).removeClass('btn-info');
         $(bSelect_III).addClass('btn-primary');
      }
      else{
         $(".roundCheckbox_III").prop('checked', true);
         $(bSelect_III).text('Odznacz');
         $(bSelect_III).removeClass('btn-primary');
         $(bSelect_III).addClass('btn-info');
      }
      selectState_III = !selectState_III;
    });
    $('#select_IV').on('click', function() {
      if(selectState_IV){
         $(".roundCheckbox_IV").prop('checked', false);
         $(bSelect_IV).text('Zaznacz');
         $(bSelect_IV).removeClass('btn-info');
         $(bSelect_IV).addClass('btn-primary');
      }
      else{
         $(".roundCheckbox_IV").prop('checked', true);
         $(bSelect_IV).text('Odznacz');
         $(bSelect_IV).removeClass('btn-primary');
         $(bSelect_IV).addClass('btn-info');
      }
      selectState_IV = !selectState_IV;
    });
    $('#select_V').on('click', function() {
      if(selectState_V){
         $(".roundCheckbox_V").prop('checked', false);
         $(bSelect_V).text('Zaznacz');
         $(bSelect_V).removeClass('btn-info');
         $(bSelect_V).addClass('btn-primary');
      }
      else{
         $(".roundCheckbox_V").prop('checked', true);
         $(bSelect_V).text('Odznacz');
         $(bSelect_V).removeClass('btn-primary');
         $(bSelect_V).addClass('btn-info');
      }
      selectState_V = !selectState_V;
    });
    $('#select_VI').on('click', function() {
      if(selectState_VI){
         $(".roundCheckbox_VI").prop('checked', false);
         $(bSelect_VI).text('Zaznacz');
         $(bSelect_VI).removeClass('btn-info');
         $(bSelect_VI).addClass('btn-primary');
      }
      else{
         $(".roundCheckbox_VI").prop('checked', true);
         $(bSelect_VI).text('Odznacz');
         $(bSelect_VI).removeClass('btn-primary');
         $(bSelect_VI).addClass('btn-info');
      }
      selectState_VI = !selectState_VI;
    });
    $('#select_VII').on('click', function() {
      if(selectState_VII){
         $(".roundCheckbox_VII").prop('checked', false);
         $(bSelect_VII).text('Zaznacz');
         $(bSelect_VII).removeClass('btn-info');
         $(bSelect_VII).addClass('btn-primary');
      }
      else{
         $(".roundCheckbox_VII").prop('checked', true);
         $(bSelect_VII).text('Odznacz');
         $(bSelect_VII).removeClass('btn-primary');
         $(bSelect_VII).addClass('btn-info');
      }
      selectState_VII = !selectState_VII;
    });
    $('#select_VIII').on('click', function() {
      if(selectState_VIII){
         $(".roundCheckbox_VIII").prop('checked', false);
         $(bSelect_VIII).text('Zaznacz');
         $(bSelect_VIII).removeClass('btn-info');
         $(bSelect_VIII).addClass('btn-primary');
      }
      else{
         $(".roundCheckbox_VIII").prop('checked', true);
         $(bSelect_VIII).text('Odznacz');
         $(bSelect_VIII).removeClass('btn-primary');
         $(bSelect_VIII).addClass('btn-info');
      }
      selectState_VIII = !selectState_VIII;
    });
    $('#select_IX').on('click', function() {
      if(selectState_IX){
         $(".roundCheckbox_IX").prop('checked', false);
         $(bSelect_IX).text('Zaznacz');
         $(bSelect_IX).removeClass('btn-info');
         $(bSelect_IX).addClass('btn-primary');
      }
      else{
         $(".roundCheckbox_IX").prop('checked', true);
         $(bSelect_IX).text('Odznacz');
         $(bSelect_IX).removeClass('btn-primary');
         $(bSelect_IX).addClass('btn-info');
      }
      selectState_IX = !selectState_IX;
    });
    </script>
@stop