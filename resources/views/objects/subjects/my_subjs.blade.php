@extends('layouts.app', ['title' => "Мои субъекты"])
@section('content')
    <script src="{{ asset('js/preloader/preloader.js') }}"></script>
    <link href="{{ asset('css/details/details.css') }}" rel="stylesheet">
    <link href="{{ asset('css/subj/card_subj.css') }}" rel="stylesheet">
    @include('blocks.nav_main_menu')
    <section class="section py-2">
        <!-- Заголовки -->
        <div class="container px-4">
            <div class="row">
                <div class="col-12">
                    @if(!empty($error))
                        <div class="alert alert-danger">
                            {{$error}}
                        </div>
                    @endif
                    @if(!empty($message))
                        <div class="alert alert-success">
                            {{$message}}
                        </div>
                    @endif
                    @if($data)
                        <div class="row mb-4">
                            <div class="col-12">
                                <h1 class="section-title display-5">{{ $data['name_obj'] }}</h1>
                                <h2 class="h4 text-muted">Мои субъекты</h2>
                            </div>
                        </div>
                        <!-- Сетка карточек -->
                        <div class="row">
                            @if(!empty($data['subjects']))
                                @foreach($data['subjects'] as $value)
                                    <div class="col-12 col-sm-8 col-md-6 col-lg-4 restaurants-grid">
                                        <div class="restaurant-card opacity"
                                             data-id="{{ $value['id'] }}"
                                             style="@if($value['published'] == 0) opacity: .7; @endif">
                                            <!-- Изображение -->
                                            <div class="position-relative">
                                                <div class="restaurant-image">
                                                    @if($value['primaryImg'] && $value['primaryImg']->small_img)
                                                        <img src="{{ $value['primaryImg']->small_img }}"
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
                                                    <div class="detail" title="Вместимость человек">
                                                        👥<span class="detail-value">{{ $value['capacity_to'] }} мест</span>
                                                    </div>
                                                    <!-- Стоимость -->
                                                    @if(!empty($value['per_person']))
                                                        <div class="detail" title="Цена на человека">
                                                            💰<span class="detail-value price">От {{ number_format($value['per_person'], 0, ' ', ' ') }} ₽</span>
                                                        </div>
                                                    @endif

                                                    <!-- Стоимость -->
                                                    @if(!empty($value['minimum_cost']))
                                                        <div class="detail" title="Минимальная сумма">
                                                            💵 <span class="detail-value price">От {{ number_format($value['minimum_cost'], 0, ' ', ' ') }} ₽</span>
                                                        </div>
                                                    @endif
                                                </div>

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
                                                            <a title="Карта {{$value['name_subj']}}"
                                                               href="{{route('map.edit', ['id' => $value['id']])}}">
                                                                <img src="{{ asset('icons/map.svg') }}"
                                                                     style="width: 18px; height: 18px;"
                                                                     alt="Карта {{$value['name_subj']}}"></a>
                                                            <a title="Фотоальбом {{$value['name_subj']}}"
                                                               href="{{route('edit.img_subj', ['id' => $value['id']])}}">
                                                                <img src="{{ asset('icons/photo_st.svg') }}"
                                                                     style="width: 18px; height: 18px;"
                                                                     alt="Фотоальбом {{$value['name_subj']}}"></a>
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
                            @endif
                            @if(!isset($data['features']) && !empty($data['id']))
                                <div class="row mt-5">
                                    <div class="col-12 text-center">
                                        <a class="btn-festive-gradient btn-festive-gradient-green"
                                           href="{{ route('create.obj_features', ['id' => $data['id']]) }}">
                                            <i class="bi bi-pencil-square me-2"></i>
                                            Добавьте особенности объекта
                                        </a>
                                        <br>
                                        <br>
                                        <span>
                                            Особенности объекта — это более подробная информация об особенностях вашего объекта.
                                        </span>
                                    </div>
                                </div>
                            @endif
                            <div class="col-12">
                                <div class="row mt-5">
                                    <div class="col-12 text-center">
                                        <a class="btn-festive-gradient btn-festive-gradient-green"
                                           href="{{ route('create.subj') }}">
                                            <i class="bi bi-pencil-square me-2"></i>
                                            Добавьте субъект
                                        </a>
                                        <br>
                                        <br>
                                        <span>
                                                Субъект — это структурное подразделение вашей компании: например, банкетный зал, кафе или ресторан.
                                            </span>
                                    </div>
                                </div>
                            </div>
                            @if(!empty($data['details_obj']))
                                <div class="row g-4">
                                    <div class="col-12">
                                        <h3 class="section-title fs-4 mb-4">Особенности и услуги</h3>
                                        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                                            <!-- Все блоки особенностей -->
                                            @php
                                                $sections = [
                                                    ['title' => 'Подходит для:', 'icon' => 'bi-calendar-event', 'color' => 'text-danger', 'data' => $data['details_obj']['for_events']],
                                                    ['title' => 'Сервис:', 'icon' => 'bi-calendar-edit', 'color' => 'text-danger', 'data' => $data['details_obj']['service']],
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
                                                            @foreach($section['data'] ?? [] as $item)
                                                                <span class="feature-badge bg-white border rounded px-2 py-1">{{ $item }}</span>
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
                                                    @if($data['details_obj']['alcohol'] == 0)
                                                        <span class="badge bg-success bg-gradient">Разрешён</span>
                                                    @elseif($data['details_obj']['alcohol'] == 1)
                                                        <span class="badge bg-danger bg-gradient">Не разрешён</span>
                                                    @elseif(!empty(explode(':', $data['details_obj']['alcohol'])[0]) == 2)
                                                        <span class="badge bg-success bg-gradient">Разрешён за определённую плату</span>
                                                        <br>
                                                        <span>{!! explode(':', $data['details_obj']['alcohol'])[1] !!} руб.</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <!-- Своё -->

                                            <div class="col">
                                            </div>
                                        </div>
                                        <div style="margin-top: 40px" class="col-md-12 mb-12">
                                            <h5 class="fw-semibold mb-3"><i class="bi bi-wine text-danger me-2"></i>Описание:
                                            </h5>
                                            <div class="bg-light p-4 rounded-10 shadow-sm">
                                                <p class="lead text-muted">
                                                    {!!nl2br(e($data['details_obj']['text_obj']))!!}
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
                                        <br>
                                        <br>
                                        <span>
                                            Детали объекта — это более подробная информация о вашем объекте.
                                        </span>
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
                                <br>
                                <br>
                                <span>
Объект — это ваше предприятие, у которого существуют множества субъектов — банкетных залов, кафе, ресторанов.                                </span>
                            </div>
                        </div>
                    @endif
                    @php
                        $features = $data['features'] ?? null;
                    @endphp
                    @if(!empty($features))
                        <div class="row g-4 mt-2">
                            <div class="col-12">
                                <h3 class="section-title fs-4 mb-4">Особенности объекта</h3>
                                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">

                                    <!-- Текстильный пакет -->
                                    @if(!empty($features['textile_package']))
                                        <div class="col">
                                            <div class="p-3 bg-light rounded h-100">
                                                <h5 class="fw-semibold mb-3"><i
                                                            class="bi bi-layers-fill text-primary me-2"></i>Текстильный
                                                    пакет:</h5>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($features['textile_package'] ?? [] as $item)
                                                        <span class="feature-badge bg-white border rounded px-2 py-1">{{ $item }}</span>
                                                    @endforeach
                                                </div>
                                                @if(!empty($features['textile_colors']))
                                                    <p class="mt-2 mb-0 text-muted small">
                                                        <b>Цвета:</b> {{ $features['textile_colors'] }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Столы -->
                                    @if(!empty($features['tables']))
                                        <div class="col">
                                            <div class="p-3 bg-light rounded h-100">
                                                <h5 class="fw-semibold mb-3"><i
                                                            class="bi bi-table me-2 text-warning"></i>Столы:</h5>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($features['tables'] ?? [] as $item)
                                                        <span class="feature-badge bg-white border rounded px-2 py-1">{{ $item }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Оборудование -->
                                    @if(!empty($features['equipment']))
                                        <div class="col">
                                            <div class="p-3 bg-light rounded h-100">
                                                <h5 class="fw-semibold mb-3"><i
                                                            class="bi bi-speaker me-2 text-danger"></i>Оборудование:
                                                </h5>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($features['equipment'] ?? [] as $item)
                                                        <span class="feature-badge bg-white border rounded px-2 py-1">{{ $item }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Для детей -->
                                    @if(!empty($features['kids']))
                                        <div class="col">
                                            <div class="p-3 bg-light rounded h-100">
                                                <h5 class="fw-semibold mb-3"><i
                                                            class="bi bi-balloon-fill me-2 text-info"></i>Для детей:
                                                </h5>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($features['kids'] ?? [] as $item)
                                                        <span class="feature-badge bg-white border rounded px-2 py-1">{{ $item }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Расположение -->
                                    @if(!empty($features['location']))
                                        <div class="col">
                                            <div class="p-3 bg-light rounded h-100">
                                                <h5 class="fw-semibold mb-3"><i
                                                            class="bi bi-geo-alt-fill me-2 text-success"></i>Расположение:
                                                </h5>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($features['location'] ?? [] as $item)
                                                        <span class="feature-badge bg-white border rounded px-2 py-1">{{ $item }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif



                                    <!-- Парковка -->
                                    @if(!empty($features['parking']))
                                        <div class="col">
                                            <div class="p-3 bg-light rounded h-100">
                                                <h5 class="fw-semibold mb-3"><i
                                                            class="bi bi-p-square me-2 text-primary"></i>Парковка:</h5>
                                                <p class="mb-0">{{ $features['parking'] }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Пирс / Причал -->
                                    @if(!empty($features['pier']))
                                        <div class="col">
                                            <div class="p-3 bg-light rounded h-100">
                                                <h5 class="fw-semibold mb-3"><i class="bi bi-water me-2 text-info"></i>Пирс
                                                    / Причал:</h5>
                                                <p class="mb-0">{{ $features['pier'] }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Предоплата -->
                                    @if(!empty($features['prepayment']))
                                        <div class="col">
                                            <div class="p-3 bg-light rounded h-100">
                                                <h5 class="fw-semibold mb-3"><i
                                                            class="bi bi-cash-coin me-2 text-success"></i>Предоплата:
                                                </h5>
                                                <p class="mb-0">{{ $features['prepayment'] }}</p>
                                            </div>
                                        </div>
                                    @endif

                                </div>

                                <!-- Примечание к банкету -->
                                @if(!empty($features['banquet_note']))
                                    <div class="mt-4">
                                        <h5 class="fw-semibold mb-3"><i class="bi bi-card-text me-2 text-danger"></i>Примечание
                                            к банкету:</h5>
                                        <div class="bg-light p-4 rounded shadow-sm">
                                            <p class="lead text-muted mb-0">{!! nl2br(e($features['banquet_note'])) !!}</p>
                                        </div>
                                    </div>
                                @endif

                                <!-- Интерьер -->
                                @if(!empty($features['interior']))
                                    <div class="mt-4">
                                        <h5 class="fw-semibold mb-3"><i class="bi bi-building me-2 text-secondary"></i>Интерьер:
                                        </h5>
                                        <div class="bg-light p-4 rounded shadow-sm">
                                            <p class="lead text-muted mb-0">{!! nl2br(e($features['interior'])) !!}</p>
                                        </div>
                                    </div>
                                @endif

                                <!-- Кнопка редактирования -->
                                <div class="row mt-5">
                                    <div class="col-12 text-center">
                                        <a class="btn-festive-gradient btn-festive-gradient-green"
                                           href="{{ route('edit.obj_features', ['id' => $features['id']]) }}">
                                            <i class="bi bi-pencil-square me-2"></i>
                                            Редактировать особенности
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        @if(!isset($data['features']) && !empty($data['id']))
                            <div class="row mt-5">
                                <div class="col-12 text-center">
                                    <a class="btn-festive-gradient btn-festive-gradient-green"
                                       href="{{ route('create.obj_features', ['id' => $data['id']]) }}">
                                        <i class="bi bi-pencil-square me-2"></i>
                                        Добавьте особенности объекта
                                    </a>
                                    <br><br>
                                    <span>Особенности объекта — это более подробная информация об особенностях вашего объекта.</span>
                                </div>
                            </div>
                        @endif
                    @endif

                </div>
            </div>
        </div>
    </section>
    <script src="{{ asset('js/obj/take_off.js') }}" defer></script>
@endsection
