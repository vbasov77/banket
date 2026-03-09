@extends('layouts.app', ['title' => "Банкетные залы"])
@section('content')

    <link href="{{ asset('css/carousel/carousel.css') }}" rel="stylesheet">
    <script src="{{asset('js/preloader/preloader.js')}}"></script>
    <style>
        .carousel {
            height: 390px;
        }

        /* Блок деталей */

        .one .carousel {
            height: auto;
        }


        .front-btn{
            float: right; position: relative;
        }
    </style>

    <style>
        @media (max-width: 768px) {
            /* Скрываем оригинальную кнопку внутри блока */
        }


    </style>


    <link href="{{ asset('css/details/details.css') }}" rel="stylesheet">
    @include('blocks.nav')
    <section style="padding-bottom: 50px" class="section">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div style="margin-top: 60px" class="col-lg-11 col-md-11 col-sm-12">
                    @if(!empty($data) && count($data) > 0)
                        @php($counter = 0)
                        @php($countObj = count($data))
                        @for($i = 0; $i < $countObj; $i++)
                            @php($countSubj = count($data[$i]['subjs_data']))
                            @if($countSubj > 1)
                                <div class="festival">
                                    <h3>{!! $data[$i]['name_obj'] !!}</h3>
                                    <div class="carousel-wrapper">
                                        <div class="carousel">
                                            <div class="carousel-content">
                                                @if(!empty($data[$i]['subjs_data']))
                                                    @php($subjData = $data[$i]['subjs_data'])
                                                    @for ($j = 0; $j < $countSubj; $j++)
                                                        <div class="festival"
                                                             style="display: block; margin-bottom: 10px">
                                                            <a href="{{route('show.subj', ['id' => $data[$i]['subjs_data'][$j]['id']])}}">
                                                                <img src="{{$data[$i]['subjs_data'][$j]['path'] . '&cs=360x0'}}"
                                                                     class="item-carousel"
                                                                     alt="{{ $data[$i]['subjs_data'][$j]['name_subj']}}">
                                                                <br>
                                                            </a>

                                                            <section>
                                                                <div class="details">
                                                                    <div class="details-info">
                                                                        <h3 class="details-title">{{ $data[$i]['subjs_data'][$j]['name_subj']}}</h3>
                                                                        <!-- Вместимость в одной строке -->
                                                                        <div class="detail">
                                                                            <span class="detail-label">Вместимость:</span>
                                                                            <span class="detail-value">до {{ $data[$i]['subjs_data'][$j]['capacity_to'] }} чел.</span>
                                                                        </div>

                                                                        <!-- Цена в одной строке (уже правильно обёрнута) -->
                                                                        <div class="detail">
                                                                            <span class="detail-label">Цена:</span>
                                                                            <span class="detail-value price">
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
                                </div>
                            @else
                                @if($countSubj)
                                    <div class="festival one">
                                        <h3>{!! $data[$i]['name_obj'] !!}</h3>
                                        <div class="carousel-wrapper">
                                            <div class="carousel">
                                                <div class="carousel-content">
                                                    @if(!empty($data[$i]['subjs_data']))
                                                        @php($dataImg = $data[$i]['subjs_data'][0]['image_paths'])
                                                        @php($countImg = count($dataImg))
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
                                            <div style="vertical-align: middle" class="col-12 col-sm-9 col-md-7 col-lg-5">
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
                                            <div style="vertical-align: middle" class="col-12 col-sm-3 col-md-5 col-lg-7">
                                                <!-- position-relative + text-start -->
                                                <a href="{{route('show.subj', ['id' => $data[$i]['subjs_data'][0]['id']])}}"
                                                   class="btn-festive-gradient btn-festive-gradient-green front-btn">
                                                    Подробнее
                                                </a>
                                            </div>
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
