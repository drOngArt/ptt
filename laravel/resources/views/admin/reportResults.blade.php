@extends('admin.master')

@section('title')
    Raport
@stop

@section('content')
    <div id="page-wrapper">
        {!! Form::open(array('method' => 'get', 'url' => 'admin/report')) !!} {!! csrf_field() !!}
        <div class="row">
            <div class="col-lg-12">              
              <h1 class="page-header">Wyniki
                <div class="pull-right">
                    {!! Form::submit('Powrót', array('id'=>'submitButton1', 'class' => 'btn btn-primary button-menu')) !!}
                </div>
                </h1>
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!-- /.row -->
        <div class="row">
            <div class="col-lg-12">
                <div class="pull-right">
                    <a href="javascript:window.print()" type="button" class="btn button-menu btn-brown" >Drukuj</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
            @foreach($couples as $index => $couple)
               <div class="page-header-break">WYNIKI<br/></div>
               <div class="text-center h3" >Kategoria: {{$index}} ( par: {{$Numbers[$index]}} )<br/></div>               
                  <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover text-center table-pad-2px">
                        <thead>
                           <tr>
                              <th class="text-center">
                                 Miejsce
                              </th>
                              <th class="text-center">
                                 Numer
                              </th>
                              <th>
                                 Imię i nazwisko
                              </th>
                              <th class="text-center">
                                 Klub / Kraj
                              </th>
                           </tr>
                        </thead>
                        <tbody>                            
                           @foreach($couple as $position)
                              <tr>
                                 <td class="text-center font-14pt">
                                    {{$position->manualPosition}}                                       
                                 </td>
                                 <td class="text-center font-print-18pt">
                                    {{$position->number}}                                       
                                 </td>
                                 <td class="text-left font-12pt">
                                  {{$position->lastNameA}}&nbsp{{$position->firstNameA}}<br/>
                                  {{$position->lastNameB}}&nbsp{{$position->firstNameB}}
                                 </td>
                                 <td class="text-center font-12pt">
                                    {{$position->club}}<br/>
                                    {{$position->country}}
                                 </td>                            
                              </tr>
                           @endforeach                            
                        </tbody>
                    </table>
                </div>                 
                @endforeach                
            </div>
        </div>        
        <div class="row">
            <div class="col-lg-12">
                <div class="pull-right">
                  {!! Form::submit('Powrót', array('id'=>'submitButton1', 'class' => 'btn btn-primary button-menu')) !!}</br>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
        <div class="row">
            <div class="col-lg-12">
               <div class="pull-right">
                  <a href="javascript:window.print()" type="button" class="btn button-menu btn-brown" >Drukuj</a>
               </div>
            </div>
        </div>
    </div>
    <!-- /#page-wrapper -->
@stop

@section('customScripts')
    {!! HTML::script('js/jquery-ui.min.js') !!}
    <script>        
    </script>
@stop