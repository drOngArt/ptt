function checkResults (){
    location.reload();
     $('html, body').scrollTop(0);

    $(window).on('load', function() {
    setTimeout(function(){
        $('html, body').scrollTop(0);
    }, 0);
 });
}

$( document ).ready(function() {   
   setInterval(checkResults, parseInt(wallRefreshTimer));    
});

