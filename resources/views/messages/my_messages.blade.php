@extends('layouts.app', ['title' => 'Мои сообщения'])

@section('content')
    {{-- CSS можно убрать, если не нужен отдельный файл --}}
    {{-- <link href="{{ asset('messages/css/messages.css') }}" rel="stylesheet"> --}}

    <section>
        <div class="container px-4 px-lg-5">
            <div class="row justify-content-center text-center">
                <div class="col-xl-8">

                    @if(!empty(session('error')))
                        <div class="alert alert-danger mt-3">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Убрал дублирование с $message — оставь один блок --}}
                    @if (!empty($message))
                        <div id="mess" class="mess mb-4"
                             style="background-color: #43b143; color:#ffffff; padding: 5px; margin: 15px;">
                            <center>{{ $message }}</center>
                        </div>
                    @endif

                    <h1 class="mb-4" style="margin-top: 40px;">Мои сообщения</h1>

                    {{-- Карточка «Написать в поддержку» --}}
                    <a class="messageLink" style="text-decoration: none;"
                       href="{{ route('show.messages', ['to_user_id' => 1]) }}">
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-xl-2 col-3">
                                        <img src="{{ asset('icons/edit.svg') }}"
                                             style="width: 30px; height: 30px;"
                                             alt="Редактировать">
                                    </div>
                                    <div class="col-xl-6 col-9 text-start">
                                        <span style="font-weight: bold; color: #333;">Поддержка</span><br>
                                        <small class="text-muted">Нажмите, чтобы написать в поддержку</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>

                        @if (count($messages) > 0)
                            @foreach ($messages as $msg)
                                {{-- Пропускаем собеседника с ID = 10 --}}
                                @if ($msg['peer_id'] == 1)
                                    @continue
                                @endif

                                <div style="position: relative; display: inline-block; width: 100%;">
                                    <!-- Крестик удаления -->
                                    <button type="button"
                                            class="delete-chat-btn"
                                            data-from-id="{{ auth()->id() }}"
                                            data-to-id="{{ $msg['peer_id'] }}"
                                            style="
                        position: absolute;
                        top: 12px;
                        right: 12px;
                        background: transparent;
                        border: none;
                        color: #a0a0a0;
                        cursor: pointer;
                        font-size: 20px;
                        line-height: 1;
                        padding: 0;
                        z-index: 10;
                    "
                                            title="Удалить чат">
                                        &times;
                                    </button>

                                    <!-- Ссылка на чат -->
                                    <a class="messageLink"
                                       href="{{ route('show.messages', ['to_user_id' => $msg['peer_id']]) }}"
                                       style="text-decoration: none;">

                                        <div class="card mb-3"
                                             style="box-shadow: 0 1px 3px rgba(0,0,0,.12); border: 1px solid #e0e0e0; position: relative;">

                                            {{-- Индикатор непрочитанных --}}
                                            @if ($msg['unread_count'] > 0)
                                                <span style="
                            position: absolute;
                            top: 8px;
                            right: 45px;
                            background-color: #dc3545;
                            color: #fff;
                            font-size: 11px;
                            padding: 2px 6px;
                            border-radius: 10px;
                            font-weight: bold;
                            z-index: 5;
                        ">
                            {{ $msg['unread_count'] }}
                        </span>
                                            @endif

                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <!-- Аватарка -->
                                                    <div class="col-xl-2 col-3">
                                                        @if (!empty($msg['path']))
                                                            <img src="{{ asset('images/' . $msg['path']) }}"
                                                                 style="width: 80px; height: auto; object-fit: cover; border-radius: 4px;"
                                                                 alt="Avatar">
                                                        @else
                                                            <img src="{{ asset('images/no_image/no_image.jpg') }}"
                                                                 style="width: 80px; height: auto; object-fit: cover; border-radius: 4px;"
                                                                 alt="No image">
                                                        @endif
                                                    </div>

                                                    <!-- Имя, статус «Вы написали», текст сообщения -->
                                                    <div class="col-xl-6 col-9 text-start">
                                <span style="font-weight: bold; color: #333; display: block;">
                                    {{ $msg['peer_name'] }}
                                </span>

                                                        @if ($msg['from_user_id'] === auth()->id())
                                                            <small class="text-muted" style="display: block; margin-top: 4px; color: #007bff;">
                                                                (Вы написали)
                                                            </small>
                                                        @endif

                                                        <small class="text-muted" style="display: block; margin-top: 4px;">
                                                            {!! Str::limit($msg['body'], 40) !!}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted text-center py-5">У вас пока нет сообщений.</p>
                        @endif

                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            window.deleteChat = '{{route('delete.chat')}}';
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                if (!csrfToken) {
                    console.error('CSRF token not found');
                    return;
                }

                const buttons = document.querySelectorAll('.delete-chat-btn');
                buttons.forEach(btn => {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        const fromId = this.getAttribute('data-from-id');
                        const toId = this.getAttribute('data-to-id');

                        if (!confirm('Вы уверены, что хотите удалить этот чат? Все сообщения будут удалены безвозвратно.')) {
                            return;
                        }

                        const formData = new FormData();
                        formData.append('from_user_id', fromId);
                        formData.append('to_user_id', toId);

                        fetch(window.deleteChat, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                // fetch не отправляет Content-Type автоматически для FormData, но Laravel его поймёт
                            },
                            body: formData,
                        })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    alert(data.message);
                                    // Перезагружаем страницу, чтобы список обновился
                                    window.location.reload();
                                } else {
                                    alert('Ошибка: ' + (data.message || 'Не удалось удалить чат'));
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                alert('Произошла ошибка соединения. Попробуйте позже.');
                            });
                    });
                });
            });
        </script>
    @endpush
@endsection
