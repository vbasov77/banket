@extends('layouts.app')
@section('content')
    @push('styles')
        <link href="{{ asset('subjs/css/search_address.css') }}" rel="stylesheet">
        <link rel="stylesheet" href="{{asset('map/leaflet/css/leaflet.css')}}"/>
        <style>
            #find-on-map-btn:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }

            #find-on-map-btn:not(:disabled) {
                cursor: pointer;
            }

            #district-suggestions .list-group-item.active {
                background-color: #f0f0f0;
                color: #333;
            }
            #manual-district {
                border: 1px solid #ff0000; /* красный бордер */
            }

            #manual-district:focus {
                border-color: #cc0000;
                outline: none;
            }
        </style>
    @endpush
    <div class="container">
        <div class="row">
            <div class="col-md-8 mt-5">
                <div id="map" style="height: 500px; width: 100%; border: 1px solid #ddd;"></div>
            </div>
            <div class="col-md-4 mt-5">
                <h4>Добавить новую точку</h4>
                <form id="addPointForm">
                    @csrf
                    <div class="form-group position-relative">
                        <label for="address-input">Адрес:</label>
                        <input type="text" id="address-input" class="form-control"
                               placeholder="Начните вводить адрес" autocomplete="off">
                        <ul id="suggestions-list" class="list-group position-absolute w-100"
                            style="z-index: 1000; display: none; max-height: 300px; overflow-y: auto;"></ul>
                    </div>

                    <input type="hidden" id="dadata-city">
                    <input type="hidden" id="dadata-city-fias-id">
                    <input type="hidden" id="dadata-district">
                    <input type="hidden" id="dadata-street">
                    <input type="hidden" id="dadata-house">
                    <input type="hidden" id="dadata-lat">
                    <input type="hidden" id="dadata-lon">

                    <div class="mt-3">
                        <div class="card">
                            <div class="card-body">
                                <p class="mb-1"><strong>Город:</strong> <span id="info-city"></span></p>

                                <!-- Район: либо текст, либо инпут -->
                                <div class="form-group mb-1 position-relative">
                                    <div class="d-flex align-items-center mb-1">
                                        <label for="manual-district" class="mb-0 mr-2"
                                               style="white-space: nowrap;"><strong>Район:&ensp;</strong></label>
                                        <span id="info-district-text" style="display: inline;"></span>
                                        <input type="text" id="manual-district" class="form-control form-control-sm"
                                               placeholder="Укажите район вручную" style="display: none; flex: 1;"
                                               autocomplete="off"/>
                                    </div>
                                    <ul id="district-suggestions" class="list-group position-absolute w-100"
                                        style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto;"></ul>
                                </div>

                                <p class="mb-1"><strong>Улица:</strong> <span id="info-street"></span></p>
                                <p class="mb-1"><strong>Дом:</strong> <span id="info-house"></span></p>
                                <p class="mb-1"><strong>Координаты:</strong> <span id="info-coords"></span></p>
                                <br>
                                <button type="button" id="find-on-map-btn" class="btn btn-sm mt-2" disabled
                                        style="background: #4caf50; color: #fff; border: none; border-radius: 6px; font-weight: 500; padding: 6px 18px;">
                                    Найти на карте
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.csrfToken = "{{ csrf_token() }}";
        window.subjId = "{{ $subj->id }}";
        window.objId = "{{ $subj->obj_id }}";
        window.nameSubj = "{{ $subj->name_subj }}";
        window.savePointUrl = "{{ route('address.store') }}";
        window.myObjUrl = "{{ route('my.obj') }}";
        window.cityId = "{{ session('city_id') }}";
    </script>

    <script src="{{asset('map/leaflet/js/leaflet.js')}}" defer></script>
    <script>
        let dadataAbortController = null;
        let selectedAddress = null;
        let map;
        let currentPopup = null;

        const addressInput = document.getElementById('address-input');
        const suggestionsList = document.getElementById('suggestions-list');
        const findBtn = document.getElementById('find-on-map-btn');



        function debounce(func, wait) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        document.addEventListener('DOMContentLoaded', function () {
            map = L.map('map').setView([59.9343, 30.3351], 10);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Клик по карте — перемещаем балун и обновляем координаты
            map.on('click', function (e) {
                if (selectedAddress) {
                    showAddressForm(e.latlng);
                }
            });

            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (form.id === 'popupForm' && !form._submitHandlerProcessed) {
                    e.preventDefault();
                    handleFormSubmit(e);
                    form._submitHandlerProcessed = true;
                }
            });

            // --- ИНИЦИАЛИЗАЦИЯ РУЧНОГО ВВОДА РАЙОНА ---
            const manualDistrictInput = document.getElementById('manual-district');
            if (manualDistrictInput) {
                manualDistrictInput.addEventListener('input', function (e) {
                    const value = e.target.value.trim();
                    document.getElementById('dadata-district').value = value;

                    const textEl = document.getElementById('info-district-text');
                    if (value) {
                        textEl.textContent = value;
                        // Пользователь начал вводить — активируем кнопку
                        findBtn.disabled = false;
                    } else {
                        textEl.textContent = '—';
                        // Поле очистили — снова блокируем
                        findBtn.disabled = true;
                    }
                });
            }

            // Поиск через DaData
            addressInput.addEventListener('input', debounce(async function () {
                const query = addressInput.value;
                findBtn.disabled = true;

                if (query.length < 3) {
                    suggestionsList.style.display = 'none';
                    return;
                }

                if (dadataAbortController) dadataAbortController.abort();
                dadataAbortController = new AbortController();

                try {
                    const response = await fetch(`/address/suggest?q=${encodeURIComponent(query)}`, {
                        signal: dadataAbortController.signal
                    });

                    if (!response.ok) return;
                    const data = await response.json();

                    if (data.error || data.length === 0) {
                        suggestionsList.innerHTML = '<li class="list-group-item text-muted">Ничего не найдено</li>';
                        suggestionsList.style.display = 'block';
                        return;
                    }

                    suggestionsList.innerHTML = '';
                    data.forEach(item => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item list-group-item-action';
                        li.style.cursor = 'pointer';
                        li.textContent = item.value;

                        // --- ПРАВИЛЬНЫЙ ОБРАБОТЧИК КЛИКА ПО ПОДСКАЗКЕ ---
                        li.addEventListener('click', function () {
                            addressInput.value = item.value;
                            selectedAddress = item;
                            suggestionsList.style.display = 'none';

                            const city = item.city || '';
                            const district = item.area || '';
                            const street = item.street || '';
                            const house = item.house || '';
                            const lat = item.lat || '';
                            const lon = item.lon || '';

                            document.getElementById('dadata-city').value = city;
                            document.getElementById('dadata-district').value = district;
                            document.getElementById('dadata-street').value = street;
                            document.getElementById('dadata-house').value = house;
                            document.getElementById('dadata-lat').value = lat;
                            document.getElementById('dadata-lon').value = lon;

                            document.getElementById('info-city').textContent = city || '';
                            document.getElementById('info-street').textContent = street || '';
                            document.getElementById('info-house').textContent = house || '';
                            document.getElementById('info-coords').textContent =
                                (lat && lon) ? `${lat}, ${lon}` : '';

                            toggleManualDistrict(!!district);

                            if (!district) {
                                // Района нет — кнопка заблокирована, ждём ручного ввода
                                findBtn.disabled = true;
                                document.getElementById('manual-district').value = '';
                                const inputEl = document.getElementById('manual-district');
                                if (inputEl) inputEl.focus();
                            } else {
                                // Район есть из DaData — кнопка активна
                                findBtn.disabled = false;
                                document.getElementById('info-district-text').textContent = district;
                            }
                        });

                        suggestionsList.appendChild(li);
                    });
                    suggestionsList.style.display = 'block';

                } catch (error) {
                    if (error.name === 'AbortError') return;
                    console.error('Fetch error:', error);
                }
            }, 400));

            // --- ПОДСКАЗКИ ДЛЯ РУЧНОГО ВВОДА РАЙОНА ---
            const districtInput = document.getElementById('manual-district');
            const districtSuggestions = document.getElementById('district-suggestions');
            let districtAbortController = null;
            let districtSuggestionsData = [];
            let districtHighlightedIndex = -1;

            districtInput.addEventListener('input', debounce(async function () {
                const query = districtInput.value.trim();
                const cityId = window.cityId;

                if (query.length < 2 || !cityId) {
                    districtSuggestions.style.display = 'none';
                    return;
                }

                if (districtAbortController) districtAbortController.abort();
                districtAbortController = new AbortController();

                try {
                    const response = await fetch(
                        `/api/districts?q=${encodeURIComponent(query)}&city_data_city_id=${encodeURIComponent(cityId)}`,
                        {signal: districtAbortController.signal}
                    );

                    if (!response.ok) {
                        if (response.status === 404) {
                            districtSuggestions.innerHTML =
                                '<li class="list-group-item text-muted">Районы не найдены — введите вручную</li>';
                            districtSuggestions.style.display = 'block';
                        } else {
                            districtSuggestions.style.display = 'none';
                        }
                        return;
                    }

                    const data = await response.json();

                    if (!data || data.length === 0) {
                        districtSuggestions.innerHTML =
                            '<li class="list-group-item text-muted">Районы не найдены — введите вручную</li>';
                        districtSuggestions.style.display = 'block';
                        return;
                    }

                    districtSuggestions.innerHTML = '';
                    districtSuggestionsData = data;
                    districtHighlightedIndex = -1;

                    data.forEach(item => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item list-group-item-action';
                        li.style.cursor = 'pointer';
                        li.textContent = item.name;

                        li.addEventListener('click', function () {
                            districtInput.value = item.name;
                            document.getElementById('dadata-district').value = item.name;
                            document.getElementById('info-district-text').textContent = item.name;
                            districtSuggestions.style.display = 'none';
                            districtHighlightedIndex = -1;
                        });

                        districtSuggestions.appendChild(li);
                    });


                    districtSuggestions.style.display = 'block';

                } catch (error) {
                    if (error.name === 'AbortError') return;
                    console.error('District fetch error:', error);
                }
            }, 300));

            districtInput.addEventListener('keydown', function (e) {
                const visible = districtSuggestions.style.display === 'block';
                if (!visible) return;

                const items = districtSuggestions.querySelectorAll('li');
                if (items.length === 0) return;

                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        if (districtHighlightedIndex >= 0) {
                            items[districtHighlightedIndex].classList.remove('active');
                        }
                        districtHighlightedIndex = (districtHighlightedIndex + 1) % items.length;
                        items[districtHighlightedIndex].classList.add('active');
                        items[districtHighlightedIndex].scrollIntoView({block: 'nearest'});
                        break;

                    case 'ArrowUp':
                        e.preventDefault();
                        if (districtHighlightedIndex >= 0) {
                            items[districtHighlightedIndex].classList.remove('active');
                        }
                        districtHighlightedIndex = districtHighlightedIndex <= 0
                            ? items.length - 1
                            : districtHighlightedIndex - 1;
                        items[districtHighlightedIndex].classList.add('active');
                        items[districtHighlightedIndex].scrollIntoView({block: 'nearest'});
                        break;

                    case 'Enter':
                        e.preventDefault();
                        if (districtHighlightedIndex !== -1 && districtSuggestionsData[districtHighlightedIndex]) {
                            const item = districtSuggestionsData[districtHighlightedIndex];
                            districtInput.value = item.name;
                            document.getElementById('dadata-district').value = item.name;
                            document.getElementById('info-district-text').textContent = item.name;
                            districtSuggestions.style.display = 'none';
                            districtHighlightedIndex = -1;
                            findBtn.disabled = false;
                        }
                        break;

                    case 'Escape':
                        districtSuggestions.style.display = 'none';
                        districtHighlightedIndex = -1;
                        break;
                }
            });


// Скрыть подсказки при клике вне поля
            document.addEventListener('click', function (e) {
                if (!districtInput.contains(e.target) && !districtSuggestions.contains(e.target)) {
                    districtSuggestions.style.display = 'none';
                }
            });

        });

        // Кнопка "Найти на карте" — центрируем и показываем балун
        findBtn.addEventListener('click', function () {
            if (!selectedAddress) return;

            const lat = parseFloat(document.getElementById('dadata-lat').value);
            const lon = parseFloat(document.getElementById('dadata-lon').value);

            if (lat && lon) {
                const latlng = L.latLng(lat, lon);
                map.setView(latlng, 15);
                showAddressForm(latlng);
            }
        });

        // Балун с формой
        function showAddressForm(latlng) {
            if (currentPopup) map.closePopup();

            const city = document.getElementById('dadata-city').value;
            const district = document.getElementById('dadata-district').value;
            const street = document.getElementById('dadata-street').value;
            const house = document.getElementById('dadata-house').value;

            const formHTML = `<form id="popupForm">
            <input type="hidden" name="_token" value="${window.csrfToken}">
            <input type="hidden" name="city_name" value="${city}">
            <input type="hidden" name="district_name" value="${district}">
            <input type="hidden" name="street" value="${street}">
            <input type="hidden" name="houseNumber" value="${house}">
            <input type="hidden" name="latitude" value="${latlng.lat.toFixed(6)}">
            <input type="hidden" name="longitude" value="${latlng.lng.toFixed(6)}">
            <input type="hidden" name="subj_id" value="${window.subjId}">
            <input type="hidden" name="obj_id" value="${window.objId}">

            <span><b>${window.nameSubj}</b></span><br>
            <span>${city ? city + ', ' : ''}${street ? street + ', ' : ''}${house || ''}</span><br><br>
            <span>Район: ${district || 'не указан'}</span><br>
            <span>Координаты: ${latlng.lat.toFixed(6)}, ${latlng.lng.toFixed(6)}</span><br>
            <span class="text-muted small">Кликните по карте, чтобы переместить точку</span><br><br>

            <button type="submit" class="btn btn-sm btn-success">Сохранить</button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="map.closePopup()">Отмена</button>
        </form>`;

            currentPopup = L.popup()
                .setLatLng(latlng)
                .setContent(formHTML)
                .openOn(map);
        }

        // Отправка
        async function handleFormSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const lat = formData.get('latitude');
            const lon = formData.get('longitude');

            try {
                const response = await fetch(window.savePointUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {'X-Requested-With': 'XMLHttpRequest'}
                });
                const res = await response.json();

                if (res.success === false) {
                    alert(res.message);
                    window.location.href = window.myObjUrl;
                } else {
                    map.closePopup();
                    L.marker([lat, lon]).addTo(map).bindPopup('<b>Точка добавлена</b>');
                    alert('Точка успешно сохранена!');
                    window.location.href = window.myObjUrl;
                }
            } catch (error) {
                console.error('Ошибка:', error);
                alert('Не удалось сохранить точку.');
            }
        }

        // Вспомогательная функция: показать/скрыть ручной ввод района
        function toggleManualDistrict(hasDistrict) {
            const textEl = document.getElementById('info-district-text');
            const inputEl = document.getElementById('manual-district');

            if (hasDistrict) {
                textEl.style.display = 'inline';
                if (inputEl) inputEl.style.display = 'none';
            } else {
                textEl.style.display = 'none';
                if (inputEl) {
                    inputEl.style.display = 'inline-block';
                    inputEl.focus();
                }
            }
        }

        document.addEventListener('click', function (e) {
            if (!addressInput.contains(e.target) && !suggestionsList.contains(e.target)) {
                suggestionsList.style.display = 'none';
            }
        });
    </script>

@endsection
