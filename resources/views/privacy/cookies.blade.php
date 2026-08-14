@extends('layouts.app', ['title' => "Политика использования файлов cookie"])
@section('content')
    <section style="padding-bottom: 50px" class="section">
        <div class="container-fluid">
            <div class="row justify-content-center text-center">
                <div style="margin-top: 10px" class="col-lg-8 col-md-11 col-sm-12 mt-5">
                    <h1>Политика использования файлов cookie</h1>
                    <p>Мы используем файлы cookie для корректной работы сайта и улучшения пользовательского опыта.</p>
                    <h2>Какие cookie мы используем</h2>
                    <ul>
                        <li><strong>Необходимые (технические).</strong> Нужны для работы сайта (сессия, авторизация,
                            корзина). Работают
                            всегда.
                        </li>
                        <li><strong>Аналитические.</strong> Помогают понять, как пользователи взаимодействуют с сайтом.
                            Используются при
                            согласии.
                        </li>
                        <li><strong>Маркетинговые.</strong> Используются для показа релевантной рекламы. Используются
                            при согласии.
                        </li>
                    </ul>
                    <h2>Как управлять cookie</h2>
                    <p>Вы можете отключить cookie в настройках браузера. Отключение необходимых cookie может нарушить
                        работу сайта.</p>
                    <p>Нажимая «Принять», вы соглашаетесь на использование аналитических и маркетинговых cookie.</p>
                </div>
            </div>
        </div>
    </section>

@endsection