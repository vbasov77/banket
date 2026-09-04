@extends('layouts.app', ['title' => 'Профиль'])

@section('content')
    <div style="padding-bottom: 50px" class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9 col-md-12">
                <div class="container mt-4">

                    @if(!empty($message))
                        <div class="alert alert-success mt-3">
                            {{ $message }}
                        </div>
                    @endif

                    <h1>Профиль пользователя</h1>

                    {{-- Статус верификации (твой существующий блок) --}}
                    @php
                        $isVerified = !empty($userData['is_verified']);
                        $linkSentAt = session('verification_link_sent_at');
                        $canRequestAgain = false;
                        $minutesLeft = 0;

                        if (!$isVerified && $linkSentAt) {
                            $nextAllowedAt = \Carbon\Carbon::parse($linkSentAt)->addMinutes(60);
                            if (!$nextAllowedAt->isFuture()) {
                                $canRequestAgain = true;
                            } else {
                                $minutesLeft = $nextAllowedAt->diffInMinutes(now());
                            }
                        } elseif (!$isVerified) {
                            $canRequestAgain = true;
                        }
                    @endphp

                    @if (!$isVerified)
                        <div class="alert alert-warning mt-3">
                            <p>Ваш email не подтверждён.</p>

                            @if ($canRequestAgain)
                                <form action="{{ route('verification.send') }}" method="post" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        Отправить ссылку для подтверждения email
                                    </button>
                                </form>
                            @else
                                <p class="text-muted small mb-0">
                                    Ссылка уже была отправлена. Повторный запрос будет доступен через {{ $minutesLeft }}
                                    мин.
                                </p>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-success mt-3">
                            Ваш email подтверждён!
                        </div>
                    @endif

                    <div>
                        <strong>Имя:</strong> {{ $userData['name'] }}
                    </div>
                    <div>
                        <strong>Email:</strong> {{ $userData['email'] }}
                    </div>
                    <div>
                        <strong>Ваш id:</strong> {{ $userData['id'] }}
                    </div>
                    <a class="btn-festive-gradient btn-festive-gradient-green mt-2 mb-3"
                       href="{{route('account.edit')}}">Редактировать
                        профиль</a>
                    <br>
                    <br>

                    {{-- ФОРМА СМЕНЫ ПАРОЛЯ --}}
                    <div class="card p-3 bg-light">
                        <h4>Сменить пароль</h4>
                        @if(session('password_changed'))
                            <div class="alert alert-success">{{ session('password_changed') }}</div>
                        @endif
                        @if($errors->has('current_password') || $errors->has('password') || $errors->has('password_confirmation'))
                            <div class="alert alert-danger">
                                Проверьте правильность введённых данных.
                            </div>
                        @endif

                        <form action="{{ route('profile.change-password') }}" method="POST">
                            @csrf

                            <div class="mb-2">
                                <label for="current_password">Текущий пароль</label>
                                <input type="password" name="current_password" id="current_password"
                                       class="form-control" required>
                                @error('current_password')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="mb-2">
                                <label for="password">Новый пароль</label>
                                <input type="password" name="password" id="password" class="form-control" minlength="8"
                                       required>
                                @error('password')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation">Подтверждение нового пароля</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="form-control" required>
                                @error('password_confirmation')<small
                                        class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <button type="submit" class="btn-festive-gradient btn-festive-gradient-green">Сменить пароль</button>
                        </form>
                    </div>
                    {{-- Конец формы смены пароля --}}

                    <br>

                    <!-- Ссылка удаления профиля БЕЗ модального окна -->
                    <a href="{{ route('profile.delete_profile') }}" style="color: red; text-decoration: underline;">
                        Удалить профиль
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
