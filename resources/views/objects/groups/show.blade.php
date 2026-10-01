@extends('layouts.app', ['title' => $group['obj']['name_obj'], 'metaDescription' => $metaDescription])

@section('content')

    <link href="{{ asset('css/parallax/parallax.css') }}" rel="stylesheet">
    <link href="{{ asset('css/details/details.css') }}" rel="stylesheet">
    <link href="{{ asset('css/subj/card_subj.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('map/leaflet/css/leaflet.css') }}"/>
    <script src="{{ asset('map/leaflet/js/leaflet.js') }}" defer></script>
    <link href="{{ asset('css/carousel/carousel.css') }}" rel="stylesheet">

    <style>
        .carousel-wrapper {
            position: relative;
            overflow: hidden;
        }
        .carousel {
            overflow-x: auto;
            scroll-behavior: smooth;
            scrollbar-width: none;
        }
        .carousel::-webkit-scrollbar { display: none; }
        .carousel-content {
            display: flex;
            gap: 10px;
            padding-bottom: 10px;
        }
        .item-carousel {
            width: 360px;
            height: 240px;
            object-fit: cover;
            border-radius: 8px;
            flex-shrink: 0;
        }
        .carousel-prev, .carousel-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.9);
            border: 1px solid #ddd;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            font-size: 18px;
            cursor: pointer;
            z-index: 10;
            transition: background 0.2s;
        }
        .carousel-prev { left: 5px; }
        .carousel-next { right: 5px; }
        .carousel-prev:hover, .carousel-next:hover {
            background: rgba(255,255,255,1);
        }
        .metro-station-item {
            font-size: 13px;
            color: #555;
            margin-right: 4px;
        }
        .metro-inline-icon {
            display: inline-flex;
            align-items: center;
            margin-right: 2px;
        }
        .related-subj-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .related-subj-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        .action-badge {
            background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 14px;
            display: inline-block;
            margin-bottom: 6px;
        }
        @media (max-width: 768px) {
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
            .item-carousel {
                width: 280px;
                height: 190px;
            }
        }
        @media (max-width: 480px) {
            .text-muted { font-size: 16px; }
        }
    </style>

    {{-- Параллакс с первой картинкой первого зала --}}
    @if (!empty($subjs[0]['image_paths']) && count($subjs[0]['image_paths']) > 0)
        <div class="parallax-container">
            <div class="parallax-bg" style="background-image: url('{{ $subjs[0]['big_image_paths'][0] ?? $subjs[0]['image_paths'][0] }}');"></div>
            <div class="parallax-content">
                <div class="parallax-title-center">
                    <h1 class="parallax-title">{!! $group['obj']['name_obj'] !!}</h1>
                </div>
            </div>
        </div>
    @endif

    <div style="padding-bottom: 50px" class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-11 col-sm-12">

                {{-- Заголовок объекта --}}
                <div class="row">
                    <section class="p-4 mb-5">
                        <div class="row align-items-center">
                            <h1 class="section-title display-5 fw-bold text-dark mb-3">
                                {{ $group['obj']['name_obj'] }}
                            </h1>
                        </div>
                    </section>
                </div>

                {{-- Залы с каруселью --}}
                @if($subjs)
                    @foreach($subjs as $subj)
                        <div class="festival one mb-5">
                            <h3>{{ $subj['name_subj'] }}</h3>

                            {{-- Карусель фотографий --}}
                            <div class="carousel-wrapper">
                                <div class="carousel" id="carousel-{{ $subj['id'] }}">
                                    <div class="carousel-content">
                                        @if(!empty($subj['image_paths']))
                                            @php
                                                $images = $subj['image_paths'] ?? $subj['big_image_paths'];
                                                $countImg = count($images);
                                            @endphp
                                            @for ($j = 0; $j < $countImg; $j++)
                                                <div style="display: block; margin-bottom: 10px">
                                                    <img src="{{ $images[$j] }}"
                                                         class="item-carousel"
                                                         alt="{{ $subj['name_subj'] }}"
                                                         >
                                                </div>
                                            @endfor
                                        @else
                                            <div style="display: block; margin-bottom: 10px">
                                                <img src="{{ asset('images/no_image/no_image.jpg') }}"
                                                     class="item-carousel"
                                                     alt="Нет фото">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @if(!empty($subj['image_paths']) && count($subj['image_paths']) > 1)
                                    <button class="carousel-prev" onclick="scrollCarousel('carousel-{{ $subj['id'] }}', -1)">❮</button>
                                    <button class="carousel-next" onclick="scrollCarousel('carousel-{{ $subj['id'] }}', 1)">❯</button>
                                @endif
                            </div>

                            {{-- Информация о зале --}}
                            <div class="row mt-3">
                                <div style="vertical-align: middle" class="col-12 col-sm-9 col-md-7 col-lg-5">
                                    <section>
                                        <div class="details">
                                            <div class="details-info">
                                                <h3 class="details-title">{{ $subj['name_subj'] }}</h3>

                                                {{-- Адрес и район --}}
                                                <div class="location-flow">
                                                    🚩 {{ $group['address'] ?? 'Адрес не указан' }}
                                                    <span class="district-text">
                                                        📍 {{ $group['district_name'] ?? 'Район не указан' }}
                                                    </span>
                                                </div>

                                                {{-- Метро --}}
                                                @if (!empty($subj['nearest_metros']))
                                                    @php
                                                        $metros = $subj['nearest_metros'];
                                                        usort($metros, fn($a, $b) => ($a['rank'] ?? 0) <=> ($b['rank'] ?? 0));
                                                        $metros = array_slice($metros, 0, 3);
                                                    @endphp
                                                    <div class="location-flow mt-1">
                                                        <span class="metro-inline-icon">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <circle cx="12" cy="12" r="9" fill="#0077b6"/>
                                                                <text x="12" y="17" text-anchor="middle" font-family="Arial, sans-serif" font-weight="bold" font-size="13" fill="#fff">M</text>
                                                            </svg>
                                                        </span>
                                                        @foreach($metros as $m)
                                                            @php
                                                                $dist = (float)($m['distance_km'] ?? 0);
                                                                $formattedDist = number_format($dist, 1);
                                                            @endphp
                                                            <span class="metro-station-item">
                                                                {{ $m['station_name'] }} ({{ $formattedDist }} км)
                                                            </span>
                                                            @if(!$loop->last)
                                                                <span class="text-muted">·</span>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif

                                                {{-- Типы площадки --}}
                                                @if(!empty($subj['site_type']))
                                                    <div class="mt-2">
                                                        @foreach($subj['site_type'] as $type)
                                                            <span class="badge bg-light text-dark border me-1">{{ $type }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                {{-- Вместимость --}}
                                                <div class="detail mt-2">
                                                    <span class="detail-label">Вместимость:</span>
                                                    <span class="detail-value">до {{ $subj['capacity_to'] }} чел.</span>
                                                </div>

                                                {{-- Фуршет --}}
                                                @if(!empty($subj['furshet']))
                                                    <div class="detail">
                                                        <span class="detail-label">Фуршет:</span>
                                                        <span class="detail-value">до {{ $subj['furshet'] }} чел.</span>
                                                    </div>
                                                @endif

                                                {{-- Цена --}}
                                                @if(!empty($subj['per_person']))
                                                    <div class="detail">
                                                        <span class="detail-label">От:</span>
                                                        <span class="detail-value price">
                                                            {{ number_format($subj['per_person'], 0, ' ', ' ') }} ₽ / чел.
                                                        </span>
                                                    </div>
                                                @endif

                                                {{-- Минимальная стоимость --}}
                                                @if(!empty($subj['minimum_cost']))
                                                    <div class="detail">
                                                        <span class="detail-label">Мин. чек:</span>
                                                        <span class="detail-value price">
                                                            {{ number_format($subj['minimum_cost'], 0, ' ', ' ') }} ₽
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </section>
                                </div>

                                {{-- Кнопка --}}
                                <div class="col-12 col-sm-3 col-md-5 col-lg-7 d-flex align-items-center justify-content-center">
                                    <a style="width: auto"
                                       href="{{ route('show.subj', ['id' => $subj['id']]) }}"
                                       class="btn-festive-gradient btn-festive-gradient-blue front-btn m-3">
                                        Подробнее
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- Акции объекта --}}
                @if(!empty($group['actions']))
                    <div class="row" style="margin-top: 30px; margin-bottom: 30px">
                        <div class="col-12">
                            <h3 class="fw-bold text-dark mb-3">
                                <i class="bi bi-megaphone text-danger me-2"></i>Акции
                            </h3>
                            <div class="row g-3">
                                @foreach($group['actions'] as $action)
                                    <div class="col-12">
                                        <div class="p-3 bg-light rounded-10 shadow-sm">
                                            <span class="action-badge">
                                                <i class="bi bi-tag me-1"></i>Акция
                                            </span>
                                            <p class="mb-0 mt-2">{!! nl2br(e($action['actions'])) !!}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Детали объекта: услуги, кухня, оплата --}}
                @if(!empty($group['details_obj']))
                    <div class="row" style="margin-top: 50px; margin-bottom: 50px">
                        <div class="col-12">
                            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                                @php
                                    $sections = [
                                        ['title' => 'Подходит для:', 'data' => $group['details_obj']['for_events'] ?? []],
                                        ['title' => 'Кухня:', 'data' => $group['details_obj']['kitchen'] ?? []],
                                        ['title' => 'Способы оплаты:', 'data' => $group['details_obj']['payment_methods'] ?? []],
                                        ['title' => 'Можно принести с собой:', 'data' => $group['details_obj']['bring_with_you'] ?? []],
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
                                                @if (empty($section['data']))
                                                    <span class="text-muted small">Не указано</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                {{-- Сервис --}}
                                @if(!empty($group['details_obj']['service']))
                                    <div class="col">
                                        <div class="p-3 bg-light rounded h-100">
                                            <h5 class="fw-semibold mb-3">Сервис:</h5>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($group['details_obj']['service'] as $item)
                                                    @if(!empty($item))
                                                        <span class="feature-badge bg-white border rounded px-2 py-1">{{ $item }}</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Алкоголь --}}
                                <div class="col">
                                    <div class="p-3 bg-light rounded h-100">
                                        <h5 class="fw-semibold mb-3">
                                            <i class="bi bi-wine text-danger me-2"></i>Алкоголь:
                                        </h5>
                                        @if($group['details_obj']['alcohol'] == 0)
                                            <span class="badge bg-success bg-gradient">Разрешено</span>
                                        @elseif($group['details_obj']['alcohol'] == 1)
                                            <span class="badge bg-danger bg-gradient">Не разрешено</span>
                                        @elseif(!empty(explode(':', $group['details_obj']['alcohol'])[0]) == 2)
                                            <span class="badge bg-success bg-gradient">Разрешено за плату</span><br>
                                            <span>{!! explode(':', $group['details_obj']['alcohol'])[1] !!} руб.</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Особенности объекта --}}
                @if(!empty($group['obj_features']))
                    <div class="row justify-content-center" style="margin-top: 50px; margin-bottom: 50px">
                        <div class="col-lg-10 col-md-11 col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Особенности объекта</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless obj-features-table">
                                        <tbody>
                                        @if(!empty($group['obj_features']['banquet_note']))
                                            <tr><th style="width: 30%">Примечание</th><td>{{ $group['obj_features']['banquet_note'] }}</td></tr>
                                        @endif
                                        @if(!empty($group['obj_features']['prepayment']))
                                            <tr><th>Предоплата</th><td>{{ $group['obj_features']['prepayment'] }}</td></tr>
                                        @endif
                                        @if(!empty($group['obj_features']['textile_package']))
                                            <tr><th>Текстильный пакет</th>
                                                <td>
                                                    @if(is_array($group['obj_features']['textile_package']))
                                                        {{ implode(', ', $group['obj_features']['textile_package']) }}
                                                    @else
                                                        {{ $group['obj_features']['textile_package'] }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                        @if(!empty($group['obj_features']['textile_colors']))
                                            <tr><th>Расцветки текстиля</th><td>{{ $group['obj_features']['textile_colors'] }}</td></tr>
                                        @endif
                                        @if(!empty($group['obj_features']['tables']))
                                            <tr><th>Столы</th>
                                                <td>
                                                    @if(is_array($group['obj_features']['tables']))
                                                        {{ implode(', ', $group['obj_features']['tables']) }}
                                                    @else
                                                        {{ $group['obj_features']['tables'] }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                        @if(!empty($group['obj_features']['loud_music']))
                                            <tr><th>Громкая музыка до</th><td>{{ $group['obj_features']['loud_music'] }}</td></tr>
                                        @endif
                                        @if(!empty($group['obj_features']['parking']))
                                            <tr><th>Парковка</th><td>{{ $group['obj_features']['parking'] }}</td></tr>
                                        @endif
                                        @if(!empty($group['obj_features']['pier']))
                                            <tr><th>Причал</th><td>{{ $group['obj_features']['pier'] }}</td></tr>
                                        @endif
                                        @if(!empty($group['obj_features']['interior']))
                                            <tr><th>Интерьер</th><td>{{ $group['obj_features']['interior'] }}</td></tr>
                                        @endif
                                        @if(!empty($group['obj_features']['location']))
                                            <tr><th>Месторасположение</th>
                                                <td>
                                                    @if(is_array($group['obj_features']['location']))
                                                        {{ implode(', ', $group['obj_features']['location']) }}
                                                    @else
                                                        {{ $group['obj_features']['location'] }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>

                                    <div class="row">
                                        <div class="col-lg-8 col-12">
                                            @php
                                                $equipLabels = [
                                                    'own_equipment'    => 'Своё оборудование',
                                                    'projector_screen' => 'Проекторный экран',
                                                    'music_stage'      => 'Музыкальная сцена',
                                                    'sound_equipment'  => 'Звуковое оборудование',
                                                    'light_equipment'  => 'Световое оборудование',
                                                    'dimming_system'   => 'Система затемнения',
                                                    'karaoke'          => 'Караоке',
                                                    'wifi'             => 'Wi-Fi',
                                                    'dance_floor'      => 'Танцпол',
                                                    'air_conditioner'  => 'Кондиционер',
                                                    'wardrobe'         => 'Гардероб',
                                                    'dressing_rooms'   => 'Гримёрки для артистов',
                                                ];
                                                $equipment = !empty($group['obj_features']['equipment'])
                                                    ? (is_string($group['obj_features']['equipment'])
                                                        ? json_decode($group['obj_features']['equipment'], true)
                                                        : $group['obj_features']['equipment'])
                                                    : [];
                                            @endphp

                                            @if(!empty($equipment))
                                                <h6 class="mt-3 mb-2">Оборудование</h6>
                                                <table class="table table-borderless table-sm">
                                                    <tbody>
                                                    @foreach($equipLabels as $key => $label)
                                                        @if(!empty($equipment[$key]) && is_string($equipment[$key]))
                                                            <tr><th style="width: 30%">{{ $label }}</th><td>{{ $equipment[$key] }}</td></tr>
                                                        @endif
                                                    @endforeach
                                                    @foreach($equipment as $key => $val)
                                                        @if(is_int($key) && is_string($val) && isset($equipLabels[$val]))
                                                            <tr><th style="width: 30%">{{ $equipLabels[$val] }}</th><td>в наличии</td></tr>
                                                        @endif
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            @endif

                                            @php
                                                $kidsLabels = [
                                                    'kids_room'   => 'Детская комната',
                                                    'kids_menu'   => 'Детское меню',
                                                    'kids_corner' => 'Детский уголок',
                                                ];
                                                $kids = !empty($group['obj_features']['kids'])
                                                    ? (is_string($group['obj_features']['kids'])
                                                        ? json_decode($group['obj_features']['kids'], true)
                                                        : $group['obj_features']['kids'])
                                                    : [];
                                            @endphp

                                            @if(!empty($kids))
                                                <h6 class="mt-3 mb-2">Для детей</h6>
                                                <table class="table table-borderless table-sm">
                                                    <tbody>
                                                    @foreach($kidsLabels as $key => $label)
                                                        @if(!empty($kids[$key]) && is_string($kids[$key]))
                                                            <tr><th style="width: 30%">{{ $label }}</th><td>{{ $kids[$key] }}</td></tr>
                                                        @endif
                                                    @endforeach
                                                    @foreach($kids as $key => $val)
                                                        @if(is_int($key) && is_string($val) && isset($kidsLabels[$val]))
                                                            <tr><th style="width: 30%">{{ $kidsLabels[$val] }}</th><td>в наличии</td></tr>
                                                        @endif
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Описание --}}
                @if(!empty($group['details_obj']['text_obj']))
                    <div style="margin-top: 40px" class="col-md-12 mb-12">
                        <h5 class="fw-semibold mb-3"><i class="bi bi-wine text-danger me-2"></i>Описание:</h5>
                        <div class="bg-light p-4 rounded-10 shadow-sm">
                            <p class="lead text-muted">
                                {!! nl2br(e($group['details_obj']['text_obj'])) !!}
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Адрес и контакты --}}
                <section style="margin-top: 50px" class="hero-section p-4 mb-5">
                    <div class="row align-items-center">
                        <h3 class="section-title fw-bold text-dark mb-3">Адрес, Контакты</h3>
                        <p class="lead text-muted mb-2">
                            Район: {{ $group['district_name'] }}<br>
                            Адрес: {{ $group['address'] }}<br>
                            @if(!empty($group['obj']['phone_obj']))
                                Телефон:
                                <span class="phone-display-copy cursor-pointer"
                                      data-phone="{{ $group['obj']['phone_obj'] }}"
                                      title="Нажмите, чтобы показать телефон">
        +7(***) Показать телефон
    </span>
                            @endif
                        </p>
                    </div>
                </section>

                {{-- Карта --}}
                @if(!empty($group['latitude']) && !empty($group['longitude']))
                    <div class="map-container">
                        <div id="map" style="height: 350px; width: 100%;"></div>
                    </div>
                @endif

                @if(!empty($nearestObjects))
                    @include('blocks.card_subj')
                @endif
            </div>
        </div>
    </div>

    <script src="{{ asset('js/parallax/parallax.js') }}" defer></script>
    <script src="{{ asset('js/carousels/carousel.js') }}" defer></script>
    <script>
        // Прокрутка карусели
        function scrollCarousel(carouselId, direction) {
            const carousel = document.getElementById(carouselId);
            if (!carousel) return;
            const scrollAmount = 370;
            carousel.scrollBy({ left: scrollAmount * direction, behavior: 'smooth' });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const mapData = @json($group);

            const map = L.map('map').setView([mapData.latitude, mapData.longitude], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            const marker = L.marker([mapData.latitude, mapData.longitude]).addTo(map);

            const balloonContent = `
                <div class="custom-balloon">
                    <div class="balloon-title">${mapData.obj.name_obj}</div>
                    <div class="balloon-address">${mapData.address}</div>
                </div>
            `;

            marker.bindPopup(balloonContent);
            setTimeout(() => { marker.openPopup(); }, 500);
            window.map = map;
        });
    </script>
    <script>
        document.getElementById('phone-display')?.addEventListener('click', function () {
            const phone = this.dataset.phone;
            this.innerHTML = '<i class="bi bi-telephone text-danger me-2"></i> ' + (phone || 'Телефон не указан');
        });

        document.querySelectorAll('.phone-display-copy').forEach(function (el) {
            el.addEventListener('click', function () {
                const phone = this.dataset.phone;
                this.textContent = phone || 'Телефон не указан';
            });
        });
    </script>

@endsection
