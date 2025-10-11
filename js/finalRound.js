var selectedCouple = null;
var submitButton = $("#submitRoundButton");
var votesReceived = 0;

$("#couples :button").click(function () {
    var coupleNumber = $(this).attr('id');
    if(selectedCouple != null)
        var selectedCoupleNumber = selectedCouple.attr('id');
    else
        var selectedCoupleNumber = -1;
    if(selectedCoupleNumber != coupleNumber){
        if(selectedCouple != null) {
            selectCouple(selectedCouple, false);
        }
        selectCouple($(this), true);
        selectedCouple = $(this);
    }
    else{
        selectCouple($(this), false);
        selectedCouple = null;
    }
});

function selectCouple(couple, select){
    if(select){
        couple.removeClass('btn-couple-normal');
        couple.removeClass('btn-final-couple-selected');
        couple.addClass('btn-couple-highlighted');
    }
    else{
        couple.removeClass('btn-couple-highlighted');
        couple.addClass('btn-couple-normal');
        if(selectedCouple.find("#couplePlace").html() != ""){
            selectedCouple.addClass('btn-final-couple-selected');
        }
    }
}

function changeSubmitButton(value){
    submitButton.text(value);
}

function activatePlaceButton(number){
    $("#voteButtons").find("#"+number).attr('disabled', false);
}

$("#voteButtons :button").each(function(){
    var width = 100/votesRequired;
    $(this).css("width", Math.floor(width) + "%");
});

$("#voteButtons :button").click(function (){
    var placeNumber = $(this).attr('id');
    if(selectedCouple != null){
        var previousPlace = null;
        if(selectedCouple.find("#couplePlace").html() != ""){
            previousPlace = selectedCouple.find("#couplePlace").html();
        }
        selectedCouple.find("#couplePlace").html(placeNumber);
        selectedCouple.addClass('btn-final-couple-selected');
        selectCouple(selectedCouple, false);
        $(this).attr('disabled', true);
        if(previousPlace != null){
            activatePlaceButton(previousPlace);
        }
        else{
            votesReceived += 1;
        }
        selectedCouple = null;
        if(votesReceived == votesRequired){
            submitButton.removeClass("btn-danger");
            submitButton.addClass("btn-success");
        }
    }
});

submitButton.click(function () {
    var couples = {};
    $(".coupleButton").each(function(){
        var vote = {};
        var coupleNumber = $(this).attr('id');
        vote['judgeSign'] = judgeSign;
        vote['note'] = $(this).find("#couplePlace").html();
        vote['rmark'] = false;
        couples[coupleNumber] = vote;
    });

    if(votesReceived == votesRequired){
        changeSubmitButton("Czekaj...");
        $.ajax({
            url: "",
            type: 'POST',
            data: {'couples': couples, 'roundId': roundId, 'danceSign': danceSign, 'judgeSign': judgeSign}
        }).done(function () {
            changeSubmitButton("Zapisano");
        });
    }
    else{
        alert("Nie wybrano miejsc dla wszystkich par");
    }
});