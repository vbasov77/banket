@extends('layouts.app')

@section('content')
    <style>
        .leaflet-control-attribution {
            display: none !important;
        }
    </style>
    <!-- Стили для кастомного балуна -->
    <style>
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

        .balloon-content {
            color: #666;
            line-height: 1.4;
        }

        .balloon-footer {
            margin-top: 10px;
            text-align: right;
            font-size: 0.9em;
            color: #999;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 8px !important;
        }

        .leaflet-popup-tip {
            background: white !important;
            border: none !important;
        }
    </style>
    <!-- Подключение Leaflet -->

    <link href="{{ asset('css/details/details.css') }}" rel="stylesheet">
    <link href="{{ asset('css/contact-content/contact-content.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="{{asset('map/leaflet/css/leaflet.css')}}"/>
    <script src="{{asset('map/leaflet/js/leaflet.js')}}" defer></script>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-sm-12">
                <div id="map" style="height: 350px; width: 100%; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px;"></div>
            </div>
            <div class="col-md-4 col-sm-12">
                <h3 class="h4 mb-3">{{$map->data_subj['name_subj']}} на карте</h3>
                <div class="details bg-light p-3 rounded">
                    <div class="details-info">
                        <div class="detail mb-2">
                            <span class="detail-label text-muted">Вместимость:</span>
                            <span class="detail-value d-block">от {{ $map->data_subj['capacity_from'] }} до {{ $map->data_subj['capacity_to'] }} чел</span>
                        </div>
                        <div class="detail mb-2">
                            <span class="detail-label text-muted">На фуршет до:</span>
                            <span class="detail-value d-block">{{ $map->data_subj['furshet'] }} чел</span>
                        </div>
                        <div class="detail mb-2">
                            <span class="detail-label text-muted">На человека от:</span>
                            <span class="detail-value d-block">{{ number_format($map->data_subj['per_person'], 0, ' ', ' ') }} ₽/чел</span>
                        </div>
                        <div class="detail">
                            <span class="detail-label text-muted">Стоимость от:</span>
                            <span class="detail-value price d-block fw-bold">{{ number_format($map->data_subj['minimum_cost'], 0, ' ', ' ') }} ₽</span>
                        </div>
                    </div>
                </div>

                <div class="contact-card bg-primary text-white text-center p-4 rounded mt-3" id="contact-card"
                     style="cursor: pointer; margin: 50px 0 50px 0;">
                    <i class="bi bi-telephone fs-1 mb-2"></i>
                    <div id="contact-content" class="contact-content">
                        <h5 class="mb-2" id="trigger-text">Свяжитесь с нами</h5>
                        <a style="text-decoration: none; color: white;" href="tel:{{ $map->data_subj['obj']['phone_obj'] }}" class="phone-link" id="phone-link">
                            {{ $map->data_subj['obj']['phone_obj'] }}
                        </a>
                    </div>
                </div>
                <br>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Получаем данные из PHP-переменной
            const mapData = @json($map);

            // Инициализация карты
            const map = L.map('map').setView([mapData.latitude, mapData.longitude], 13);

            // Добавление слоя OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Создание маркера с балуном
            const marker = L.marker([mapData.latitude, mapData.longitude]).addTo(map);

            // HTML содержимого балуна с использованием данных из $map
            const balloonContent = `
            <div class="custom-balloon">
                <div class="balloon-title">${mapData.data_subj.name_subj}</div>

            </div>
        `;

            // Прикрепление балуна к маркеру
            marker.bindPopup(balloonContent);

            // Автоматическое открытие балуна при загрузке
            setTimeout(() => {
                marker.openPopup();
            }, 500);

            // Сохраняем ссылку на карту в глобальной переменной для использования в других функциях
            window.map = map;
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const card = document.getElementById('contact-card');
            const container = document.getElementById('contact-content');

            card.addEventListener('click', function () {
                // Добавляем класс, который меняет видимость элементов
                container.classList.add('show-phone');
            });
        });
    </script>
@endsection
