@extends('wall.master')

@section('title')
    Program Turnieju
@stop



@section('content')

	<?php $lastDescription = null; ?>
	<div id="page-wrapper-left">
		<div class="row col-lg-12">
            <h1 class="page-header">PATRIOTYCZNY</br>PROGRAM&nbsp TURNIEJU</h1>
        </div>
        <!-- /.row -->
		@include('wall.scheduleTable')
	</div>
	
	
	<div id="page-wrapper-right">
        <!-- ?? dance -->
		@if($rounds == null )
			<div class="row col-lg-12">
				<h3 class="w_page-header">...trwa przygotowanie danych turnieju ... :))</h3>
			</div>
		@else
			<?php $pos = 0; ?>
			@foreach($rounds as $round)
			<div class="row col-lg-12">
				@if( $roundDescriptions[$pos] != null && $lastDescription != $roundDescriptions[$pos] )
					<h2 class="w_page-header">
						@if($roundAlternativeDescriptions[$pos] != "")
							{{$roundAlternativeDescriptions[$pos]}}
                        @else
							{{$roundDescriptions[$pos]}}
                        @endif
                        @if( $couplesNo[$pos] )
                            (<i class="fa fa-female" style="color: white;" aria-hidden="true"></i><i class="fa fa-male" style="color: red; background-color: white;" aria-hidden="true"></i>
                            {{$couplesNo[$pos]}})
                        @endif                    
					</h2></br>
					<?php $lastDescription = $roundDescriptions[$pos]; ?>
				@endif
				@if($round != null)
					<h4 class="w_page-header-dance">&nbsp{{$danceNames[$pos]}}&nbsp</h3>
				@endif
			</div>
				
			<div class="col-lg-12">
				@if($round != null)
				<table class="table table-responsive">
					<tbody>
						@if($couples[$pos] != null)
							@foreach($couples[$pos] as $index => $group)
							<tr>
								<td>
								<div class="table-couples-main">
								@if(count($couples[$pos]) > 1)
									Grupa&nbsp{{$index+1}}:
								@else
									Pary:
								@endif                        
								</div></td><td><div class="table-couples-main">
                                    <?php $idx = 0; ?>
									@foreach($group as $couple)
										{{$couple->number}}
                                        <?php $idx += 1; ?>
                  						@if( $idx < count($group) )
                                           ,                                            
										@endif
									@endforeach
								</div>
								</td>
							</tr>
							@endforeach
						@endif
					</tbody>
				</table>
				@endif
				<?php $pos = $pos + 1; ?>
			</div> 
			@endforeach
		@endif	
    </div>
    
    <!-- /#page-wrapper -->
@stop


@section('customScripts')
    {!! HTML::script('js/wallRound.js') !!}
    <script>
        var wallRefreshTimer = "{{Config::get('ptt.wallRefreshTimer')}}";
        @if($rounds[0] != null)
            var roundName = "{{$roundDescriptions[0]}}" + ", " + "{{$danceNames[0]}}";
        @endif
    </script>
@stop 

