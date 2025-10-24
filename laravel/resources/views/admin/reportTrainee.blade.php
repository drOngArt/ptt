@extends('admin.master')

@section('title')
    Papiery dla stażysty
@stop

@section('content')

  <div id="page-wrapper">
    {!! Form::open(array('method' => 'get', 'url' => 'admin/report')) !!} {!! csrf_field() !!}
      <div class="row">
        <div class="col-lg-12">
          <h1 class="page-header">Stażysta
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
        <?php $pos=0; $name = '';?>
        @foreach($rounds as $round)
          @if( $round != false )
            <div style="clear: both;"></div>
            <div class="table-responsive">
            <table class="trainee table table-cont table-striped table-bordered" style="table-layout: fixed; width: auto;">
              <tbody>
                @if($couples[$pos] != false)
                  @if( strpos($name, $round->roundName.$round->categoryName.$round->className.$round->styleName) === false ) 
                    <tr>
                      <th class="row-name text-center" width="80px">
                        @if( $round->isFinal != true )
                         {{$couplesNo[$pos]}} =&gt; {{$round->votesRequired}}
                        @endif
                      </th>
                      <th class="row-name" colspan="20">
                          {{$round->roundName}} {{$round->categoryName}} {{$round->className}} {{$round->styleName}}
                      </th>
                    </tr>
                    <?php $name = $round->roundName.$round->categoryName.$round->className.$round->styleName;?>
                  @endif
                  @foreach($couples[$pos] as $index => $group)
                  <tr>
                    <th class="row-name" width="80px">
                      <div class="text-center">
                      @if( count($couples[$pos]) > 1 )
                        {{$danceNames[$pos]}} - {{$index+1}}
                      @else
                        {{$danceNames[$pos]}}
                      @endif
                      </div>
                    </th>
                    @foreach($group as $couple)
                      <td class="row-number" width="45px">
                      <div class="text-center">
                        {{$couple->number}}
                      </div>
                      </td>
                    @endforeach
                    @if( (7 - count($group)) > 0 )
                      @for($i = 7 - count($group);$i != 0; $i--)
                      <td class="row-number" width="45px"></td>
                      @endfor
                    @endif
                  </tr>
                  <tr>
                    <th width="80px">
                      <div class="text-center">
                        @if( $round->isFinal != true )
                           X =&gt;
                        @else
                          Miejsce
                        @endif
                      </div>
                    </th>
                    @foreach($group as $couple)
                      <td> </td>
                    @endforeach
                    @if( (7 - count($group)) > 0 )
                      @for($i = 7 - count($group);$i != 0; $i--)
                      <td class="row-number" width="45px"></td>
                      @endfor
                    @endif
                  </tr>
                  @endforeach
                @endif
              </tbody>
            </table>
            </div>
          @endif
          <?php $pos = $pos+1; ?>
        @endforeach
      </div>
   </div>
   <!-- /#page-wrapper -->
@stop

@section('customScripts')
   {!! HTML::script('js/jquery-ui.min.js') !!}
   <script>
   </script>
@stop