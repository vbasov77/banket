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
        <input type="hidden" name="city" value="${selectedCityId}">
        <input type="hidden" name="district" value="${selectedDistrictId}">
        <input type="hidden" name="street" value="${selectedStreet}">
        <input type="hidden" name="houseNumber" value="${houseNumberInput.value}">
        <input type="hidden" name="latitude" value="${latlng.lat.toFixed(6)}">
        <input type="hidden" name="longitude" value="${latlng.lng.toFixed(6)}">
        <input type="hidden" name="subj_id" value="${window.subjId}">
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

    const cityField = form.querySelector('[name="city"]');
    const districtField = form.querySelector('[name="district"]');
    const streetField = form.querySelector('[name="street"]');
    const houseNumberField = form.querySelector('[name="houseNumber"]');
    const latitude = form.querySelector('[name="latitude"]').value;
    const longitude = form.querySelector('[name="longitude"]').value;

    if (!cityField.value || !districtField || !streetField || !houseNumberField ||  !latitude || !longitude) {
        alert('Недостаточно данных для сохранения метки.');
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
