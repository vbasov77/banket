@extends('layouts.app')
@section('content')
    <style>
        .group-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f9f9f9;
        }

        .subjects-list {
            list-style: none;
            padding-left: 0;
        }

        .subjects-list li {
            padding: 5px 0;
            border-bottom: 1px dashed #eee;
        }

        .popup-content h4 {
            margin: 0 0 10px 0;
            color: #333;
        }

        .popup-content ul {
            margin: 10px 0 0 0;
            padding-left: 20px;
        }

        .leaflet-control-attribution {
            display: none !important;
        }

        .link a{
            color: black;
            text-decoration: none;
        }
    </style>

    <style>
        /* Стили для списка субъектов */
        .subjects-list {
            list-style: none;
            padding: 0;
            margin: 15px 0;
        }

        /* Карточка субъекта */
        .subject-card {
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background: #f9f9f9;
            transition: all 0.3s ease;
        }

        /* Ссылка на субъект */
        .subject-link {
            text-decoration: none;
            color: #2c3e50;
            display: block;
        }

        /* Эффект при наведении на ссылку */
        .subject-link:hover .subject-card {
            background: #e8f4fd;
            border-color: #3498db;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Стили для списка объектов */
        .objects-list {
            list-style: none;
            padding: 0;
            margin: 15px 0;
        }

        .object-item {
            margin: 8px 0;
            padding: 10px;
            background: #f0f8ff;
            border: 1px solid #87ceeb;
            border-radius: 6px;
        }

        /* Общие стили для popup */
        .popup-content h5 {
            color: #2c3e50;
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 16px;
        }

    </style>

    <!-- Исправлено: rel="stylesheet" → rel="stylesheet" -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <div class="map-container">
        <div id="map" style="height: 450px; width: 100%;"></div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof L === 'undefined') {
                console.error('Leaflet не загружен!');
                return;
            }

            const map = L.map('map').setView([60.0, 30.0], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            const groups = @json($groups);
            console.log('Полученные данные:', groups);

            groups.forEach(group => {
                if (!group.latitude || !group.longitude) {
                    console.warn('Пропускаем группу без координат:', group.id);
                    return;
                }

                const groupMarker = L.marker([group.latitude, group.longitude])
                    .addTo(map)
                    .bindPopup(`
            <div class="popup-content">
                <!-- Список субъектов -->

                <ul class="subjects-list">
                    ${group.subjects.map(subject => `
                <li>
                    <a href="/show_subj/id${subject.id}"
               class="subject-link"
               title="Перейти к субъекту ${subject.name_subj}">
                <div class="subject-card">
                    <strong>${subject.name_subj}</strong><br>
            На человека: ${subject.per_person}<br>
            Вместимость: ${subject.capacity_from}–${subject.capacity_to} чел.
                </div>
            </a>
                </li>`).join('')}
                </ul>

                <!-- Информация об уникальных объектах -->
                ${group.objects.length > 0 ? `
                <h5>Связанные объекты:</h5>
                <ul class="objects-list">
            ${group.objects.map(obj => `
                <li class="object-item">
            <strong>${obj.name_obj}</strong><br>
            Телефон: ${obj.phone_obj || 'Не указан'}
                </li>`).join('')}
                </ul>` : ''}
            </div>`);
            });

            if (groups.length > 0) {
                const bounds = new L.LatLngBounds();
                groups.forEach(group => {
                    if (group.latitude && group.longitude) {
                        bounds.extend([group.latitude, group.longitude]);
                    }
                    group.subjects.forEach(subject => {
                        if (subject.address_data.latitude && subject.address_data.longitude) {
                            bounds.extend([
                                subject.address_data.latitude,
                                subject.address_data.longitude
                            ]);
                        }
                    });
                });
                map.fitBounds(bounds, {padding: [50, 50]});
            }
        });
    </script>
@endsection
