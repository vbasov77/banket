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

                    <div>
                        <strong>Имя:</strong> {{ $userData['name'] }}
                    </div>
                    <div>
                        <strong>Email:</strong> {{ $userData['email'] }}
                    </div>

                    @php
                        $isVerified = !empty($userData['is_verified']); // или $userData['email_verified_at'] !== null
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
                            // Не подтверждён и ещё ни разу не запрашивали ссылку
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
                                    Ссылка уже была отправлена. Повторный запрос будет доступен через {{ $minutesLeft }} мин.
                                </p>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-success mt-3">
                            Ваш email подтверждён!
                        </div>
                    @endif

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
