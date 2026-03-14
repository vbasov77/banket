@extends('layouts.app', ['title' => "Мои субъекты"])
@section('content')
    <script src="{{ asset('js/preloader/preloader.js') }}"></script>
    <link href="{{ asset('css/details/details.css') }}" rel="stylesheet">
    <style>
        @media (min-width: 992px) {
            .row-cols-lg-4 > * {
                width: auto;
            }
        }
    </style>
    @include('blocks.nav_main_menu')
    <section class="section py-2">
        <!-- Заголовки -->
        <div class="container px-4">
            <div class="row">
                <div class="col-12">
                    @if($data)
                        <div class="row mb-4">
                            <div class="col-12">
                                <h1 class="section-title display-5">{{ $data['name_obj'] }}</h1>
                                <h2 class="h4 text-muted">Мои субъекты</h2>
                            </div>
                        </div>
                        <!-- Сетка карточек -->
                        <div class="row">
                            @if(!empty($data['subjs_all']))
                                @foreach($data['subjs_all'] as $value)
                                    <div class="col-12 col-sm-8 col-md-6 col-lg-4">
                                        <div class="card opacity festival"
                                             data-id="{{ $value['id'] }}">
                                            <!-- Изображение -->
                                            <div class="position-relative">
                                                @if($value['primaryImg'] && $value['primaryImg']->path)
                                                    <img src="{{ $value['primaryImg']->path . '&cs=360x0' }}"
                                                         class="card-img-top" alt="Фото субъекта"
                                                         style="height: 200px; object-fit: cover; @if($value['published'] == 0) opacity: .7; @endif">
                                                @else
                                                    <img src="{{ asset('images/no_image/no_image.jpg') }}"
                                                         class="card-img-top" alt="Нет фото"
                                                         style="height: 200px; object-fit: cover;">
                                                @endif
                                            </div>
                                            <!-- Тело карточки -->
                                            <div class="details">
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


                                                <!-- Действия -->
                                                <div class="mt-auto">
                                                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                                        <!-- Контейнер для иконок -->
                                                        <div class="actions d-flex align-items-center gap-3">
                                                            <a title="Редактировать {{$value['name_subj']}}"
                                                               class="text-decoration-none text-muted"
                                                               href="{{ route('edit.subj', ['id' => $value['id']]) }}">
                                                                <img src="{{ asset('icons/edit.svg') }}"
                                                                     style="width: 18px; height: 18px;"
                                                                     alt="Редактировать">
                                                            </a>
                                                            <a title="Смотреть субъект {{$value['name_subj']}}"
                                                               class="text-decoration-none text-muted"
                                                               href="{{ route('show.subj', ['id' => $value['id']]) }}">
                                                                <img src="{{ asset('icons/eye.svg') }}"
                                                                     style="width: 18px; height: 18px;"
                                                                     alt="Посмотреть">
                                                            </a>
                                                            <img class="{{ $value['published'] ? 'takeOff' : 'publish' }}"
                                                                 data-id="{{ $value['id'] }}"
                                                                 data-url="{{ $value['published'] ? route('subj.take_off', ['id' => $value['id']]) : route('subj.publish', ['id' => $value['id']]) }}"
                                                                 data-route-publish="{{ route('subj.publish', ['id' => '__id__']) }}"
                                                                 data-route-takeoff="{{ route('subj.take_off', ['id' => '__id__']) }}"
                                                                 src="{{ asset('icons/' . ($value['published'] ? 'take_off.svg' : 'publish.svg')) }}"
                                                                 style="width: {{ $value['published'] ? '18' : '15' }}px; cursor: pointer;"
                                                                 title="{{ $value['published'] ? 'Снять с публикации' : 'Опубликовать' }}"
                                                                 alt="{{ $value['published'] ? 'Снять с публикации' : 'Опубликовать' }}">
                                                            <a title="Карта {{$value['name_subj']}}" href="{{route('map.edit', ['id' => $value['id']])}}">
                                                            <img src="{{ asset('icons/map.svg') }}"
                                                                 style="width: 18px; height: 18px;"
                                                                 alt="Карта {{$value['name_subj']}}"></a>
                                                        </div>

                                                        <!-- Статус публикации -->
                                                        <div class="publication-status" data-id="{{ $value['id'] }}">
                                                            @if($value['published'] == 0)
                                                                <span class="badge bg-danger">Не опубликовано</span>
                                                            @else
                                                                <span class="badge bg-success">Опубликовано</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="col-12">
                                    <p class="text-center text-muted fs-5">К сожалению, у вас пока нет субъектов...</p>
                                    <div class="row mt-5">
                                        <div class="col-12 text-center">
                                            <a class="btn-festive-gradient btn-festive-gradient-green"
                                               href="{{ route('create.subj') }}">
                                                <i class="bi bi-pencil-square me-2"></i>
                                                Добавьте субъект
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <!-- Особенности и услуги -->
                            @if(!empty($data['details_obj']))
                                <div class="row g-4">
                                    <div class="col-12">
                                        <h3 class="section-title fs-4 mb-4">Особенности и услуги</h3>
                                        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                                            <!-- Все блоки особенностей -->
                                            @php
                                                $sections = [
                                                    ['title' => 'Подходит для:', 'icon' => 'bi-calendar-event', 'color' => 'text-danger', 'data' => $data['details_obj']['for_events']],
                                                    ['title' => 'Кухня:', 'icon' => 'bi-cutlery', 'color' => 'text-warning', 'data' => $data['details_obj']['kitchen']],
                                                    ['title' => 'Способы оплаты:', 'icon' => 'bi-credit-card', 'color' => 'text-dark', 'data' => $data['details_obj']['payment_methods']]
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
                                                    @if($data['details_obj']['alcohol'])
                                                        <span class="badge bg-success bg-gradient">Разрешён</span>
                                                    @else
                                                        <span class="badge bg-danger bg-gradient">Не разрешён</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div style="margin-top: 40px" class="col-md-12 mb-12">
                                            <h5 class="fw-semibold mb-3"><i class="bi bi-wine text-danger me-2"></i>Описание:
                                            </h5>
                                            <div class="bg-light p-4 rounded-10 shadow-sm">
                                                <p class="lead text-muted">
                                                    {{ $data['details_obj']['text_obj'] }}
                                                </p>
                                            </div>
                                        </div>
                                        <!-- Кнопка редактирования -->
                                        <div class="row mt-5">
                                            <div class="col-12 text-center">
                                                <a class="btn-festive-gradient btn-festive-gradient-green"
                                                   href="{{ route('edit.details_obj', ['id' => $data['details_obj']['id']]) }}">
                                                    <i class="bi bi-pencil-square me-2"></i>
                                                    Редактировать
                                                </a>
                                            </div>
                                        </div>
                                    </div> <!-- Закрытие col-12 с особенностями и услугами -->
                                </div> <!-- Закрытие основного row секции -->
                            @else


                                <p class="text-center text-muted fs-5">Заполните больше о своем объекте</p>
                                <div class="row mt-5">
                                    <div class="col-12 text-center">
                                        <a class="btn-festive-gradient btn-festive-gradient-green"
                                           href="{{ route('create.details_obj') }}">
                                            <i class="bi bi-pencil-square me-2"></i>
                                            Добавьте детали объекта
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="row mt-5">
                            <div class="col-12 text-center">
                                <a class="btn-festive-gradient btn-festive-gradient-green"
                                   href="{{ route('create.obj') }}">
                                    <i class="bi bi-pencil-square me-2"></i>
                                    Добавьте объект
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
    <script src="{{ asset('js/obj/take_off.js') }}" defer></script>
@endsection
