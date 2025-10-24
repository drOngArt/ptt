@extends('admin.master')

@section('title')
    Raport
@stop

@section('content')
    <div id="page-wrapper">
        {!! Form::open(array('method' => 'get', 'url' => 'admin/report')) !!} {!! csrf_field() !!}
        <div class="row">
            <div class="col-lg-12">
              <div class="page-header-break">ZESTAW PAR<br/></div>
              <h1 class="page-header">Numery par w rundach
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
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover text-center">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%">
                                    Lp.
                                </th>
                                <th style="width: 40%">
                                    Kategoria / Klasa
                                </th>
                                <th class="text-center" style="width: 10%">
                                    Liczba par
                                </th>
                                <th class="text-center">
                                    Numery par
                                </th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            <?php $idx = 0 ?>
                            @foreach($program as $index => $programRound)
                              @if ($programRound->baseNumberOfCouples > 0 )
                                 <tr>
                                    <td class="btn-circle">
                                       {{$idx+1}}.
                                       <?php $idx = $idx+1 ?>
                                    </td>
                                    <td class="text-left font-14pt">
                                     {{$programRound->description}}
                                    </td>
                                    <td class="font-print-24pt">
                                       {{$programRound->baseNumberOfCouples}}
                                    </td>
                                    <td class="text-left font-print-18pt">
                                    <?php $idx1 = 0; ?>
                                    @foreach($couples[$index] as $couple)
                                       {{$couple->number}}
                                       <?php $idx1 += 1; ?>
                                       @if( $idx1 < count($couples[$index]) )
                                           ,
                                       @endif
                                    @endforeach
                                    </td>
                                </tr>
                                 @endif
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