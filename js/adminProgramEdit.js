var orderProgramming = false;
var lastOrder = 0;
var altNamesEdit = false;

$('#altNames').on('click', function() {
    if(!altNamesEdit) {
        $('#submitButton1').prop('disabled', true);
        $('#submitButton2').prop('disabled', true);
        $('#startStop').prop('disabled', true);
        $(".alternativeInput").prop('hidden', false);
        $(".alternativeDescription").prop('hidden', true);
        $(".connectedSortable").sortable('disable');
        $(".danceCheckbox").prop('disabled', true);
        $(this).removeClass('btn-warning');
        $(this).addClass('btn-success');
        $(this).text("Zakończ zmiany");
        altNamesEdit = true;
    }    
    else {
        $('#submitButton1').prop('disabled', false);
        $('#submitButton2').prop('disabled', false);
        $('#startStop').prop('disabled', false);
        $(".alternativeInput").prop('hidden', true);
        $(".alternativeDescription").prop('hidden', false);
        $(".connectedSortable").sortable('enable');
        $(".danceCheckbox").prop('disabled', false);
        $(this).removeClass('btn-success');
        $(this).addClass('btn-warning');
        $(this).text("Zmień nazwy");
        altNamesEdit = false;

        var altNames = document.getElementsByClassName("alternativeDescription");
        var altInputs = document.getElementsByClassName("alternativeInput");
        for(var i = 0; i < altInputs.length; i++) {
            altNames[i].innerHTML = altInputs[i].value;
        }
    }
});

$('#startStop').on('click', function() {
    if(!orderProgramming) {
        $('#submitButton1').prop('disabled', true);
        $('#submitButton2').prop('disabled', true);
        $('#altNames').prop('disabled', true);
        $(".connectedSortable").sortable('disable');
        $(".danceCheckbox").prop('disabled', true);
        $(this).removeClass('btn-warning');
        $(this).addClass('btn-success');
        $(this).text("Zakończ zmiany");
        orderProgramming = true;
        lastOrder = 0;
    }
    else {
        $('#submitButton1').prop('disabled', false);
        $('#submitButton2').prop('disabled', false);
        $('#altNames').prop('disabled', false);
        $(".connectedSortable").sortable('enable');
        $(".danceCheckbox").prop('disabled', false);
        $(this).removeClass('btn-success');
        $(this).addClass('btn-warning');
        $(this).text("Zmień kolejność");
        orderProgramming = false;
    }
});

var cells = document.getElementsByTagName("tablecell");
if(cells != null) {
    for(var i = 0; i < cells.length; i++) {
        cells[i].onclick = function() {
        orderClick(this);
        };
    }
}

function orderClick(cell) {
    if(!orderProgramming)
        return;
    if(lastOrder == 0) {
        var cells = document.getElementsByTagName("tablecell");
        for(var i = 0; i < cells.length; i++) {
            cells[i].getElementsByTagName("tc-order")[0].innerHTML = "";
            var inputs = cells[i].getElementsByTagName("input");
            for(var j = 0; j < inputs.length; j++) {
                if(inputs[j].name.indexOf("order") == 0)
                    inputs[j].setAttribute("value", "");
            }
        }
    }else{ //maybe clear counter
        var cells = document.getElementsByTagName("tablecell");
        var noClear = false;
        for(var i = 0; i < cells.length; i++) {
            var inputs = cells[i].getElementsByTagName("input");
            for(var j = 0; j < inputs.length; j++) {
                if(inputs[j].name.indexOf("order") == 0)
                    if( inputs[j].getAttribute("value") > 0 ){
                       noClear = true;
                       break;
                    }
            }
        }
        if( noClear == false )
            lastOrder = 0;
    }
    lastOrder++;
    console.log(lastOrder);
    var inputs = cell.getElementsByTagName("input");
    for(var j = 0; j < inputs.length; j++) {
        if(inputs[j].name.indexOf("order") == 0) {
            //if(lastOrder > 1 && lastOrder - inputs[j].getAttribute("value") <= 1) {
            if(lastOrder > 1 && inputs[j].getAttribute("value") > 0) {
                if( lastOrder - inputs[j].getAttribute("value") <= 1 )
                   lastOrder = inputs[j].getAttribute("value")-1;
                else
                   lastOrder--;
                inputs[j].setAttribute("value", "");
                cell.getElementsByTagName("tc-order")[0].innerHTML = "";
                console.log(lastOrder);
                return;
            }
            else{
               inputs[j].setAttribute("value", lastOrder.toString());
               cell.getElementsByTagName("tc-order")[0].innerHTML = lastOrder;
               console.log(lastOrder);
               return;
            }
        }
    }
};