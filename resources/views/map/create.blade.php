@extends('layouts.app')
@section('content')
    <style>
        .suggestions-container {
            position: relative;
            width: 100%;
        }

        .leaflet-control-attribution {
            display: none !important;
        }

    </style>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <div id="map" style="height: 500px; width: 100%; border: 1px solid #ddd;"></div>
            </div>
            <div class="col-md-4">
                <h4>Добавить новую точку</h4>
                <form id="addPointForm">
                    @csrf
                    <div class="mb-2">
                        <label for="address" class="form-label">Адрес</label>
                        <input type="text" class="form-control form-control-sm" id="address" name="address" required>
                        <!-- Контейнер для подсказок -->
                        <div id="suggestions-container" class="suggestions-container"></div>
                    </div>

                    <button type="button" class="btn btn-outline-secondary btn-sm mb-2" onclick="geocodeAddress()">Найти
                        на карте
                    </button>
                </form>

                <p class="text-muted mt-2">Кликните на карте для быстрого добавления точки</p>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        let map;
        let currentPopup = null;

        // Инициализация карты после загрузки DOM
        document.addEventListener('DOMContentLoaded', function () {
            // Создаём карту
            map = L.map('map').setView([59.9343, 30.3351], 10);

            // Добавляем слой OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Обработчик клика на карте
            map.on('click', function (e) {
                const latlng = e.latlng;
                showAddressForm(latlng);
            });
        });

        // Обратное геокодирование — получаем адрес по координатам
        async function reverseGeocode(lat, lng) {
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=ru`);
                const data = await response.json();

                if (data && data.display_name) {
                    return data.display_name;
                }
                return 'Адрес не определён';
            } catch (error) {
                console.error('Ошибка при обратном геокодировании:', error);
                return 'Ошибка получения адреса';
            }
        }

        // Показываем форму в точке клика
        async function showAddressForm(latlng) {
            // Закрываем предыдущий popup, если есть
            if (currentPopup) {
                map.closePopup();
            }

            // Сначала получаем адрес по координатам
            const address = await reverseGeocode(latlng.lat, latlng.lng);

            // Генерируем HTML формы с подставленными координатами и реальным адресом
            const formHTML = `
        <form id="popupForm">
            @csrf
            <input type="hidden" name="address" value="${address}">
            <input type="hidden" name="latitude" value="${latlng.lat.toFixed(6)}">
            <input type="hidden" name="longitude" value="${latlng.lng.toFixed(6)}">
            <input type="hidden" name="subj_id" value="{{$subj->id}}">
            <span><b>{{$subj->name_subj}}</b></span><br>
             <span>Мы здесь 🙂</span><br>
            <button type="submit" class="btn btn-sm btn-success">Сохранить</button>
        </form>`;

            // Создаём popup с формой
            currentPopup = L.popup()
                .setLatLng(latlng)
                .setContent(formHTML)
                .openOn(map);

            // Прикрепляем обработчик submit сразу после создания формы
            document.getElementById('popupForm').addEventListener('submit', handleFormSubmit);
        }

        // Обработчик отправки формы
        async function handleFormSubmit(e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);

            try {
                // Отправляем данные через AJAX на route("map.points.store")
                const response = await fetch("{{ route('map_subj.points.store') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Ошибка сервера');
                }

                // Закрываем форму
                map.closePopup();

                // Добавляем маркер на карту
                L.marker([form.querySelector('[name="latitude"]').value, form.querySelector('[name="longitude"]').value])
                    .addTo(map)
                    .bindPopup(`<b>Точка добавлена</b><br>${form.querySelector('[name="address"]').value}`);

                alert('Точка успешно сохранена!');
                window.location.href = "{{ route('show.subj', ['id' => $subj->id]) }}";
            } catch (error) {
                console.error('Ошибка при сохранении точки:', error);
                alert('Не удалось сохранить точку. Проверьте подключение.');
            }
        }

        // Функция для поиска адреса (вызывается по кнопке «Найти на карте»)
        async function geocodeAddress() {
            const addressInput = document.getElementById('address');
            const address = addressInput.value.trim();

            if (!address) {
                alert('Введите адрес для поиска');
                return;
            }

            try {
                // Используем Nominatim для геокодирования
                const response = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(address)}&format=json&limit=1`);
                const data = await response.json();

                if (data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lng = parseFloat(data[0].lon);

                    // Центрируем карту на найденном адресе
                    map.setView([lat, lng], 15);

                    // Показываем форму добавления точки
                    showAddressForm(L.latLng(lat, lng));
                } else {
                    alert('Адрес не найден. Уточните запрос.');
                }
            } catch (error) {
                console.error('Ошибка при поиске адреса:', error);
                alert('Произошла ошибка при поиске адреса.');
            }
        }
    </script>

@endsection