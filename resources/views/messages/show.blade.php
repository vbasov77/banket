@extends('layouts.app', ['title' => 'Мои сообщения'])
@push('styles')
    <link href="{{ asset('messages/css/messages.css') }}" rel="stylesheet">
    <style>
        @media (max-width: 768px) {
            #framechat {
                max-height: 600px;
            }
        }
        @media (max-width: 480px) {
            #framechat {
                max-height: 400px;
            }
        }
    </style>
@endpush
@section('content')
    <section>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10 col-sm-12">
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
                                    <button type="button" id="editBtn" style="display:none; margin-right:8px;"
                                            title="Редактировать">✏️
                                    </button>
                                    <button type="button" id="trashBtn" style="display:none;">🗑️</button>
                                </div>

                            </div>

                            {{-- Область сообщений --}}
                            {{-- Кнопка загрузки старых сообщений --}}
                            <div id="loadMoreWrapper" style="display:none; text-align:center; padding:10px 0;">
                                <button type="button" id="loadMoreBtn" class="btn btn-light btn-sm rounded-pill px-4">
                                    Показать ещё сообщения
                                </button>
                            </div>

                            <div class="messages">
                                <ul>
                                    @if (count($messages) > 0)
                                        @foreach ($messages as $msg)
                                            {{-- твой существующий цикл --}}
                                            @php
                                                $isMine = ($msg['from_user_id'] == $userId);
                                                $msgDate = \Carbon\Carbon::parse($msg['created_at']);
                                                $isToday = $msgDate->isToday();
                                                $timeString = $isToday
                                                    ? $msgDate->format('H:i')
                                                    : $msgDate->format('d.m H:i');
                                            @endphp

                                            <li class="{{ $isMine ? 'sent' : 'received' }}"
                                                style="@if($isMine && $msg['status'] == 0)background-color: #e5e7eb; @endif">
                                                <div class="myClass">
                                                    <div class="messageBlock"
                                                         id="{{ $msg['id'] }}"
                                                         data-id="{{ $msg['id'] }}"
                                                         data-notified="{{ $msg['status'] }}">
                                                        {!! $msg['body'] !!}
                                                        <span class="message-time">{{ $timeString }}</span>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
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

        <script>
            var to_user_id = @json($toUser);
            var from_user_id = @json($userId);

            window.updateMsg = '{{route('update.msg')}}';
            window.checkMsg = '{{route('check.message')}}';
            window.deleteMsg = '{{route('delete.message')}}';
            window.addMessage = '{{route('add.message')}}';

        </script>

        {{-- Firebase SDK --}}
        <script type="module">
            import {initializeApp} from "https://www.gstatic.com/firebasejs/12.18.0/firebase-app.js";
            import {
                getMessaging,
                getToken,
                onMessage
            } from "https://www.gstatic.com/firebasejs/12.18.0/firebase-messaging.js";

            const firebaseConfig = {
                apiKey: "AIzaSyARz2ukBnphzY8PPzBZkvf1tHXEB4oEACw",
                authDomain: "feast-boom.firebaseapp.com",
                projectId: "feast-boom",
                storageBucket: "feast-boom.firebasestorage.app",
                messagingSenderId: "1016746447319",
                appId: "1:1016746447319:web:1e10399fee5c251f6efb92"
            };

            const vapidKey = "BFjVNr93TR5ZHpBIwb3gNCE0uVe5Z6-RvXLWli8271qgrZZXmMQ0cXkC_HJ4yl3SOTdHG0ZDZu_MyTBnjZIPW-M";

            const app = initializeApp(firebaseConfig);
            const messaging = getMessaging(app);

            // 1. Запрос разрешения и получение токена
            window.requestAndSendToken = async function () {
                const permission = await Notification.requestPermission();
                if (permission !== 'granted') {
                    console.log('Пользователь запретил пуши');
                    window.showNotifBlockedModal();
                    return;
                }

                try {
                    const token = await getToken(messaging, {vapidKey});
                    if (token) {
                        fetch('/add-fcm-token', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: new URLSearchParams({
                                token: token,
                                device_model: navigator.userAgent.substring(0, 64)
                            })
                        }).then(res => res.json())
                            .catch(err => console.error('Ошибка:', err));
                    }
                } catch (error) {
                    console.error('Ошибка получения токена:', error);
                }
            };

            // 2. Обработка пушей, когда вкладка открыта
            onMessage(messaging, (payload) => {
                handlePush(payload);
            });

            function handlePush(payload) {
                const data = payload.data || {};
                const code = data.code;

                if (!code) return;
                if (code === '111') {
                    console.log('Получен код 111');
                    window.checkNewMsg();
                } else if (code === '222') {
                    const ids = (data.message_ids || '')
                        .split(',')
                        .map(s => parseInt(s, 10))
                        .filter(n => !isNaN(n));
                    window.markMessagesAsRead(ids);
                } else if (code === '333') {
                    console.log('Получен код 333: удаляем сообщения');

                    const idsStr = data.message_ids || '';
                    if (!idsStr) {
                        console.warn('В пуше нет message_ids');
                        return;
                    }

                    // Парсим IDs: "1,2,3" → [1, 2, 3]
                    const idsToRemove = idsStr
                        .split(',')
                        .map(s => parseInt(s.trim(), 10))
                        .filter(n => !isNaN(n));

                    if (idsToRemove.length === 0) {
                        return;
                    }

                    console.log('Удаляем сообщения с IDs:', idsToRemove);

                    idsToRemove.forEach(id => {
                        // Ищем элемент по data-id (числовое сравнение)
                        const $block = $('.messageBlock[data-id="' + id + '"]');
                        if ($block.length) {
                            const $li = $block.closest('li');
                            createParticleEffect($block[0]); // эффект частиц
                            $li.fadeOut(300, () => {
                                $li.remove();
                                scrollToBottom();
                            });
                        } else {
                            // Можно логировать, если сообщение не найдено в DOM (например, ещё не загружено)
                            console.log(`Сообщение с ID ${id} не найдено в DOM — возможно, не было загружено`);
                        }
                    });
                }

            }

            // 3. Модалка "Уведомления заблокированы" — чистый DOM, без jQuery
            window.showNotifBlockedModal = function () {
                if (document.getElementById('notifBlockedModal')) return;

                const modal = document.createElement('div');
                modal.id = 'notifBlockedModal';
                modal.style.cssText = `
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); display: flex;
            align-items: center; justify-content: center; z-index: 9999;
        `;

                const box = document.createElement('div');
                box.style.cssText = `
            background: #fff; border-radius: 12px; padding: 24px;
            max-width: 380px; text-align: center; font-family: sans-serif;
        `;
                box.innerHTML = `
            <h3 style="margin: 0 0 12px;">🔔 Уведомления заблокированы</h3>
            <p style="color: #555; margin-bottom: 16px; font-size: 14px;">
                Чтобы получать мгновенные сообщения,<br>
                разрешите уведомления в настройках браузера.
            </p>
            <p style="color: #999; margin-bottom: 16px; font-size: 13px;">
                Нажмите на замок 🔒 слева в адресной строке →<br>
                «Уведомления» → «Разрешить».
            </p>
            <button id="retryNotifBtn" style="
                background: #007bff; color: #fff; border: none;
                border-radius: 8px; padding: 10px 24px; font-size: 14px;
                cursor: pointer; margin-right: 8px;
            ">Попробовать снова</button>
            <button id="closeNotifModal" style="
                background: #e5e7eb; color: #333; border: none;
                border-radius: 8px; padding: 10px 24px; font-size: 14px;
                cursor: pointer;
            ">Закрыть</button>
        `;

                modal.appendChild(box);
                document.body.appendChild(modal);

                document.getElementById('retryNotifBtn').addEventListener('click', function () {
                    modal.remove();
                    window.requestAndSendToken();
                });

                document.getElementById('closeNotifModal').addEventListener('click', function () {
                    modal.remove();
                });
            };

            // 4. Функции для пушей (видны глобально через window)
            window.addMessageToChat = function (msg) {
                const msgDate = new Date(msg.created_at);
                const hours = String(msgDate.getHours()).padStart(2, '0');
                const minutes = String(msgDate.getMinutes()).padStart(2, '0');
                const timeString = `${hours}:${minutes}`;

                const $newLi = $(`<li class="received">
            <div class="myClass">
                <div id="${msg.id}"
                     data-id="${msg.id}"
                     style="float: left; font-size: 17px; background-color: #e1f5f0;"
                     class="messageBlock">
                    ${escapeHtml(msg.body)}<br>
                    <small style="font-size: 10px; color: #666;" class="mb-0 text-right">${timeString}</small>
                </div>
            </div>
        </li>`);

                $('.messages ul').append($newLi);
                scrollToBottom();
            };

            window.markMessagesAsRead = function (ids) {
                ids.forEach(id => {
                    const $el = $('[data-id="' + id + '"]');
                    if ($el.length) {
                        $el.closest('li').css('background-color', '');
                    }
                });
            };
        </script>

        {{-- Скрипты (вынесены наружу, чтобы не ломать структуру) --}}
        <script src="{{ asset('js/jquery/3.5.1/jquery-3.5.1.js') }}"></script>

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

                // Показываем кнопку, когда доскроллили к верху
                $('.messages').on('scroll', function () {
                    if ($(this).scrollTop() < 50) {
                        $('#loadMoreWrapper').fadeIn(200);
                    } else {
                        $('#loadMoreWrapper').fadeOut(200);
                    }
                });

                // Клик по кнопке: подгрузка старых сообщений
                $('#loadMoreBtn').on('click', function () {
                    const $btn = $(this);
                    $btn.prop('disabled', true).text('Загрузка...');

                    // Страница: начинаем с 2 (первая уже загружена при рендере страницы)
                    if (!window.chatPage) window.chatPage = 2;

                    const data = {
                        to_user_id: window.to_user_id,
                        page: window.chatPage,
                    };

                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: '/messages/load-older',
                        type: 'GET',
                        data: data,
                        dataType: 'json',
                        success: function (res) {
                            const $ul = $('.messages ul');
                            let htmlItems = '';

                            res.messages.forEach(msg => {
                                const isMine = (msg.from_user_id == window.from_user_id);
                                const d = new Date(msg.created_at);
                                const h = String(d.getHours()).padStart(2, '0');
                                const m = String(d.getMinutes()).padStart(2, '0');
                                const timeString = `${h}:${m}`;
                                const bgStyle = isMine && msg.status == 0 ? 'background-color:#e5e7eb;' : '';

                                htmlItems += `
                        <li class="${isMine ? 'sent' : 'received'}" style="${bgStyle}">
                            <div class="myClass">
                                <div class="messageBlock"
                                     id="${msg.id}"
                                     data-id="${msg.id}"
                                     data-notified="${msg.status}">
                                    ${escapeHtml(msg.body)}
                                    <span class="message-time">${timeString}</span>
                                </div>
                            </div>
                        </li>`;
                            });

                            // Вставляем СВЕРХУ — старые сообщения должны быть выше новых
                            $ul.prepend(htmlItems);

                            window.chatPage++; // следующая страница будет +1

                            // Если больше нет сообщений — скрываем кнопку
                            if (!res.has_more) {
                                $('#loadMoreWrapper').hide();
                            }

                            $btn.prop('disabled', false).text('Показать ещё сообщения');
                        },
                        error: function (xhr) {
                            console.error('Ошибка загрузки старых сообщений', xhr);
                            $btn.prop('disabled', false).text('Ошибка, попробуйте снова');
                            setTimeout(() => $btn.text('Показать ещё сообщения'), 3000);
                        }
                    });
                });
            });

            function newMessage() {
                // 1. Получаем текст
                if (!window.notificationsAsked) {
                    window.notificationsAsked = true;
                    window.requestAndSendToken();
                }

                var messageText = $('.message-input input').val();
                var editorHtml = $('.message-input .emoji-wysiwyg-editor').html();

                // Если поле ввода пустое, пробуем взять из редактора
                if ($.trim(messageText) == '') {
                    messageText = editorHtml;
                }

                if ($.trim(messageText) === '') {
                    return false; // Ничего не отправляем
                }

                const to_user_id = window.to_user_id;
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

                const $newLi = $(`<li class="sent" style="background-color: #e5e7eb;">
            <div class="myClass">
                <div id="${tempMessageId}"
                     data-id="${tempMessageId}"
                     style="float: right; font-size: 17px; background-color: #ccd7ec; "
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
                    url: window.addMessage,
                    type: 'post',
                    data: data,
                    dataType: 'json',
                    success: function (res) {
                        if (res.data && res.data.id) {
                            const $block = $('#' + tempMessageId);
                            $block.attr('id', res.data.id);
                            $block.attr('data-id', res.data.id);
                            arrayId.push(res.data.id);

                            let timeToShow = localTimeString;

                            if (res.data.date) {
                                try {
                                    const serverDate = new Date(res.data.date);
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

            function checkNewMsg() {
                const data = {
                    "to_user_id": window.to_user_id,
                    "from_user_id": window.from_user_id
                };

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: window.checkMsg,
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function (res) {
                        if (res.bool === true && res.messages) {
                            res.messages.forEach(msg => {
                                // ВАЖНО: класс "received", а не "sent", и float: left
                                const $li = $(`<li class="received">
                        <div class="myClass">
                            <div id="${msg.id}" data-id="${msg.id}"
     style="float: left; font-size: 17px; background-color: #e5e7eb;"
     class="messageBlock">
    ${escapeHtml(msg.body)}<br>
    <small style="font-size: 10px; color: #666;" class="mb-0 text-right">
        ${(() => {
                                    const d = new Date(msg.created_at);
                                    let h = String(d.getHours()).padStart(2, '0');
                                    let m = String(d.getMinutes()).padStart(2, '0');
                                    return `${h}:${m}`;
                                })()}
    </small>
</div>
                        </div>
                    </li>`);

                                $('.messages ul').append($li);
                                scrollToBottom();
                            });
                        }
                    },
                    error: function (xhr) {
                        console.error('Ошибка checkNewMsg:', xhr.responseJSON || xhr.responseText);
                    }
                });
            }


            let selectionMode = false;
            let selectedIds = [];

            // Двойной клик — вход в режим отмечания
            $('.messages').on('dblclick', '.messageBlock', function () {
                const $li = $(this).closest('li');
                const isMine = $li.hasClass('sent'); // 'sent' — это мои сообщения

                selectionMode = true;
                selectedIds = [];
                $('.messageBlock').removeClass('msg-selected');
                $(this).addClass('msg-selected');
                selectedIds.push($(this).data('id'));

                // Корзина — всегда, редактирование — только для своих
                $('#trashBtn').show();
                if (isMine && selectedIds.length === 1) {
                    $('#editBtn').show();
                } else {
                    $('#editBtn').hide();
                }
                updateActionButtons();
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
                updateActionButtons();
            });

            // Клик по корзине — подтверждение и удаление массива
            $('#trashBtn').on('click', function () {
                if (!selectedIds.length) return;
                if (!confirm('Подтвердите удаление ' + selectedIds.length + ' сообщений?')) return;

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: window.deleteMsg,
                    type: 'get',
                    data: {ids: selectedIds, to_user_id: to_user_id},
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
        <script>
            // --- Редактирование сообщения ---

            let editingMessageId = null;
            let originalText = '';

            // Показываем/скрываем кнопки в зависимости от количества выделенных
            function updateActionButtons() {
                const $editBtn = $('#editBtn');
                const $trashBtn = $('#trashBtn');

                if (selectionMode && selectedIds.length === 1) {
                    // Проверяем, что выделенное сообщение — своё
                    const $selected = $('.messageBlock.msg-selected');
                    const isMine = $selected.closest('li').hasClass('sent');

                    if (isMine) {
                        $editBtn.show();
                    } else {
                        $editBtn.hide();
                    }
                    $trashBtn.show();
                } else {
                    $editBtn.hide();
                    if (selectionMode && selectedIds.length > 1) {
                        $trashBtn.show();
                    }
                }
            }

            // Клик по кнопке «редактировать»
            $('#editBtn').on('click', function () {
                if (!selectionMode || selectedIds.length !== 1) return;

                const msgId = selectedIds[0];
                const $block = $('.messageBlock[data-id="' + msgId + '"]');
                if (!$block.length) return;

                editingMessageId = msgId;
                // Текст без <span class="message-time">...</span>
                originalText = $block.clone()
                    .find('.message-time').remove().end()
                    .text().trim();

                // Модалка
                if (document.getElementById('editModal')) {
                    document.getElementById('editModal').remove();
                }
                const modal = document.createElement('div');
                modal.id = 'editModal';
                modal.style.cssText = `
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); display: flex;
            align-items: center; justify-content: center; z-index: 9999;
        `;

                const box = document.createElement('div');
                box.style.cssText = `
            background: #fff; border-radius: 12px; padding: 24px;
            max-width: 400px; width: 90%; font-family: sans-serif;
        `;
                box.innerHTML = `
            <h3 style="margin: 0 0 16px; font-size: 18px;">Редактировать сообщение</h3>
            <textarea id="editTextarea" rows="4" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px; box-sizing:border-box;"></textarea>
            <div style="margin-top:16px; display:flex; gap:8px;">
                <button id="saveEditBtn" style="flex:1; padding:10px; background:#007bff; color:#fff; border:none; border-radius:6px; cursor:pointer;">Сохранить</button>
                <button id="cancelEditBtn" style="flex:1; padding:10px; background:#e5e7eb; color:#333; border:none; border-radius:6px; cursor:pointer;">Отменить</button>
            </div>
        `;
                modal.appendChild(box);
                document.body.appendChild(modal);

                const $textarea = $('#editTextarea');
                $textarea.val(originalText);
                $textarea.focus();

                $('#saveEditBtn').on('click', function () {
                    const newText = $textarea.val().trim();
                    if (!newText) {
                        alert('Текст не может быть пустым');
                        return;
                    }

                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: window.updateMsg,
                        type: 'POST',
                        data: {
                            id: editingMessageId,
                            body: newText,
                            to_user_id: window.to_user_id,
                            from_user_id: window.from_user_id
                        },
                        dataType: 'json',
                        success: function (res) {
                            if (res.answer === 'ok') {
                                const $block = $('.messageBlock[data-id="' + editingMessageId + '"]');
                                if ($block.length) {
                                    const $time = $block.find('.message-time').clone();
                                    $block.html(escapeHtml(newText)).append($time);
                                }
                                modal.remove();
                                selectionMode = false;
                                selectedIds = [];
                                $('.messageBlock').removeClass('msg-selected');
                                $('#editBtn').hide();
                                $('#trashBtn').hide();
                            } else {
                                alert('Не удалось сохранить изменения');
                            }
                        },
                        error: function () {
                            alert('Ошибка при сохранении');
                        }
                    });
                });

                $('#cancelEditBtn').on('click', function () {
                    modal.remove();
                    editingMessageId = null;
                    originalText = '';
                });
            });
        </script>
    @endpush
@endsection
