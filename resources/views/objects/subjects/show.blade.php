@extends('layouts.app', ['title' => $subj['name_subj'], 'metaDescription' => $metaDescription])

@push('styles')

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

        #phone-display {
            font-size: 23px;
        }

        .restaurant-image {
            min-width: 350px;
        }

        .related .restaurant-image {
            height: 230px;
        }

        h3.section-title {
            font-size: 30px;
        }

        @media (max-width: 768px) {
            .parallax-container {
                height: 50vh;
            }

            .parallax-title {
                font-size: clamp(24px, 10vw, 80px);
            }

            .obj-features-table th,
            .obj-features-table td {
                display: block;
                width: 100% !important;
            }

            .obj-features-table th {
                padding-bottom: 0;
                font-size: 13px;
                color: #888;
            }

            .obj-features-table td {
                padding-top: 2px;
                padding-bottom: 12px;
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

            .nameBoom {
                font-size: 14px;
            }

            h3.section-title {
                font-size: 20px;
            }
        }
    </style>
@endpush

@section('content')

    @if (!empty($subj['image_paths']) && count($subj['image_paths']) > 0)
        <div class="parallax-container">
            <div class="parallax-bg" style="background-image: url('{{ $subj['image_paths'][0] }}');"></div>
            <div class="parallax-content">
                <div class="parallax-title-center">
                    <h1 class="parallax-title">{!! $subj['obj']['name_obj'] !!}</h1>
                </div>
            </div>
        </div>
    @endif

    <div style="padding-bottom: 50px" class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-11 col-md-12">
                <!-- Hero section -->
                <div class="row align-items-center">
                    @include('blocks.favorite')
                    <h1 class="section-title display-5 fw-bold text-dark mb-3 nameBoom">
                        {{ $subj['name_subj'] }}
                    </h1>
                </div>

                <section class="mb-5">
                    @auth
                        @if($isAuthorOrAdmin)
                            <div style="margin-bottom: 10px; margin-top: 10px; width: auto; float: right">
                                @if($subj['published'])
                                    <span class="badge bg-success fs-5 px-4 py-2">Опубликовано</span>
                                @else
                                    <span class="badge bg-warning text-dark fs-5 px-4 py-2">Не опубликовано</span>
                                @endif
                            </div>
                        @endif
                    @endauth
                </section>

                @if(!empty($subj['image_paths']))
                    <div class="carousel-wrapper">
                        <div class="carousel">
                            <div class="carousel-content">
                                @foreach ($subj['image_paths'] as $index => $image)
                                    <img
                                            class="item-carousel"
                                            src="{{ $image }}"
                                            alt="{{ $subj['name_subj'] }}"
                                            data-index="{{ $index }}"
                                            width="350" height="233"
                                            data-big-image="{{ $subj['big_image_paths'][$index] ?? $image }}">
                                @endforeach
                            </div>
                        </div>
                        <button class="carousel-prev">❮</button>
                        <button class="carousel-next">❯</button>
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
                                            <span class="detail-value">до {{ $subj['capacity_to'] }} чел</span>
                                        </div>
                                        @if(!empty($subj['furshet']))
                                            <div class="detail">
                                                <span class="detail-label">На фуршет:</span>
                                                <span class="detail-value">до {{ $subj['furshet'] }} чел</span>
                                            </div>
                                        @endif
                                        <div class="detail">
                                            <span class="detail-label">На человека:</span>
                                            <span class="detail-value">от {{ number_format($subj['per_person'], 0, ' ', ' ') }} ₽/чел</span>
                                        </div>
                                        @if($subj['minimum_cost'] != 0)
                                            <div class="detail">
                                                <span class="detail-label">Стоимость:</span>
                                                <span class="detail-value price">от {{ number_format($subj['minimum_cost'], 0, ' ', ' ') }} ₽</span>
                                            </div>
                                        @endif
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
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="12" r="9" fill="#0077b6"/>
                                            <text x="12" y="17" text-anchor="middle" font-family="Arial, sans-serif"
                                                  font-weight="bold" font-size="13" fill="#fff">M</text>
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

                            Адрес: {{ $subj['address'] ?? 'Адрес не указан' }}

                            <br>

                            <div class="mt-2">
                                <span id="phone-display"
                                      class="cursor-pointer"
                                      data-phone="{{ $subj['obj']['phone_obj'] ?? '' }}"
                                      title="Нажмите, чтобы показать телефон">
                                    +7(***) Показать телефон
                                </span>
                            </div>

                            <div class="d-flex flex-column align-items-center justify-content-center text-center mt-4">
                                @if($subj['map'])
                                    <div class="p-3">
                                        <a href="{{ route('show.map', ['id' => $subj['subj_id']]) }}"
                                           id="map"
                                           class="btn-festive-gradient btn-festive-gradient-white">
                                            Смотреть карту
                                        </a>
                                    </div>
                                @else
                                    @auth
                                        @if($isAuthorOrAdmin)
                                            <br>
                                            <a href="{{ route('map.create', ['id' => $subj['subj_id']]) }}"
                                               id="map"
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
                {{-- Акции --}}
                @if(!empty($subj['actions']))
                    <div class="row mb-5">
                        <div class="col-12">
                            <div class="bg-light p-4 rounded-10 shadow-sm">
                                <h4 class="section-title mb-4">
                                    <i class="bi bi-gift text-danger me-2"></i>Акции и спецпредложения
                                </h4>
                                <div class="d-flex flex-column gap-3">
                                    @foreach($subj['actions'] as $action)
                                        <div class="p-3 bg-white rounded border">
                                            <p class="mb-0 text-muted" style="line-height: 1.6;">
                                                {!! nl2br(e(preg_replace('/\n{2,}/', "\n", str_replace(["\r\n", "\r"], "\n", $action['actions'])))) !!}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @auth
                    @if($isAuthorOrAdmin)
                        @if(!empty($subj['actions']))
                            <a href="{{ route('action.edit', ['obj' => $subj['obj']['obj_id'],
'subj' => $subj['subj_id']]) }}"
                               class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-pencil"></i> Редактировать акцию
                            </a>
                        @else
                            <a href="{{ route('action.create', ['obj' => $subj['obj']['obj_id']]) }}"
                               class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-gift"></i> Добавить акцию
                            </a>
                        @endif
                    @endif
                @endauth

                <hr class="my-5">

                <section class="mt-5">
                    <h3 class="section-title fs-4 mb-4">Общая информация "{!! $subj['obj']['name_obj'] !!}"</h3>
                </section>

                @if(!empty($subj['details_obj']))
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                                @php
                                    $sections = [
                                        ['title' => 'Тип площадки:', 'data' => $subj['site_type'] ?? []],
                                        ['title' => 'Подходит для:', 'data' => $subj['details_obj']['for_events'] ?? []],
                                        ['title' => 'Кухня:', 'data' => $subj['details_obj']['kitchen'] ?? []],
                                        ['title' => 'Способы оплаты:', 'data' => $subj['details_obj']['payment_methods'] ?? []],
                                        ['title' => 'Можно принести с собой:', 'data' => $subj['details_obj']['bring_with_you'] ?? []],
                                    ];
                                @endphp

                                @foreach($sections as $section)
                                    <div class="col">
                                        <div class="p-3 bg-light rounded h-100">
                                            <h5 class="fw-semibold mb-3">{{ $section['title'] }}</h5>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($section['data'] as $item)
                                                    @if (!empty($item))
                                                        <span class="feature-badge bg-white border rounded px-2 py-1">{{ $item }}</span>
                                                    @endif
                                                @endforeach

                                                @if (empty($section['data']) || (is_array($section['data']) && count($section['data']) === 0))
                                                    <span class="text-muted small">Не указано</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                {{-- Алкоголь --}}
                                @php
                                    $alcohol = $subj['details_obj']['alcohol'] ?? null;
                                    $alcoholParts = $alcohol !== null ? explode(':', (string)$alcohol) : [];
                                @endphp
                                <div class="col">
                                    <div class="p-3 bg-light rounded h-100">
                                        <h5 class="fw-semibold mb-3">
                                            <i class="bi bi-wine text-danger me-2"></i>
                                            Алкоголь:
                                        </h5>
                                        @if($alcohol === 0 || $alcohol === '0')
                                            <span class="badge bg-success bg-gradient">Разрешено</span>
                                        @elseif($alcohol === 1 || $alcohol === '1')
                                            <span class="badge bg-danger bg-gradient">Не разрешено</span>
                                        @elseif(($alcoholParts[0] ?? '') === '2')
                                            <span class="badge bg-success bg-gradient">Разрешено за определённую плату</span>
                                            <br>
                                            <span>{{ $alcoholParts[1] ?? '' }} руб.</span>
                                        @endif
                                    </div>
                                </div>

                                @if($subj['details_obj']['service_fee'])
                                    <div class="col">
                                        <div class="p-3 bg-light rounded h-100">
                                            <h5 class="fw-semibold mb-3">
                                                <i class="bi bi-receipt text-danger me-2"></i>
                                                Сервисный сбор:
                                            </h5>
                                            <div>
                                                <h4>{!! $subj['details_obj']['service_fee'] !!} %</h4>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if(!empty($subj['obj_features']))
                                <div class="row justify-content-center">
                                    <div class="col-lg-10 col-md-11 col-sm-12">
                                        <div class="card mt-4">
                                            <div class="card-header">
                                                <h5 class="mb-0">Особенности объекта</h5>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-borderless obj-features-table">
                                                    <tbody>
                                                    @if(!empty($subj['obj_features']['banquet_note']))
                                                        <tr>
                                                            <th style="width: 30%">Примечание</th>
                                                            <td>{{ $subj['obj_features']['banquet_note'] }}</td>
                                                        </tr>
                                                    @endif

                                                    @if(!empty($subj['obj_features']['prepayment']))
                                                        <tr>
                                                            <th>Предоплата</th>
                                                            <td>{{ $subj['obj_features']['prepayment'] }}</td>
                                                        </tr>
                                                    @endif

                                                    @if(!empty($subj['obj_features']['textile_package']))
                                                        <tr>
                                                            <th>Текстильный пакет</th>
                                                            <td>
                                                                @if(is_array($subj['obj_features']['textile_package']))
                                                                    {{ implode(', ', $subj['obj_features']['textile_package']) }}
                                                                @else
                                                                    {{ $subj['obj_features']['textile_package'] }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endif

                                                    @if(!empty($subj['obj_features']['textile_colors']))
                                                        <tr>
                                                            <th>Расцветки текстиля</th>
                                                            <td>{{ $subj['obj_features']['textile_colors'] }}</td>
                                                        </tr>
                                                    @endif

                                                    @if(!empty($subj['obj_features']['tables']))
                                                        <tr>
                                                            <th>Столы</th>
                                                            <td>
                                                                @if(is_array($subj['obj_features']['tables']))
                                                                    {{ implode(', ', $subj['obj_features']['tables']) }}
                                                                @else
                                                                    {{ $subj['obj_features']['tables'] }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endif

                                                    @if(!empty($subj['obj_features']['loud_music']))
                                                        <tr>
                                                            <th>Громкая музыка до</th>
                                                            <td>{{ $subj['obj_features']['loud_music'] }}</td>
                                                        </tr>
                                                    @endif

                                                    @if(!empty($subj['obj_features']['parking']))
                                                        <tr>
                                                            <th>Парковка</th>
                                                            <td>{{ $subj['obj_features']['parking'] }}</td>
                                                        </tr>
                                                    @endif

                                                    @if(!empty($subj['obj_features']['pier']))
                                                        <tr>
                                                            <th>Причал</th>
                                                            <td>{{ $subj['obj_features']['pier'] }}</td>
                                                        </tr>
                                                    @endif

                                                    @if(!empty($subj['obj_features']['interior']))
                                                        <tr>
                                                            <th>Интерьер</th>
                                                            <td>{{ $subj['obj_features']['interior'] }}</td>
                                                        </tr>
                                                    @endif

                                                    @if(!empty($subj['obj_features']['location']))
                                                        <tr>
                                                            <th>Месторасположение</th>
                                                            <td>
                                                                @if(is_array($subj['obj_features']['location']))
                                                                    {{ implode(', ', $subj['obj_features']['location']) }}
                                                                @else
                                                                    {{ $subj['obj_features']['location'] }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endif

                                                    @if(!empty($subj['obj_features']['equipment']))
                                                        <tr>
                                                            <th>Оборудование</th>
                                                            <td>
                                                                @if(is_array($subj['obj_features']['equipment']))
                                                                    {{ implode(', ', $subj['obj_features']['equipment']) }}
                                                                @else
                                                                    {{ $subj['obj_features']['equipment'] }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endif

                                                    @if(!empty($subj['obj_features']['kids']))
                                                        <tr>
                                                            <th>Для детей</th>
                                                            <td>
                                                                @if(is_array($subj['obj_features']['kids']))
                                                                    {{ implode(', ', $subj['obj_features']['kids']) }}
                                                                @else
                                                                    {{ $subj['obj_features']['kids'] }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div style="margin-top: 40px" class="col-md-12 mb-5">
                                <h5 class="fw-semibold mb-3">
                                    <i class="bi bi-text-paragraph text-danger me-2"></i>Описание:
                                </h5>
                                <div class="bg-light p-4 rounded-10 shadow-sm">
                                    <p class="lead text-muted">
                                        {!! nl2br(e($subj['details_obj']['text_obj'])) !!}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <section>
                    @if(!empty($subj['related_subjs']))
                        <h3 class="section-title">Ещё залы {!! $subj['obj']['name_obj'] !!}</h3>
                        <div class="carousel-wrapper moreSubj @if(count($subj['related_subjs']) > 2)festival @endif">
                            <div class="carousel">
                                <div class="carousel-content">
                                    @php($countSubj = count($subj['related_subjs']))
                                    @for ($j = 0; $j < $countSubj; $j++)
                                        <div class="restaurant-card">
                                            <div class="related item-carousel">
                                                <a href="{{ route('show.subj', ['id' => $subj['related_subjs'][$j]['subj_id']]) }}">
                                                    <img class="restaurant-image"
                                                         src="{{ $subj['related_subjs'][$j]['image_path'] }}"
                                                         alt="{{ $subj['related_subjs'][$j]['name_subj'] }}"
                                                         width="350" height="auto">
                                                </a>
                                                <div class="details">
                                                    <h5 class="card-title fw-bold mb-3">{{ \Illuminate\Support\Str::limit($subj['related_subjs'][$j]['name_subj'], 30) }}</h5>
                                                    <div class="details-info">
                                                        <div class="detail">
                                                            <span class="detail-label">Вместимость:</span>
                                                            <span class="detail-value">
                                                                до {{ $subj['related_subjs'][$j]['capacity_to'] }} чел.
                                                            </span>
                                                        </div>
                                                        @if($subj['related_subjs'][$j]['minimum_cost'] != 0)
                                                            <div class="detail">
                                                                <span class="detail-label">Цена:</span>
                                                                <span class="detail-value price">
                                                                    {{ number_format($subj['related_subjs'][$j]['minimum_cost'], 0, ' ', ' ') }} ₽
                                                                </span>
                                                            </div>
                                                        @endif
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
                        @if($isAuthorOrAdmin)
                            <div>
                                <button class="btn-festive-gradient btn-festive-gradient-green"
                                        style="margin-top: 25px; margin-bottom: 50px"
                                        onclick="window.location.href = '{{ route('edit.subj', ['id' => $subj['subj_id']]) }}'">
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

    <!-- Модальный лайтбокс для мобильных -->
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

    @auth
        <script>
            window.favStore = '{{ route("favorites_subj.store", ["id" => $subj["subj_id"]]) }}';
            window.favDestroy = '{{ route("favorites_subj.destroy", ["id" => $subj["subj_id"]]) }}';

        </script>
    @endauth
    <script>
        window.clickPhone = '{{ route('click_phone.store', ['id' => $subj['subj_id']]) }}';
    </script>
    <script>
        document.getElementById('phone-display')?.addEventListener('click', function () {
            const phone = this.dataset.phone;
            this.textContent = phone || 'Телефон не указан';
            this.classList.remove('text-primary');
            // отправляем клик в бэк
            fetch(window.clickPhone, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                keepalive: true
            }).catch(() => {});
        });
    </script>

    <script src="{{ asset('js/parallax/parallax.js') }}" defer></script>
    <script src="{{ asset('js/carousels/carousel.js') }}" defer></script>
    <script src="{{ asset('js/lightbox/lightbox.js') }}" defer></script>

@endsection
