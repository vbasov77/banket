@extends('layouts.app', ['title' => 'Редактирование профиля'])

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-7">

                @if(session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{$error}}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card p-4">
                    <h4>Основные данные</h4>
                    <form action="{{ route('account.update') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Имя</label>
                            <input type="text" name="name" id="name" class="form-control"
                                   value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control"
                                   value="{{ old('email', $user->email) }}" required>
                            <div class="form-text">При смене email на новый адрес придёт письмо для подтверждения.</div>
                        </div>

                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </form>
                </div>

                <div class="mt-4">
                    <a href="{{ route('profile.show') }}" class="btn btn-link">← Назад к профилю</a>
                </div>
            </div>
        </div>
    </div>
@endsection
