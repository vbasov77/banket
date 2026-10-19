@extends('layouts.app', ['title' => $category->name . ' ' . session('user_city'), 'metaDescription' => $metaDescription])

@section('content')
    <link href="{{ asset('css/carousel/carousel.css') }}" rel="stylesheet">
    <link href="{{ asset('css/details/details.css') }}" rel="stylesheet">
    <script src="{{asset('js/preloader/preloader.js')}}"></script>


    <section style="padding-bottom: 50px" class="section p-2">
        <div class="container-fluid d-flex justify-content-center">
            <div class="col-lg-11 col-12">
                <div class="row justify-content-center">
                    <div class="row align-items-center">
                        <h1 class="section-title display-5 fw-bold text-dark mb-3 nameBoom mt-4">
                            {{ $category->name }} - {!! session('user_city') !!}
                        </h1>
                    </div>

                    @if(!empty($data) && count($data) > 0)
                        @php
                            $countObj = count($data);
                        @endphp
                        @for($i = 0; $i < $countObj; $i++)
                            <div class="festival one">
                                <h3>{!! $data[$i]->name !!}</h3>
                                <div class="carousel-wrapper">
                                    <div class="carousel">
                                        <div class="carousel-content">
                                            @if(!empty($data[$i]->photos))
                                                @php
                                                    $dataImg = $data[$i]->photos;
                                                    $countImg = count($dataImg);
                                                @endphp
                                                @for ($j = 0; $j < $countImg; $j++)
                                                    <div class="carousel-item-wrapper">
                                                        <img src="{{$dataImg[$j]->small_img}}"
                                                             class="item-carousel"
                                                             alt="{{ $data[$i]->name }}">
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
                                                    <div class="detail">
                                                        @php
                                                            $minPrice = $data[$i]->min_price;
                                                        @endphp

                                                        @if ($minPrice !== null)
                                                            <span class="detail-label">Цена:</span>
                                                            <span class="text-danger fw-bold">
                                                                от {{ number_format($minPrice, 0, ',', ' ') }} ₽
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </section>
                                    </div>
                                    @php
                                        $categorySlug = $category->slug ?? $data[$i]->category->slug ?? '';
                                    @endphp
                                    <div class="col-12 col-sm-3 col-md-5 col-lg-7 d-flex align-items-center justify-content-center">
                                        <a style="width: auto"
                                           href="{{route('catalog.show', ['category' => $categorySlug, 'performer' => $data[$i]->id])}}"
                                           class="btn-festive-gradient btn-festive-gradient-blue front-btn m-3">
                                            Подробнее
                                        </a>
                                    </div>
                                </div>
                            </div>

                        @endfor
                    @else
                        Пока ничего не найдено
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{ $data->withQueryString()->links() }}


    <script src="{{ asset('js/carousels/carousel.js') }}" defer></script>
@endsection
