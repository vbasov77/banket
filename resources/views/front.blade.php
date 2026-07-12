@extends('layouts.app', ['title' => "Банкетные залы"])
@section('content')

    {{--    <script src="{{asset('css/bootstrap/bootstrap-icons@1.10.0/bootstrap-icons.css')}}"></script>--}}
    <script src="{{asset('js/preloader/preloader.js')}}"></script>
    <link href="{{ asset('css/details/details.css') }}" rel="stylesheet">
    <link href="{{ asset('css/front.css') }}" rel="stylesheet">
    <link href="{{ asset('css/parallax/parallax.css') }}" rel="stylesheet">

    <style>
        .details-title {
            white-space: nowrap; /* Запрещаем перенос строк */
            overflow: hidden; /* Скрываем выходящий за границы текст */
            text-overflow: ellipsis; /* Добавляем многоточие в конце обрезанного текста */
            width: 100%; /* Занимаем всю доступную ширину родителя */
        }

        .dimmed-card {
            opacity: 0.6;
        }

    </style>
    <link href="{{ asset('css/carousel/carousel.css') }}" rel="stylesheet">
    @if(!empty($data) && count($data) > 0)
        <div class="relative w-full h-64 md:h-96 overflow-hidden flex items-center justify-center">
            <!-- Фон: карта из public/map.jpg -->
            <div class="absolute inset-0 bg-cover bg-center"
                 style="background-image: url('{{ asset('map.jpg') }}')"></div>

            <!-- Затемнение (опционально, чтобы текст/кнопка читались лучше) -->
            <div class="absolute inset-0 bg-black/50"></div>

            <!-- Кнопка по центру -->
            <a href="{{ route('map.index') }}"
               class="btn-festive-gradient btn-festive-gradient-white m-3 z-10 px-6 py-3 rounded-lg font-bold text-white shadow-lg hover:scale-105 transition-transform">
                Смотреть на карте
            </a>
        </div>
    @endif
    @include('blocks.nav')
    <section style="padding-bottom: 50px" class="section">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div style="margin-top: 10px" class="col-lg-11 col-md-11 col-sm-12">
                    @if(!empty($message))
                        <div class="alert alert-success mt-3">
                            {{$message}}
                        </div>
                    @endif

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
                                                        <div class="col-lg-5 col-md-6 col-sm-12 restaurant-card
    @if(!empty($arrayDistricts) && !in_array($data[$i]['subjs_data'][$j]['district_name'] ?? '', $arrayDistricts))
        dimmed-card
    @endif"
                                                             style="display: block; margin-bottom: 10px;">
                                                            <a href="{{route('show.subj', ['id' => $data[$i]['subjs_data'][$j]['id']])}}">
                                                                <div class="restaurant-image">
                                                                    <img src="{{$data[$i]['subjs_data'][$j]['path']}}"
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
                                            {{ $data[$i]['details_obj']['description'] }}
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
                                                   class="btn-festive-gradient btn-festive-gradient-green front-btn m-3">
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
                                                {{ $data[$i]['details_obj']['description'] }}
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
                    <center>К сожалению, ничего не найдено...</center>
                @endif
            </div>
        </div>
    </section>
    <script src="{{ asset('js/carousels/carousel.js') }}" defer></script>
    <script src="{{ asset('js/parallax/parallax.js') }}" defer></script>
@endsection
