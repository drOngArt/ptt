@extends('admin.master')

@section('title')
    Raport
@stop

@section('content')
    <div id="page-wrapper">
        {!! Form::open(array('method' => 'get', 'url' => 'admin/report')) !!} {!! csrf_field() !!}
        <div class="row">
            <div class="col-lg-12">
            <div class="page-header-break">ZESTAWIENIE RUND<br/></div>
              <h1 class="page-header">Zestaw rund (liczby par, grup)
                <div class="pull-right">
                    {!! Form::submit('Powrót', array('id'=>'submitButton1', 'class' => 'btn btn-primary button-menu')) !!}
                </div>
                </h1>
            </div>
            <!-- /.col-lg-12 -->
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
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover text-center">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%">
                                    Lp.
                                </th>
                                <th style="width: 45%">
                                    Runda
                                </th>
                                <th class="text-center">
                                    Liczba par
                                </th>
                                <th class="text-center">
                                    Typowań
                                </th>
                                <th class="text-center">
                                    Grup
                                </th>
                            </tr>
                        </thead>
                        <tbody">
                            <?php $idx = 0 ?>
                            @foreach($program as $programRound)
                                <tr>
                                    <td class="btn-circle">
                                    @if($programRound->baseNumberOfCouples > 0 )
                                        {{$idx+1}}.
                                        <?php $idx = $idx+1 ?>
                                    @endif
                                    </td>
                                    <td class="text-left font-14pt">
                                     {{$programRound->description}}
                                    </td>
                                    @if($programRound->baseNumberOfCouples > 0 )
                                       <td class="font-print-24pt">
                                          {{$programRound->baseNumberOfCouples}}
                                       </td>
                                    @else   
                                       <td>
                                       </td>
                                    @endif
                                    <td>
                                    </td>
                                    <td>
                                    </td>
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
@stop

@section('customScripts')
    {!! HTML::script('js/jquery-ui.min.js') !!}
    <script>        
    </script>
@stop