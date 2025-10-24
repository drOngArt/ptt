@extends('admin.master')

@section('title')
    Raport
@stop

@section('content')
    <div id="page-wrapper">
        {!! Form::open(array('method' => 'get', 'url' => 'admin/report')) !!} {!! csrf_field() !!}
        <div class="row">
            <div class="col-lg-12">
              <h1 class="page-header">Listy startowe
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
               <div class="page-header-break">LISTA STARTOWA {{$couples[$index][0]->section}}</div>
                  <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover text-center table-pad-2px">
                       <thead>
                         <tr>
                           @if($couples[$index][0]->marker[0] == 'a')
                              <th colspan=5 class="text-center">
                                 <p class="alignleft font-print-18pt">&nbsp&nbspKategoria: {{$index}}</p><p class="alignright">[ Liczba par:<a class="font-print-24pt">&nbsp&nbsp{{$couples[$index][0]->NoCpl1}}</a>&nbsp&nbsp]</p>
                              </th>
                           @else
                              <th colspan=5 class="text-center">
                                 <p class="alignleft font-print-18pt">&nbsp&nbspKategoria: {{$index}}</p><p class="alignright">[ Liczba par:<a class="font-print-24pt">&nbsp&nbsp{{$couples[$index][0]->NoCpl1}}&nbsp/&nbsp{{$couples[$index][0]->NoCpl2}}</a>&nbsp&nbsp]</p>
                              </th>
                           @endif
                         </tr>
                       </thead>
                        <tbody>
                           <tr>
                                <th class="text-center">
                                    Lp.
                                </th>
                                <th>
                                    Imię i nazwisko
                                </th>
                                <th class="text-center">
                                    Klub / Kraj
                                </th>
                                @if($couples[$index][0]->marker[0] == 'a')
                                    <th class="text-center">
                                    Numer</br>
                                    </th>
                                @else
                                    <th class="text-center">
                                    Standard
                                    </th>
                                    <th class="text-center">
                                    Latin
                                    </th>
                                 @endif
                            </tr>
                            <?php $idx = 0; ?>
                            @foreach($couple as $position)
                              <tr>
                                    <td class="btn-circle">
                                       {{$idx+1}}.
                                       <?php $idx = $idx+1 ?>
                                    </td>
                                    <td class="text-left">
                                     {{$position->lastNameA}}&nbsp{{$position->firstNameA}}<br/>
                                     {{$position->lastNameB}}&nbsp{{$position->firstNameB}}
                                    </td>
                                    <td class="text-center">
                                       {{$position->club}}<br/>
                                       {{$position->country}}
                                    </td>
                                    @if($position->marker[0] == 'a')
                                       <td class="h3 media-middle">
                                       {{$position->number}}
                                       </td>
                                    @else
                                       @if($position->marker[0] == '1')
                                          <td class="h3 media-middle">
                                          {{$position->number}}
                                          </td>
                                          <td>
                                          </td>
                                       @elseif($position->marker[0] == '2')
                                          <td>
                                          </td>
                                          <td class="h3 media-middle">
                                          {{$position->number}}
                                          </td>
                                       @else
                                          <td class="h3 media-middle">
                                          {{$position->number}}
                                          </td>
                                          <td class="h3 media-middle">
                                          {{$position->number2}}
                                          </td>
                                       @endif
                                    @endif
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