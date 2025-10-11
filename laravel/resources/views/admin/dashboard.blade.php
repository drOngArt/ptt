@extends('admin.master')

@section('title')
    Admin Panel
@stop

@section('content')
   <div id="page-wrapper">
      <div class="row">
         <div class="page-header-break">LISTA SĘDZIÓW</div>
         <div class="col-lg-12">
            <h1 class="page-header">Sędziowie
               <div class="pull-right">
                  <button type="button" class="btn btn-light-blue button-menu" data-toggle="modal" data-target=".passwordAllModal">Hasła automatyczne</button>
                  <a href="admin/panel"><button class="btn btn-deep-orange" id="main-menu">Panel sędziowski</button></a>
               </div>
            </h1>
            @if($filterInProgram)
               <div class="pull-left ekran">
                  <h5><input id="presentFilter" type="checkbox"><label style="margin-left: 10px;" for="presentFilter">Pokaż wszystkich</label></h5>
               </div>
            @endif
            @if(count($judges) )
               <div class="pull-right">
                  <a href="javascript:window.print()" type="button" class="btn button-menu btn-brown" >Drukuj</a>
               </div>
            @endif

         </div>
            <!-- /.col-lg-12 -->
      </div>
      <!-- /.row -->
      <div class="table-responsive">
         <table class="table table-striped ekran">
            <tbody id="judgesTable">
               @foreach($judges as $judge)
                  <tr @if($judge->isInProgram || !$filterInProgram) class="present" @else hidden @endif >
                     <td>{{$judge->lastName}} {{$judge->firstName}}</td>
                     <td><a href="{{$baseURI}}/admin/password/{{$judge->id}}/true" class="btn btn-primary" role="button">Ustaw hasło</a></td>
                     <td>
                        @if($judge->status)
                           <i class="fa fa-mobile fa-lg"></i>
                           ver: {{$judge->softwareVersion}}
                           @if($judge->batteryLevel < 20)
                              <i class="fa fa-battery-empty fa-rotate-270"></i> 
                           @elseif($judge->batteryLevel < 40)
                              <i class="fa fa-battery-quarter fa-rotate-270"></i> 
                           @elseif($judge->batteryLevel < 60)
                              <i class="fa fa-battery-half fa-rotate-270"></i> 
                           @elseif($judge->batteryLevel < 80)
                              <i class="fa fa-battery-three-quarters fa-rotate-270"></i> 
                           @else
                              <i class="fa fa-battery-full fa-rotate-270"></i>
                           @endif 
                            {{$judge->batteryLevel}}%
                        @endif
                     </td>
                  </tr>
               @endforeach
            </tbody>
         </table>
      </div>
      <!-- for print only -->
      <div class="table-responsive">
         <table class="table table-striped drukarka">
            <thead>
               <tr class="present">
                  <th>Lp.</th>
                  <th>Imię i Nazwisko</th>
                  <th>Miasto</th>
                  <th>Kraj</th>
               </tr>
            </thead>
            <tbody id="judgesTablePrint">
               <tr class="present">
                  <td class="font-14pt">Główny</td>
                  @if( $mainJudge )
                     <td class="font-14pt">{{$mainJudge->firstName}}&nbsp{{$mainJudge->lastName}}</td>
                     <td class="font-14pt">{{$mainJudge->city}}</td>
                     <td class="font-14pt">{{$mainJudge->country}}</td>
                  @else
                     <td></td>
                     <td></td>
                     <td></td>
                  @endif
               </tr>
               <?php $idx = 1; ?>
               @foreach($judgestoPrint as $judge)
                  <tr @if($judge->isInProgram || !$filterInProgram ) class="present" @else hidden @endif>
                     <td class="btn-circle">{{$idx}}.</td>
                     @if($judge->isInProgram || !$filterInProgram )
                        <?php $idx++; ?>
                     @endif
                     <td class="font-14pt">{{$judge->firstName}}&nbsp{{$judge->lastName}}</td>
                     <td class="font-14pt">{{$judge->city}}</td>
                     <td class="font-14pt">{{$judge->country}}</td>
                  </tr>
               @endforeach
                  <tr class="present">
                     <td></td>
                     <td colspan=5><h2>SKRUTINERZY</h2></td>
                  </tr>
               <?php $idx = 1; ?>
               @foreach($scrutineers as $judge)
                  <tr class="present">
                     <td class="btn-circle">{{$idx}}.</td>
                     <?php $idx++; ?>
                     <td class="font-14pt">{{$judge->firstName}}&nbsp{{$judge->lastName}}</td>
                     <td class="font-14pt">{{$judge->city}}</td>
                     <td class="font-14pt">{{$judge->country}}</td>
                  </tr>
               @endforeach
            </tbody>
         </table>
      </div>
    </div>
    <!-- /#page-wrapper -->
        <!-- Save parameters selector -->
   <div class="modal fade passwordAllModal" tabindex="-1" role="dialog" aria-labelledby="passwordAll" aria-hidden="true">
      {!! Form::open(array('method' => 'get','action' => array('Admin\DashboardController@savePasswordAll'))) !!}
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
               <h4 class="modal-title" id="myModalLabel">Wprowadź wspólną częśc hasła, która wystąpi po inicjałach każdego sędziego<br/> (pierwsze imię, drugie nazwisko):</h4>
            </div>
            <div class="modal-body">
               <div class="form-group ekran">
                  dla Artur Skrutiner => as{!!Form::password('myPass', ['placeholder' => 'hasło','maxlength' => '10','class' => 'width_100px text-left']); !!} - zawsze małe litery
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
    
@stop

@section('customScripts')
    <script>
        $("#presentFilter").click(function(){
            var rows = $("#judgesTable").find("tr");
            var rowsp = $("#judgesTablePrint").find("tr");
            var checked = $("#presentFilter").prop('checked');
            
            if (!checked) {
               rows.each(function() {
                  if (!$(this).hasClass("present")) {
                     $(this).hide();
                  } else {
                      $(this).show();
                  }
               });
               var idx = 1;
               rowsp.each(function() {
                  if (!$(this).hasClass("present")) {
                     $(this).hide();
                  } else {
                     $(this).show();
                     if( isNaN(parseInt($(this).find('td:first').text(),10)) == false ) {
                        $(this).find('td:first').text(idx);
                        $(this).find('td:first').append('.');
                        idx = idx+1;
                     }
                     else
                        idx = 1;
                  }
               });
            } else {
               rows.each(function() {
                  $(this).show();
               });
               var idx = 1;
               rowsp.each(function() {
                  $(this).show();
                  if( isNaN(parseInt($(this).find('td:first').text(),10)) == false ) {
                     $(this).find('td:first').text(idx);
                     $(this).find('td:first').append('.');
                     idx = idx+1;
                  }
                  else
                     idx = 1;
               });
            }
        });
    </script>
@stop