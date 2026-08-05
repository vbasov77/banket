const escapeHtml = (unsafe) => {
    return unsafe.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
};

let arrayId = [];

// Формируем массив по data
const attributes = document.getElementsByClassName('messageBlock');
for (const attribute of attributes) {

    if (attribute.getAttribute('data-notified') == 0) {
        arrayId.push(attribute.getAttribute('id'))
    }
}

setInterval(checkNewMsg, 3000);
setInterval(notified, 5000);

function notified() {
    if (arrayId.length) {
        data = {
            "to_user_id": to_user_id,
            "from_user_id": from_user_id,
            "obj_id": obj_id,
            "array_id": arrayId,
        };

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/notified',
            type: 'post',
            data: data,
            dataType: 'json',
            success: function (res) {
                if (res.length) {
                    const checkBackgroun = (id) => {
                        let element = document.getElementById(id);
                        element.style.backgroundColor = '#f5f5f5';
                    };
                    for (let j = 0; j < res.length; j++) {
                        if (res[j].to_user_id = to_user_id) {
                            checkBackgroun(res[j].id);
                        }
                    }
                    arrayId = [];
                }
            }


        });
    }

}

function checkNewMsg() {
    data = {
        "to_user_id": to_user_id,
        "from_user_id": from_user_id,
        "obj_id": obj_id,
    };
    $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/check_message',
            type: 'post',
            data: data,
            dataType: 'json',
            success: function (res) {
                if (res.bool === true) {
                    for (let i = 0; i < res.messages.length; i++) { // выведет 0, затем 1, затем 2
                        if (res.messages[i].to_user_id = to_user_id) {
                            $(`<li class="sent"> <div class="myClass">
<div id="` + res.messages[i].id + `" data-id="` + res.messages[i].id + `" style="font-size: 17px; background-color: #f5f5f5; " class="messageBlock">
                ${res.messages[i].body}<br>
                <small  style="font-size: 10px; opacity: 0.6" class="mb-0 text-left">${res.messages[i].created_at.toLocaleString()}</small >
                </div></div></li>`).appendTo($('.messages ul'));
                            $('.message-input .emoji-wysiwyg-editor').html('');
                            $('.messages').animate({scrollTop: $('.messages ul').height()}, "fast");
                        }
                    }
                }
            }
        }
    );
}

$('.messages').animate({scrollTop: $('.messages ul').height()}, "fast");

function newMessage() {
    var message = escapeHtml($('.message-input input').val());
    data = {
        "to_user_id": to_user_id,
        "from_user_id": from_user_id,
        "obj_id": obj_id,
        "body": message,
    };
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: '/add_message',
        type: 'post',
        data: data,
        dataType: 'json',
        success: function (res) {
            arrayId.push(res.id);
            let date = new Date(res.date);
            if ($.trim(message) == '') {
                message = $('.message-input .emoji-wysiwyg-editor').html();
                if ($.trim(message) == '') {
                    return false;
                }
            }
            $(`<li class="sent"> <div class="myClass">
<div id="` + res.id + `" data-id="` + res.id + `" style="float: right; font-size: 17px; background-color: #dad6f5; " class="messageBlock">
<div class="round-popup">
<button data-id="` + res.id + `" type="button" class="close"
                                            aria-label="Close"><span aria-hidden="true">&times;</span></button> </div>
${res.body}<br>
                <small  style="font-size: 10px" class="mb-0 text-left">${date.toLocaleString()}</small >
                </div></div></li>`).appendTo($('.messages ul'));
            $('.message-input input').val('');
            $('.message-input .emoji-wysiwyg-editor').html('');
            $('.messages').animate({scrollTop: $('.messages ul').height()}, "fast");
        }
    });


};

$('.submit').click(function () {
    newMessage();
});

// отправить сообщение по Enter
$("#framechat .content .message-input").keyup(function (event) {
    if (event.keyCode === 13) {
        $(".submit").click();
    }
});


// Удаление сообщения
$('body').on('click', '.close', function () {
    if (!confirm('Подтвердите удаление')) return false;
    let $this = $(this);
    data = {'id': $this.data('id')};
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: '/delete_message',
        type: 'get',
        data: data,
        dataType: 'json',

        success: function (res) {
            const removeItems = (number) => {
                let elements = document.querySelectorAll(`div[data-id="${number}"]`);
                elements.forEach((e) => {
                    e.remove()
                });
            };
            if (res.answer === 'ok') {
                removeItems($this.data('id'));
            }
        }
    });

});
