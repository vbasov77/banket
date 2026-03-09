$(window).keyup(function (e) {
    let target = $('.checkbox-btn2 input:focus');
    if (e.keyCode == 9 && $(target).length) {
        $(target).parent().addClass('focused');
    }
});

$('.checkbox-btn2 input').focusout(function () {
    $(this).parent().removeClass('focused');
});


document.querySelector("#address_subj").onclick = function () {
    let countId = document.getElementById('count');
    if (rooms.checked) {
        countId.value = "";
        countId.disabled = true;
    } else{
        countId.disabled = false;
    }
};