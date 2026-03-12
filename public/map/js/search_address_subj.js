let selectedCity = '';
let selectedStreet = '';
let selectedCoordinates = {};
let address = null;

const cityInput = document.getElementById('city');
const streetInput = document.getElementById('street');
const houseNumberInput = document.getElementById('house-number');
const saveButton = document.getElementById('save-address');

const citySuggestions = document.getElementById('city-suggestions');
const streetSuggestions = document.getElementById('street-suggestions');

saveButton.disabled = true;

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
        li.dataset.lat = item.lat;
        li.dataset.lon = item.lon;
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

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Expected JSON, but got:', text.substring(0, 200));
            throw new Error('Invalid response format: expected JSON');
        }

        const cities = await response.json();
        showSuggestions(citySuggestions, cities);
        setupMapClickHandler();

    } catch (error) {
        console.error('Error fetching cities:', error);
        citySuggestions.innerHTML = '<li class="error">Ошибка загрузки городов</li>';
        citySuggestions.classList.add('visible');
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

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Expected JSON, but got:', text.substring(0, 200));
            throw new Error('Invalid response format: expected JSON');
        }

        const streets = await response.json();
        showSuggestions(streetSuggestions, streets);
        setupMapClickHandler();
    } catch (error) {
        console.error('Error fetching streets:', error);
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
        selectedCity = item.name;
        streetInput.disabled = false;
        houseNumberInput.disabled = true;
        saveButton.disabled = true;
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

