@extends('layouts.app', ['title' => "Банкетные залы"])
@section('content')

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


        .district-with-icon {
            display: block;
            margin-bottom: 12px;
            font-size: 14px;
            color: #555;
            line-height: 1.4;
        }

        .details-info h3.details-title {
            margin-bottom: 4px; /* чуть плотнее к району */
        }

        .details-title {
            margin: 0 0 0 0;
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

        @media (max-width: 767px) {
            .restaurant-card {
                min-width: 280px;
                flex-shrink: 0;
                flex-basis: 280px;
            }
        }

    </style>

    <link href="{{ asset('css/carousel/carousel.css') }}" rel="stylesheet">
    @if(!empty($data) && count($data) > 0)
        <div class="relative w-full h-64 md:h-96 overflow-hidden flex items-center justify-center">
            <!-- Фон: карта из public/map.jpg -->
            <div class="absolute inset-0 bg-cover bg-center"
                 style="background-image: url('{{ asset('map/img/map.jpg') }}')"></div>

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
                                    @if(!empty(count($data[$i]['details_obj']['for_events'])))
                                        @php
                                            $sections = [
                                                    ['title' => 'Кухня:', 'icon' => 'bi bi-fork-knife', 'color' => 'text-warning', 'data' => $data[$i]['details_obj']['kitchen']],
                                            ];
                                        @endphp

                                        @foreach($sections as $section)
                                            <div class="col">
                                                <div class="p-1 rounded h-100">

                                                    <div class="d-flex flex-wrap gap-2">
                                                        <i style="font-size: 25px"
                                                           class="{{ $section['icon'] }} {{ $section['color'] }} me-2"></i>
                                                        @foreach($section['data'] as $item)
                                                            <span class="feature-badge bg-white border rounded px-2 py-1">{{ $item }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif

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
                                                                    <img src="{{$data[$i]['subjs_data'][$j]['image_paths'][0]}}"
                                                                         alt="{{ $data[$i]['subjs_data'][$j]['name_subj']}}">
                                                                    <br>
                                                                </div>
                                                            </a>
                                                            <section>
                                                                <div class="details">
                                                                    <div class="details-info">
                                                                        <h3 class="details-title">{{ $data[$i]['subjs_data'][$j]['name_subj'] }}</h3>

                                                                        {{-- Район + метро в одной строке (переносится по словам) --}}
                                                                        <div class="location-flow">
    <span class="district-text">
        📍 {{ $data[$i]['subjs_data'][$j]['district_name'] ?? 'Район не указан' }}
    </span>

                                                                            @if (!empty($data[$i]['subjs_data'][$j]['metro_stations']))
                                                                                @php
                                                                                    $stations = $data[$i]['subjs_data'][$j]['metro_stations'];
                                                                                    $count = count($stations);
                                                                                @endphp

                                                                                <span class="metro-inline-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <circle cx="12" cy="12" r="9" fill="#0077b6"/>
  <text x="12" y="17" text-anchor="middle" font-family="Arial, sans-serif" font-weight="bold" font-size="13"
        fill="#fff">M</text>
</svg>


        </span>

                                                                                {{-- Цикл по индексам --}}
                                                                                @for ($k = 0; $k < $count; $k++)
                                                                                    @php
                                                                                        $s = $stations[$k];
                                                                                        $dist = (float)($s['distance_km'] ?? 0);
                                                                                        $name = $s['station_name'];
                                                                                        $formattedDist = number_format($dist, 1);
                                                                                    @endphp

                                                                                    <span class="metro-station-item">
                {{ $name }} ({{ $formattedDist }} км)</span>
                                                                                @endfor
                                                                            @endif
                                                                        </div>

                                                                        <div class="detail">
                                                                            <span class="detail-label">Вместимость:</span>
                                                                            <span class="detail-value">до: {{ $data[$i]['subjs_data'][$j]['capacity_to'] ?? '-' }} чел.</span>
                                                                        </div>

                                                                        <div class="detail">
                                                                            <span class="detail-label">Цена:</span>
                                                                            <span class="detail-value price">
                    от: {{ number_format($data[$i]['subjs_data'][$j]['per_person'] ?? 0, 0, ' ', ' ') }} ₽
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
                                    @if(!empty($data[$i]['details_obj']['description']))
                                        <div class="bg-light p-4 rounded-10 shadow-sm">
                                            <p class="lead text-muted" @style(['font-size: 18px'])>
                                                {!!   $data[$i]['details_obj']['description'] !!}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                @if($countSubj)
                                    <div class="festival one">
                                        <h3>{!! $data[$i]['name_obj'] !!}</h3>
                                        @if(!empty(count($data[$i]['details_obj']['for_events'])))
                                            @php
                                                $sections = [
                                                        ['title' => 'Кухня:', 'icon' => 'bi bi-fork-knife', 'color' => 'text-warning', 'data' => $data[$i]['details_obj']['kitchen']],
                                                ];
                                            @endphp

                                            @foreach($sections as $section)
                                                <div class="col">
                                                    <div class="p-1 rounded h-100">

                                                        <div class="d-flex flex-wrap gap-2">
                                                            <i style="font-size: 25px"
                                                               class="{{ $section['icon'] }} {{ $section['color'] }} me-2"></i>
                                                            @foreach($section['data'] as $item)
                                                                <span class="feature-badge bg-white border rounded px-2 py-1">{{ $item }}</span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
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
                                                            {{-- Район + метро в одной строке (переносится по словам) --}}
                                                            <div class="location-flow">
    <span class="district-text">
        📍 {{ $data[$i]['subjs_data'][0]['district_name'] ?? 'Район не указан' }}
    </span>

                                                                @if (!empty($data[$i]['subjs_data'][0]['metro_stations']))
                                                                    @php
                                                                        $stations = $data[$i]['subjs_data'][0]['metro_stations'];
                                                                        $count = count($stations);
                                                                    @endphp

                                                                    <span class="metro-inline-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <circle cx="12" cy="12" r="9" fill="#0077b6"/>
  <text x="12" y="17" text-anchor="middle" font-family="Arial, sans-serif" font-weight="bold" font-size="13"
        fill="#fff">M</text>
</svg>


        </span>

                                                                    {{-- Цикл по индексам --}}
                                                                    @for ($k = 0; $k < $count; $k++)
                                                                        @php
                                                                            $s = $stations[$k];
                                                                            $dist = (float)($s['distance_km'] ?? 0);
                                                                            $name = $s['station_name'];
                                                                            $formattedDist = number_format($dist, 1);
                                                                        @endphp

                                                                        <span class="metro-station-item">
                {{ $name }} ({{ $formattedDist }} км)</span>
                                                                    @endfor
                                                                @endif
                                                            </div>
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
                                        @if(!empty($data[$i]['details_obj']['description']))
                                            <div class="bg-light p-4 rounded-10 shadow-sm">
                                                <p class="lead text-muted" @style(['font-size: 18px'])>
                                                    {{ $data[$i]['details_obj']['description'] }}
                                                </p>
                                            </div>
                                        @endif
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
