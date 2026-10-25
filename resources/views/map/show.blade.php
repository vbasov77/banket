@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/details/details.css') }}" rel="stylesheet">
    <link href="{{ asset('css/contact-content/contact-content.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('map/leaflet/css/leaflet.css') }}"/>
    <script src="{{ asset('map/leaflet/js/leaflet.js') }}" defer></script>

    <style>
        :root {
            --fs-h2:      clamp(20px, 5vw, 26px);
            --fs-body:    15px;
            --fs-small:   13px;
            --fs-phone:   20px;

            --card-gap:   1rem;
            --card-pad:   1.25rem;
        }

        /* Leaflet: скрываем атрибуцию и выравниваем попапы */
        .leaflet-control-attribution {
            display: none !important;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 8px !important;
        }

        .leaflet-popup-tip {
            background: white !important;
            border: none !important;
        }

        /* Балун */
        .custom-balloon {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px;
            max-width: 300px;
        }

        .balloon-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
            font-size: 1.1em;
        }

        /* ===== Вёрстка ===== */
        .map-container {
            height: 350px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;           /* скруглённые углы не обрезаются картинкой тайлов */
        }

        .details-card {
            background: #f8f9fa;
            padding: var(--card-pad);
            border-radius: 10px;
        }

        .detail {
            margin-bottom: 0.5rem;
            font-size: var(--fs-body);
            line-height: 1.5;
        }

        .detail-label {
            color: #888;
            margin-right: 6px;
        }

        .detail-value.price {
            font-weight: 600;
            font-size: 17px;
        }

        #phone-display {
            font-size: var(--fs-phone);
            word-break: break-word;
        }

        @media (max-width: 768px) {
            :root {
                --card-pad: 1rem;
            }

            .map-container {
                height: 260px;
                margin-bottom: var(--card-gap);
            }

            .phone-wrap {
                margin-top: 0.75rem;
            }
        }

        @media (max-width: 480px) {
            .map-container {
                height: 220px;
            }

            .detail-label {
                display: block;         /* на телефонах подпись над значением — читается легче */
                margin-bottom: 2px;
            }

            #phone-display {
                font-size: 18px;
            }
        }
    </style>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-sm-12">
                <div id="map" class="map-container"></div>
            </div>
            <div class="col-md-4 col-sm-12">
                <h3 class="h4 mb-3">{{ $map['data_subj']['name_subj'] }} на карте</h3>

                <div class="details-card">
                    <div class="detail">
                        <span class="detail-label">Вместимость:</span>
                        <span class="detail-value">до {{ $map['data_subj']['capacity_to'] }} чел</span>
                    </div>
                    <div class="detail">
                        <span class="detail-label">На фуршет до:</span>
                        <span class="detail-value">{{ $map['data_subj']['furshet'] }} чел</span>
                    </div>
                    <div class="detail">
                        <span class="detail-label">На человека от:</span>
                        <span class="detail-value">{{ number_format($map['data_subj']['per_person'], 0, ' ', ' ') }} ₽/чел</span>
                    </div>
                    <div class="detail">
                        <span class="detail-label">Стоимость от:</span>
                        <span class="detail-value price">{{ number_format($map['data_subj']['minimum_cost'], 0, ' ', ' ') }} ₽</span>
                    </div>
                </div>

                <div class="phone-wrap mt-3">
                    <span id="phone-display"
                          class="cursor-pointer text-primary"
                          data-phone="{{ $map['data_subj']['obj']['phone_obj'] ?? '' }}"
                          title="Нажмите, чтобы показать телефон">
                        +7(***) Показать телефон
                    </span>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.clickPhone = '{{ route('click_phone.store', ['id' => $map['data_subj']['subj_id']]) }}';
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ===== Телефон: тот же механизм, что на странице зала =====
            document.getElementById('phone-display')?.addEventListener('click', function () {
                const phone = this.dataset.phone;

                if (!phone) {
                    this.textContent = 'Телефон не указан';
                    this.classList.remove('text-primary');
                    return;
                }

                // Преобразуем номер в кликабельную tel-ссылку
                const digits = phone.replace(/[^+\d]/g, ''); // оставляем только цифры и +
                this.outerHTML = `<a href="tel:${digits}"
        style="text-decoration:none; color:#000; font-weight:600; font-size:inherit;">${phone}</a>`;

                // Отправляем клик в бэк (статистика)
                fetch(window.clickPhone, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    keepalive: true
                }).catch(() => {});
            });

            // ===== Карта =====
            const mapData = @json($map);

            const map = L.map('map').setView([mapData.latitude, mapData.longitude], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            const marker = L.marker([mapData.latitude, mapData.longitude]).addTo(map);

            const balloonContent = `
                <div class="custom-balloon">
                    <div class="balloon-title">${mapData.data_subj.name_subj}</div>
                </div>
            `;

            marker.bindPopup(balloonContent);

            setTimeout(() => marker.openPopup(), 500);

            window.map = map;
        });
    </script>
@endsection
