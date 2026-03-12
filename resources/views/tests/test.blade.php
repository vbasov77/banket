<script>
    let selectedCity = '';
    let selectedStreet = '';
    let selectedCoordinates = {};

    const cityInput = document.getElementById('city');
    const streetInput = document.getElementById('street');
    const houseNumberInput = document.getElementById('house-number');
    const saveButton = document.getElementById('save-address');
    const statusMessage = document.getElementById('status-message');

    const citySuggestions = document.getElementById('city-suggestions');
    const streetSuggestions = document.getElementById('street-suggestions');

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
        if (data.length === 0) {
            suggestionsList.classList.remove('visible');
            return;
        }
        data.forEach(item => {
            const li = document.createElement('li');
            li.textContent = item.name;
            li.dataset.lat = item.lat;
            li.dataset.lon = item.lon;
            li.addEventListener('click', () => {
                if (suggestionsList === citySuggestions) {
                    cityInput.value = item.name;
                    selectedCity = item.name;
                    streetInput.disabled = false;
                    houseNumberInput.disabled = true;
                    saveButton.disabled = true;
                    streetInput.focus();
                    selectedCoordinates = { lat: item.lat, lon: item.lon };
                } else {
                    streetInput.value = item.name;
                    selectedStreet = item.name;
                    houseNumberInput.disabled = false;
                    houseNumberInput.focus();
                    selectedCoordinates = { lat: item.lat, lon: item.lon };
                }
                suggestionsList.innerHTML = '';
                suggestionsList.classList.remove('visible');
                updateSaveButtonState();
            });
            suggestionsList.appendChild(li);
        });
        suggestionsList.classList.add('visible');
    }

    // Обновляет состояние кнопки «Сохранить»
    function updateSaveButtonState() {
        const hasCity = selectedCity !== '';
        const hasStreet = selectedStreet !== '';
        const hasHouseNumber = houseNumberInput.value.trim() !== '';
        saveButton.disabled = !(hasCity && hasStreet && hasHouseNumber);
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
        } catch (error) {
            console.error('Error fetching streets:', error);
            streetSuggestions.innerHTML = '<li class="error">Ошибка загрузки улиц</li>';
            streetSuggestions.classList.add('visible');
        }
    }, 300));

    // Обработчик для номера дома
    houseNumberInput.addEventListener('input', () => {
        updateSaveButtonState();
    });

    // Обработчик кнопки «Сохранить»
    saveButton.addEventListener('click', async () => {
        const addressData = {
            city: selectedCity,
            street: selectedStreet,
            house_number: houseNumberInput.value.trim(),
            coordinates: selectedCoordinates
        };

        try {
            statusMessage.style.display = 'block';
            statusMessage.textContent = 'Отправка данных...';
            statusMessage.className = '';

            const response = await fetch('/api/save-address', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(addressData)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            // Успешная отправка
            statusMessage.textContent = 'Адрес успешно сохранён!';
            statusMessage.className = 'success';

            // Сброс формы после успешной отправки
            setTimeout(() => {
                cityInput.value = '';
                streetInput.value = '';
                houseNumberInput.value = '';
                selectedCity = '';
                selectedStreet = '';
                selectedCoordinates = {};
                streetInput.disabled = true;
                houseNumberInput.disabled = true;
                saveButton.disabled = true;
                statusMessage.style.display = 'none';
            }, 2000);

        } catch (error) {
            console.error('Error saving address:', error);
            statusMessage.textContent = 'Ошибка при сохранении адреса. Попробуйте ещё раз.';
            statusMessage.className = 'error';
        }
    });

</script>
