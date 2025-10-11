@extends('admin.master')

@section('title')
    Program Turnieju
@stop

@section('content')
    <div id="page-wrapper">
        <div class="row">
          <div class="page-header-break">PROGRAM TURNIEJU &nbsp{{$parts}}</div>
            <div class="col-lg-12">
                <h1 class="page-header">Program turnieju &nbsp{{$parts}}
                    <div class="pull-right">
                        <nav id="navbar-darkblue" class="navbar navbar-default">
                        <div class="container-fluid">
                           <ul class="nav navbar-nav">
                              @if(count($additionalRounds) > 0 && count($program) > 0)
                                 <li><button type="button" class="btn btn-amber" class="dropdown-toggle" data-toggle="modal" data-target=".additionalRoundModal" class="button-menu">Dodatkowa / Baraż</button></li>
                              @endif

                              @if(count($program) > 0)
                                 <li><a href="program/editProgram" type="button" class="btn btn-orange" id="main-menu" role="button"> Modyfikuj</a></li>
                                 <li><button type="button" class="btn btn-deep-orange" data-toggle="modal" data-target=".programAddRoundModal" id="main-menu">Dodaj rundę</button></li>
                              @endif
                              <li class="dropdown"><button type="button" class="btn btn-primary button-menu" class="dropdown-toggle" data-toggle="dropdown" href='#'>Program <i class="fa fa-caret-down"></i></button>
                                 <ul class="dropdown-menu">
                                    <li><a href="#" data-toggle="modal" data-target=".programSelectorModal"><i class="fa fa-folder-open">&emsp;Pobierz</i></a></li>
                                    @if(count($program) > 0)
                                       <li><a href="#" data-toggle="modal" data-target=".additionalProgramModal"><i class="fa fa-plus-square">&emsp; Dołącz</i></a></li>
                                       <li><a href="#" data-toggle="modal" data-target=".saveProgramModal"><i class="fa fa-file-text">&emsp; Zapisz</i></a></li>
                                    @endif
                                    <li><a href="program/newProgram"><i class="fa fa-pencil-square-o">&emsp;Nowy</i></a></li>
                                 </ul>
                              </li>
                           </ul>
                        </div>
                    </div>
                </h1>
            </div>
        </div>
        <!-- /.row -->
   @if(session('status'))
      @if(session('status') == 'success')
         <div class="alert alert-success">
            Program został zapisane do pliku.
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
         </div>
      @else
         <div class="alert alert-danger">
            Błąd dostępu do pliku. Program nie został zapisany.
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
         </div>
      @endif
   @endif
        
      @if(count($program) > 0)
         <div class="row">
            <div class="col-lg-12">
               <div class="pull-left font-14pt userTime ekran">
                  Czas rozpoczęcia: <button class="btn-light-blue font-14pt">{{$layout->startTime}}</button>&nbsp
                  zakończenia: <button class="btn-light-blue font-14pt">{{$times[count($compressedProgram)]}}</button>
               </div>
               <div class="pull-right"> 
               <nav id="navbar-brown" class="navbar navbar-default">
                 <ul class="nav navbar-nav">
                  <li class="dropdown"><button type="button" class="btn btn-brown button-menu" class="dropdown-toggle" data-toggle="dropdown" href='#'>Drukuj <i class="fa fa-caret-down"></i></button>
                     <ul class="dropdown-menu">
                        <li><a href="#"><i class="fa fa-plus-square" id='withTimes'>&emsp; z czasami</i></a></li>
                        <li><a href="#"><i class="fa fa-minus-square" id='withoutTimes'>&emsp; bez czasów</i></a></li>
                     </ul>
                  </li>
                  </ul>
               </div>
            </div>
        </div>
      @endif
      @include('admin.scheduleTable')
      @if(count($program) > 0)
         <div class="row">
            <div class="col-lg-12">
                <div class="pull-right">
                  <nav id="navbar-brown" class="navbar navbar-default">
                     <ul class="nav navbar-nav">
                     <li class="dropup"><button type="button" class="btn btn-brown button-menu" class="dropdown-toggle" data-toggle="dropdown" href='#'>Drukuj <i class="fa fa-caret-down"></i></button>
                     <ul class="dropdown-menu">
                        <li><a href="#"><i class="fa fa-plus-square" id='withTimes2'>&emsp; z czasami</i></a></li>
                        <li><a href="#"><i class="fa fa-minus-square" id='withoutTimes2'>&emsp; bez czasów</i></a></li>
                     </ul>
                  </li>
                  </ul>
                </div>
            </div>
        </div>
        @endif
    </div>
    <!-- /#page-wrapper -->

    <!-- File input selector -->
    <div class="modal fade programSelectorModal" tabindex="-1" role="dialog" aria-labelledby="programSelector" aria-hidden="true">
        {!! Form::open(array('url' => 'admin/program', 'method' => 'POST', 'files'=>true)) !!}
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Program turnieju</h4>
                </div>
                <div class="modal-body">
                    <div>
                        Wybierz plik z programem turnieju
                    </div>
                    <div>
                        <input type="file" name="program" accept=".csv,.dbf">
                    </div>
                    <div class="modal-footer">
                      <div class="pull-left">
                        <button type="button" class="btn btn-lg btn-warning button-menu" data-dismiss="modal">Anuluj</button>
                      </div>
                      <div class="pull-right">
                        {!! Form::submit('Wczytaj', array('class' => 'btn btn-primary button-menu')) !!}
                      </div>
                    </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    <!-- Add round selector -->
    <div class="modal fade programAddRoundModal" tabindex="-1" role="dialog" aria-labelledby="programAddRound" aria-hidden="true">
      {!! Form::open(array('method' => 'post','action' => array('Admin\DashboardController@postAddedRound', 'size'=>'50%'))) !!}
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
               <h4 class="modal-title" id="myModalLabel">Dodaj rundę do program turnieju</h4>
            </div>
            <div class="modal-body">
               <div class="form-group">
                  {!! Form::select('round', $roundNames, '1/2 Finału', ['id' => 'my_round'] ); !!}
                  @if( $categoriesNames != false )
                     {!! Form::select('category', $categoriesNames, null, ['id' => 'my_category'] ); !!}
                  @endif
                  {!! Form::select('additional', $additNames, null, ['id' => 'my_additional'] ); !!}
                  {!! Form::text('myround', null, ['id' => 'my_name','placeholder' => 'Nazwa własna?','maxlength' => '20','class' => 'text-left']); !!}
                  {!! Form::text('mybreakshow_name', null, ['id' => 'my_sb_name','placeholder' => 'nazwa?','maxlength' => '20','class' => 'text-left']); !!}
                  {!! Form::text('mybreakshow_dance', null, ['id' => 'my_sb_dance','placeholder' => 'tance/minuty?','maxlength' => '20','class' => 'text-left']); !!}
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
   
	<!-- File input selector for additional program -->
    <div class="modal fade additionalProgramModal" tabindex="-1" role="dialog" aria-labelledby="additionalProgram" aria-hidden="true">
        {!! Form::open(array('url' => 'admin/program/linkProgram', 'method' => 'POST', 'files'=>true)) !!}
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Dodatkowy program turnieju</h4>
                </div>
                <div class="modal-body">
                    <div>
                        Wybierz dodatkowy plik z programem turnieju
                    </div>
                    <div>
                        <input type="file" name="program_add" accept=".csv,.dbf">
                    </div>
                    <div class="modal-footer">
                        <div class="pull-left">
                           <button type="button" class="btn btn-lg btn-warning button-menu" data-dismiss="modal">Anuluj</button>
                        </div>
                        <div class="pull-right">
                           {!! Form::submit('Dodaj do programu', array('class' => 'btn btn-primary button-menu')) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
   </div>

   <!-- File save selector -->
   <div class="modal fade saveProgramModal" tabindex="-1" role="dialog" aria-labelledby="saveProgram" aria-hidden="true">
      {!! Form::open(array('method' => 'get','action' => array('Admin\DashboardController@saveCurrentProgram'))) !!}
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
               <h4 class="modal-title" id="myModalLabel">Zapisz program turnieju</h4>
            </div>
            <div class="modal-body">
               <div class="form-group">
                  Zapisz nazwę programu
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
   
    <!-- Additional round -->
   <div class="modal fade additionalRoundModal" tabindex="-1" role="dialog" aria-labelledby="additionalRound" aria-hidden="true">
      {!! Form::open(array('url' => 'admin/program/postAdditionalRound', 'method' => 'POST')) !!}
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                   <h4 class="modal-title" id="myModalLabel">Wybierz dodatkową rundę</h4>
               </div>
               <div class="modal-body">
                  <div class="form-group">
                     <select name="additionalRoundId" class="form-control">
                        @foreach($additionalRounds as $additionalRound)
                           <option value="{{$additionalRound->roundId}}">{{$additionalRound->roundName}} {{$additionalRound->categoryName}} {{$additionalRound->className}} {{$additionalRound->styleName}} {{$additionalRound->matchType}}                                    
                           </option>
                        @endforeach
                     </select>
                  </div>
               </div>
               <div class="modal-footer">
                  <div class="pull-left">
                     <button type="button" class="btn btn-lg btn-warning button-menu" data-dismiss="modal">Anuluj</button>
                  </div>
                  <div class="pull-right">
                     {!! Form::submit('Dodaj do programu', array('class' => 'btn btn-primary button-menu')) !!}                     
                  </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>

@stop

@section('customScripts')
    {!! HTML::script('js/jquery-ui.min.js') !!}
    
<script>
    $(function(){
        $('#my_name').hide();
        $('#my_sb_name').hide();
        $('#my_sb_dance').hide();
        $('#my_category').show();
        $('#my_additional').show();
        $('#my_round').on('change', function () {
            var v = this.value;
            if( v == 'my' )
            {
               //console.log('my_round - my ',this.value);
               $('#my_name').show();
               $('#my_category').show();
               $('#my_additional').show();
               $('#my_sb_name').hide();
               $('#my_sb_dance').hide();
            }
            else if( v == 'sh_br' )
            {
               //console.log('my_round - break ',this.value);
               $('#my_sb_name').show();
               $('#my_sb_dance').show();
               $('#my_category').hide();
               $('#my_additional').hide();
               $('#my_name').hide();
            }
            else
            {
               //console.log('my_round - inny ',this.value);
               $('#my_category').show();
               $('#my_additional').show();
               $('#my_sb_name').hide();
               $('#my_sb_dance').hide();
            }
        });
        $('#withTimes').on('click', function () {
            $('.userTime').addClass('drukarka');
            $('.userTime').removeClass('ekran');
            window.print();
            $('.userTime').addClass('ekran');
            $('.userTime').removeClass('drukarka');
        });
        $('#withoutTimes').on('click', function () {
            window.print();
        });
        $('#withTimes2').on('click', function () {
            $('.userTime').addClass('drukarka');
            $('.userTime').removeClass('ekran');
            window.print();
            $('.userTime').addClass('ekran');
            $('.userTime').removeClass('drukarka');
        });
        $('#withoutTimes2').on('click', function () {
            window.print();
        });

        var printFormat = false;
        var css_h = '@page { size: landscape; }',
            css_v = '@page { size: portrait; }',
            head = document.head || document.getElementsByTagName('head')[0],
            style = document.createElement('style');
        style.type = 'text/css';
        style.media = 'print';
        $('#printFormat').on('click', function() {
           if(printFormat){
              $('#printFormat').text('Pionowo');
              if (style.styleSheet){
                 style.styleSheet.cssText = css_v;
              } else {
                 style.appendChild(document.createTextNode(css_v));
              }
              head.appendChild(style);
           }
           else{
              $('#printFormat').text('Poziomo');
              if (style.styleSheet){
                 style.styleSheet.cssText = css_h;
              } else {
                 style.appendChild(document.createTextNode(css_h));
              }
              head.appendChild(style);
           }
           printFormat = !printFormat;
        });
    });
</script>
@stop