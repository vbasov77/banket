@extends('layouts.app')

@section('title', 'Доступ запрещён')

@section('content')
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-danger shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-lock me-2"></i>
                            Доступ запрещён
                        </h4>
                    </div>
                    <div class="card-body text-center p-4">
                        <div class="mb-4">
                            <i class="fas fa-exclamation-triangle text-warning display-1"></i>
                        </div>

                        <h5 class="text-danger mb-3">У вас нет прав для выполнения этого действия</h5>

                        <p class="text-muted mb-4">
                            Вы пытаетесь получить доступ к ресурсу, для которого у вас недостаточно прав.
                            Это может быть связано с тем, что:
                        </p>

                        <ul class="text-start mb-4 text-muted">
                            <li>Вы не являетесь владельцем этого объекта</li>
                            <li>У вас нет необходимых разрешений</li>
                            <li>Ресурс принадлежит другому пользователю</li>
                        </ul>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Если вы считаете, что это ошибка, обратитесь к администратору сайта.
                        </div>

                        <div class="mt-4">
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-arrow-left me-2"></i>
                                Назад
                            </a>
                            <a href="{{ route('my.obj') }}" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>
                                На главную
                            </a>
                        </div>
                    </div>
                </div>
            </div>
@endsection
