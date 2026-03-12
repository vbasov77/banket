@extends('layouts.app')
@section('content')
    <link rel="stylesheet" href="{{asset('map/leaflet/css/leaflet.css')}}"/>
    <script src="{{asset('map/leaflet/js/leaflet.js')}}" defer></script>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-sm-12">
                <div id="map"
                     style="height: 350px; width: 100%; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px;"></div>
            </div>
            <div class="col-md-4 col-sm-12">
                <div class="details bg-light p-3 rounded">
                    <div class="details-info">
                        <div class="detail mb-2">
                            <span class="detail-label text-muted">Адрес:</span>
                            <span class="detail-value d-block">{{ $map->address }}</span>
                        </div>
                        <button type="button" id="destroy"
                                class="btn-festive-gradient btn-festive-gradient-red">Удалить метку
                        </button>
                    </div>
                </div>

                <br>
            </div>
        </div>
    </div>
    <script>
        window.destroy = '{{route('destroy.map.address', ['id' => $map['subj_id']])}}';
        window.obj = '{{route('my.obj')}}';
    </script>
    <script>
        const element = document.querySelector('#destroy');
        element.onclick = function () {
            if (confirm('Подтвердите удаление')) {
                send(`${window.destroy}`);
            } else {
                alert('Удаление отменено');
            }
        };

        async function send(url) {
            let response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            let result = await response.json();
            if (result.success === true) {
                alert(result.message);
                window.location.replace(`${window.obj}`);
            } else {
                alert(result);
            }
        }
    </script>
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
                <div class="balloon-content">
                    ${mapData.address}<br>
                </div>
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
@endsection