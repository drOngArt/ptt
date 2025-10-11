var submitButton = $("#submitRoundButton");
var votesReceived = 0;
var placesSelected = [];
var couplesSelected = [];
var rMarkCheckbox  =$("#rMark");

$("#couples :button").each(function () {
    $(this).css("width", "100%");
    $(this).css("font-size", judgeFinalRoundFontSize);
});

$("#couples :button").click(function () {
    var placeNumber = $(this).html();
    var coupleNumber = $(this).attr('id');
    if($.inArray(placeNumber, placesSelected) == -1 && $.inArray(coupleNumber, couplesSelected) == -1){
        placesSelected.push(placeNumber);
        couplesSelected.push(coupleNumber);
        $(this).removeClass("btn-default");

        if(!rMarkCheckbox.is(':checked')) {
            $(this).addClass("btn-success");
        }
        else{
            $(this).addClass("btn-danger");
        }

        votesReceived += 1;
    }
    else if($(this).hasClass("btn-success") || $(this).hasClass("btn-danger")){ //same couple, same place
        couplesSelected = _.without(couplesSelected, coupleNumber);
        placesSelected = _.without(placesSelected, placeNumber);
        if($(this).hasClass("btn-success"))
            $(this).removeClass("btn-success");
        else if($(this).hasClass("btn-danger"))
            $(this).removeClass("btn-danger");
        $(this).addClass("btn-default");
        votesReceived -= 1;
    }
    if(votesReceived == votesRequired){
        submitButton.removeClass("btn-danger");
        submitButton.addClass("btn-success");
    }
    else{
        if(submitButton.hasClass("btn-success")){
            submitButton.removeClass("btn-success");
            submitButton.addClass("btn-danger");
        }
    }
});

function changeSubmitButton(value){
    submitButton.text(value);
}

submitButton.click(function () {
    var couples = {};

    $("#couples :button").each(function(){
        var vote = {};
        var couplesNumber = $(this).attr('id');
        var place = $(this).html();
        if($(this).hasClass("btn-success")) {
            vote['judgeSign'] = judgeSign;
            vote['note'] = place;
            vote['rmark'] = false;
            couples[couplesNumber] = vote;
        }
        else if($(this).hasClass("btn-danger")){
            vote['judgeSign'] = judgeSign;
            vote['note'] = place;
            vote['rmark'] = true;
            couples[couplesNumber] = vote;
        }
    });

    console.log(couples);

    if(votesReceived == votesRequired){
        changeSubmitButton("Czekaj...");
        $.ajax({
            url: "",
            type: 'POST',
            data: {'couples': couples, 'roundId': roundId, 'danceSign': danceSign, 'judgeSign': judgeSign},
            error: function(){
                changeSubmitButton("Network Error");
            },
            success: function(result){
                console.log(result);
                if(result['error'] == "true")
                    changeSubmitButton("Error");
                else
                    changeSubmitButton("Zapisano")
            },
            timeout: 10000
        });
    }
    else{
        alert("Nie wybrano miejsc dla wszystkich par");
    }
});