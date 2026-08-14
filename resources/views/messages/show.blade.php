@extends('layouts.app', ['title' => 'Мои сообщения'])

@section('content')
    {{-- Стили вынесены в отдельный блок style, но лучше перенести их в messages.css --}}
    <style>
        #framechat {
            width: 100%;
            max-width: 1000px;
            height: 92vh;
            min-height: 300px;
            max-height: 720px;
            box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            overflow: hidden;
            margin: 0 auto;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        @media screen and (max-width: 360px) {
            #framechat {
                height: 100vh;
                border-radius: 0;
            }
        }

        #framechat .content {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        /* --- Header: современный, на Flex --- */
        #framechat .content .header {
            height: 64px;
            background-color: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .header-avatar-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #e5e7eb;
        }

        .header-info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .header-name {
            font-weight: 600;
            font-size: 16px;
            color: #111827;
        }

        .header-id {
            font-size: 12px;
            color: #6b7280;
        }

        .header-action {
            display: flex;
            align-items: center;
        }

        .delete-chat-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            opacity: 0.5;
            transition: opacity 0.2s, background-color 0.2s;
            display: inline-flex;
            align-items: center;
        }

        .delete-chat-btn:hover {
            opacity: 1;
            background-color: #fee2e2;
            color: #dc2626;
        }

        .delete-chat-icon {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }

        /* --- Messages area --- */
        #framechat .content .messages {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            background-color: #f8fafc;
        }

        #framechat .content .messages ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        #framechat .content .messages ul li {
            clear: both;
            margin-bottom: 14px;
            display: block;
        }

        .messageBlock {
            padding: 10px 14px;
            border-radius: 12px;
            max-width: 75%;
            word-wrap: break-word;
            line-height: 1.4;
        }

        li.sent .messageBlock {
            float: right;
            background-color: #d3e3f3;
            border-bottom-right-radius: 2px;
            margin: 5px;
        }

        li.received .messageBlock {
            float: left;
            background-color: #d3e3f3;
            border-bottom-left-radius: 2px;
        }

        .message-time {
            display: block;
            margin-top: 4px;
            font-size: 11px;
            opacity: 0.7;
        }

        .myClass {
            position: relative; /* обязательно: чтобы popup позиционировался относительно сообщения */
            display: inline-block;
            width: 100%; /* чтобы блок занимал всю ширину сообщения */
        }

        /* Popup удаления сообщения */
        .round-popup {
            display: none;
        }

        .myClass:hover .round-popup {
            display: block;
            position: absolute;
            top: 4px;
            right: 8px;
            z-index: 10; /* чтобы был поверх всего */
        }

        .close-msg-btn {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 24px;
            height: 24px;
            text-align: center;
            line-height: 22px;
            cursor: pointer;
            font-size: 13px;
            color: #555;
        }

        /* Input area */
        #framechat .content .message-input {
            background-color: #ffffff;
            border-top: 1px solid #e5e7eb;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #framechat .content .message-input .wrap {
            flex: 1;
            display: flex;
            align-items: center;
        }

        #framechat .content .message-input input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d1d5db;
            border-radius: 20px;
            outline: none;
            font-size: 15px;
        }

        #framechat .content .message-input input:focus {
            border-color: #3b82f6;
        }

        #framechat .content .message-input button {
            background-color: #3b82f6;
            color: white;
            border: none;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        #framechat .content .message-input button:hover {
            background-color: #2563eb;
        }

        /* Scrollbar */
        #framechat .content .messages::-webkit-scrollbar {
            width: 6px;
        }

        #framechat .content .messages::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.15);
            border-radius: 3px;
        }

        .particle {
            position: fixed;
            top: 0;
            left: 0;
            pointer-events: none;
            border-radius: 50%;
            background: currentColor; /* цвет берётся из элемента */
            z-index: 9999;
        }

        .msg-unread { background-color: #fff; border: 1px solid #dad6f5; }
        .msg-read  { background-color: #f3f4f6; border: none; }
        li.sent .msg-unread { float: right; background-color: #dad6f5; margin: 5px; }
        li.sent .msg-read  { float: right; background-color: #e5e7eb; margin: 5px; }

    </style>

    <section>
        <div class="container px-4 px-lg-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    @csrf

                    <div class="mb-5 mt-4" id="framechat">
                        <div class="content">

                            {{-- Header: чистый, современный, без float --}}
                            <div class="header">
                                <div class="header-avatar-wrapper">
                                    <img src="{{ asset('icons/user.svg') }}"
                                         alt="Avatar"
                                         class="header-avatar">
                                    <div class="header-info">
                                        <span class="header-name">{{ $name }}</span>
                                    </div>
                                </div>

                                {{-- Кнопка удаления чата --}}
                                @if (!empty($messages))
                                    <div class="header-action">
                                        <button type="button"
                                                class="delete-chat-btn"
                                                onclick="return confirm('Подтвердите удаление чата?') ? window.location.href='{{ route('delete.chat', [
                                                    'to_user_id' => $toUser,
                                                    'from_user_id' => $userId
                                                ]) }}' : false;">
                                            <svg class="delete-chat-icon" viewBox="0 0 24 24" fill="none"
                                                 stroke="currentColor" stroke-width="2">
                                                <path d="M18 6L6 18M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                @endif
                            </div>

                            {{-- Область сообщений --}}
                            <div class="messages">
                                <ul>
                                    @if (count($messages) > 0)
                                        @foreach ($messages as $msg)
                                            @php
                                                $isMine = ($msg['from_user_id'] == $userId);
                                            @endphp

                                            <li class="{{ $isMine ? 'sent' : 'received' }}">
                                                <div class="myClass">
                                                    {{-- Popup удаления сообщения --}}
                                                    @if ($isMine)
                                                        <div class="round-popup">
                                                            <button type="button"
                                                                    class="close-msg-btn"
                                                                    data-id="{{ $msg['id'] }}">
                                                                &times;
                                                            </button>
                                                        </div>
                                                    @endif
                                                    <div class="messageBlock {{ $msg['status'] ? 'msg-read' : 'msg-unread' }}"
                                                         id="{{ $msg['id'] }}"
                                                         data-id="{{ $msg['id'] }}"
                                                         data-notified="{{ $msg['status'] }}">
                                                        {!! $msg['body'] !!}
                                                        <span class="message-time">{{ $msg['created_at'] }}</span>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    @else

                                    @endif
                                </ul>
                            </div>

                            {{-- Поле ввода сообщения --}}
                            <div class="message-input">
                                <div class="wrap">
                                    <input type="text"
                                           placeholder="Ваше сообщение..."
                                           id="messageInput"
                                           autocomplete="off">
                                </div>
                                <button class="submit" id="sendBtn">
                                    <img src="{{ asset('icons/chat.svg') }}"
                                         alt="send">
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        {{-- Скрипты (вынесены наружу, чтобы не ломать структуру) --}}
        <script src="{{ asset('js/jquery/3.5.1/jquery-3.5.1.js') }}"></script>
        <script>
            var to_user_id = @json($toUser);
            var from_user_id = @json($userId);
            // obj_id больше не нужен, так как мы убрали привязку к объекту
        </script>
        {{--    <script src="{{ asset('messages/js/message.js') }}"></script>--}}
        <script>
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

            $('.messages').animate({scrollTop: $('.messages ul').height()}, "fast");

            function newMessage() {
                var message = escapeHtml($('.message-input input').val());
                data = {
                    "to_user_id": to_user_id,
                    "from_user_id": from_user_id,
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
<button data-id="${res.id}" type="button" class="close-msg-btn">&times;</button>
 </div>
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
            // Удаление сообщения с эффектом взрыва
            $('body').on('click', '.close-msg-btn', function (e) {
                e.preventDefault();
                if (!confirm('Подтвердите удаление')) return;

                const $btn = $(this);
                const id = $btn.data('id');

                // Находим блок сообщения по data-id
                const messageBlock = document.querySelector(`div[data-id="${id}"]`);
                if (!messageBlock) {
                    console.warn('Сообщение не найдено в DOM');
                    return;
                }

                data = {'id': id};

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '/delete_message',
                    type: 'get',
                    data: data,
                    dataType: 'json',
                    success: function (res) {
                        if (res.answer === 'ok') {
                            // 1. Запускаем эффект взрыва на блоке сообщения
                            createParticleEffect(messageBlock);

                            // 2. Удаляем элемент через то же время, что и частицы (900 мс)
                            setTimeout(() => {
                                messageBlock.remove();
                                // Если нужно, можно прокрутить чат вниз
                                $('.messages').animate({scrollTop: $('.messages ul').height()}, 'fast');
                            }, 900);
                        } else {
                            alert('Не удалось удалить сообщение');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Ошибка удаления:', xhr.responseText);
                        alert('Ошибка при удалении сообщения');
                    }
                });
            });

            function createParticleEffect(element, forceColor = null) {
                const rect = element.getBoundingClientRect();
                const container = document.body;
                const particlesCount = 80;

                const elementColor = forceColor || window.getComputedStyle(element).color;

                for (let i = 0; i < particlesCount; i++) {
                    const particle = document.createElement('div');
                    particle.className = 'particle';
                    const size = Math.random() * 7 + 1;
                    particle.style.width = `${size}px`;
                    particle.style.height = `${size}px`;

                    const x = Math.random() * rect.width;
                    const y = Math.random() * rect.height;

                    particle.style.left = `${rect.left + x}px`;
                    particle.style.top = `${rect.top + y}px`;
                    particle.style.color = elementColor;

                    const angle = Math.random() * Math.PI * 2;
                    const speed = Math.random() * 200 + 100;
                    const distance = Math.random() * 150 + 120;

                    const endX = x + Math.cos(angle) * distance;
                    const endY = y + Math.sin(angle) * distance;

                    container.appendChild(particle);

                    particle.animate([
                        { transform: `translate(0, 0) scale(1)`, opacity: 1 },
                        {
                            transform: `translate(${endX}px, ${endY}px) scale(${Math.random() * 0.3 + 0.1})`,
                            opacity: Math.random() * 0.3
                        }
                    ], {
                        duration: Math.random() * 400 + 500,
                        easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
                        fill: 'forwards'
                    });

                    setTimeout(() => particle.remove(), 900);
                }

                element.style.transition = 'opacity 0.2s ease-out';
                element.style.opacity = '0';
            }


        </script>
    @endpush
@endsection
