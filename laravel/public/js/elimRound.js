var couplesSelectedNumber = 0;
var couplesSelected = [];
var rMarkedCouples = [];
var couplesSelectedDiv = $("#couplesSelected");
var submitButton = $("#submitRoundButton");
var rMarkCheckbox = $("#rMarkCheckbox");
var radioX = $("#radioX")

$('#groupsTabs a:first').tab('show') // Select first tab


function confirmExit()
{
    if(checkIfSelectedAtLeastOne() == true)
        return "Zostały zaznaczone pary";
}

function checkIfSelectedAtLeastOne(){
    var isMarked = false;
    $(".coupleButton").each(function(){
        if($(this).hasClass("couple-color1") || $(this).hasClass("couple-color2") || $(this).hasClass("btn-danger") || $(this).hasClass("btn-success"))
            isMarked = true;
    });
    return isMarked;
}

$("#couples :button").click(function () {
    var coupleNumber = $(this).attr('id');
    /*if(!rMarkCheckbox.is(':checked')){
        if($.inArray(coupleNumber, couplesSelected) != -1){
            couplesSelected = _.without(couplesSelected, coupleNumber);
            couplesSelectedNumber -= 1;
            updateCouplesSelected();
        }
        else{
            couplesSelected.push(coupleNumber);
            couplesSelectedNumber += 1;
            updateCouplesSelected();
        }
        changeSubmitButton("Wyślij");
    }
    else{
        if($.inArray(coupleNumber, rMarkedCouples) != -1){
            rMarkedCouples = _.without(rMarkedCouples, coupleNumber);
        }
        else{
            rMarkedCouples.push(coupleNumber);
        }
    }
    */
    if($(this).hasClass("couple-color1") || $(this).hasClass("couple-color2")){
        markCouple($(this), "selected");
    }
    else if($(this).hasClass("btn-danger") || $(this).hasClass("btn-success")){
        markCouple($(this), "default");
    }
    else{
        switch($('input[name=color]:checked').val()){
            case "X":
                markCouple($(this), "selected");
                break;
            case "color1":
                markCouple($(this), "color1");
                break;
            case "color2":
                markCouple($(this), "color2");
                break;
            case "rMark":
                markCouple($(this), "rMark");
                break;
        }
    }/*
    else if(rMarkCheckbox.is(':checked')){
        markCouple($(this), "rMark");
    }
    else{
        markCouple($(this), "selected");
    }*/
    /*
    if ($(this).hasClass("btn-success")) {
        $(this).removeClass("btn-success");
        couplesSelectedNumber -= 1;
        updateCouplesSelected();
    }
    else {
        $(this).addClass("btn-success");
        couplesSelectedNumber += 1;
        updateCouplesSelected();
    }*/
    //redrawButtons();
    countSelectedCouples();
    updateCouplesSelected();
    return false;
});

function countSelectedCouples(){
    var temp = 0;
    $("#couples :button").each(function(){
        if($(this).hasClass("btn-success")){
            temp += 1;
        }
    });
    couplesSelectedNumber = temp;
}

function redrawButtons(){
    $(".coupleButton").each(function(){
        var coupleNumber = $(this).attr('id');
        $(this).removeClass();
        $(this).addClass("btn btn-default btn-xl coupleButton");
        if(!rMarkCheckbox.is(':checked')){
            if($.inArray(coupleNumber, couplesSelected) != -1){
                $(this).addClass("btn-success");
            }
            else{
                $(this).addClass("btn-default");
            }
        }
        else{
            if($.inArray(coupleNumber, rMarkedCouples) != -1){
                $(this).addClass("btn-danger");
            }
            else{
                $(this).addClass("btn-default");
            }
        }
    });
}

function markCouple(couple, color){
    couple.removeClass();
    couple.addClass("btn");
    couple.addClass("btn-xl");
    couple.addClass("coupleButton");

    switch(color) {
        case "default":
            couple.addClass("btn-default");
            break;
        case "selected":
            couple.addClass("btn-success");
            break;
        case "color1":
            couple.addClass("couple-color1");
            break;
        case "color2":
            couple.addClass("couple-color2");
            break;
        case "rMark":
            couple.addClass("btn-danger");
            break;
    }
}

rMarkCheckbox.click(function(){
    if(!rMarkCheckbox.is(':checked')){
        //$("#tab-content").css('background', 'transparent');
        $(".coupleButton").each(function(){
            $(this).attr('style', 'border-color: #2e3436 !important');
        });
    }
    else{
        //$("#tab-content").css("background-color","#FF9999");
        $(".coupleButton").each(function(){
            $(this).attr('style', 'border-color: red !important');
        });
    }
    //redrawButtons();
});

function updateCouplesSelected(){
    couplesSelectedDiv.text("Typy: " + couplesSelectedNumber + "/" + votesRequired);
    if(couplesSelectedNumber != votesRequired){
        if(!couplesSelectedDiv.hasClass("alert-danger")) {
            couplesSelectedDiv.removeClass();
            couplesSelectedDiv.addClass("alert alert-danger");

            submitButton.removeClass("btn-info").removeClass("btn-success");
            submitButton.addClass("btn-danger");
        }
    }
    else{
        couplesSelectedDiv.removeClass();
        couplesSelectedDiv.addClass("alert alert-success");

        submitButton.removeClass("btn-info").removeClass("btn-danger");
        submitButton.addClass("btn-success");
    }
}

function changeSubmitButton(value){
    submitButton.text(value);
}

submitButton.click(function () {

    var couples = {};
    $(".coupleButton").each(function(){
        var vote = {};
        var coupleNumber = $(this).attr('id');
        vote['judgeSign'] = judgeSign;
        //vote['note'] = $.inArray(coupleNumber, couplesSelected) != -1 ? "X" : "";
        //vote['rmark'] = $.inArray(coupleNumber, rMarkedCouples) != -1 ? true : false;
        vote['note'] = $(this).hasClass("btn-success") ? "X" : "";
        vote['rmark'] = $(this).hasClass("btn-danger") ? true : false;
        couples[coupleNumber] = vote;
    });

    if(couplesSelectedNumber == votesRequired){
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
        alert("Nieprawidłowa liczba wybranych par");
    }
});
