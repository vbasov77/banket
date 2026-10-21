@extends('layouts.app', ['title' => $data->name . ' — ' . $category->name . ', ' . session('user_city'), 'metaDescription' => $metaDescription])

@section('content')
    <link href="{{ asset('css/lightbox/lightbox.css') }}" rel="stylesheet">
    <link href="{{ asset('css/parallax/parallax.css') }}" rel="stylesheet">

    <style>
        /* ═══════════════ ПЕРЕМЕННЫЕ ═══════════════ */
        :root {
            --accent: #4facf5;
            --accent-dark: #2b7fc7;
            --price: #e63946;
            --radius: 14px;
            --shadow-soft: 0 8px 24px rgba(30, 40, 70, .08);
            --shadow-lift: 0 14px 36px rgba(30, 40, 70, .14);
        }

        /* ═══════════════ КАРТОЧКА ПРОФИЛЯ ═══════════════ */
        .profile-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 12px 40px rgba(30, 40, 70, .14);
            padding: 36px;
            margin-top: -90px;
            position: relative;
            z-index: 2;
        }

        .profile-card img.avatar {
            display: block;
            width: 140px;
            height: 140px;
            object-fit: cover;
            border: 5px solid #fff;
            box-shadow: 0 8px 24px rgba(30, 40, 70, .18);
            margin: -105px auto 14px;
            transition: transform .25s ease;
        }

        .profile-card img.avatar:hover {
            transform: scale(1.04);
        }

        .profile-name {
            font-size: 26px;
            font-weight: 700;
            margin-top: 6px;
            line-height: 1.25;
        }

        .profile-role {
            display: inline-block;
            color: #fff;
            background: var(--accent);
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 5px 16px;
            border-radius: 30px;
            margin-top: 10px;
        }

        /* Разделитель между колонками карточки */
        .profile-divider {
            border: 0;
            border-top: 1px dashed #e3e8f0;
            margin: 0;
        }

        .lead-about {
            font-size: 16.5px;
            line-height: 1.75;
            color: #55607a;
        }

        @media (min-width: 992px) {
            .lead-about {
                height: 100%;
                display: flex;
                align-items: center;
            }
        }

        /* ═══════════════ СОЦКНОПКИ ═══════════════ */
        .social-links {
            display: flex;
            gap: 24px;
            justify-content: center;
            align-items: center;
            --social-color: var(--accent);
        }

        .social-btn {
            width: 120px;
            height: 120px;
            display: inline-flex;
            flex-direction: column;
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
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: visible;
        }

        .social-btn svg {
            width: 56px;
            height: 56px;
            stroke-width: 0.8;
        }

        .social-btn--vk svg {
            width: 90px;
            height: 90px;
            stroke-width: 0.5;
        }

        .social-btn:hover {
            text-decoration: none;
            transform: translateY(-4px);
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

        /* ═══════════════ ТЕЛЕФОН ═══════════════ */
        #phone-display {
            display: inline-block;
            font-size: clamp(19px, 3.5vw, 26px);
            font-weight: 700;
            color: var(--accent-dark);
            background: #f0f7fe;
            border: 2px dashed rgba(79, 172, 245, .55);
            border-radius: 50px;
            padding: 12px 34px;
            text-decoration: none;
            text-align: center;
            margin: 26px 0;
            transition: background .2s, transform .15s, border-color .2s;
        }

        #phone-display:hover {
            background: #e2f0fd;
            border-color: var(--accent);
            transform: translateY(-2px);
            text-decoration: none;
        }

        /* ═══════════════ ЗАГОЛОВКИ СЕКЦИЙ ═══════════════ */
        .section-title {
            color: #2c3244;
        }

        /* ═══════════════ ФОТОАЛЬБОМ ═══════════════ */
        .photo-album {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 12px;
        }

        .photo-album .item-carousel {
            width: 100%;
            aspect-ratio: 3 / 2;
            object-fit: cover;
            border-radius: var(--radius);
            cursor: pointer;
            transition: transform .25s ease, box-shadow .25s ease;
        }

        .photo-album .item-carousel:hover {
            transform: scale(1.02);
            box-shadow: var(--shadow-lift);
        }

        /* ═══════════════ ВИДЕО ═══════════════ */
        .video-section {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 16px;
        }

        .video-frame {
            width: 100%;
            aspect-ratio: 16 / 9;
            border: 0;
            border-radius: var(--radius);
            background: #000;
            box-shadow: var(--shadow-soft);
        }

        .video-fallback {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 8px;
            aspect-ratio: 16 / 9;
            background: #f4f7fb;
            border: 1px dashed #d3dce8;
            border-radius: var(--radius);
            text-align: center;
            padding: 16px;
        }

        /* ═══════════════ ПАКЕТЫ УСЛУГ ═══════════════ */
        .packages-box {
            border: 1px solid #eef1f6;
            border-radius: var(--radius);
            box-shadow: var(--shadow-soft);
        }

        .package-item {
            padding: 14px 0;
            border-bottom: 1px solid #eef1f6;
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
            color: var(--price);
            white-space: nowrap;
        }

        .package-desc {
            font-size: 14px;
            color: #666;
            line-height: 1.5;
            margin-top: 4px;
            max-width: 640px;
        }

        /* ═══════════════ ОПИСАНИЕ ═══════════════ */
        .description-box {
            background: #fff;
            border: 1px solid #eef1f6;
            border-left: 4px solid var(--accent);
            border-radius: var(--radius);
            box-shadow: var(--shadow-soft);
        }

        .description-text {
            font-size: 1rem;
            line-height: 1.7;
        }

        /* ═══════════════ ПАРАЛЛАКС ═══════════════ */
        .parallax-title {
            text-shadow: 0 2px 14px rgba(0, 0, 0, .55);
        }

        /* ═══════════════ МОБИЛЬНАЯ АДАПТАЦИЯ ═══════════════ */
        @media (max-width: 576px) {
            .profile-card {
                margin-top: -60px;
                padding: 24px 16px;
            }

            .profile-card img.avatar {
                width: 110px;
                height: 110px;
                margin-top: -82px;
            }

            .profile-name {
                font-size: 21px;
            }

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

            #phone-display {
                margin: 18px 0;
                padding: 10px 24px;
            }

            .photo-album {
                grid-template-columns: repeat(2, 1fr);
                gap: 6px;
            }

            .photo-album .item-carousel {
                border-radius: 8px;
            }

            .video-section {
                grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
                gap: 10px;
            }

            .parallax-title {
                font-size: clamp(1.3rem, 6vw, 2rem);
                padding: 0 12px;
            }

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
    </style>

    {{-- ================= HERO ================= --}}
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

    {{-- ================= Подготовка данных о контактах ================= --}}
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

    {{-- ================= КАРТОЧКА ПРОФИЛЯ ================= --}}
    <section style="padding-bottom: 30px">
        <div class="container px-2">
            <div class="profile-card">
                <div class="row align-items-center">

                    {{-- Левая колонка: аватар, имя, категория --}}
                    <div class="col-lg-4 text-center">
                        <img src="{{ ($data->avatar && $data->avatar->small_img)
                                ? $data->avatar->small_img
                                : asset('images/no_image/no_image.jpg') }}"
                             class="rounded-circle avatar"
                             alt="{{ $data->name }}">

                        <div class="profile-name">{!! $data->name !!}</div>
                        <span class="profile-role">{{ $category->name }}</span>
                    </div>

                    {{-- Разделитель на больших экранах --}}
                    <div class="col-auto d-none d-lg-block">
                        <hr class="profile-divider" style="height: 120px; width: 1px; border-top-width: 1px;">
                    </div>

                    {{-- Правая колонка: краткое описание --}}
                    <div class="col-lg mt-3 mt-lg-0">
                        <p class="lead-about my-lg-4 mb-0">
                            {!! \Illuminate\Support\Str::limit(strip_tags($data->description), 260) !!}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= СОЦСЕТИ И ТЕЛЕФОН ================= --}}
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
    <div class="text-center">
        <a id="phone-display"
           class="cursor-pointer"
           data-phone="{{ $data->phone ?? '' }}"
           title="Нажмите, чтобы показать телефон">
            +7(***) Показать телефон
        </a>
    </div>

    <section style="padding-bottom: 50px" class="mt-4 p-2">
        <div class="row justify-content-center">
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
                        <div class="packages-box p-3 p-md-4">
                            <h3 class="section-title fs-4 mb-3">
                                <i class="bi bi-tags me-2" style="color: var(--accent)"></i>Пакеты услуг
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
                            <i class="bi bi-play-btn me-2" style="color: var(--accent)"></i>Видео
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
                                        <i class="bi bi-play-circle fs-1" style="color: var(--accent)"></i>
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
<br>
<br>
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
