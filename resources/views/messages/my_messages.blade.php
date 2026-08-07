@extends('layouts.app', ['title' => 'Мои сообщения'])

@section('content')
    <link href="{{ asset('messages/css/messages.css') }}" rel="stylesheet">

    <section>
        <div class="container px-4 px-lg-5">
            <div class="row justify-content-center text-center">
                <div class="col-xl-8">

                    @if (!empty($message))
                        <div id="mess" class="mess mb-4" style="background-color: #43b143; color:#ffffff; padding: 5px; margin: 15px;">
                            <center>{{ $message }}</center>
                        </div>
                    @endif

                    <h1 class="mb-4" style="margin-top: 40px;">Мои сообщения</h1>

                    {{-- Карточка «Написать в поддержку» — в стиле остальных сообщений --}}
                    <a class="messageLink" style="text-decoration: none;" href="{{ route('show.messages', ['to_user_id' => 1]) }}">
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    {{-- Вместо фото — SVG --}}
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
                    {{-- Конец карточки поддержки --}}

                    {{-- Список обычных сообщений --}}
                    @if (count($messages) > 0)
                        @foreach ($messages as $msg)
                            @php
                                $peerId = ($msg['from_user_id'] === auth()->id()) ? $msg['to_user_id'] : $msg['from_user_id'];
                            @endphp

                            <a class="messageLink" style="text-decoration: none;"
                               href="{{ route('show.messages', ['to_user_id' => $peerId]) }}">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-xl-2 col-3">
                                                @if(!empty($msg['path']))
                                                    <img src="{{ asset('images/' . $msg['path']) }}"
                                                         style="width: 80px; height: auto; object-fit: cover; border-radius: 4px;" alt="Image">
                                                @else
                                                    <img src="{{ asset('images/no_image/no_image.jpg') }}"
                                                         style="width: 80px; height: auto; object-fit: cover; border-radius: 4px;" alt="No image">
                                                @endif
                                            </div>

                                            <div class="col-xl-6 col-9 text-start">
                                                {!! $msg['body'] !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    @else
                        <p class="text-muted text-center py-5">У вас пока нет сообщений.</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="{{ asset('messages/js/message_hide.js') }}"></script>
    @endpush
@endsection
