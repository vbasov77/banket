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
    if (houseNumberInput.value.length >= 1) {
        saveButton.disabled = false;
    }

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
        // selectedCoordinates = {lat: item.lat, lon: item.lon};

    } else if (suggestionsList === districtSuggestions) {
        districtInput.value = item.name;
        selectedDistrict = item.name;
        selectedDistrictId = item.id;
        streetInput.disabled = false;
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
    address = [selectedCity, selectedStreet, houseNumberInput.value]
        .filter(part => part && part.trim())
        .join(', ');
}

function isFormComplete() {
    return !!selectedCity &&
        !!selectedDistrict &&
        !!selectedStreet &&
        !!houseNumberInput.value &&
        houseNumberInput.value.trim() !== '';
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

