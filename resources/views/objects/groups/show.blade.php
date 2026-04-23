@extends('layouts.app')
@section('content')

    <link href="{{ asset('css/parallax/parallax.css') }}" rel="stylesheet">
    <link href="{{ asset('css/details/details.css') }}" rel="stylesheet">
    <link href="{{ asset('css/subj/card_subj.css') }}" rel="stylesheet">


    @if (!empty($subjs[0]['image_paths']) && count($subjs[0]['image_paths']) > 0)
        <div class="parallax-container">
            <div class="parallax-bg" style="background-image: url('{{ $subjs[0]['image_paths'][0] }}');"></div>
            <div class="parallax-content">
                <div class="parallax-title-center">
                    <h1 class="parallax-title">{!! $group['obj']['name_obj'] !!}</h1>
                </div>
            </div>
        </div>
    @endif

    <div style="padding-bottom: 50px" class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-12">
                <section class="hero-section p-4 mb-5">
                    <div class="row align-items-center">
                        <h1 class="section-title display-5 fw-bold text-dark mb-3">
                            {{ $group['obj']['name_obj'] }}
                        </h1>

                        <p class="lead text-muted mb-4">
                            {{ $group['details_obj']['text_obj'] }}
                        </p>
                    </div>
                </section>
                @if($subjs)

                    @foreach($subjs as $value)
                        <div class="col-12 col-sm-8 col-md-6 col-lg-4 restaurants-grid">
                            <div class="restaurant-card"
                                 data-id="{{ $value['id'] }}" >
                                <!-- Изображение -->
                                <div class="position-relative">
                                    <div class="restaurant-image">
                                        @if($value['image_paths'])

                                            <img src="{{ $value['image_paths'][0] . '&cs=360x0' }}"
                                                 class="card-img-top" alt="Фото субъекта"
                                                 style="height: 200px; object-fit: cover;">
                                        @else
                                            <img src="{{ asset('images/no_image/no_image.jpg') }}"
                                                 class="card-img-top" alt="Нет фото"
                                                 style="height: 200px; object-fit: cover;">
                                        @endif
                                    </div>
                                </div>
                                <!-- Тело карточки -->
                                <div class="restaurant-content">
                                    <!-- Название -->
                                    <h5 class="card-title fw-bold mb-3">{{ $value['name_subj'] }}</h5>

                                    <!-- Характеристики -->
                                    <div class="details-info">
                                        <!-- Вместимость -->
                                        <div class="detail">
                                            <img src="{{ asset('icons/user.svg') }}"
                                                 class="detail-label" style="width: 16px; height: 16px;"
                                                 alt="Вместимость">
                                            <span class="detail-value">{{ $value['capacity_to'] }} мест</span>
                                        </div>

                                        <!-- Стоимость -->
                                        @if(!empty($value['minimum_cost']))
                                            <div class="detail">
                                                <img src="{{ asset('icons/ruble.svg') }}"
                                                     class="detail-label" style="width: 16px; height: 16px;"
                                                     alt="Рубль">
                                                <span class="detail-value price">От {{ number_format($value['minimum_cost'], 0, ' ', ' ') }} ₽</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                            <!-- Контейнер для иконок -->
                                            <div class="actions d-flex align-items-center gap-3">

                                            </div>

                                            <!-- Статус публикации -->
                                            <div class="publication-status">
                                                @if($value['published'] == 0)
                                                    <span class="badge bg-danger">Не опубликовано</span>
                                                @else
                                                    <span class="badge bg-success"><a title="Смотреть субъект {{$value['name_subj']}}"
                                                                                      class="text-decoration-none text-muted"
                                                                                      href="{{ route('show.subj', ['id' => $value['id']]) }}">
                                                    <div style="color: white">Посмотреть</div>
                                                </a></span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
               <br>
                @if(!empty($group['details_obj']))
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                                <!-- Все блоки особенностей -->
                                @php
                                    $sections = [
                                        ['title' => 'Подходит для:', 'icon' => 'bi-calendar-event', 'color' => 'text-danger', 'data' => $group['details_obj']['for_events']],
                                        ['title' => 'Кухня:', 'icon' => 'bi-cutlery', 'color' => 'text-warning', 'data' => $group['details_obj']['kitchen']],
                                        ['title' => 'Способы оплаты:', 'icon' => 'bi-credit-card', 'color' => 'text-dark', 'data' => $group['details_obj']['payment_methods']]
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
                                        @if($group['details_obj']['alcohol'] == 0)
                                            <span class="badge bg-success bg-gradient">Разрешён</span>
                                        @elseif($group['details_obj']['alcohol'] == 1)
                                            <span class="badge bg-danger bg-gradient">Не разрешён</span>
                                        @elseif(!empty(explode(':', $group['details_obj']['alcohol'])[0]) == 2)
                                            <span class="badge bg-success bg-gradient">Разрешён за определённую плату</span>
                                            <br>
                                            <span>{!! explode(':', $group['details_obj']['alcohol'])[1] !!} руб.</span>
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
                                        @if($group['details_obj']['more'] == 0)
                                            <span class="badge bg-success bg-gradient">Разрешён</span>
                                        @elseif($group['details_obj']['more'] == 1)
                                            <span class="badge bg-danger bg-gradient">Не разрешён</span>
                                        @elseif(!empty(explode(':', $group['details_obj']['more'])[0]) == 2)
                                            <span class="badge bg-success bg-gradient">Разрешёно за определённую плату</span>
                                            <br>
                                            <span>{!! explode(':', $group['details_obj']['more'])[1] !!} руб.</span>
                                        @endif
                                    </div>
                                </div>


                            </div>

                        </div> <!-- Закрытие col-12 с особенностями и услугами -->
                    </div> <!-- Закрытие основного row секции -->
                @endif

                @if($nearestObjects)
                    @include('blocks.card_subj')
                @endif

            </div>
        </div>
    </div>
    <script src="{{ asset('js/parallax/parallax.js') }}" defer></script>
@endsection
