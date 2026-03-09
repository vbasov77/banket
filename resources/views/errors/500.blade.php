@extends('layouts.app', ['title' => "Что-то пошло не так"])
@section('content')
    <section class="vh-100 d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8 col-12">
                    <div class="error-container bg-white p-4 rounded shadow-sm text-center">
                        <h1 class="text-danger mb-3">Ой, что-то пошло не так!</h1>
                        <p class="text-muted mb-4">Произошла непредвиденная ошибка. Пожалуйста, попробуйте ещё раз или свяжитесь с поддержкой.</p>
                        <a href="{{ url()->previous() }}" class="btn-festive-gradient btn-festive-gradient-green">Попробовать ещё раз</a>
                    </div>
                </div>
            </div>
    </section>
@endsection

<style>
    .error-container {
        padding: 40px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    h1 {
        color: #dc3545;
    }

    p {
        color: #6c757d;
        line-height: 1.6;
    }
</style>
