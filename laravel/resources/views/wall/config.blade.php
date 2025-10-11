@extends('wall.master')

@section('title')
    WALL - Konfiguracja
@stop

@section('content')

   <div id="page-wrapper">
      {!! Form::open(array('method' => 'get','action' => array('Wall\DashboardController@showDashboard'))) !!}
         <div class="row">
            <div class="col-lg-12">
               <h1 class="page-header">Aktualna konfiguracja
               <div class="pull-right">
                  <nav id="navbar-darkgreen" class="navbar navbar-default">
                     <div class="container-fluid">
                        <ul class="nav navbar-nav">
                           <li>{!! Form::select('colorSet', $colorSet, '', ['class' => 'btn btn-success button-menu'] )!!}</button></li>
                           <li>{!! Form::select('divideFactor', $divideFactor, '', ['class' => 'btn btn-info button-menu'] ) !!}</button></li>
                           <li>{!! Form::submit('Zatwierdź', array('id'=>'submitButton1','class' => 'btn btn-primary button-menu')) !!}</li>
                        </ul>
                     </div>
                  </nav>
               </div>
               </h1>
            </div>
         </div>
         <!-- /.row -->
         <div class="row">
            <div class="col-lg-12">
               <div style="border: 2px solid orange;">
               </div>
            </div>
         </div>

      {!! Form::close() !!}
   </div>
   <!-- /#page-wrapper -->
      <div id="page-wrapper-left" style="position: absolute; top: 210px; left: 2%;  min-height: 200px;">
      <div class="row col-lg-12">
            <h1 class="page-header">PROGRAM&nbsp; TURNIEJU</h1>
      </div>
        <!-- /.row -->
      <div class="row">

      <div class="col-lg-12"> 
        <div class="table-responsive">
            <table class="table table-striped">
               <tbody>
                  <tr>
                     <td>
                        1/2 Finału 10-11F Kombinacja 6T
                     </td>
                     <td>
                        WA VW Q
                     </td>
                  </tr>
                  <tr>
                     <td>
                        1/2 Finału 12-13E Kombinacja 8T
                     </td>
                     <td>
                        WA T WW Q 
                     </td>
                  </tr>
                </tbody>
            </table>
         </div>
      </div>

      <div id="page-wrapper-right" style="position: fixed; top: 210px; left: 38%;  min-height: 200px; text-align: left;  text-shadow: 0px 0px 0px #ddd; width: 60%;">
        <!-- ?? dance -->
         <div class="row col-lg-12">
            <h2 class="w_page-header">
               1/2 Finału 10-11F Kombinacja 6T  (<i class="fa fa-female" aria-hidden="true"></i><i class="fa fa-male" aria-hidden="true"></i> 12)
            </h2><br/>
         
            <h4 class="w_page-header-dance">&nbsp; WA VW Q &nbsp;</h4>
         </div>
            
         <div class="col-lg-12">
            <table class="table table-responsive">
               <tbody>
                     <tr>
                        <td>
                           <div class="table-couples-main">
                              Grupa&nbsp;stała&nbsp;1:
                           </div>
                        </td>
                        <td><div class="table-couples-main">
                           31, 32, 34, 36, 41, 44
                        </div>
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <div class="table-couples-main">
                              Grupa&nbsp;stała&nbsp;2:
                           </div>
                        </td>
                        <td><div class="table-couples-main">
                           33, 35, 37, 38, 42, 45
                        </div>
                        </td>
                     </tr>
               </tbody>
            </table>
         </div>       
    </div>
    
@stop 

@section('customScripts')
   {!! HTML::script('js/jquery-ui.min.js') !!}
   <script>
   var state_color = 0;
   $( document ).ready(function() {
      if( state_color == 0){
         state_color = 1;
         var page_wrapper_left_c = $("#page-wrapper-left").css("color");
         var page_wrapper_left_bc = $("#page-wrapper-left").css("background");
         var page_wrapper_right_c = $("#page-wrapper-right").css("color");
         var page_wrapper_right_bc = $("#page-wrapper-right").css("background");
         var w_page_header_c = $(".w_page-header").css("color");
         var w_page_header_border = $(".w_page-header").css("border-top");
         var w_page_header_dance_c = $(".w_page-header-dance").css("color");
         var w_page_header_dance_bc = $(".w_page-header-dance").css("background");
         var table_couples_main_c = $(".table-couples-main").css("color");
         var table_couples_main_bc = $(".table-couples-main").css("background");
         var table_striped_odd_c = $(".table-striped>tbody>tr:nth-of-type(odd)").css("color");
         var table_striped_odd_bc = $(".table-striped>tbody>tr:nth-of-type(odd)").css("background-color");
         var table_striped_even_c = $(".table-striped>tbody>tr:nth-of-type(even)").css("color");
         var table_striped_even_bc = $(".table-striped>tbody>tr:nth-of-type(even)").css("background");
         var page_wrapper_left_w = $("#page-wrapper-left").css("width");
         var page_wrapper_right_l = $("#page-wrapper-right").css("left");
         var page_wrapper_right_w = $("#page-wrapper-right").css("width");
         var page_wrapper_left_ta = $("#page-wrapper-left").css("text-align");
         var page_wrapper_left_ff = $("#page-wrapper-left").css("font-family");
         var page_wrapper_left_fs = $("#page-wrapper-left").css("font-style");
         var page_wrapper_left_tx = $("#page-wrapper-left").css("text-shadow");
         var page_wrapper_right_fs = $("#page-wrapper-right").css("font-style");
      }
   });
   
   $('select').on('change', function()
   {
      //alert( this.value );
      switch( this.value ) {
         case '1':
            $("#page-wrapper-left").css("color", "white");
            $("#page-wrapper-left").css("background", "black");
            $("#page-wrapper-right").css("color", "white");
            $("#page-wrapper-right").css("background", "black");
            $("#page-wrapper-left").css("text-align", "left");
            $("#page-wrapper-left").css("font-family", "Verdana");
            $("#page-wrapper-left").css("font-style", "normal");
            $("#page-wrapper-right").css("font-style", "normal");
            $(".w_page-header").css("color", "white");
            $(".w_page-header").css("border-top", "4px solid white");
            $(".w_page-header-dance").css("color", "white");
            $(".w_page-header-dance").css("background", "black");
            $(".table-couples-main").css("color", "white");
            $(".table-couples-main").css("background", "black");
            $(".table-striped>tbody>tr:nth-of-type(odd)").css("color", "white");
            $(".table-striped>tbody>tr:nth-of-type(odd)").css("background-color", "black");
            $(".table-striped>tbody>tr:nth-of-type(even)").css("color", "black");
            $(".table-striped>tbody>tr:nth-of-type(even)").css("background", "white");
            break;
         case '2':
            $("#page-wrapper-left").css("color", "black");
            $("#page-wrapper-left").css("background", "white");
            $("#page-wrapper-right").css("color", "black");
            $("#page-wrapper-right").css("background", "white");
            $("#page-wrapper-left").css("text-align", "left");
            $("#page-wrapper-left").css("font-family", "Verdana");
            $("#page-wrapper-left").css("font-style", "normal");
            $("#page-wrapper-right").css("font-style", "normal");
            $(".w_page-header").css("color", "black");
            $(".w_page-header").css("border-top", "4px solid black");
            $(".w_page-header-dance").css("color", "black");
            $(".w_page-header-dance").css("background", "white");
            $(".table-couples-main").css("color", "black");
            $(".table-couples-main").css("background", "white");
            $(".table-striped>tbody>tr:nth-of-type(odd)").css("color", "black");
            $(".table-striped>tbody>tr:nth-of-type(odd)").css("background-color", "white");
            $(".table-striped>tbody>tr:nth-of-type(even)").css("color", "white");
            $(".table-striped>tbody>tr:nth-of-type(even)").css("background", "black");
            break;
         case '3':
            $("#page-wrapper-left").css("color", "#FFFCa0");
            $("#page-wrapper-left").css("background", "darkblue");
            $("#page-wrapper-right").css("color", "black");
            $("#page-wrapper-right").css("background", "#FFFC00");
            $("#page-wrapper-left").css("text-shadow", "6px 8px 6px #ddd" );
            $("#page-wrapper-left").css("-webkit-font-smoothing", "antialiased");
            $("#page-wrapper-left").css("-moz-osx-font-smoothing", "grayscale");
            $("#page-wrapper-left").css("text-align", "left");
            $("#page-wrapper-left").css("font-family", "Verdana");
            $("#page-wrapper-left").css("font-style", "normal");
            $("#page-wrapper-right").css("font-style", "normal");
            $("#page-wrapper-right").css("-webkit-font-smoothing", "antialiased");
            $("#page-wrapper-right").css("-moz-osx-font-smoothing", "grayscale");
            $(".w_page-header").css("color", "darkblue");
            $(".w_page-header").css("border-top", "4px solid darkblue");
            $(".w_page-header-dance").css("color", "#ffffe0");
            $(".w_page-header-dance").css("background", "linear-gradient(to right, darkblue, blue, black, gold )");
            $(".table-couples-main").css("color", "#ffffe0");
            $(".table-couples-main").css("background", "linear-gradient(to right, #333, blue, #111 )");
            $(".table-striped>tbody>tr:nth-of-type(odd)").css("color", "darkblue");
            $(".table-striped>tbody>tr:nth-of-type(odd)").css("background-color", "#eee");
            $(".table-striped>tbody>tr:nth-of-type(even)").css("color", "#eee");
            $(".table-striped>tbody>tr:nth-of-type(even)").css("background", "darkblue");
            break;
         case '4':
            $("#page-wrapper-left").css("color", "#FCDFFF");
            $("#page-wrapper-left").css("background", "#7F525D");
            $("#page-wrapper-right").css("color", "#B87333");
            $("#page-wrapper-right").css("background", "#FDD017");
            $("#page-wrapper-left").css("text-align", "left");
            $("#page-wrapper-left").css("font-family", "Verdana");
            $("#page-wrapper-left").css("font-style", "normal");
            $("#page-wrapper-right").css("font-style", "normal");
            $(".w_page-header").css("color", "#7F5217");
            $(".w_page-header-dance").css("color", "#ffffe0");
            $(".w_page-header-dance").css("background", "linear-gradient(to right, #7F5217, goldenrod, #E56717 )");
            $(".table-couples-main").css("color", "#ffffe0");
            $(".table-couples-main").css("background", "linear-gradient(to right, #7F5217, #E56717, #FFD801 )");
            $(".table-striped>tbody>tr:nth-of-type(odd)").css("color", "#7D0552");
            $(".table-striped>tbody>tr:nth-of-type(odd)").css("background-color", "#FDD7E4");
            $(".table-striped>tbody>tr:nth-of-type(even)").css("color", "#FDD7E4");
            $(".table-striped>tbody>tr:nth-of-type(even)").css("background", "#7D0552");
            break;
         case '5':
            $("#page-wrapper-left").css("width", "44.7%");
            $("#page-wrapper-left").css("color", "#FFFC00");
            $("#page-wrapper-left").css("background", "darkblue");
            $("#page-wrapper-left").css("text-align", "center");
            $("#page-wrapper-left").css("font-family", "Arial");
            $("#page-wrapper-left").css("font-style", "bold");
            $("#page-wrapper-left").css("text-shadow", "0px 0px 0px" );
            $("#page-wrapper-left").css("-webkit-font-smoothing", "none");
            $("#page-wrapper-left").css("-moz-osx-font-smoothing", "none");
            $("#page-wrapper-right").css("left", "45%");
            $("#page-wrapper-right").css("width", "54.7%");
            $("#page-wrapper-right").css("color", "black");
            $("#page-wrapper-right").css("background", "#FFFC00");
            $("#page-wrapper-right").css("-webkit-font-smoothing", "none");
            $("#page-wrapper-right").css("-moz-osx-font-smoothing", "none");
            $(".w_page-header").css("color", "darkblue");
            $(".w_page-header").css("font-family", "Arial");
            $(".w_page-header").css("margin", "2px 0 0px");
            $(".w_page-header-dance").css("color", "darkblue");
            $(".w_page-header-dance").css("font-family", "Arial");
            $(".w_page-header-dance").css("font-style", "normal");
            $(".w_page-header-dance").css("background", "#FFFC00");
            $(".table-couples-main").css("color", "#ffffff");
            $(".table-couples-main").css("background", "darkblue");
            $(".table-striped>tbody>tr:nth-of-type(odd)").css("color", "darkblue");
            $(".table-striped>tbody>tr:nth-of-type(odd)").css("background-color", "white");
            $(".table-striped>tbody>tr:nth-of-type(even)").css("color", "#EEE");
            $(".table-striped>tbody>tr:nth-of-type(even)").css("background", "darkblue");
            $(".table-striped>tbody>tr:nth-of-type(even)").css("text-shadow", "0px 0px 0px #eee");
            break;

         case '50':
            $("#page-wrapper-left").css("width", "47.7%");
            $("#page-wrapper-right").css("left", "50%");
            $("#page-wrapper-right").css("width", "49.7%");
            break;
         case '45':
            $("#page-wrapper-left").css("width", "42.7%");
            $("#page-wrapper-right").css("left", "45%");
            $("#page-wrapper-right").css("width", "54.7%");
            break;
         case '40':
            $("#page-wrapper-left").css("width", "37.7%");
            $("#page-wrapper-right").css("left", "40%");
            $("#page-wrapper-right").css("width", "59.7%");
            break;
         case '35':
            $("#page-wrapper-left").css("width", "33.7%");
            $("#page-wrapper-right").css("left", "36%");
            $("#page-wrapper-right").css("width", "63.7%");
         default:
            break;
            //alert( this.value );
      }
      //alert( this.value );
      //location.reload();
   });
   
      /*var color = document.getElementById('submitButton1');
      //$(submit1).prop('disabled', true);
      submit1.classList.add('btn-primary');

      $(function() {  
         var removeIntent = false;
         $( 'td' )
            .each(function(){
               $(this).css('width', $(this).width() +'px');
         });
         ui.item.css('background-color', '#F2F5A9');
         ui.item.css('border', '2px solid #428bca');
         ui.item.css('border-radius','8px');
         $(submit1).prop('disabled', false);
         submit1.classList.add('btn-danger');
         submit1.classList.remove('btn-primary');
         $(save_as).prop('disabled', true);

      });*/
   </script>
@stop
