
@extends('layouts.app', ['title' => $data->name . ' — ' . $category->name . ', ' . session('user_city'), 'metaDescription' => $metaDescription])

@section('content')
    <link href="{{ asset('css/lightbox/lightbox.css') }}" rel="stylesheet">
    <link href="{{ asset('css/parallax/parallax.css') }}" rel="stylesheet">

    <style>
        .social-links {
            display: flex;
            gap: 24px;
            justify-content: center;
            align-items: center;
            --social-color: #4facf5;
        }

        .social-btn {
            width: 120px;
            height: 120px;
            display: inline-flex;
            flex-direction: column; /* иконка сверху, подпись снизу */
            align-items: center;
            justify-content: center;
            gap: 6px;
            border-radius: 50%;
            background: none;
            color: var(--social-color);
            border: 1px solid transparent;
            text-decoration: none;
            transition: transform .15s, border-color .15s, color .15s;
        }

        .social-icon-wrap {
            height: 60px; /* единый слот под иконку для всех */
            display: flex;
            align-items: center; /* иконка центрируется внутри слота */
            justify-content: center;
            overflow: visible;
        }

        .social-btn svg {
            width: 56px;
            height: 56px;
            stroke-width: 0.8;
        }

        /* VK крупнее, но всё равно внутри слота не выше него */
        .social-btn--vk svg {
            width: 90px;
            height: 90px;
            stroke-width: 0.5;
        }

        .social-btn:hover {
            text-decoration: none;
        }

        .social-btn:hover {
            transform: translateY(-2px);
            border-color: var(--social-color);
        }

        .social-label {
            font-size: 14px;
            font-weight: 500;
            line-height: 1;
            color: #333;
        }

        .social-btn:hover .social-label {
            color: var(--social-color);
        }

        #phone-display {
            font-size: 35px;
            font-weight: bold;
            display: block;
            text-align: center;
            text-decoration: none;
            color: inherit;
            margin: 30px 0 30px 0;
        }

        .photo-album {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 10px;
        }

        .photo-album .item-carousel {
            width: 100%;
            aspect-ratio: 3 / 2;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: opacity .15s;
        }

        .photo-album .item-carousel:hover {
            opacity: .85;
        }

        /* ─── Видео ─── */
        .video-section {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 16px;
        }

        .video-frame {
            width: 100%;
            aspect-ratio: 16 / 9;
            border: 0;
            border-radius: 8px;
            background: #000;
        }

        .video-fallback {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 8px;
            aspect-ratio: 16 / 9;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
            padding: 16px;
        }

        .packages-box {
            border: 1px solid #eee;
        }

        .package-item {
            padding: 14px 0;
            border-bottom: 1px solid #eee;
        }

        .package-item:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .package-line {
            display: flex;
            align-items: baseline;
            gap: 10px;
        }

        .package-name {
            font-weight: 600;
            font-size: 16px;
            line-height: 1.3;
        }

        .package-dots {
            flex: 1;
            min-width: 20px;
            border-bottom: 2px dotted #d9d9d9;
            transform: translateY(-4px);
        }

        .package-price {
            font-weight: 700;
            font-size: 16px;
            color: #e63946;
            white-space: nowrap;
        }

        .package-desc {
            font-size: 14px;
            color: #666;
            line-height: 1.5;
            margin-top: 4px;
            max-width: 640px;
        }

        .description-text {
            font-size: 1rem;
        }

        @media (max-width: 576px) {
            .package-dots {
                display: none;
            }

            .package-line {
                flex-wrap: wrap;
                gap: 4px 12px;
            }

            .package-price {
                order: 2;
                font-size: 15px;
            }

            .package-name {
                order: 1;
                width: 100%;
            }
        }

        /* ─── Мобильная адаптация карточки ─── */
        @media (max-width: 576px) {
            /* Телефон: масштабируемый размер вместо жёстких 35px */
            #phone-display {
                font-size: clamp(19px, 5.5vw, 28px);
                margin: 20px 0;
            }

            /* Соцкнопки: компактнее и с переносом */
            .social-links {
                flex-wrap: wrap;
                gap: 10px;
            }

            .social-btn {
                width: 76px;
                height: 76px;
                gap: 4px;
            }

            .social-icon-wrap {
                height: 42px;
            }

            .social-btn svg {
                width: 36px;
                height: 36px;
            }

            .social-btn--vk svg {
                width: 52px;
                height: 52px;
            }

            .social-label {
                font-size: 11px;
            }

            /* Фотоальбом: две колонки вместо одной во всю ширину */
            .photo-album {
                grid-template-columns: repeat(2, 1fr);
                gap: 6px;
            }

            .photo-album .item-carousel {
                border-radius: 6px;
            }

            /* Видео: сузить минимум, чтобы две колонки влезали на 400px+ */
            .video-section {
                grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
                gap: 10px;
            }

            /* Заголовок параллакса не должен занимать пол-экрана */
            .parallax-title {
                font-size: clamp(1.3rem, 6vw, 2rem);
                padding: 0 12px;
            }

            .package-price {
                font-size: 15px;
            }
        }
    </style>

    @if (!empty($data->photos[0]->big_img))
        <div class="parallax-container">
            <div class="parallax-bg" style="background-image: url('{{ $data->photos[0]->big_img }}');"></div>
            <div class="parallax-content">
                <div class="parallax-title-center">
                    <h1 class="parallax-title">{!! $data->name !!}</h1>
                </div>
            </div>
        </div>
    @endif

    <section style="padding-bottom: 50px" class="mt-4 p-2">
        <div class="row justify-content-center">
            <div class="col-lg-11 col-md-12">

                @php
                    $socialNames = [
                        'vk' => 'ВК', 'telegram' => 'Telegram',
                        'max' => 'MAX', 'whatsapp' => 'WhatsApp',
                    ];

                    $links = [];

                    foreach ($data->user->contacts as $contact) {
                        $value = trim($contact->value);
                        $url   = match ($contact->type) {
                            'whatsapp' => str_starts_with($value, 'http') ? $value
                                : 'https://wa.me/' . preg_replace('/^(\+?7|8)?(\d{10})$/', '7$2',
                                    preg_replace('/\D+/', '', $value) ?: ''),
                            'telegram' => 'https://t.me/' . ltrim(str_replace(['https://t.me/', 't.me/'], '', $value), '@'),
                            default    => $value !== '' ? 'https://' . preg_replace('#^https?://#', '', ltrim($value, '/')) : null,
                        };

                        if ($url && $url !== 'https://') {
                            $links[] = [
                                'type' => $contact->type,
                                'url'  => $url,
                                'name' => $socialNames[$contact->type] ?? $contact->type,
                            ];
                        }
                    }
                @endphp

                <div class="social-links">
                    @foreach ($links as $link)
                        <a href="{{ $link['url'] }}"
                           class="social-btn social-btn--{{ $link['type'] }}"
                           target="_blank"
                           rel="noopener"
                           aria-label="{{ $link['name'] }}">
                            <span class="social-icon-wrap">@include('components.icons.' . $link['type'])</span>
                            <span class="social-label">{{ $link['name'] }}</span>
                        </a>
                    @endforeach
                </div>

                {{-- Телефон --}}
                <div class="col-12 mt-3 text-center">
                    <a id="phone-display"
                       class="cursor-pointer"
                       data-phone="{{ $data->phone ?? '' }}"
                       title="Нажмите, чтобы показать телефон">
                        +7(***) Показать телефон
                    </a>
                </div>
            </div>

            <div class="col-lg-8 col-md-10 col-sm-12">
                {{-- Фотоальбом --}}
                @if ($data->photos->count())
                    <div class="photo-album mt-4" data-lightbox>
                        @foreach ($data->photos as $i => $photo)
                            <img class="item-carousel"
                                 src="{{ $photo->small_img }}"
                                 alt="{{ $data->name }}"
                                 data-index="{{ $i }}"
                                 data-big-image="{{ $photo->big_img ?? $photo->img }}"
                                 loading="lazy">
                        @endforeach
                    </div>
                @endif

                {{-- Пакеты услуг --}}
                @if ($data->packages->count())
                    @php
                        $mainPackages = $data->packages->where('is_addon', false);
                        $addonPackages = $data->packages->where('is_addon', true);
                        $fmt = fn ($p) => $p === null ? null : number_format($p, 0, ',', ' ');
                    @endphp

                    <div class="col-12 mt-5">
                        <div class="packages-box bg-light rounded-3 p-3 p-md-4">
                            <h3 class="section-title fs-4 mb-2">
                                <i class="bi bi-tags text-danger me-2"></i>Пакеты услуг
                            </h3>

                            <div class="package-list">
                                @foreach ($mainPackages as $package)
                                    <div class="package-item">
                                        <div class="package-line">
                                            <span class="package-name">{{ $package->title }}</span>
                                            <span class="package-dots"></span>
                                            @if ($package->price_min !== null || $package->price_max !== null)
                                                <span class="package-price">
                                                    @if ($package->price_min !== null && $package->price_max !== null)
                                                        {{ $fmt($package->price_min) }}
                                                        – {{ $fmt($package->price_max) }} ₽
                                                    @else
                                                        от {{ $fmt($package->price_min ?? $package->price_max) }} ₽
                                                    @endif
                                                </span>
                                            @endif
                                        </div>

                                        @if ($package->description)
                                            <p class="package-desc mb-0">{{ $package->description }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            @if ($addonPackages->count())
                                <h6 class="text-muted text-uppercase mt-4 mb-2"
                                    style="letter-spacing: .05em; font-size: 12px">
                                    Дополнительно
                                </h6>

                                <div class="package-list">
                                    @foreach ($addonPackages as $package)
                                        <div class="package-item">
                                            <div class="package-line">
                                                <span class="package-name">{{ $package->title }}</span>
                                                <span class="package-dots"></span>
                                                @if ($package->price_min !== null || $package->price_max !== null)
                                                    <span class="package-price">
                                                        @if ($package->price_min !== null && $package->price_max !== null)
                                                            {{ $fmt($package->price_min) }}
                                                            – {{ $fmt($package->price_max) }} ₽
                                                        @else
                                                            от {{ $fmt($package->price_min ?? $package->price_max) }} ₽
                                                        @endif
                                                    </span>
                                                @endif
                                            </div>

                                            @if ($package->description)
                                                <p class="package-desc mb-0">{{ $package->description }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- ─── ВИДЕО ─── --}}
                @php
                    $videos = is_array($data->video_url) ? array_filter($data->video_url) : [];
                @endphp

                @if (count($videos))
                    <div class="col-12 mt-5">
                        <h3 class="section-title fs-4 mb-3">
                            <i class="bi bi-play-btn text-danger me-2"></i>Видео
                        </h3>

                        <div class="video-section">
                            @foreach ($videos as $url)
                                @php
                                    // vk.com/video-123_456, vkvideo.ru/..., vk.ru/...
                                    $embed = null;
                                    if (preg_match('/video(-?\d+)_(\d+)/i', $url, $m)) {
                                        $embed = 'https://vk.com/video_ext.php?oid=' . $m[1]
                                            . '&id=' . $m[2] . '&hd=2';
                                    }
                                @endphp

                                @if ($embed)
                                    <iframe class="video-frame"
                                            src="{{ $embed }}"
                                            allow="autoplay; encrypted-media; fullscreen; picture-in-picture"
                                            allowfullscreen
                                            loading="lazy"
                                            title="Видео {{ $data->name }}"></iframe>
                                @else
                                    <div class="video-fallback">
                                        <i class="bi bi-play-circle fs-1 text-danger"></i>
                                        <a href="{{ $url }}" target="_blank" rel="noopener"
                                           class="btn btn-outline-primary btn-sm">
                                            Смотреть видео <i class="bi bi-box-arrow-up-right ms-1"></i>
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Описание --}}
                <div style="margin-top: 40px" class="col-md-12 mb-5">
                    <div class="bg-light p-3 p-md-4 rounded-10 shadow-sm">
                        <h5 class="fw-semibold mb-3">
                            <i class="bi bi-text-paragraph text-danger me-2"></i>Описание:
                        </h5>
                        <p class="lead text-muted description-text mb-0">
                            {!! nl2br(e($data->description)) !!}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Модальный лайтбокс --}}
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

    <script src="{{ asset('js/parallax/parallax.js') }}" defer></script>
    <script src="{{ asset('js/lightbox/lightbox.js') }}" defer></script>

    <script>
        window.clickPhone = '{{ route('click_phone.store', ['id' => $data->id]) }}';

        document.getElementById('phone-display')?.addEventListener('click', function (e) {
            const phone = this.dataset.phone;

            if (!phone) {
                this.textContent = 'Телефон не указан';
                e.preventDefault();
                return;
            }

            if (this.getAttribute('href')) {
                return;
            }

            e.preventDefault();
            this.textContent = phone;
            this.setAttribute('href', 'tel:' + phone.replace(/[^\d+]/g, ''));

            fetch(window.clickPhone, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                keepalive: true
            }).catch(() => {
            });
        });
    </script>
@endsection
