@extends('layouts.app', ['title' => "Удаление профиля"])
@section('content')
    <div style="padding-bottom: 50px" class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 col-sm-12">
                <div class="mt-4 p-3 border border-danger rounded">
                    <h5 class="text-danger">Удаление профиля</h5>
                    <p class="text-muted">Внимание: эта операция необратима. Все данные будут удалены.</p>

                    <!-- Форма удаления с полем для пароля -->
                    <form action="{{ route('profile.destroy') }}" method="POST" class="mt-2">
                        @csrf
                        @method('DELETE')

                        <!-- Блок для отображения ошибок -->
                        @if ($errors->any())
                            <div class="alert alert-danger mb-3">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Поле для ввода пароля -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Введите пароль для подтверждения:</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>

                        <!-- Кнопки -->
                        <div class="d-flex gap-2">
                            <!-- Кнопка удаления -->
                            <button type="submit" class="btn btn-danger">
                                Удалить профиль
                            </button>

                            <!-- Кнопка отмены (возвращает на главную) -->
                            <a href="{{ route('profile.show') }}" class="btn btn-secondary">
                                Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
