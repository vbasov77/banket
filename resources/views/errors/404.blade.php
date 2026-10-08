@extends('layouts.app')

@section('content')
    <div class="container py-5 text-center">
        <style>
            .nf-wrap { max-width: 560px; margin: 0 auto; }
            .nf-code {
                font-size: clamp(6rem, 18vw, 11rem);
                font-weight: 800;
                line-height: 1;
                letter-spacing: -0.04em;
                background: linear-gradient(120deg, #6c5ce7, #00b894);
                -webkit-background-clip: text;
                background-clip: text;
                color: transparent;
                animation: nf-float 3s ease-in-out infinite;
            }
            .nf-ghost { font-size: 3rem; display: inline-block; animation: nf-float 3s ease-in-out infinite .4s; }
            @keyframes nf-float {
                0%, 100% { transform: translateY(0); }
                50%      { transform: translateY(-12px); }
            }
            .nf-sub { color: #6c757d; max-width: 420px; margin: 0 auto 1.5rem; }
            .nf-code::selection { background: transparent; }
        </style>

        <div class="nf-wrap">
            <div class="nf-code">404</div>
            <div class="nf-ghost" aria-hidden="true">👻</div>

            <h1 class="h4 mt-3">Страница не найдена</h1>
            <p class="nf-sub">
                Возможно, её убрали, переименовали или её и не было.
                Но у нас есть карта и каталог — начните с них.
            </p>

            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ url('/') }}" class="btn btn-primary px-4">На главную</a>
                <a href="{{ url('/maps') }}" class="btn btn-outline-secondary px-4">Карта</a>
            </div>
        </div>
    </div>
@endsection
