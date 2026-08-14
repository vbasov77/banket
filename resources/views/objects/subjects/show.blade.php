@extends('layouts.app', ['title' => $subj['name_subj']])
@section('content')

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="{{ asset('css/subj/show_subj.css') }}" rel="stylesheet">
    <link href="{{ asset('css/subj/card_subj.css') }}" rel="stylesheet">

    <link href="{{ asset('css/modal/modal.css') }}" rel="stylesheet">
    <link href="{{ asset('css/carousel/carousel.css') }}" rel="stylesheet">
    <link href="{{ asset('css/cards/cards.css') }}" rel="stylesheet">

    <link href="{{ asset('css/lightbox/lightbox.css') }}" rel="stylesheet">
    <link href="{{ asset('css/parallax/parallax.css') }}" rel="stylesheet">
    <link href="{{ asset('css/details/details.css') }}" rel="stylesheet">
    {{--    <link href="{{ asset('css/contact-content/contact-content.css') }}" rel="stylesheet">--}}

    <style>
        .restaurant-card {
            margin-top: 0px;
        }

        .location-flow {
            font-size: 13px;
            color: #555;
            line-height: 0.7;
            margin-bottom: 14px;

            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;

            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            gap: 8px;
        }

        .district-text {
            white-space: nowrap;
            flex-shrink: 0;
        }

        .metro-inline-icon {
            display: inline-block;
            vertical-align: middle;
            flex-shrink: 0;
        }

        .metro-station-item {
            white-space: nowrap;
            flex-shrink: 0;
        }

        .metro-separator {
            flex-shrink: 0;
        }

        .d-flex.flex-wrap {
            line-height: 1.2;
            font-size: 15px;
        }

        @media (max-width: 768px) {
            .parallax-container {
                height: 50vh;
            }

            .parallax-title {
                font-size: clamp(24px, 10vw, 80px);
            }


        }

        @media (max-width: 480px) {
            .parallax-container {
                height: 30vh;
            }

            .parallax-title {
                font-size: clamp(24px, 10vw, 80px);
            }

            .text-muted {
                font-size: 16px;
            }

            .restaurant-card {
                margin-bottom: 0px;
            }
        }

    </style>
    @if (!empty($subj['image_paths']) && count($subj['image_paths']) > 0)
        <div class="parallax-container">
            <div class="parallax-bg" style="background-image: url('{{ $subj['image_paths'][0] }}'); "></div>
            <div class="parallax-content">
                <div class="parallax-title-center">
                    <h1 class="parallax-title">{!! $subj['obj']['name_obj'] !!}</h1>
                </div>
            </div>
        </div>
    @endif
    <div style="padding-bottom: 50px" class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Hero section -->
                <section class="hero-section p-4 mb-5">
                    <div class="row align-items-center">
                        @include('blocks.favorite')
                        <h1 class="section-title display-5 fw-bold text-dark mb-3">
                            {{ $subj['name_subj'] }}
                        </h1>

                        <p class="lead text-muted mb-4">
                            {!!nl2br(e($subj['text_subj']))!!}

                        </p>
                        <br>
                    </div>
                </section>
                <section class="mb-5">
                    @auth
                        @if(Auth::user()->isAuthor(Auth::id()))
                            <div style="margin-bottom: 10px; margin-top: 10px; width: auto; float: right">
                                @if($subj['published'])
                                    <span class="badge bg-success fs-5 px-4 py-2">Опубликовано</span>
                                @else
                                    <span class="badge bg-warning text-dark fs-5 px-4 py-2">Не опубликовано</span>
                                @endif
                            </div>
                        @endif
                    @endauth
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
                                @foreach ($subj['image_paths'] as $index => $image)
                                    <img
                                            class="item-carousel"
                                            src="{{ $image }}"
                                            alt="{{ $subj['name_subj'] }}"
                                            data-index="{{ $index }}"
                                            data-big-image="{{ $subj['big_image_paths'][$index] ?? $image }}">
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
                                            <span class="detail-value">до {{ $subj['capacity_to'] }} чел </span>
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
                            <h4 class="section-title mb-4">Адрес, Связь</h4>

                            {{-- Район + метро в едином потоке --}}
                            <div class="location-flow mb-3">
            <span class="district-text">
                📍 {{ $subj['district_name'] ?? 'Район не указан' }}
            </span>

                                @if (!empty($subj['nearest_metros']))
                                    @php
                                        $stations = $subj['nearest_metros'];
                                        $count = count($stations);
                                    @endphp

                                    <span class="metro-inline-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="9" fill="#0077b6"/>
                        <text x="12" y="17" text-anchor="middle" font-family="Arial, sans-serif" font-weight="bold"
                              font-size="13" fill="#fff">M</text>
                    </svg>
                </span>

                                    @for ($k = 0; $k < $count; $k++)
                                        @php
                                            $s = $stations[$k];
                                            $dist = (float)($s['distance_km'] ?? 0);
                                            $name = $s['station_name'];
                                            $formattedDist = number_format($dist, 1);
                                        @endphp

                                        <span class="metro-station-item">
                        {{ $name }} ({{ $formattedDist }} км)
                    </span>
                                    @endfor
                                @endif
                            </div>

                            <p class="mb-4">
                                Адрес: {{ $subj['address'] ?? 'Адрес не указан' }}
                            </p>

                            <div>
                                <span id="phone-masked">+7 (•••)</span>
                                <a
                                        id="phone-full"
                                        style="display: none; text-decoration: none; color: black; font-weight: 500;"
                                        href="tel:{{ $subj['obj']['phone_obj'] ?? '' }}"
                                >
                                    {{ $subj['obj']['phone_obj'] ?? '' }}
                                </a>
                                <button type="button" id="toggle-phone" class="btn btn-outline-dark btn-sm ms-2">
                                    Показать номер для звонка
                                </button>
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    const masked = document.getElementById('phone-masked');
                                    const full = document.getElementById('phone-full');
                                    const button = document.getElementById('toggle-phone');

                                    if (!masked || !full || !button) return;

                                    button.addEventListener('click', function () {
                                        if (full.style.display === 'none') {
                                            masked.style.display = 'none';
                                            full.style.display = 'inline';
                                            button.textContent = 'Скрыть номер';
                                        } else {
                                            masked.style.display = 'inline';
                                            full.style.display = 'none';
                                            button.textContent = 'Показать номер для звонка';
                                        }
                                    });
                                });
                            </script>

                            <div class="d-flex flex-column align-items-center justify-content-center text-center mt-4">
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

                @if(!empty($subj['details_obj']))
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                                <!-- Все блоки особенностей -->
                                @php
                                    $sections = [
                                        ['title' => 'Подходит для:', 'icon' => 'bi-calendar-event', 'color' => 'text-danger', 'data' => $subj['details_obj']['for_events']],
                                        ['title' => 'Кухня:', 'icon' => 'bi-cutlery', 'color' => 'text-warning', 'data' => $subj['details_obj']['kitchen']],
                                        ['title' => 'Способы оплаты:', 'icon' => 'bi-credit-card', 'color' => 'text-dark', 'data' => $subj['details_obj']['payment_methods']]
                                    ];
                                @endphp

                                @foreach($sections as $section)
                                    <div class="col">
                                        <div class="p-3 bg-light rounded h-100">
                                            <h5 class="fw-semibold mb-3">
                                                <i class="{{ $section['icon'] }} {{ $section['color'] }} me-2"></i>
                                                {{ $section['title'] }}
                                            </h5>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($section['data'] as $item)
                                                    <span class="feature-badge bg-white border rounded px-2 py-1">
{{ $item }}
</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <!-- Алкоголь -->
                                <div class="col">
                                    <div class="p-3 bg-light rounded h-100">
                                        <h5 class="fw-semibold mb-3">
                                            <i class="bi bi-wine text-danger me-2"></i>
                                            Алкоголь:
                                        </h5>
                                        @if($subj['details_obj']['alcohol'] == 0)
                                            <span class="badge bg-success bg-gradient">Разрешёно</span>
                                        @elseif($subj['details_obj']['alcohol'] == 1)
                                            <span class="badge bg-danger bg-gradient">Не разрешёно</span>
                                        @elseif(!empty(explode(':', $subj['details_obj']['alcohol'])[0]) == 2)
                                            <span class="badge bg-success bg-gradient">Разрешёно за определённую плату</span>
                                            <br>
                                            <span>{!! explode(':', $subj['details_obj']['alcohol'])[1] !!} руб.</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Своё -->
                                <div class="col">
                                    <div class="p-3 bg-light rounded h-100">
                                        <h5 class="fw-semibold mb-3">
                                            <i class="bi bi-wine text-danger me-2"></i>
                                            Свои фрукты, другое:
                                        </h5>
                                        @if($subj['details_obj']['more'] == 0)
                                            <span class="badge bg-success bg-gradient">Разрешёно</span>
                                        @elseif($subj['details_obj']['more'] == 1)
                                            <span class="badge bg-danger bg-gradient">Не разрешёно</span>
                                        @elseif(!empty(explode(':', $subj['details_obj']['more'])[0]) == 2)
                                            <span class="badge bg-success bg-gradient">Разрешёно за определённую плату</span>
                                            <br>
                                            <span>{!! explode(':', $subj['details_obj']['more'])[1] !!} руб.</span>
                                        @endif
                                    </div>
                                </div>
                                @if($subj['details_obj']['service_fee'])
                                    <div class="col">
                                        <div class="p-3 bg-light rounded h-100">
                                            <h5 class="fw-semibold mb-3">
                                                <i class="bi bi-wine text-danger me-2"></i>
                                                Сервисный сбор:
                                            </h5>
                                            <div>
                                                <h4>{!! $subj['details_obj']['service_fee'] !!} %</h4>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div style="margin-top: 40px" class="col-md-12 mb-12">
                                <h5 class="fw-semibold mb-3"><i class="bi bi-wine text-danger me-2"></i>Описание:
                                </h5>
                                <div class="bg-light p-4 rounded-10 shadow-sm">
                                    <p class="lead text-muted">
                                        {!!nl2br(e($subj['details_obj']['text_obj']))!!}

                                    </p>
                                </div>
                            </div>
                        </div> <!-- Закрытие col-12 с особенностями и услугами -->
                    </div> <!-- Закрытие основного row секции -->
                @endif


                <section>
                    @if(!empty($subj['related_subjs']))
                        <h3 class="section-title">Ещё залы</h3>
                        <div class="carousel-wrapper moreSubj @if(count($subj['related_subjs']) > 2)festival @endif">
                            <div class="carousel">
                                <div class="carousel-content">

                                    @php($countSubj = count($subj['related_subjs']))
                                    @for ($j = 0; $j < $countSubj; $j++)
                                        <div class="restaurant-card">
                                            <div class="item-carousel">
                                                <a href="{{ route('show.subj', ['id' => $subj['related_subjs'][$j]['subj_id']]) }}">
                                                    <img class="restaurant-image"
                                                         src="{{ $subj['related_subjs'][$j]['image_path']}}"
                                                         alt="{{ $subj['related_subjs'][$j]['name_subj'] }}"
                                                    >
                                                </a>
                                                <div class="details">
                                                    <h3 class="details-title">{{ $subj['related_subjs'][$j]['name_subj'] }}</h3>
                                                    <div class="details-info">
                                                        <div class="detail">
                                                            <span class="detail-label">Вместимость:</span>
                                                            <span class="detail-value">
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
                                        </div>
                                    @endfor
                                </div>
                            </div>
                            <button class="carousel-prev">❮</button>
                            <button class="carousel-next">❯</button>
                        </div>
                    @endif
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
                    @if($nearestObjects)
                        @include('blocks.card_subj')
                    @endif
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
    <script>
        window.favStore = '{{route('favorites_subj.store', ['id' => $subj['subj_id']])}}';
        window.favDestroy = '{{route('favorites_subj.destroy', ['id' => $subj['subj_id']])}}';
    </script>
    <script src="{{ asset('js/parallax/parallax.js') }}" defer></script>
    <script src="{{ asset('js/carousels/carousel.js') }}" defer></script>
    <script src="{{ asset('js/lightbox/lightbox.js') }}" defer></script>

@endsection




