$( document ).ready(function() {
   
   if( typeof(color) != 'undefined' ){
      switch( color ) {
         case '1':
            $("#page-wrapper-left").css("color", "white");
            $("#page-wrapper-left").css("background", "black");
            $("#page-wrapper-right").css("color", "white");
            $("#page-wrapper-right").css("background", "black");
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
            $("#page-wrapper-left").css("text-shadow", "6px 8px 6px #ddd" );
            $("#page-wrapper-left").css("-webkit-font-smoothing", "antialiased");
            $("#page-wrapper-left").css("-moz-osx-font-smoothing", "grayscale");
            $("#page-wrapper-left").css("text-align", "left");
            $("#page-wrapper-left").css("font-family", "Verdana");
            $("#page-wrapper-left").css("font-style", "normal");
            $("#page-wrapper-right").css("font-style", "normal");
            $("#page-wrapper-right").css("-webkit-font-smoothing", "antialiased");
            $("#page-wrapper-right").css("-moz-osx-font-smoothing", "grayscale");
            $("#page-wrapper-right").css("color", "black");
            $("#page-wrapper-right").css("background", "#FFFC00");
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
            $(".w_page-header").css("color", "#7F5217");
            $(".w_page-header").css("border-top", "4px solid #D4A017");
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

         default:
            break;
     }
   }
   if( typeof(factor) != 'undefined' ){
      switch( factor ) {
         case '50':
            $("#page-wrapper-left").css("width", "49.7%");
            $("#page-wrapper-right").css("left", "50%");
            $("#page-wrapper-right").css("width", "49.7%");
            break;
         case '45':
            $("#page-wrapper-left").css("width", "44.7%");
            $("#page-wrapper-right").css("left", "45%");
            $("#page-wrapper-right").css("width", "54.7%");
            break;
         case '40':
            $("#page-wrapper-left").css("width", "39.7%");
            $("#page-wrapper-right").css("left", "40%");
            $("#page-wrapper-right").css("width", "59.7%");
            break;
         case '35':
            $("#page-wrapper-left").css("width", "35.7%");
            $("#page-wrapper-right").css("left", "36%");
            $("#page-wrapper-right").css("width", "63.7%");
            break;
         default:
            break;
      }
   }
});

