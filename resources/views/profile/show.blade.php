@extends('layouts.app', ['title' => "Профиль"])
@section('content')
    <div style="padding-bottom: 50px" class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9 col-md-12">
                <div class="container mt-4">
                    @if(!empty($message))
                        <div class="alert alert-success mt-3">
                            {{$message}}
                        </div>
                    @endif

                    <h1>Профиль пользователя</h1>

                    <div>
                        <strong>Имя:</strong> {{ $userData['name'] }}
                    </div>
                    <div>
                        <strong>Email:</strong> {{ $userData['email'] }}
                    </div>

                    @if (!$userData['is_verified'])
                        <div class="alert alert-warning mt-3">
                            <p>Ваш email не подтверждён.</p>
                            <form action="{{ route('verification.send') }}" method="post" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">
                                    Отправить ссылку для подтверждения email
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="alert alert-success mt-3">
                            Ваш email подтверждён!
                        </div>
                    @endif
                    <br>
                    <!-- Ссылка удаления профиля БЕЗ модального окна -->
                    <a href="{{route('profile.delete_profile')}}" style="color: red">Удалить профиль</a>
                </div>
            </div>
        </div>
    </div>
@endsection
