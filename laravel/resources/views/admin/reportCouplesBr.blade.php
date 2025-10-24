@extends('admin.master')

@section('title')
    Raport
@stop

@section('content')
    <div id="page-wrapper">
        {!! Form::open(array('method' => 'get', 'url' => 'admin/report')) !!} {!! csrf_field() !!}
        <div class="row">
            <div class="col-lg-12">
              <div class="page-header-break">ZESTAW PAR TAŃCZĄCYCH W RÓŻNYCH KLASACH<br/></div>
              <h1 class="page-header">Pary tańczące w różnych klasach.
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
                    <table class="table table-striped table-bordered table-hover text-center table-pad-2px">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 10%">
                                    Lp.
                                </th>
                                <th class="text-center" style="width: 15%">
                                    Para
                                </th>
                                <th style="width: 75%">
                                    Style
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $idx = 0 ?>
                            @foreach($couples as $number=>$description)
                              <tr>
                                 <td class="btn-circle">
                                    {{$idx+1}}.
                                    <?php $idx = $idx+1 ?>
                                 </td>
                                 <td class="text-center font-12pt">
                                     {{$number}}
                                 </td>
                                 <td class="text-left font-12pt">
                                     {{$description}}
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