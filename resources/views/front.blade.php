
@extends('layouts.app', ['title' => "Банкетные залы, Кафе, Рестораны", 'metaDescription' => $metaDescription ?? null])

@section('content')


    <script src="{{asset('js/preloader/preloader.js')}}"></script>
    <style>
        /* ========== DETAILS ========== */
        .details {
            padding: 5px;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .details-title {
            margin: 0 0 12px 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }

        .details-info h3.details-title {
            margin-bottom: 4px;
        }

        .details-info {
            display: grid;
            gap: 8px;
            padding: 15px;
        }

        .detail {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .detail-label {
            color: #7f8c8d;
            font-weight: 500;
        }

        .detail-value {
            color: #2c3e50;
            font-weight: 500;
        }

        .price {
            color: #e74c3c;
            font-weight: 600;
            letter-spacing: -0.2px;
        }

        /* ========== LOCATION FLOW ========== */
        .location-flow {
            font-size: 13px;
            color: #555;
            line-height: 1.4;
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
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .metro-inline-icon {
            vertical-align: middle;
            flex-shrink: 0;
        }

        .metro-station-item {
            white-space: nowrap;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        /* ========== CAROUSEL ========== */
        .carousel-wrapper {
            position: relative;
            margin: 20px 0 0 0;
            max-width: 100%;
            width: 100%;
            overflow: hidden;
        }

        .carousel {
            display: block;
            overflow-x: auto;
            scroll-behavior: smooth;
            scrollbar-width: none;
            -ms-overflow-style: none;
            width: 100%;
            padding: 5px;
            box-sizing: border-box;
            -webkit-overflow-scrolling: touch;
        }

        .carousel::-webkit-scrollbar {
            height: 6px;
            background: #f1f1f1;
            border-radius: 3px;
        }

        .carousel::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .carousel-content {
            display: flex;
            flex-wrap: nowrap;
            gap: 16px;
            padding: 0 10px;
            margin: 0 auto;
            width: max-content;
        }

        .carousel-prev,
        .carousel-next {
            display: none;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            color: #333;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .carousel-prev { left: 15px; }
        .carousel-next { right: 15px; }

        .carousel-prev:hover,
        .carousel-next:hover {
            background: rgba(255, 255, 255, 1);
            transform: translateY(-50%) scale(1.1);
        }

        .carousel-prev.visible,
        .carousel-next.visible {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .carousel-prev:focus,
        .carousel-next:focus {
            outline: 2px solid #007bff;
            outline-offset: 2px;
        }

        /* ========== КАРТОЧКА В КАРУСЕЛИ ========== */
        .restaurant-card {
            flex: 0 0 380px;
            min-width: 0;
            max-width: 85vw;
            box-sizing: border-box;
            margin-bottom: 10px;
            overflow: hidden;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid #f0f0f0;
        }

        .restaurant-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .restaurant-image {
            overflow: hidden;
            border-radius: 8px 8px 0 0;
            background: #f8f9fa;
        }

        .restaurant-image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .carousel-item-wrapper {
            flex-shrink: 0;
            width: 380px;
            max-width: 85vw;
        }

        .item-carousel {
            display: block;
            width: 100%;
            height: 230px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
            transition: box-shadow 0.3s ease;
            cursor: pointer;
        }

        .item-carousel:hover {
            box-shadow: 0 5px 8px rgba(0, 0, 0, 0.1);
        }

        .one .carousel {
            height: auto;
        }

        /* ========== PAGE STYLES ========== */
        .dimmed-card {
            opacity: 0.6;
        }

        .descriptionBoom {
            padding: 20px;
        }

        .festival {
            border: 1px solid #4facf5;
        }

        .festival:hover {
            box-shadow: 0 0 0 rgba(0, 0, 0, 0);
            border-color: #4facf5;
        }

        .front-btn {
            float: right;
            position: relative;
        }

        /* ========== АДАПТИВ ========== */
        @media (max-width: 768px) {
            .carousel-content {
                gap: 12px;
                padding: 0 10px;
            }

            .restaurant-card {
                flex: 0 0 290px;
                max-width: 80vw;
            }

            .restaurant-image img {
                height: 180px;
            }

            .carousel-item-wrapper {
                width: 290px;
                max-width: 80vw;
            }

            .item-carousel {
                height: 248px;
            }

            .carousel-prev,
            .carousel-next {
                width: 35px;
                height: 35px;
                font-size: 16px;
            }

            .front-body {
                font-size: 16px;
            }
        }

        @media (max-width: 480px) {
            .carousel-content {
                gap: 8px;
                padding: 0 5px;
            }

            .restaurant-card {
                flex: 0 0 270px;
                max-width: 85vw;
            }

            .restaurant-image img {
                height: 160px;
            }

            .carousel-item-wrapper {
                width: 270px;
                max-width: 85vw;
            }

            .item-carousel {
                height: 210px;
            }

            .front-body {
                font-size: 14px;
            }

            .descriptionBoom {
                padding: 10px;
            }

            .carousel-wrapper {
                margin: 5px 0 0 0;
            }
        }
    </style>


    @include('blocks.search')
    @if(!empty($data) && count($data) > 0)
        <div class="relative w-full overflow-hidden flex items-center justify-center">
            <div class="absolute inset-0 bg-cover bg-center"
                 style="background-image: url('{{ asset('map/img/map.jpg') }}')"></div>
            <div class="absolute inset-0 bg-black/50"></div>
            <a href="{{ route('map.index') }}"
               class="btn-festive-gradient btn-festive-gradient-white m-3 z-10 px-6 py-3 rounded-lg font-bold text-white shadow-lg hover:scale-105 transition-transform"
               style="width: 70%">
                Смотреть на карте
            </a>
        </div>
    @endif

    @include('blocks.nav')
    <section style="padding-bottom: 50px" class="section">
        <div class="container-fluid d-flex justify-content-center">
            <div class="col-lg-11 col-12">
                <div class="row justify-content-center">

                    <div style="margin-top: 10px">
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
                                                            <div class="restaurant-card
    @if(!empty($arrayDistricts) && !in_array($data[$i]['subjs_data'][$j]['district_name'] ?? '', $arrayDistricts))
        dimmed-card
    @endif">
                                                                <a href="{{route('show.subj', ['id' => $data[$i]['subjs_data'][$j]['id']])}}">
                                                                    <div class="restaurant-image">
                                                                        <img src="{{$data[$i]['subjs_data'][$j]['image_paths'][0]}}"
                                                                             alt="{{ $data[$i]['subjs_data'][$j]['name_subj']}}">
                                                                    </div>
                                                                </a>
                                                                <section>
                                                                    <div class="details">
                                                                        <div class="details-info">
                                                                            <h3 class="details-title">{{ $data[$i]['subjs_data'][$j]['name_subj'] }}</h3>

                                                                            <div class="location-flow">
    <span class="district-text">
        🚩 {{ $data[$i]['subjs_data'][$j]['address'] ?? 'Адрес не указан' }}
    </span>
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

                                                                                        <span class="metro-station-item">{{ $name }} ({{ $formattedDist }} км)</span>
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
                                            <button class="carousel-prev">❮</button>
                                            <button class="carousel-next">❯</button>
                                        </div>
                                        @if(!empty($data[$i]['details_obj']['description']))
                                            <div class="bg-light rounded-10 shadow-sm descriptionBoom">
                                                <p class="lead text-muted">
                                                    {!!   $data[$i]['details_obj']['description'] !!}
                                                </p>
                                            </div>
                                        @endif
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
                                                                <div class="carousel-item-wrapper">
                                                                    <img src="{{$dataImg[$j]}}"
                                                                         class="item-carousel"
                                                                         alt="{{ $data[$i]['subjs_data'][0]['name_subj']}}">
                                                                </div>
                                                            @endfor
                                                        @endif
                                                    </div>
                                                </div>
                                                <button class="carousel-prev">❮</button>
                                                <button class="carousel-next">❯</button>
                                            </div>
                                            <div class="row">
                                                <div style="vertical-align: middle"
                                                     class="col-12 col-sm-9 col-md-7 col-lg-5">
                                                    <section>
                                                        <div class="details">
                                                            <div class="details-info">
                                                                <h3 class="details-title">{{ $data[$i]['subjs_data'][0]['name_subj']}}</h3>
                                                                <div class="location-flow">
                                                                    <span class="district-text">
                                                                        🚩 {{ $data[$i]['subjs_data'][0]['address'] ?? 'Адрес не указан' }}
                                                                    </span>
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
                                                                <div class="detail">
                                                                    <span class="detail-label">Вместимость:</span>
                                                                    <span class="detail-value">до {{ $data[$i]['subjs_data'][0]['capacity_to'] }} чел.</span>
                                                                </div>
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
                                                       class="btn-festive-gradient btn-festive-gradient-blue front-btn m-3">
                                                        Подробнее
                                                    </a>
                                                </div>
                                            </div>
                                            @if(!empty($data[$i]['details_obj']['description']))
                                                <div class="bg-light rounded-10 shadow-sm descriptionBoom">
                                                    <p class="lead text-muted front-body">
                                                        {{ $data[$i]['details_obj']['description'] }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            @endfor
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
                    </div>


                    @else
                        <center>К сожалению, ничего не найдено...</center>
                    @endif
                </div>
            </div>

        </div>
    </section>

    <script src="{{ asset('js/carousels/carousel.js') }}" defer></script>
@endsection