@php
    $cityValue = old('city_id', $performer->city_id ?? '');
    $cityName  = old('city_name', $performer->city->name ?? '');
@endphp

<div class="col-md-6 position-relative">
    <label for="city-input" class="form-label">Город <span class="text-danger">*</span></label>

    {{-- скрытое поле — в форму уходит именно ID --}}
    <input type="hidden" name="city_id" id="city_id" value="{{ $cityValue }}">

    <input type="text" id="city-input" autocomplete="off"
           class="form-control @error('city_id') is-invalid @enderror"
           value="{{ $cityName }}"
           placeholder="Начните вводить: Санкт-Петербург..." required>
    @error('city_id')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror

    <ul id="city-suggestions"
        class="list-unstyled position-absolute bg-white border rounded shadow-sm w-100 m-0 p-0"
        style="z-index: 1050; max-height: 240px; overflow-y: auto; display: none;">
    </ul>
</div>

<style>
    #city-suggestions li { padding: 8px 12px; cursor: pointer; font-size: 14px; }
    #city-suggestions li:hover { background: #f3f4f6; }
    #city-suggestions li.highlighted { background: #f3f4f6; color: #ff5722; font-weight: 600; }
    #city-suggestions li.error { color: #9ca3af; cursor: default; }
</style>

<script>
    (function () {
        const cityInput = document.getElementById('city-input');
        const cityIdInput = document.getElementById('city_id');
        const citySuggestions = document.getElementById('city-suggestions');

        let suggestionsData = [];
        let currentHighlightedIndex = -1;

        function debounce(func, wait) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func(...args), wait);
            };
        }

        function hideSuggestions() {
            citySuggestions.style.display = 'none';
            currentHighlightedIndex = -1;
        }

        function showSuggestions(data) {
            suggestionsData = data;
            currentHighlightedIndex = -1;
            citySuggestions.innerHTML = '';

            if (!data.length) {
                citySuggestions.innerHTML = '<li class="error">Города не найдены</li>';
                citySuggestions.style.display = 'block';
                return;
            }

            data.forEach((item, index) => {
                const li = document.createElement('li');
                li.textContent = item.name;
                li.dataset.id = item.id;
                li.dataset.index = index;
                li.addEventListener('click', () => selectCity(item));
                citySuggestions.appendChild(li);
            });

            citySuggestions.style.display = 'block';
        }

        function selectCity(item) {
            cityInput.value = item.name;
            cityIdInput.value = item.id;
            hideSuggestions();
        }

        function highlightNext() {
            const items = citySuggestions.querySelectorAll('li:not(.error)');
            if (!items.length) return;

            if (currentHighlightedIndex >= 0) items[currentHighlightedIndex].classList.remove('highlighted');
            currentHighlightedIndex = (currentHighlightedIndex + 1) % items.length;
            items[currentHighlightedIndex].classList.add('highlighted');
            items[currentHighlightedIndex].scrollIntoView({ block: 'nearest' });
        }

        function highlightPrevious() {
            const items = citySuggestions.querySelectorAll('li:not(.error)');
            if (!items.length) return;

            if (currentHighlightedIndex >= 0) items[currentHighlightedIndex].classList.remove('highlighted');
            currentHighlightedIndex = currentHighlightedIndex <= 0 ? items.length - 1 : currentHighlightedIndex - 1;
            items[currentHighlightedIndex].classList.add('highlighted');
            items[currentHighlightedIndex].scrollIntoView({ block: 'nearest' });
        }

        cityInput.addEventListener('input', debounce(async (e) => {
            const query = e.target.value.trim();

            // сбрасываем выбранный город при правке текста
            cityIdInput.value = '';

            if (query.length < 2) {
                hideSuggestions();
                return;
            }

            try {
                const response = await fetch(`/api/cities?q=${encodeURIComponent(query)}`);
                if (!response.ok) {
                    citySuggestions.innerHTML = '<li class="error">Ошибка загрузки. Попробуйте позже.</li>';
                    citySuggestions.style.display = 'block';
                    return;
                }

                const cities = await response.json();
                showSuggestions(Array.isArray(cities) ? cities : []);
            } catch (error) {
                citySuggestions.innerHTML = '<li class="error">Нет соединения. Проверьте интернет.</li>';
                citySuggestions.style.display = 'block';
            }
        }, 300));

        cityInput.addEventListener('keydown', (e) => {
            const visible = citySuggestions.style.display === 'block';
            if (!visible) return;

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    highlightNext();
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    highlightPrevious();
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (currentHighlightedIndex !== -1 && suggestionsData[currentHighlightedIndex]) {
                        selectCity(suggestionsData[currentHighlightedIndex]);
                    }
                    break;
                case 'Escape':
                    hideSuggestions();
                    break;
            }
        });

        // клик мимо — закрываем список
        document.addEventListener('click', (e) => {
            if (!citySuggestions.contains(e.target) && e.target !== cityInput) {
                hideSuggestions();
            }
        });
    })();
</script>
