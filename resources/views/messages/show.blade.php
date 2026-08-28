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

        .msg-unread {
            background-color: #fff;
            border: 1px solid #dad6f5;
        }

        .msg-read {
            background-color: #f3f4f6;
            border: none;
        }

        li.sent .msg-unread {
            float: right;
            background-color: #dad6f5;
            margin: 5px;
        }

        li.sent .msg-read {
            float: right;
            background-color: #e5e7eb;
            margin: 5px;
        }

        .msg-selected {
            outline: 2px solid #3b82f6;
            background-color: #dbeafe;
        }

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
                                <div class="header-action">
                                    <button type="button" id="trashBtn" style="display:none;">🗑️</button>
                                </div>

                            </div>

                            {{-- Область сообщений --}}
                            <div class="messages">
                                <ul>
                                    @if (count($messages) > 0)
                                        @foreach ($messages as $msg)
                                            @php
                                                $isMine = ($msg['from_user_id'] == $userId);

                                                // Создаём Carbon-объект из created_at
                                                $msgDate = \Carbon\Carbon::parse($msg['created_at']);

                                                // Проверяем, сегодня ли это сообщение
                                                $isToday = $msgDate->isToday();

                                                // Формируем строку времени
                                                if ($isToday) {
                                                    $timeString = $msgDate->format('H:i'); // Только часы и минуты
                                                } else {
                                                    $timeString = $msgDate->format('d.m H:i'); // Дата + часы и минуты
                                                }
                                            @endphp

                                            <li class="{{ $isMine ? 'sent' : 'received' }}">
                                                <div class="myClass">
                                                    <div class="messageBlock {{ $msg['status'] ? 'msg-read' : 'msg-unread' }}"
                                                         id="{{ $msg['id'] }}"
                                                         data-id="{{ $msg['id'] }}"
                                                         data-notified="{{ $msg['status'] }}">

                                                        {!! $msg['body'] !!}

                                                        <span class="message-time">{{ $timeString }}</span>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    @else
                                        {{-- Опционально: блок "нет сообщений" --}}
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
        </script>
        {{--    <script src="{{ asset('messages/js/message.js') }}"></script>--}}
        <script>
            const escapeHtml = (unsafe) => {
                if (!unsafe) return '';
                return unsafe
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            };

            let arrayId = [];

            // Инициализация массива ID при загрузке (если нужно)
            document.querySelectorAll('.messageBlock').forEach(el => {
                if (el.getAttribute('data-notified') == 0) {
                    arrayId.push(el.getAttribute('id'));
                }
            });

            function scrollToBottom() {
                $('.messages').animate({scrollTop: $('.messages ul').prop('scrollHeight')}, "fast");
            }

            $(document).ready(function () {
                scrollToBottom();
            });

            function newMessage() {
                // 1. Получаем текст
                var messageText = $('.message-input input').val();
                var editorHtml = $('.message-input .emoji-wysiwyg-editor').html();

                // Если поле ввода пустое, пробуем взять из редактора
                if ($.trim(messageText) == '') {
                    messageText = editorHtml;
                }

                // Экранируем только если это обычный текст, если это HTML из редактора - экранировать нельзя, иначе теги сломаются
                // Но будь осторожен с XSS, если используешь .html() ниже. Для простого текста оставь escapeHtml.
                // В твоем коде ты вставляешь ${res.body} прямо в HTML. Если body может содержать теги - ок.
                // Если там только текст - лучше использовать .text() или экранировать.
                // Оставим как у тебя, но для переменной messageText (для проверки пустоты) используем trim.

                if ($.trim(messageText) === '') {
                    return false; // Ничего не отправляем
                }

                const to_user_id = window.to_user_id; // Убедись, что эта переменная определена где-то глобально
                const from_user_id = window.from_user_id;

                data = {
                    "to_user_id": to_user_id,
                    "from_user_id": from_user_id,
                    "body": messageText,
                };

                // --- ГЛАВНОЕ ИСПРАВЛЕНИЕ: Время ДО отправки ---
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const localTimeString = `${hours}:${minutes}`; // Формат 14:35

                // Сразу рисуем сообщение со временем "Сейчас", чтобы интерфейс не висел
                const tempMessageId = 'temp_' + Date.now(); // Временный ID, если сервер не ответит

                const $newLi = $(`<li class="sent">
            <div class="myClass">
                <div id="${tempMessageId}"
                     data-id="${tempMessageId}"
                     style="float: right; font-size: 17px; background-color: #dad6f5; "
                     class="messageBlock">
                    ${escapeHtml(messageText)}<br>
                    <small style="font-size: 10px; color: #666;" class="mb-0 text-left">${localTimeString}</small>
                </div>
            </div>
        </li>`);

                $('.messages ul').append($newLi);
                scrollToBottom();

                // Очищаем поля
                $('.message-input input').val('');
                $('.message-input .emoji-wysiwyg-editor').html('');

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '/add_message',
                    type: 'post',
                    data: data,
                    dataType: 'json',
                    success: function (res) {
                        // Обновляем ID и время, если сервер ответил успешно
                        if (res.id) {
                            const $block = $('#' + tempMessageId);
                            $block.attr('id', res.id);
                            $block.data('id', res.id);
                            arrayId.push(res.id);

                            // Если сервер прислал дату - обновляем время.
                            // Если нет - оставляем локальное (оно самое точное для пользователя)
                            let timeToShow = localTimeString;

                            if (res.date) {
                                try {
                                    const serverDate = new Date(res.date);
                                    if (!isNaN(serverDate.getTime())) {
                                        const h = String(serverDate.getHours()).padStart(2, '0');
                                        const m = String(serverDate.getMinutes()).padStart(2, '0');
                                        timeToShow = `${h}:${m}`;
                                    }
                                } catch (e) {
                                    console.warn('Не удалось распарсить дату с сервера', e);
                                }
                            }

                            $block.find('small').text(timeToShow);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Ошибка отправки:', xhr.responseText);
                        const $block = $('#' + tempMessageId);
                        $block.css('background-color', '#ffcccc'); // Показываем ошибку красным
                        alert('Не удалось отправить сообщение. Проверьте соединение.');
                        // Можно добавить кнопку "Повторить" здесь
                    }
                });
            }

            $('.submit').click(function () {
                newMessage();
            });

            $("#framechat .content .message-input").keyup(function (event) {
                if (event.keyCode === 13) {
                    $(".submit").click();
                }
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
                    const distance = Math.random() * 150 + 120;

                    const endX = x + Math.cos(angle) * distance;
                    const endY = y + Math.sin(angle) * distance;

                    container.appendChild(particle);

                    particle.animate([
                        {transform: `translate(0, 0) scale(1)`, opacity: 1},
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

            let selectionMode = false;
            let selectedIds = [];

            // Двойной клик — вход в режим отмечания
            $('.messages').on('dblclick', '.messageBlock', function () {
                selectionMode = true;
                selectedIds = [];
                $('.messageBlock').removeClass('msg-selected');
                $(this).addClass('msg-selected');
                selectedIds.push($(this).data('id'));
                $('#trashBtn').show();
            });

            // Одинарный клик в режиме — отметить/снять
            $('.messages').on('click', '.messageBlock', function () {
                if (!selectionMode) return;

                const id = $(this).data('id');
                if ($(this).hasClass('msg-selected')) {
                    $(this).removeClass('msg-selected');
                    selectedIds = selectedIds.filter(x => x != id);
                } else {
                    $(this).addClass('msg-selected');
                    selectedIds.push(id);
                }
            });

            // Клик по корзине — подтверждение и удаление массива
            $('#trashBtn').on('click', function () {
                if (!selectedIds.length) return;
                if (!confirm('Подтвердите удаление ' + selectedIds.length + ' сообщений?')) return;

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '/delete_message',
                    type: 'get',
                    data: {ids: selectedIds},
                    dataType: 'json',
                    success: function (res) {
                        if (res.answer === 'ok') {
                            $('.messageBlock.msg-selected').each(function () {
                                createParticleEffect(this);
                            });
                            setTimeout(() => {
                                $('.messageBlock.msg-selected').closest('li').remove();
                                selectedIds = [];
                                selectionMode = false;
                                $('#trashBtn').hide();
                                scrollToBottom();
                            }, 900);
                        } else {
                            alert('Не удалось удалить сообщения');
                        }
                    },
                    error: function () {
                        alert('Ошибка при удалении сообщений');
                    }
                });
            });
        </script>
    @endpush
@endsection
