let selectedCity = '';
let selectedCityId = '';
let selectedDistrictId = '';
let selectedStreet = '';
let selectedCoordinates = {};
let address = null;
let selectedDistrict = '';

const cityInput = document.getElementById('city');
const districtInput = document.getElementById('district');
const districtSuggestions = document.getElementById('district-suggestions');
const streetInput = document.getElementById('street');
const houseNumberInput = document.getElementById('house-number');
const saveButton = document.getElementById('save-address');

const citySuggestions = document.getElementById('city-suggestions');
const streetSuggestions = document.getElementById('street-suggestions');

saveButton.disabled = true;

function isFormComplete() {
    return !!selectedCity &&
        !!selectedDistrict &&
        !!streetInput.value &&
        !!houseNumberInput.value &&
        houseNumberInput.value.trim() !== '';
}

function showErrorAlert(message) {
    // Создаём контейнер для алерта, если его ещё нет
    let alertContainer = document.getElementById('error-alert');
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.id = 'error-alert';
        alertContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #ff4444;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            z-index: 10000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        `;
        document.body.appendChild(alertContainer);
    }

    // Устанавливаем сообщение и показываем
    alertContainer.textContent = message;
    alertContainer.style.display = 'block';

    // Автоматически скрываем через 5 секунд
    setTimeout(() => {
        alertContainer.style.display = 'none';
    }, 5000);
}


// Функция debounce
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Вспомогательная функция для показа подсказок
function showSuggestions(suggestionsList, data) {
    suggestionsList.innerHTML = '';
    suggestionsData = data; // сохраняем данные для навигации
    currentHighlightedIndex = -1; // сбрасываем выделение

    if (data.length === 0) {
        suggestionsList.classList.remove('visible');
        return;
    }

    data.forEach((item, index) => {
        const li = document.createElement('li');
        li.textContent = item.name;
        li.dataset.id = item.id;
        li.dataset.index = index; // добавляем индекс для навигации
        li.addEventListener('click', () => {
            selectSuggestion(item, suggestionsList);
        });

        suggestionsList.appendChild(li);
    });

    suggestionsList.classList.add('visible');
}


// Обработчик для города
cityInput.addEventListener('input', debounce(async (e) => {
    const query = e.target.value;

    if (query.length < 2) {
        citySuggestions.innerHTML = '';
        citySuggestions.classList.remove('visible');
        streetInput.disabled = true;
        streetSuggestions.innerHTML = '';
        streetSuggestions.classList.remove('visible');
        selectedCity = '';
        updateCurrentAddress();
        return;
    }

    try {
        const response = await fetch(`/api/cities?q=${encodeURIComponent(query)}`);

        // Обработка сетевых ошибок (нет соединения, CORS и т. д.)
        if (!response.ok) {
            if (response.status >= 500) {
                // Ошибки сервера 5xx
                console.error('Server error in city search:', {
                    status: response.status,
                    statusText: response.statusText,
                    url: response.url
                });
                showErrorAlert('Непредвиденная ошибка, попробуйте чуть позже.');
            } else if (response.status === 404) {
                // Нет результатов
                citySuggestions.innerHTML = '<li class="error">Города не найдены</li>';
                citySuggestions.classList.add('visible');
            } else {
                // Другие HTTP‑ошибки (4xx)
                console.warn('Client error in city search:', {
                    status: response.status,
                    statusText: response.statusText
                });
                showErrorAlert('Ошибка загрузки данных. Проверьте запрос.');
            }
            return;
        }

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Expected JSON, but got:', text.substring(0, 200));
            showErrorAlert('Ошибка формата данных. Попробуйте позже.');
            return;
        }

        const cities = await response.json();

        // Дополнительная проверка структуры данных
        if (!Array.isArray(cities)) {
            console.error('Invalid data structure from server:', cities);
            showErrorAlert('Ошибка данных с сервера. Попробуйте позже.');
            return;
        }

        showSuggestions(citySuggestions, cities);
        setupMapClickHandler();

    } catch (error) {
        // Сетевые ошибки (нет интернета, прерванное соединение и т. д.)
        console.error('Network error or unexpected error in city search:', error);

        if (error.name === 'TypeError' && error.message.includes('failed to fetch')) {
            showErrorAlert('Проверьте подключение к интернету.');
        } else {
            showErrorAlert('Непредвиденная ошибка, попробуйте чуть позже.');
        }
    }
}, 300));

// Обработчик для района
districtInput.addEventListener('input', debounce(async (e) => {
    const query = e.target.value;

    if (!selectedCity || query.length < 2) {
        districtSuggestions.innerHTML = '';
        districtSuggestions.classList.remove('visible');
        return;
    }

    try {
        const response = await fetch(
            `/api/districts?city_data_city_id=${encodeURIComponent(selectedCityId)}&q=${encodeURIComponent(query)}`
        );

        // Обработка сетевых и HTTP‑ошибок
        if (!response.ok) {
            if (response.status >= 500) {
                // Ошибки сервера 5xx
                console.error('Server error in district search:', {
                    status: response.status,
                    statusText: response.statusText,
                    url: response.url
                });
                showErrorAlert('Непредвиденная ошибка, попробуйте чуть позже.');
            } else if (response.status === 404) {
                // Нет результатов
                districtSuggestions.innerHTML = '<li class="error">Районы не найдены</li>';
                districtSuggestions.classList.add('visible');
            } else {
                // Другие HTTP‑ошибки (4xx)
                console.warn('Client error in district search:', {
                    status: response.status,
                    statusText: response.statusText
                });
                showErrorAlert('Ошибка загрузки данных. Проверьте запрос.');
            }
            return;
        }

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Expected JSON, but got:', text.substring(0, 200));
            showErrorAlert('Ошибка формата данных. Попробуйте позже.');
            return;
        }

        const districts = await response.json();

        // Дополнительная проверка структуры данных
        if (!Array.isArray(districts)) {
            console.error('Invalid data structure from server:', districts);
            showErrorAlert('Ошибка данных с сервера. Попробуйте позже.');
            return;
        }

        showSuggestions(districtSuggestions, districts);
        setupMapClickHandler();

    } catch (error) {
        // Сетевые ошибки (нет интернета, прерванное соединение и т. д.)
        console.error('Network error or unexpected error in district search:', error);

        if (error.name === 'TypeError' && error.message.includes('failed to fetch')) {
            showErrorAlert('Проверьте подключение к интернету.');
        } else {
            showErrorAlert('Непредвиденная ошибка, попробуйте чуть позже.');
        }
    }
}, 300));


// Обработчик для улицы
streetInput.addEventListener('input', debounce(async (e) => {
    if (streetInput.value.length >= 1) {
        saveButton.disabled = false;
    }
    const query = e.target.value;

    if (!selectedCity || query.length < 2) {
        streetSuggestions.innerHTML = '';
        streetSuggestions.classList.remove('visible');
        return;
    }

    try {
        const response = await fetch(
            `/api/streets?city=${encodeURIComponent(selectedCity)}&q=${encodeURIComponent(query)}`
        );

        // Обработка HTTP‑ошибок (4xx, 5xx)
        if (!response.ok) {
            if (response.status >= 500) {
                // Ошибки сервера 5xx
                console.error('Server error in street search:', {
                    status: response.status,
                    statusText: response.statusText,
                    url: response.url
                });
                showErrorAlert('Непредвиденная ошибка, попробуйте чуть позже.');
            } else if (response.status === 404) {
                // Нет результатов
                streetSuggestions.innerHTML = '<li class="error">Улицы не найдены</li>';
                streetSuggestions.classList.add('visible');
            } else {
                // Другие HTTP‑ошибки (4xx)
                console.warn('Client error in street search:', {
                    status: response.status,
                    statusText: response.statusText
                });
                showErrorAlert('Ошибка загрузки данных. Проверьте запрос.');
            }
            return;
        }

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Expected JSON, but got:', text.substring(0, 200));
            showErrorAlert('Ошибка формата данных. Попробуйте позже.');
            return;
        }

        const streets = await response.json();

        // Дополнительная проверка структуры данных
        if (!Array.isArray(streets)) {
            console.error('Invalid data structure from server:', streets);
            showErrorAlert('Ошибка данных с сервера. Попробуйте позже.');
            return;
        }

        showSuggestions(streetSuggestions, streets);
        setupMapClickHandler();

    } catch (error) {
        // Сетевые ошибки (нет интернета, прерванное соединение и т. д.)
        console.error('Network error or unexpected error in street search:', error);

        if (error.name === 'TypeError' && error.message.includes('failed to fetch')) {
            // Конкретная ошибка отсутствия интернета
            showErrorAlert('Проверьте подключение к интернету.');
        } else {
            // Все остальные ошибки
            showErrorAlert('Непредвиденная ошибка, попробуйте чуть позже.');
        }

        // Показываем ошибку в подсказках
        streetSuggestions.innerHTML = '<li class="error">Ошибка загрузки улиц</li>';
        streetSuggestions.classList.add('visible');
    }
}, 300));


// Обработчик для номера дома
houseNumberInput.addEventListener('input', () => {

    updateCurrentAddress();
    setupMapClickHandler();
});

function selectSuggestion(item, suggestionsList) {
    if (suggestionsList === citySuggestions) {
        cityInput.value = item.name;
        cityInput.dataset.cityId = item.id;
        selectedCity = item.name;
        selectedCityId = item.id;
        districtInput.disabled = false;
        streetInput.disabled = true;
        houseNumberInput.disabled = true;
        saveButton.disabled = true;
        districtInput.focus();

    } else if (suggestionsList === districtSuggestions) {
        districtInput.value = item.name;
        selectedDistrict = item.name;
        selectedDistrictId = item.id;
        streetInput.disabled = false;
        houseNumberInput.disabled = false;
        streetInput.focus();
        selectedCoordinates = {lat: item.lat, lon: item.lon};

    } else {
        streetInput.value = item.name;
        selectedStreet = item.name;
        houseNumberInput.disabled = false;
        houseNumberInput.focus();
        selectedCoordinates = {lat: item.lat, lon: item.lon};
    }
    suggestionsList.innerHTML = '';
    suggestionsList.classList.remove('visible');
    updateCurrentAddress();
}

// Функция для актуализации адреса
function updateCurrentAddress() {
    address = [selectedCity, streetInput.value, houseNumberInput.value]
        .filter(part => part && part.trim())
        .join(', ');
}



function highlightNextItem() {
    const items = citySuggestions.querySelectorAll('li');
    if (items.length === 0) return;

    // снимаем выделение с предыдущего
    if (currentHighlightedIndex >= 0) {
        items[currentHighlightedIndex].classList.remove('highlighted');
    }

    // переходим к следующему (или к первому)
    currentHighlightedIndex = (currentHighlightedIndex + 1) % items.length;
    items[currentHighlightedIndex].classList.add('highlighted');

    // скроллим к выделенному элементу
    scrollToHighlighted(items[currentHighlightedIndex]);
}

function highlightPreviousItem() {
    const items = citySuggestions.querySelectorAll('li');
    if (items.length === 0) return;

    // снимаем выделение с предыдущего
    if (currentHighlightedIndex >= 0) {
        items[currentHighlightedIndex].classList.remove('highlighted');
    }

    // переходим к предыдущему (или к последнему)
    currentHighlightedIndex = currentHighlightedIndex <= 0 ? items.length - 1 : currentHighlightedIndex - 1;
    items[currentHighlightedIndex].classList.add('highlighted');

    // скроллим к выделенному элементу
    scrollToHighlighted(items[currentHighlightedIndex]);
}

function highlightNextStreetItem() {
    const items = streetSuggestions.querySelectorAll('li');
    if (items.length === 0) return;

    if (currentHighlightedIndex >= 0) {
        items[currentHighlightedIndex].classList.remove('highlighted');
    }

    currentHighlightedIndex = (currentHighlightedIndex + 1) % items.length;
    items[currentHighlightedIndex].classList.add('highlighted');
    scrollToHighlighted(items[currentHighlightedIndex]);
}

function highlightPreviousStreetItem() {
    const items = streetSuggestions.querySelectorAll('li');
    if (items.length === 0) return;

    if (currentHighlightedIndex >= 0) {
        items[currentHighlightedIndex].classList.remove('highlighted');
    }

    currentHighlightedIndex = currentHighlightedIndex <= 0 ? items.length - 1 : currentHighlightedIndex - 1;
    items[currentHighlightedIndex].classList.add('highlighted');
    scrollToHighlighted(items[currentHighlightedIndex]);
}

function highlightNextDistrictItem() {
    const items = districtSuggestions.querySelectorAll('li');
    if (items.length === 0) return;

    if (currentHighlightedIndex >= 0) {
        items[currentHighlightedIndex].classList.remove('highlighted');
    }

    currentHighlightedIndex = (currentHighlightedIndex + 1) % items.length;
    items[currentHighlightedIndex].classList.add('highlighted');
    scrollToHighlighted(items[currentHighlightedIndex]);
}

function highlightPreviousDistrictItem() {
    const items = districtSuggestions.querySelectorAll('li');
    if (items.length === 0) return;

    if (currentHighlightedIndex >= 0) {
        items[currentHighlightedIndex].classList.remove('highlighted');
    }

    currentHighlightedIndex = currentHighlightedIndex <= 0 ? items.length - 1 : currentHighlightedIndex - 1;
    items[currentHighlightedIndex].classList.add('highlighted');
    scrollToHighlighted(items[currentHighlightedIndex]);
}


function scrollToHighlighted(element) {
    element.scrollIntoView({
        block: 'nearest',
        inline: 'start'
    });
}

// Обработчик клавиатуры для навигации
cityInput.addEventListener('keydown', (e) => {
    const suggestionsVisible = citySuggestions.classList.contains('visible');

    if (!suggestionsVisible) return;

    switch (e.key) {
        case 'ArrowDown':
            e.preventDefault();
            highlightNextItem();
            break;
        case 'ArrowUp':
            e.preventDefault();
            highlightPreviousItem();
            break;
        case 'Enter':
            e.preventDefault();
            if (currentHighlightedIndex !== -1 && suggestionsData[currentHighlightedIndex]) {
                selectSuggestion(suggestionsData[currentHighlightedIndex], citySuggestions);
            }
            break;
        case 'Escape':
            citySuggestions.classList.remove('visible');
            currentHighlightedIndex = -1;
            break;
    }

});

districtInput.addEventListener('keydown', (e) => {
    const suggestionsVisible = districtSuggestions.classList.contains('visible');

    if (!suggestionsVisible) return;

    switch (e.key) {
        case 'ArrowDown':
            e.preventDefault();
            highlightNextDistrictItem();
            break;
        case 'ArrowUp':
            e.preventDefault();
            highlightPreviousDistrictItem();
            break;
        case 'Enter':
            e.preventDefault();
            if (currentHighlightedIndex !== -1 && suggestionsData[currentHighlightedIndex]) {
                selectSuggestion(suggestionsData[currentHighlightedIndex], districtSuggestions);
            }
            break;
        case 'Escape':
            districtSuggestions.classList.remove('visible');
            currentHighlightedIndex = -1;
            break;
    }
});


streetInput.addEventListener('keydown', (e) => {
    const suggestionsVisible = streetSuggestions.classList.contains('visible');

    if (!suggestionsVisible) return;

    switch (e.key) {
        case 'ArrowDown':
            e.preventDefault();
            highlightNextStreetItem();
            break;
        case 'ArrowUp':
            e.preventDefault();
            highlightPreviousStreetItem();
            break;
        case 'Enter':
            e.preventDefault();
            if (currentHighlightedIndex !== -1 && suggestionsData[currentHighlightedIndex]) {
                selectSuggestion(suggestionsData[currentHighlightedIndex], streetSuggestions);
            }
            break;
        case 'Escape':
            streetSuggestions.classList.remove('visible');
            currentHighlightedIndex = -1;
            break;
    }
});

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

    // Настраиваем обработчик клика по карте
    setupMapClickHandler();

    // Добавляем делегированный обработчик
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form.id === 'popupForm' && !form._submitHandlerProcessed) {
            e.preventDefault();
            handleFormSubmit(e);
            form._submitHandlerProcessed = true; // защита от дублирования
        }
    });
});

// Функция для настройки обработчика клика по карте
function setupMapClickHandler() {
    map.off('click');
    if (isFormComplete()) {
        map.on('click', function (e) {
            const latlng = e.latlng;
            updateCurrentAddress(); // Перегенерируем адрес с учётом района
            showAddressForm(latlng, address);
        });
    } else {
        map.on('click', function () {
            alert('Сначала заполните город, район, улицу и номер дома.');
        });
    }
}


// Показываем форму в точке клика
async function showAddressForm(latlng, address) {

    // Проверка заполнения формы перед показом popup
    if (!isFormComplete()) {
        alert('Сначала заполните все поля формы: город, улицу и номер дома.');
        return;
    }

    // Закрываем предыдущий popup, если есть
    if (currentPopup) {
        map.closePopup();
    }

    // Генерируем HTML формы с подставленными координатами и реальным адресом
    const formHTML = `<form id="popupForm">
        <input type="hidden" name="_token" value="${window.csrfToken}">
        <input type="hidden" name="city_id" value="${selectedCityId}">
        <input type="hidden" name="district_id" value="${selectedDistrictId}">
        <input type="hidden" name="street" value="${streetInput.value}">
        <input type="hidden" name="houseNumber" value="${houseNumberInput.value}">
        <input type="hidden" name="latitude" value="${latlng.lat.toFixed(6)}">
        <input type="hidden" name="longitude" value="${latlng.lng.toFixed(6)}">
        <input type="hidden" name="subj_id" value="${window.subjId}">
        <input type="hidden" name="obj_id" value="${window.objId}">
        <span><b>${window.nameSubj}</b></span><br><br>
        <span>Мы здесь 🙂</span><br><br>
        <button type="submit" class="btn btn-sm btn-success">Сохранить</button>
    </form>`;


    // Создаём popup с формой
    currentPopup = L.popup()
        .setLatLng(latlng)
        .setContent(formHTML)
        .openOn(map);
}

// Обработчик отправки формы
async function handleFormSubmit(e) {
    e.preventDefault(); // Обязательно: предотвращаем стандартную отправку формы
    const form = e.target;

    // Повторная проверка заполнения формы
    if (!isFormComplete()) {
        alert('Форма не заполнена полностью. Проверьте все поля.');
        return;
    }

    const cityIdField = form.querySelector('[name="city_id"]');
    const districtIdField = form.querySelector('[name="district_id"]');
    const streetField = form.querySelector('[name="street"]');
    const houseNumberField = form.querySelector('[name="houseNumber"]');
    const latitude = form.querySelector('[name="latitude"]').value;
    const longitude = form.querySelector('[name="longitude"]').value;

    if (!cityIdField?.value || !districtIdField?.value || !streetField?.value || !houseNumberField?.value) {
        alert('Заполните все обязательные поля.');
        return;
    }

    const formData = new FormData(form);

    try {
        const response = await fetch(window.savePointUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const res = await response.json();
        console.log(res.success);
        if (res.success === false) {
            alert(res.message)

            window.location.href = `${window.myObjUrl}`;
        } else {
            // Закрываем форму
            map.closePopup();

            // Добавляем маркер на карту
            L.marker([latitude, longitude])
                .addTo(map)
                .bindPopup(`<b>Точка добавлена</b><br>`);
            alert('Точка успешно сохранена!');
            window.location.href = `${window.myObjUrl}`;
        }
    } catch (error) {
        console.error('Ошибка при сохранении точки:', error);
        alert('Не удалось сохранить точку. Проверьте подключение.');
    }
}

// Функция для поиска адреса (вызывается по кнопке «Найти на карте»)
async function geocodeAddress() {
    updateCurrentAddress();

    if (!address) {
        alert('Введите адрес для поиска');
        return;
    }

    try {
        // Используем Nominatim для геокодирования
        console.log(address);
        const response = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(address)}&format=json&limit=1`);
        const data = await response.json();

        if (data.length > 0) {
            const lat = parseFloat(data[0].lat);
            const lng = parseFloat(data[0].lon);

            // Центрируем карту на найденном адресе
            map.setView([lat, lng], 15);

            // Показываем форму добавления точки
            showAddressForm(L.latLng(lat, lng), address);
        } else {
            alert('Адрес не найден. Уточните запрос.');
        }
    } catch (error) {
        console.error('Ошибка при поиске адреса:', error);
        alert('Произошла ошибка при поиске адреса.');
    }
}
