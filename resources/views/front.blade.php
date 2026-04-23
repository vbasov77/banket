@extends('layouts.app', ['title' => "Банкетные залы"])
@section('content')

    <script src="{{asset('js/preloader/preloader.js')}}"></script>
    <link href="{{ asset('css/details/details.css') }}" rel="stylesheet">
    <style>
        /* Блок деталей */

        .one .carousel {
            height: auto;
        }

        .front-btn {
            float: right;
            position: relative;
        }
    </style>

    <style>
        @media (max-width: 768px) {
            /* Скрываем оригинальную кнопку внутри блока */
        }

        .details-info {
            padding: 15px;
        }

    </style>

    <style>
        .restaurants-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 24px;
            padding: 20px;
        }

        .restaurant-card {
            background: white;
            border-radius: 13px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow: clip;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #f0f0f0;
        }

        .restaurant-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .restaurant-image {
            height: 180px;
            overflow: hidden;
            background: #f8f9fa;
        }

        .restaurant-image img,
        .restaurant-image .placeholder {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .placeholder {
            display: none;
            align-items: center;
            justify-content: center;
            background: #f0f2f5;
            color: #6c757d;
            font-size: 14px;
        }

        .restaurant-content {
            padding: 20px;
        }

        .restaurant-name {
            margin: 0 0 12px 0;
            color: #2c3e50;
            font-size: 20px;
            font-weight: 600;
        }

        .restaurant-features {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 16px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            color: #495057;
        }

        .feature-icon {
            font-size: 16px;
        }

        .tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 16px;
        }

        .tag {
            background: #e9ecef;
            color: #495057;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .features-list {
            margin-bottom: 16px;
        }

        .features-list strong {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
        }

        .features-list ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .features-list li {
            margin-bottom: 4px;
            color: #495057;
            font-size: 14px;
        }

        .restaurant-description {
            color: #6c757d;
            line-height: 1.5;
            font-size: 14px;
            margin-bottom: 16px;
            max-height: 80px;
            overflow: hidden;
        }

        .restaurant-meta {
            color: #95a5a6;
            font-size: 12px;
            border-top: 1px solid #f0f0f0;
            padding-top: 12px;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .restaurants-grid {
                grid-template-columns: 1fr;
                padding: 10px;
            }

            .restaurant-card {
                margin-bottom: 14px;
            }
        }


    </style>

    <style>
        .details-title {
            white-space: nowrap; /* Запрещаем перенос строк */
            overflow: hidden; /* Скрываем выходящий за границы текст */
            text-overflow: ellipsis; /* Добавляем многоточие в конце обрезанного текста */
            width: 100%; /* Занимаем всю доступную ширину родителя */
        }
    </style>

    <link href="{{ asset('css/carousel/carousel.css') }}" rel="stylesheet">

    @include('blocks.nav')

    <section style="padding-bottom: 50px" class="section">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div style="margin-top: 60px" class="col-lg-11 col-md-11 col-sm-12">
                    @if(!empty($data) && count($data) > 0)
                        @php
                            $counter = 0;
                            $countObj = count($data);
                        @endphp
                        @for($i = 0; $i < $countObj; $i++)
                            @php
                                $countSubj = count($data[$i]['subjs_data']);
                            @endphp
                            @if($countSubj > 1)
                                <div class="festival">
                                    <h3>{!! $data[$i]['name_obj'] !!}</h3>
                                    <div class="carousel-wrapper">
                                        <div class="carousel">
                                            <div class="carousel-content">
                                                @if(!empty($data[$i]['subjs_data']))
                                                    @php
                                                        $subjData = $data[$i]['subjs_data'];
                                                    @endphp
                                                    @for ($j = 0; $j < $countSubj; $j++)
                                                        <div class="col-lg-5 col-md-6 col-sm-12 restaurant-card"
                                                             style="display: block; margin-bottom: 10px">
                                                            <a href="{{route('show.subj', ['id' => $data[$i]['subjs_data'][$j]['id']])}}">
                                                                <div class="restaurant-image">
                                                                    <img src="{{$data[$i]['subjs_data'][$j]['path'] . '&cs=360x0'}}"
                                                                         alt="{{ $data[$i]['subjs_data'][$j]['name_subj']}}">
                                                                    <br>
                                                                </div>
                                                            </a>

                                                            <section>
                                                                <div class="details">
                                                                    <div class="details-info">
                                                                        <h3 class="details-title">{{ $data[$i]['subjs_data'][$j]['name_subj']}}</h3>
                                                                        <!-- Вместимость в одной строке -->
                                                                        <div class="detail">
                                                                            <span class="detail-label">Район:</span>
                                                                            <span class="detail-value">{{ $data[$i]['subjs_data'][$j]['district_name'] }}</span>
                                                                        </div>
                                                                        <div class="detail">
                                                                            <span class="detail-label">Вместимость:</span>
                                                                            <span class="detail-value">до: {{ $data[$i]['subjs_data'][$j]['capacity_to'] }} чел.</span>
                                                                        </div>

                                                                        <!-- Цена в одной строке (уже правильно обёрнута) -->
                                                                        <div class="detail">
                                                                            <span class="detail-label">Цена:</span>
                                                                            <span class="detail-value price">от:
                    {{ number_format($data[$i]['subjs_data'][$j]['per_person'], 0, ' ', ' ') }} ₽
                </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </section>
                                                        </div>
                                                    @endfor
                                                @endif
                                            </div>

                                        </div>
                                        <button class="carousel-prev">
                                            ❮
                                        </button>
                                        <button class="carousel-next">
                                            ❯
                                        </button>
                                    </div>
                                    @if(!empty(count($data[$i]['details_obj']['for_events'])))
                                        @php
                                            $sections = [
                                                    ['title' => 'Кухня:', 'icon' => 'bi bi-fork-knife', 'color' => 'text-warning', 'data' => $data[$i]['details_obj']['kitchen']],
                                            ];
                                        @endphp

                                        @foreach($sections as $section)
                                            <div class="col">
                                                <div class="p-3 bg-light rounded h-100">

                                                    <div class="d-flex flex-wrap gap-2">
                                                        <i style="font-size: 30px"
                                                           class="{{ $section['icon'] }} {{ $section['color'] }} me-2"></i>
                                                        @foreach($section['data'] as $item)
                                                            <span class="feature-badge bg-white border rounded px-2 py-1">{{ $item }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                    @endif
                                    <div class="bg-light p-4 rounded-10 shadow-sm">
                                        <p class="lead text-muted" @style(['font-size: 18px'])>
                                            {{ $data[$i]['details_obj']['text_obj'] }}
                                        </p>
                                    </div>
                                </div>
                            @else
                                @if($countSubj)
                                    <div class="festival one">
                                        <h3>{!! $data[$i]['name_obj'] !!}</h3>
                                        <div class="carousel-wrapper">
                                            <div class="carousel">
                                                <div class="carousel-content">
                                                    @if(!empty($data[$i]['subjs_data']))
                                                        @php
                                                            $dataImg = $data[$i]['subjs_data'][0]['image_paths'];
                                                            $countImg = count($dataImg);
                                                        @endphp
                                                        @for ($j = 0; $j < $countImg; $j++)
                                                            <div
                                                                    style="display: block; margin-bottom: 10px">
                                                                {{--                                                            <a href="{{route('show.subj', ['id' => $data[$i]['subjs_data'][$j]['id']])}}">--}}
                                                                <img src="{{$dataImg[$j] . '&cs=360x0'}}"
                                                                     class="item-carousel"
                                                                     alt="{{ $data[$i]['subjs_data'][0]['name_subj']}}">
                                                                {{--                                                            </a>--}}

                                                            </div>
                                                        @endfor
                                                    @endif
                                                </div>
                                            </div>
                                            <button class="carousel-prev">
                                                ❮
                                            </button>
                                            <button class="carousel-next">
                                                ❯
                                            </button>
                                        </div>
                                        <div class="row">
                                            <div style="vertical-align: middle"
                                                 class="col-12 col-sm-9 col-md-7 col-lg-5">
                                                <section>
                                                    <div class="details">
                                                        <div class="details-info">
                                                            <h3 class="details-title">{{ $data[$i]['subjs_data'][0]['name_subj']}}</h3>
                                                            <!-- Вместимость -->
                                                            <div class="detail">
                                                                <span class="detail-label">Вместимость:</span>
                                                                <span class="detail-value">до {{ $data[$i]['subjs_data'][0]['capacity_to'] }} чел.</span>
                                                            </div>
                                                            <!-- Цена -->
                                                            <div class="detail">
                                                                <span class="detail-label">Цена:</span>
                                                                <span class="detail-value price">
                            {{ number_format($data[$i]['subjs_data'][0]['per_person'], 0, ' ', ' ') }} ₽
                        </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </section>
                                            </div>

                                            <div class="col-12 col-sm-3 col-md-5 col-lg-7 d-flex align-items-center justify-content-center">
                                                <a style="width: auto"
                                                   href="{{route('show.subj', ['id' => $data[$i]['subjs_data'][0]['id']])}}"
                                                   class="btn-festive-gradient btn-festive-gradient-green front-btn">
                                                    Подробнее
                                                </a>
                                            </div>
                                        </div>
                                        @if(!empty(count($data[$i]['details_obj']['for_events'])))
                                            @php
                                                $sections = [
                                                    ['title' => 'Кухня:', 'icon' => 'bi bi-fork-knife', 'color' => 'text-warning', 'data' => $data[$i]['details_obj']['kitchen']],
                                                ];
                                            @endphp

                                            @foreach($sections as $section)
                                                <div class="col">
                                                    <div class="p-3 bg-light rounded h-100">
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <i style="font-size: 30px"
                                                               class="{{ $section['icon'] }} {{ $section['color'] }} me-2"></i>
                                                            @foreach($section['data'] as $item)
                                                                <span class="feature-badge bg-white border rounded px-2 py-1">
{{ $item }}
</span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach

                                        @endif
                                        <div class="bg-light p-4 rounded-10 shadow-sm">
                                            <p class="lead text-muted" @style(['font-size: 18px'])>
                                                {{ $data[$i]['details_obj']['text_obj'] }}
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        @endfor
                </div>

                @if(!empty($pagination))
                    <div class="pagination">
                        @if($pagination['prev_page_url'])
                            <a href="{{ $pagination['prev_page_url'] }}">Назад</a>
                        @endif
                        <span>Страница {{ $pagination['current_page'] }} из {{ $pagination['last_page'] }}</span>
                        @if($pagination['next_page_url'])
                            <a href="{{ $pagination['next_page_url'] }}">Вперед</a>
                        @endif
                    </div>
                @else
                    {{ $data->links() }}
                @endif

                @else
                    К сожалению, ничего не найдено...
                @endif
            </div>
        </div>
    </section>
    <script src="{{ asset('js/carousels/carousel.js') }}" defer></script>
@endsection
