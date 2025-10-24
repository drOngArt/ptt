@extends('admin.master')

@section('title')
    Panel
@stop

@section('content')
   <div id="page-wrapper">
      <div class="page-header-break">LISTA SĘDZIÓW &nbsp{{$parts}}<br/></div>
         {!! Form::open(array('method' => 'post', 'url' => 'admin/postPanel')) !!} {!! csrf_field() !!}
         <div class="row">
            <div class="col-lg-12">
               <h1 class="page-header">Panel sędziowski &nbsp&nbsp{{$parts}}
                  <div class="pull-right">
                     {!! Form::submit('Zapisz...', array('name' => 'save','class' => 'btn btn-cyan button-menu')) !!}
                     {!! Form::submit('Powrót', array('id'=>'submitButton1', 'class' => 'btn btn-primary button-menu')) !!}
                  </div>
               </h1>
            </div>
            <!-- /.col-lg-12 -->
         </div>
         @if(session('status'))
            @if(session('status') == 'error')
            <div class="alert alert-danger">
               Brak pliku 'Listy_{{$eventId}}.csv' w katalogu turnieju lub nieprawidłowy format.
               <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            </div>
            @endif
         @endif

         <!-- /.row -->
         <div class="row">
            <div class="col-lg-12">
               <div class="pull-left">
                  {!! Form::label('sedzia', 'Sędzia główny', ['class'=>'btn button-menu btn-gray']); !!}
                  {!! Form::select('MainJudge', $judgelist, '', ['id' => 'mainJudge', 'class'=>'btn button-menu btn-blue-gray'] ); !!}</br>
                  {!! Form::text('main_judge_l', '', ['placeholder'=>'Nazwisko','maxlength' => '25','class' => 'btn text-left my_main_judge']); !!}
                  {!! Form::text('main_judge_f', '', ['placeholder'=>'Imię','maxlength' => '15','class' => 'btn text-left my_main_judge']); !!}
                  {!! Form::text('main_judge_c', '', ['placeholder'=>'Miasto','maxlength' => '15','class' => 'btn text-left my_main_judge']); !!}
               </div>
               <div class="pull-right">
                  <nav id="navbar-brown" class="navbar navbar-default">
                     <ul class="nav navbar-nav">
                     <li><button id="selectAll" type="button" class="btn btn-deep-orange button-menu">Zaznacz</button></li>
                     <li class="dropdown"><button type="button" class="btn btn-brown button-menu" class="dropdown-toggle" data-toggle="dropdown" href='#'>Drukuj <i class="fa fa-caret-down"></i></button>
                        <ul class="dropdown-menu">
                           <li><a href="#"><i class="glyphicon glyphicon-option-vertical" id='printFormatV'>&emsp;Pionowo</i></a></li>
                           <li><a href="#"><i class="glyphicon glyphicon-option-horizontal" id='printFormatH'>&emsp;Poziomo</i></a></li>
                        </ul>
                     </li>
                     </ul>
               </div>
            </div>
         </div>

         <div class="row">
            <div class="col-lg-12" > 
               <div class="table-responsive panel-col">
                  <table id="my_table" class="table table-striped table-bordered table-hover table-panel" style="table-layout: fixed; width: 100%;">
                     <thead class="font-12pt">
                        <tr>
                           <th class="headcol text-right" style="height:60px">
                              Kategoria</br>
                              Klasa</br>
                              Styl
                           </th>
                           @foreach($program as $category)
                              <th class="text-center fixed-col style="min-height:62px;" >
                                 <input hidden name="roundBaseId[]" value="{{$category->baseRoundId}}">
                                 <input hidden name="roundName[]" value="{{$category->description}}">
                                 <input hidden name="judgeNo[]" value="{{$category->judgesNo}}">
                                 {{$category->categoryName}}</br>
                                 {{$category->className}}</br>
                                 {{$category->styleName}}
                              </th>
                           @endforeach
                        </tr>
                        <tr>
                           <th class="headcol" style="height:23px">Liczba sędziów </th>
                           @foreach($program as $key => $category)
                              <th class="text-center fixed-col" style="height:24px;" id="{{$key}}">
                                 <input hidden name="{{$key}}" value="{{$category->judgesNo}}">
                                 &nbsp{{$category->judgesNo}}&nbsp
                              </th>
                           @endforeach
                        </tr>
                     </thead>
                     <tbody id="sortable">
                        @foreach($judges as $pl_id => $judge)
                           <tr>
                              <th class="headcol">
                                 <input hidden name="judgeId[]" value="{{$pl_id}}">
                                 <input hidden name="judgeName[]" value="{{$judge->firstName}} {{$judge->lastName}}">
                                 {{$judge->lastName}}</br>&nbsp&nbsp{{$judge->firstName}}
                              </th>
                              <?php $idx = 0; ?>
                              @foreach($program as $key => $category)
                                 <td class="text-center fixed-col" style="height:39px">
                                    @if( $judge->sign[$idx] != ' ' )
                                       <input class="judgeCheckbox" type="checkbox" name="{{$key}}-{{$pl_id}}" checked>
                                    @else
                                       <input class="judgeCheckbox" type="checkbox" name="{{$key}}-{{$pl_id}}">
                                    @endif
                                 </td>
                                 <?php $idx = $idx+1; ?>
                              @endforeach
                           </tr>
                        @endforeach
                        <!-- @if(count($scrutineers)> 0 )
                           <tr class="ui-state-disabled ekran">
                              <th class="headcol text-center ekran" style="height:28px">
                              Skrutinerzy
                              </th>
                              <td colspan="{{count($program)}}"> &nbsp </td>
                           </tr>
                           @foreach($scrutineers as $pl_id => $judge)
                              <tr page-header class="ui-state-disabled">
                                 <th class="headcol ekran">
                                    <input hidden name="scrId[]" value="{{$pl_id}}">
                                    <input hidden name="scrName[]" value="{{$judge->firstName}} {{$judge->lastName}}">
                                    {{$judge->lastName}}</br>&nbsp&nbsp{{$judge->firstName}}
                                 </th>
                                 <?php $idx = 0; ?>
                                 @foreach($program as $key => $category)
                                    <td class="text-center ekran" style="height:39px">
                                       @if( $judge->sign[$idx] != ' ' )
                                          <input class="judgeCheckbox " type="checkbox" name="s{{$key}}-{{$pl_id}}" checked>
                                       @else
                                          <input class="judgeCheckbox" type="checkbox" name="s{{$key}}-{{$pl_id}}">
                                       @endif
                                    </td>
                                    <?php $idx = $idx+1; ?>
                                 @endforeach
                              </tr>
                           @endforeach
                        @endif -->
                     </tbody>
                  </table>
               </div>
               <div class="col-lg-12">
                  <div class="pull-left">
                     {!! Form::select('dodaj', [ '0' => 'Dodaj sędziego', '1' => 'Dodaj sędziego z Bazy:', '2' => 'Dodaj sędziego własnego:'], 'Dodaj sędziego',['id' => 'add_judge', 'class'=>'text-left btn btn-primary button-menu']); !!}
                     {!! Form::text('term', '', ['placeholder' => 'Nazwisko i imię', 'id' => 'from_base', 'class'=>'btn text-left ekran']); !!}
                     {!! Form::text('judgeadd_l', '', ['placeholder'=>'Nazwisko', 'id' => 'judgeadd_l','maxlength' => '25','class' => 'btn text-left ekran my_add']); !!}
                     {!! Form::text('judgeadd_f', '', ['placeholder'=>'Imię','id' => 'judgeadd_f','maxlength' => '15','class' => 'btn text-left ekran my_add']); !!}
                     {!! Form::text('judgeadd_c', '', ['placeholder'=>'Miasto','id' => 'judgeadd_c','maxlength' => '15','class' => 'btn text-left ekran my_add']); !!}
                     {!! Form::label('dodaj', 'Zatwierdź',['id'=>'after_add','class' => 'btn btn-primary']) !!}
                     {!! Form::label('dodaj_m', 'Zatwierdź',['id'=>'after_add_manual','class' => 'btn btn-primary']) !!}
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
    {!! HTML::script('js/jquery.dragtable.js') !!}
     <script>
        $(function() {
            var selectAllState = false;
            var bSelectAll = document.getElementById('selectAll');
            var roundsId = '';

            $.fn.updateColorForJudgeNo = function(idx) {
               var count = 0; // = $("[type='checkbox'][name^=key]:checked").size();
               var judge_no = 0; //$("input:hidden[name=key]").val();

               $('[type="checkbox"]:checked').each(function(i,chk) {
                  var part = $(chk).attr('name').split('-')[0];
                  if( part == idx ){
                     count += 1;
                  }
               });
               $('input:hidden').each(function(i,inp) {
                  var col = $(inp).attr('name');
                  if(  col == idx ){
                     judge_no = $(inp).val();
                  }
               });
               if( count > judge_no ){
                  $("#my_table").find("th#" + idx).removeClass('btn-warning');
                  $("#my_table").find("th#" + idx).removeClass('btn-success');
                  $("#my_table").find("th#" + idx).addClass('btn-danger');
               }
               else if( count < judge_no ){
                  $("#my_table").find("th#" + idx).removeClass('btn-success');
                  $("#my_table").find("th#" + idx).removeClass('btn-danger');
                  $("#my_table").find("th#" + idx).addClass('btn-warning');
               }
               else{
                  $("#my_table").find("th#" + idx).removeClass('btn-warning');
                  $("#my_table").find("th#" + idx).removeClass('btn-danger');
                  $("#my_table").find("th#" + idx).addClass('btn-success');
               }
            }; 
               
            $('tr:first', '#my_table').find("input:hidden[name^='roundBase']").each(function () {
               if( roundsId == '')
                  roundsId += $(this).val();
               else
                  roundsId += ';' + $(this).val();
               $(this).updateColorForJudgeNo($(this).val());
            });
            $('#selectAll').on('click', function(){
               if(selectAllState){
                  $(".judgeCheckbox").prop('checked', false);
                  $(bSelectAll).text('Zaznacz');
                  $(bSelectAll).removeClass('btn-lime');
                  $(bSelectAll).addClass('btn-deep-orange');
               }
               else{
                  $(".judgeCheckbox").prop('checked', true);
                  $(bSelectAll).text('Odznacz');
                  $(bSelectAll).removeClass('btn-deep-orange');
                  $(bSelectAll).addClass('btn-lime');
               }
               selectAllState = !selectAllState;
               $('tr:first', '#my_table').find("input:hidden[name^='roundBase']").each(function () {
                  $(this).updateColorForJudgeNo($(this).val());
               });
            });
            var css_h = '@page { size: landscape; }',
                css_v = '@page { size: portrait; }',
                head = document.head || document.getElementsByTagName('head')[0],
                style = document.createElement('style');
            style.type = 'text/css';
            style.media = 'print';

            $('#printFormatV').on('click', function() {
               if (style.styleSheet){
                  style.styleSheet.cssText = css_v;
               } else {
                  style.appendChild(document.createTextNode(css_v));
               }
               head.appendChild(style);
               window.print();
            });
            $('#printFormatH').on('click', function() {
               if (style.styleSheet){
                  style.styleSheet.cssText = css_h;
               } else {
                  style.appendChild(document.createTextNode(css_h));
               }
               head.appendChild(style);
               window.print();
            }); 
            $( '#sortable' ).sortable({
                //cursorAt: { top: 25 },
                axis: 'y',
                items: 'tr:not(.ui-state-disabled)',
                start: 
                  function (event, ui){
                     ui.item.css('background-color', '#A9F5F2');
                     ui.item.css('border-radius','8px');
                     ui.item.css('border', '2px solid #428bca');
                  },
                stop: 
                  function(event,ui){
                     ui.item.css('border', '');
                     ui.item.css('background-color', '#F8ECE0');
                  }
            });
            $( '#sortable' ).disableSelection();
            $( 'td' ).each(function(){
                $(this).css('width', $(this).width() +'px');
            });
            $('#my_table').dragtable();
            $(".my_main_judge").hide();
            $(".my_add").hide();
            $("#after_add").hide();
            $("#after_add_manual").hide();
            $("#judgeadd_l").hide();
            $("#judgeadd_f").hide();
            $("#judgeadd_c").hide();
            $("#from_base").hide();
            $("#mainJudge").on('change', function () {
               if( this.value == '000000' )
                  $(".my_main_judge").show();
               else
                  $(".my_main_judge").hide();
            });
            $("#add_judge").on( 'click', function () {
               //console.log('add_judge click - ',this.value);
               if( this.value == 1 ){
                  $("#from_base").show();
                  $('#from_base').val('');
                  $(".my_add").hide();
                  $("#after_add_manual").hide();
               }
               else if(this.value == 2){
                  $("#from_base").hide();
                  $('#from_base').val('');
                  $(".my_add").show();
                  $("#after_add").hide();
                  $("#after_add_manual").show();
               }
               else{
                  $("#from_base").hide();
                  $('#from_base').val('');
                  $(".my_add").hide();
                  $("#after_add").hide();
                  $("#after_add_manual").hide();
               }
            });
            $("#after_add").on( 'click', function () {
               var val = $('#from_base').val();
               var lastname = $.trim(val.split(',')[0]);
               var firstname = $.trim(val.split(',')[1]);
               var judgeId = $.trim(val.split(',')[3]);
               var noRounds = roundsId.split(';'); 
               var tbody = '<input hidden name="judgeId[]" value="'+ judgeId +'">';
                   tbody += '<input hidden name="judgeName[]" value="' + firstname + ' ' + lastname + '">';
                   tbody += lastname + '</br>&nbsp&nbsp' + firstname;

               var clonedRow = $("#my_table tr:nth-child(3)").clone();
               $('input:checked', clonedRow).attr('checked', false); // uncheck any checked boxes
               $('th', clonedRow ).html( tbody );
               $('input:hidden[name^=judgeId]', clonedRow).attr('value', judgeId); // set new value
               $('input:hidden[name^=judgeName]', clonedRow).attr('value', firstname + ' ' + lastname ); // set new value
               $('[type="checkbox"]', clonedRow ).each(function(i, chk) {
                  $(chk).attr('name',noRounds[i]+'-'+judgeId);
               });   
               $("#my_table").append(clonedRow);
               $('[type="checkbox"]').on('click' , function() {
                  var idx = this.name.split('-')[0];
                  $(this).updateColorForJudgeNo(idx);
               });
               $(this).hide();
               $("#from_base").hide();
            });
            $("#after_add_manual").on( 'click', function () {
               var lastname = $.trim($('#judgeadd_l').val());
               var firstname = $.trim($('#judgeadd_f').val());
               var city = $.trim($('#judgeadd_c').val());
               var judgeId = lastname+';'+firstname+';'+city+';';
               var noRounds = roundsId.split(';');
               var tbody = '<input hidden name="judgeId[]" value="'+ judgeId +'">';
                   tbody += '<input hidden name="judgeName[]" value="' + firstname + ' ' + lastname + '">';
                   tbody += lastname + '</br>&nbsp&nbsp' + firstname;

               var clonedRow = $("#my_table tr:nth-child(3)").clone();
               $('input:checked', clonedRow).attr('checked', false); // uncheck any checked boxes
               $('th', clonedRow ).html( tbody );
               $('input:hidden[name^=judgeId]', clonedRow).attr('value', judgeId); // set new value
               $('input:hidden[name^=judgeName]', clonedRow).attr('value', firstname + ' ' + lastname ); // set new value
               $('[type="checkbox"]', clonedRow ).each(function(i, chk) {
                  $(chk).attr('name',noRounds[i]+'-'+judgeId);
               });   
               $("#my_table").append(clonedRow);
               $('[type="checkbox"]').on('click' , function() {
                  var idx = this.name.split('-')[0];
                  $(this).updateColorForJudgeNo(idx);
               });
               $(this).hide();
               $("#after_add_manual").hide();
               $("#judgeadd_l").hide();
               $("#judgeadd_f").hide();
               $("#judgeadd_c").hide();
               $("#from_base").hide();
            });
            $("#after_ad_d").on( 'click', function () {  
               //example 
               var tbody = '<tr class="ui-sortable-handle"><th class="headcol">' + lastname + '</br>&nbsp' + firstname + '</th>';
               tbody += '<input hidden name="judgeId[]" value="'+ judgeId +'">';
               tbody += '<input hidden name="judgeName[]" value="' + firstname + ' ' + lastname + '">';
               $.each( noRounds, function( index, value ){
                  tbody += '<td class="text-center" style="height: 39px;"><input class="judgeCheckbox" type="checkbox" name="'+value+'-'+judgeId+'"</td>';
               });
               tbody += '</tr>';
               $('#my_table tr:last').after(tbody);
               
            });
            $(".judgeCheckbox").on( 'click', function () {
               var idx = this.name.split('-')[0];
               $(this).updateColorForJudgeNo(idx);
            });
            $( '#from_base' ).autocomplete({
               source: 'autocomplete',
               minLength: 1,
               autofocus: true,
               scroll: true,
               close: function(el) {
                  $('#after_add').show();
               },
               select: function(event, ui) {
                  $('#from_base').val(ui.item.value);
                  return false;
               },
               // optional (if other layers overlap autocomplete list)
               open: function(event, ui) {
                  //input.value='';
                  $('.ui-autocomplete').css("z-index", 5000);
               }
            });
        });
    </script>
@stop