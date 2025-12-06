function checkResults (){
    var urlName = baseURI + "/admin/round/roundResults/" + roundIdFromDB;
    console.log(urlName);
    $.ajax({
        url: urlName,
        type: "GET",
        data: {}
    }).done(function (result) {
        if(result['error'] == true){
            console.log("error"); //TODO
        }
        else if(result['newRound'] == "true" && roundIdFromDB == 0){
            //console.log("new Round");
            location.reload();
        }
        else{
            //console.log("refresh judges");
            for(var i = 0; i < result['judges'].length; i++){
                var judgeSign = result['judges'][i]['sign'];
                var completedDiv = $("#completed"+judgeSign);
                var completedTextDiv = $("#completedText"+judgeSign);
                if(result['judges'][i]['completed'] == true){
                    completedDiv.removeClass('d-none');

                    var votes = result['judges'][i]['votes'];
                    var numbers = [];
                    for(var number in votes){
                        numbers.push(number);
                    }
                    var x = 0;
                    var y = 0;
                    var votesText = "";
                    var bestInRow = 8;
                    for(var inRow = bestInRow; inRow > 4; inRow--){
                        if((numbers.length % inRow) == 0) {
                            bestInRow = inRow;
                            break;
                        }
                        if((numbers.length % inRow) > (numbers.length % bestInRow))
                            bestInRow = inRow; 
                    }

                    for(var j = 0; j < numbers.length; ) {
                        votesText += "<div><table class='table table-striped table-bordered'><tr>";
                        for(; x < numbers.length;) {
                            votesText += "<td><div class='votes-numbers'>" + numbers[x] + "</div></td>";
                            j++;
                            if((++x % bestInRow) == 0)
                                break;
                        }
                        votesText += "</tr><tr>";
                        for(; y < numbers.length;) {
                            votesText += "<td><div class='votes-notes'>" + votes[numbers[y]].note;
                            if(votes[numbers[y]].rmark)
                                votesText += " (R)";
                            votesText += "</div></td>";
                            if((++y % bestInRow) == 0)
                                break;
                        }
                        votesText += "</tr></table></div>";
                    }
                    completedTextDiv.html(votesText);
                }
                else{
                    completedDiv.addClass('d-none');
                    completedTextDiv.html("");
                }
                var statusDiv = $("#status"+judgeSign);
                var judgeSignIcon = '<button class="btn-circle">'+judgeSign+'</button> ';
                var judgePass = '<span class="font-14pt"> ';
                if(result['judges'][i]['without_pass'] == true)
                   judgePass = '<a class="btn btn-dorange" role="button" href="password/' + result['judges'][i]['id'] + '/false"> USTAW HASŁO </a><span class="font-14pt"> ';
                if(result['judges'][i]['status'] == true){
                    var batteryIcon = '<i class="fa fa-battery-full fa-rotate-270"></i>';
                    if(result['judges'][i]['batteryLevel'] < 20)
                        batteryIcon = '<i class="fa fa-battery-empty fa-rotate-270"></i>';
                    else if(result['judges'][i]['batteryLevel'] < 40)
                        batteryIcon = '<i class="fa fa-battery-quarter fa-rotate-270"></i>';
                    else if(result['judges'][i]['batteryLevel'] < 60)
                        batteryIcon = '<i class="fa fa-battery-half fa-rotate-270"></i>';
                    else if(result['judges'][i]['batteryLevel'] < 80)
                        batteryIcon = '<i class="fa fa-battery-three-quarters fa-rotate-270"></i>';

                    statusDiv.html(judgeSignIcon + batteryIcon + judgePass + result['judges'][i]['lastName'] + ' ' + result['judges'][i]['firstName'] + '</span>');
                }
                else{
                    statusDiv.html(judgeSignIcon + judgePass + result['judges'][i]['lastName'] + ' ' + result['judges'][i]['firstName'] + '</span>');
                }
            }
        }
    });
}

$( document ).ready(function() {   
    checkResults();
    setInterval(checkResults, parseInt(adminRefreshTimer));
});

$('.confirmation').on('click', function (e) {
    e.preventDefault();
    var link = $(this).attr("href");
    bootbox.dialog({
        title: "Zakończyć taniec?",
        message: roundName,
        buttons: {
            cancel: {
                label: "Anuluj",
                className: "pull-left btn btn-lg btn-warning button-menu",
                callback: function (result) {
                }
            },
            success: {
                label: "OK",
                className: "pull-right btn btn-lg btn-primary button-menu",
                callback: function (result) {
                    if (result) {
                        document.location.href = link;
                    }
                }
            }
        }
    });
});

$('#judgeUndoButton').on('click', function (e) {
    e.preventDefault();
    var form = $('#judgeUndoForm');
    bootbox.dialog({
        title: "Powtórzyć taniec?",
        message: roundName,
        buttons: {
            cancel: {
                label: "Anuluj",
                className: "btn-default",
                callback: function (result) {
                }
            },
            success: {
                label: "OK",
                className: "btn-primary",
                callback: function (result) {
                    if (result) {
                        form.submit();
                    }
                }
            }
        }
    });
});

//For dismissing bootbox alerts on background click
//reference https://github.com/makeusabrew/bootbox/issues/210
$(document).on('click', '.bootbox', function(){
    var classname = event.target.className;

    if(classname && !$('.' + classname).parents('.modal-dialog').length)
        bootbox.hideAll();
});

$('.judgeResultsButton').on('click', function () {
    $('#judgeResultsSign').attr('value', $(this).data('judgeSign'));
    $('#judgeResultsSign').html("Sędzia " + $(this).data('judgeSign') + " | " + $(this).data('judgeName'));
    $('#judgeToUndo').attr('value', $(this).data('judgeSign'));
    $('#judgeResultsVotes').html($('#completedText'+$(this).data('judgeSign')).html());
});
