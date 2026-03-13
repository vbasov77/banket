@extends('layouts.app', ['title' => $subj['name_subj']])
@section('content')

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <link href="{{ asset('css/modal/modal.css') }}" rel="stylesheet">
    <link href="{{ asset('css/carousel/carousel.css') }}" rel="stylesheet">
    <link href="{{ asset('css/cards/cards.css') }}" rel="stylesheet">
    <link href="{{ asset('css/contact-content/contact-content.css') }}" rel="stylesheet">

    <style>
        .moreSubj .carousel {
            height: 360px;
        }

        /* Основной контейнер карусели */
        .carousel-wrapper.moreSubj {
            position: relative;
            width: 100%;
            margin: 20px 0;
        }

        .moreSubj .carousel {
            display: flex;
            overflow-x: auto;
            scroll-behavior: smooth;
            padding: 15px 0;
            gap: 15px;
            -webkit-overflow-scrolling: touch;
        }

        /* Скрываем скроллбар */
        .moreSubj .carousel::-webkit-scrollbar {
            display: none;
        }

        .moreSubj .carousel {
            scrollbar-width: none;
        }

        .moreSubj .item-carousel:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        /* Блок деталей */
        .moreSubj .details {
            padding: 16px;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Заголовок */
        .moreSubj .details-title {
            margin: 0 0 12px 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            line-height: 1.3;
        }

        /* Контейнер для инфо */
        .moreSubj .details-info {
            display: grid;
            gap: 8px;
        }

        /* Подпись (например, "Вместимость:") */
        .moreSubj .detail-label {
            color: #7f8c8d;
            min-width: 90px; /* Фиксирует ширину для выравнивания */
            font-weight: 500;
        }

        /* Значение (например, "от 10 до 20 чел.") */
        .moreSubj .detail-value {
            color: #2c3e50;
            font-weight: 500;
        }

        /* Особое выделение цены */
        .moreSubj .price {
            color: #e74c3c; /* Красный акцент */
            font-weight: 600;
            letter-spacing: -0.2px;
        }
    </style>

    <style>
        .lightbox {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .lightbox.hidden {
            display: none;
        }

        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 36px;
            color: white;
            background: none;
            border: none;
            cursor: pointer;
            z-index: 1010;
            padding: 10px; /* Увеличенная область нажатия */
        }

        .lightbox-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
        }

        #lightbox-img {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
            border-radius: 8px;
        }

        .lightbox-controls {
            position: absolute;
            bottom: 20px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 40px;
        }

        .lightbox-prev,
        .lightbox-next {
            font-size: 28px;
            color: white;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            backdrop-filter: blur(5px);
        }

        .lightbox-counter {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            font-size: 14px;
            background: rgba(0, 0, 0, 0.6);
            padding: 4px 12px;
            border-radius: 20px;
        }

        /* Адаптация для портретной ориентации */
        @media (orientation: portrait) {
            .lightbox-controls {
                bottom: 5%;
            }

            .lightbox-counter {
                font-size: 12px;
                padding: 3px 10px;
            }
        }

        /* Адаптация для ландшафтной ориентации */
        @media (orientation: landscape) {
            .lightbox-counter {
                bottom: 20px;
            }
        }

    </style>

    <link href="{{ asset('css/parallax/parallax.css') }}" rel="stylesheet">
    <link href="{{ asset('css/details/details.css') }}" rel="stylesheet">
    <link href="{{ asset('css/contact-content/contact-content.css') }}" rel="stylesheet">
    @if (!empty($subj['image_paths']) && count($subj['image_paths']) > 0)
        <div class="parallax-container">
            <div class="parallax-bg" style="background-image: url('{{ $subj['image_paths'][0] }}');"></div>
            <div class="parallax-content">
                <div class="parallax-title-center">
                    <h1 class="parallax-title">{!! $subj['obj']['name_obj'] !!}</h1>
                </div>
            </div>
        </div>
    @endif
    <div style="padding-bottom: 50px" class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Hero section -->
                <section class="hero-section p-4 mb-5">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="section-title display-5 fw-bold text-dark mb-3">
                                {{ $subj['name_subj'] }}
                            </h1>
                            <p class="lead text-muted mb-4">
                                {{ $subj['text_subj'] }}
                            </p>
                            @auth
                                @if(Auth::user()->isAuthor(Auth::id()))
                                    @if($subj['published'])
                                        <span style="margin-bottom: 10px;"
                                              class="badge bg-success fs-5 px-4 py-2">Опубликовано</span>
                                    @else
                                        <span style="margin-bottom: 10px;"
                                              class="badge bg-warning text-dark fs-5 px-4 py-2">Не опубликовано</span>
                                    @endif
                                @endif
                            @endauth

                        </div>
                        <div class="col-md-4 text-end">
                            <div class="price-tag fs-3 fw-bold">
                                от {{ number_format($subj['minimum_cost'], 0, ' ', ' ') }} ₽
                            </div>
                        </div>
                    </div>
                </section>
                <section class="mb-5">
                    <h4 class="section-title mb-4">Тип площадки</h4>
                    <div class="d-flex align-items-center gap-4">
                        @for ($i = 0; $i < count($subj['site_type']); $i++)
                            <span class="feature-badge">
                    {{ $subj['site_type'][$i] }}
                </span>
                        @endfor
                    </div>
                </section>
                @if(!empty(count($subj['image_paths'])))
                    <div class="carousel-wrapper festival">
                        <div class="carousel">
                            <div class="carousel-content">
                                @foreach ($subj['image_paths'] as $image)
                                    <img
                                            class="item-carousel"
                                            src="{{ $image . '&cs=360x0'}}"
                                            alt="{{ $subj['name_subj'] }}"
                                            data-index="{{ $loop->index }}">
                                @endforeach
                            </div>
                        </div>
                        <button class="carousel-prev">
                            ❮
                        </button>
                        <button class="carousel-next">
                            ❯
                        </button>
                    </div>
                @endif
                <!-- Main info cards -->
                <div style="margin-top: 40px" class="row mb-5">
                    <div class="col-md-6 mb-4">
                        <div class="bg-light p-4 rounded-10 shadow-sm h-100">
                            <h4 class="section-title mb-4">Основная информация</h4>
                            <div class="row">
                                <div class="details">
                                    <div class="details-info">
                                        <div class="detail">
                                            <span class="detail-label">Вместимость:</span>
                                            <span class="detail-value">от {{ $subj['capacity_from'] }}
                                    до {{ $subj['capacity_to'] }} чел </span>
                                        </div>
                                        <div class="detail">
                                            <span class="detail-label">На фуршет до:</span>
                                            <span class="detail-value">{{ $subj['furshet'] }} чел</span>
                                        </div>
                                        <div class="detail">
                                            <span class="detail-label">На человека от:</span>
                                            <span class="detail-value">{{ number_format($subj['per_person'], 0, ' ', ' ') }}
                                    ₽/чел</span>
                                        </div>
                                        <div class="detail">
                                            <span class="detail-label">Стоимость от:</span>
                                            <span class="detail-value price">{{ number_format($subj['minimum_cost'], 0, ' ', ' ') }}
                                    ₽</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="bg-light p-4 rounded-10 shadow-sm h-100">
                            <h4 class="section-title mb-4">Связь</h4>
                            <div class="d-flex flex-column align-items-center justify-content-center text-center">
                                <!-- Единая плашка, внутри — два состояния -->
                                <div class="contact-card" id="contact-card" style="cursor: pointer;">
                                    <i class="bi bi-telephone fs-2 mb-2"></i>
                                    <!-- Единый контейнер с фиксированным размером -->
                                    <div id="contact-content" class="contact-content">
                                        <!-- Исходный текст -->
                                        <h5 class="mb-2" id="trigger-text">Свяжитесь с нами</h5>
                                        <!-- Телефон (скрыт изначально) -->
                                        <a style="text-decoration: none" href="tel:{{ $subj['obj']['phone_obj'] }}"
                                           class="phone-link"
                                           id="phone-link">
                                            {{ $subj['obj']['phone_obj'] }}
                                        </a>
                                    </div>
                                </div>
                                @if($subj['map'])
                                    <div class="p-3">
                                        <a href="{{ route('show.map', ['id' => $subj['subj_id']])}}" id="map"
                                           class="btn-festive-gradient btn-festive-gradient-white">
                                            Смотреть карту
                                        </a>
                                    </div>
                                @else

                                    @auth
                                        @if(Auth::user()->isAuthor(Auth::id()))
                                            <br>
                                            <a href="{{ route('map.create', ['id' => $subj['subj_id']])}}" id="map"
                                               class="p-3 btn-festive-gradient btn-festive-gradient-red">
                                                Поставьте метку на карту
                                            </a>
                                        @endif
                                    @endauth
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="my-5">
                <section class="mt-5">
                    <h3 class="section-title fs-4 mb-4">Общая информация "{!! $subj['obj']['name_obj'] !!}"</h3>
                </section>
                <section>
                    <div class="row">
                        <div style="margin-top: 40px" class="col-md-12 mb-12">
                            <h5 class="fw-semibold mb-3"><i class="bi bi-calendar-event text-danger me-2"></i>Подходит
                                для:</h5>
                            @php
                                $events = $subj['details_obj']['for_events'];
                            @endphp
                            <div class="d-flex flex-wrap align-items-start gap-2">
                                @for ($i = 0; $i < count($events); $i++)
                                    <span class="feature-badge">
                {{ $events[$i] }}
            </span>
                                @endfor
                            </div>
                        </div>
                        <div style="margin-top: 40px" class="col-md-12 mb-12">
                            <h5 class="fw-semibold mb-3"><i class="bi bi-star text-info me-2"></i>Дополнительные услуги:
                            </h5>
                            @if(!empty($services))
                                @php
                                    $services = $subj['details_obj']['service'];
                                @endphp
                                <div class="d-flex flex-wrap align-items-start gap-2">
                                    @for ($i = 0; $i < count($services); $i++)
                                        <span class="feature-badge">
                {{ $services[$i] }}
            </span>
                                    @endfor

                                </div>
                            @endif
                        </div>
                        @if(!empty($features))
                            <div style="margin-top: 40px" class="col-md-12 mb-12">
                                <h5 class="fw-semibold mb-3"><i class="bi bi-lightbulb text-success me-2"></i>Особенности:
                                </h5>
                                @php
                                    $features = $subj['features'];
                                @endphp
                                <div class="d-flex flex-wrap align-items-start gap-2">
                                    @for ($i = 0; $i < count($features); $i++)
                                        <span class="feature-badge">
            {{ $features[$i] }}
        </span>
                                    @endfor
                                </div>
                            </div>
                        @endif
                        <div style="margin-top: 40px" class="col-md-4 mb-4">
                            <h5 class="fw-semibold mb-3"><i class="bi bi-credit-card text-dark me-2"></i>Способы
                                оплаты:
                            </h5>
                            @php
                                $payments = $subj['details_obj']['payment_methods'];
                            @endphp
                            <div>
                                @for ($i = 0; $i < count($payments); $i++)
                                    <span class="feature-badge">
                {{ $payments[$i] }}
            </span>
                                @endfor
                            </div>
                        </div>
                        <div style="margin-top: 40px" class="col-md-4 mb-4">
                            <h5 class="fw-semibold mb-3"><i class="bi bi-wine text-danger me-2"></i>Алкоголь:</h5>
                            @if($subj['details_obj']['alcohol'])
                                <span class="feature-badge bg-success text-white">
            Разрешён
        </span>
                            @else
                                <span class="feature-badge bg-danger text-white">
            Не разрешён
        </span>
                            @endif
                        </div>
                    </div>
                </section>
                <section>
                    @if(!empty($subj['related_subjs']))
                        <div class="carousel-wrapper moreSubj festival">
                            <div class="carousel">
                                <div class="carousel-content">
                                    @php($countSubj = count($subj['related_subjs']))
                                    @for ($j = 0; $j < $countSubj; $j++)
                                        <div class="item-carousel">
                                            <a href="{{ route('show.subj', ['id' => $subj['related_subjs'][$j]['subj_id']]) }}"
                                               class="carousel-link">
                                                <img
                                                        class="item-carousel"
                                                        src="{{ $subj['related_subjs'][$j]['image_path'] . '&cs=360x0'}}"
                                                        alt="{{ $subj['related_subjs'][$j]['name_subj'] }}"
                                                >
                                            </a>
                                            <div class="details">
                                                <h3 class="details-title">{{ $subj['related_subjs'][$j]['name_subj'] }}</h3>
                                                <div class="details-info">
                                                    <div class="detail">
                                                        <span class="detail-label">Вместимость:</span>
                                                        <span class="detail-value">
                                        от {{ $subj['related_subjs'][$j]['capacity_from'] }}
                                        до {{ $subj['related_subjs'][$j]['capacity_to'] }} чел.
                                    </span>
                                                    </div>
                                                    <div class="detail">
                                                        <span class="detail-label">Цена:</span>
                                                        <span class="detail-value price">
                                        {{ number_format($subj['related_subjs'][$j]['minimum_cost'], 0, ' ', ' ') }} ₽
                                    </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                            <button class="carousel-prev">❮</button>
                            <button class="carousel-next">❯</button>
                        </div>
                    @endif
                    <div style="margin-top: 40px" class="col-md-12 mb-12">
                        <h5 class="fw-semibold mb-3"><i class="bi bi-wine text-danger me-2"></i>Описание:</h5>
                        <div class="bg-light p-4 rounded-10 shadow-sm">
                            <p class="lead text-muted">
                                {{ $subj['details_obj']['text_obj'] }}
                            </p>
                        </div>
                    </div>

                    @auth
                        @if(Auth::user()->isAuthor(Auth::id()))
                            <div>
                                <button class="btn-festive-gradient btn-festive-gradient-green"
                                        style="margin-top: 25px; margin-bottom: 50px"
                                        onclick="window.location.href = '{{route('edit.subj', ['id'=>$subj['subj_id']])}}'">
                                    Редактировать субъект
                                </button>
                            </div>
                        @endif
                    @endauth
                </section>
            </div>
        </div>
    </div>

    <!-- Модальный лайтбокс для мобильных -->
    <div id="lightbox" class="lightbox hidden">
        <button class="lightbox-close">&times;</button>
        <div class="lightbox-content">
            <img id="lightbox-img" src="" alt="Увеличенное изображение">
            <div class="lightbox-controls">
                <button class="lightbox-prev" aria-label="Предыдущее изображение">←</button>
                <button class="lightbox-next" aria-label="Следующее изображение">→</button>
            </div>
        </div>
    </div>


    <script src="{{ asset('js/parallax/parallax.js') }}" defer></script>
    <script src="{{ asset('js/carousels/carousel.js') }}" defer></script>
    <script src="{{ asset('js/lightbox/lightbox.js') }}" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const card = document.getElementById('contact-card');
            const container = document.getElementById('contact-content');

            card.addEventListener('click', function () {
                // Добавляем класс, который меняет видимость элементов
                container.classList.add('show-phone');
            });
        });

    </script>

@endsection




