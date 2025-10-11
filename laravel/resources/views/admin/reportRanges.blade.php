@extends('admin.master')

@section('title')
    Panel
@stop

@section('content')
   <div id="page-wrapper">
      <div class="page-header-break">ZAKRES NUMERÓW STARTOWYCH<br/></div>
         {!! Form::open(array('method' => 'post', 'url' => 'admin/postRanges')) !!}
         <div class="row">
            <div class="col-lg-12">              
               <h1 class="page-header">Numery startowe
                  <div class="pull-right">
                     @if( count($lists) != 0 )
                        {!! Form::submit('Zapisz...', array('name' => 'save','class' => 'btn btn-cyan button-menu')) !!}
                     @endif
                     {!! link_to(URL::previous(), 'Anuluj', ['class' => 'btn btn-lg btn-warning button-menu']) !!}
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
                  {!! Form::label('range', 'Zakres numerów:', ['size'=>15,'class'=>'btn btn-blue ekran width_200px']); !!}&nbsp
                  {!! Form::text('main_start_no', $start, ['placeholder'=>'Od','size'=>5,'maxlength' => '4','class' => 'btn btn-lblue text-right ekran']); !!}
                  {!! Form::text('main_end_no', $finish, ['placeholder'=>'Do','size'=>5,'maxlength' => '4','class' => 'btn btn-lblue text-right ekran']); !!}</br>
                  {!! Form::label('range', 'Brak numerów:', ['size'=>15,'class'=>'btn btn-indigo ekran width_200px']); !!}&nbsp
                  {!! Form::text('lack_no', '', ['placeholder'=>'brakujące numery','size'=>20,'maxlength' => '128','class' => 'btn btn-lindigo text-left ekran']); !!}
                  (wpisz numery oddzielone przecinkiem)</br>
                  {!! Form::label('number_same', 'Ten sam numer dla pary w różnych blokach ?', ['class'=>'btn btn-light-blue ekran width_450px']); !!}&nbsp&nbsp
                  {!! Form::checkbox('agree', 'yes') !!}</br>
                  {!! Form::label('range', 'Liczba dodatkowych wolnych numerów: ', ['size'=>15,'class'=>'btn btn-cyan ekran width_450px']); !!}&nbsp
                  {!! Form::input('number', 'free_places', 2, ['id' => 'bt_free_numbers', 'size' => 5, 'class' => 'btn btn-lcyan text-center ekran', 'min' => 0, 'max' => 19, 'required' => 'required']) !!}&nbsp
                  
               </div>
            </div>
         </div>
         </br>
         <div class="row">
         <div class="col-lg-12">
            <table class="table table-striped table-bordered table-hover ekran text-center">
               <thead>
                  <tr>
                     <th class="text-right">
                        Kategoria
                     </th>
                     <th class="text-center">
                        Zdefiniowany zakres
                     </th>                     
                     <th class="text-center">
                        Liczba par
                     </th>
                     <th class="text-center">
                        Numer początkowy
                     </th>

                  </tr>
               </thead>
               <tbody>
                  <?php $idx=10; ?>
                    @foreach($lists as $category)
                        <tr>
                        @if( $idx != $category->positionW )
                           <?php $idx = $category->positionW; ?>
                           <td colspan="3">
                              <input hidden name="blockId[]" value="{{$category->positionW}}">                              
                              &nbspBLOK&nbsp{{$category->positionW}} 
                           </td>
                           <td class="text-center">
                              {!! Form::text('block_no[]','', ['placeholder'=>'start','size'=>5,'maxlength' => '4','class' => 'btn btn-teal text-right ekran']); !!}
                           </td>
                        </tr>
                        <tr>
                           <td class="text-right">
                              <input hidden name="roundId[]" value="{{$category->baseRoundId}}">
                              <input hidden name="roundName[]" value="{{$category->description}}">                              
                              {{$category->description}}&nbsp
                           </td>
                           <td class="text-center">
                              {{$category->startNo}} - {{$category->endNo}}
                           </td>
                           <td class="text-center">
                              {{$category->baseNumberOfCouples}}
                           </td>
                           <td class="text-center">
                              {!! Form::text('start_no[]','', ['placeholder'=>'start','size'=>5,'maxlength' => '4','class' => 'text-center ekran']); !!} 
                           </td>
                        @else
                           <td class="text-right">
                              <input hidden name="roundName[]" value="{{$category->description}}">
                              <input hidden name="roundId[]" value="{{$category->baseRoundId}}">
                              {{$category->description}}&nbsp
                           </td>
                           <td class="text-center">
                              {{$category->startNo}} - {{$category->endNo}}
                           </td>
                           <td class="text-center">
                              {{$category->baseNumberOfCouples}}
                           </td>                           
                           <td class="text-center">
                              {!! Form::text('start_no[]','', ['placeholder'=>'start','size'=>5,'maxlength' => '4','class' => 'text-center ekran']); !!}
                           </td>
                        @endif  
                        </tr>
                    @endforeach
                </tbody>
                </table>
            </div>
            <!-- /.col-lg-12 -->
        </div>        
        <!-- /.row -->
        {!! Form::close() !!}        
    </div>    
    <!-- /#page-wrapper -->
@stop

@section('customScripts')
    {!! HTML::script('js/jquery-ui.min.js') !!}        
     <script>
        $(function() {
           
        });
    </script>
@stop