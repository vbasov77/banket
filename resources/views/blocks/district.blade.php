<div class="dropdown-container">
    <button type="button"
            class="btn-festive-gradient btn-festive-gradient-white dropdown-toggle none-shadow"
            id="districtDropdown"
            data-bs-toggle="dropdown"
            aria-expanded="false">
        Район
    </button>
    <div class="dropdown-menu-custom" id="districtDropdownMenu">
        <h6 class="px-3 py-2 text-muted fw-semibold">Район <span id="currentCity">Санкт‑Петербурга</span></h6>
        <div class="px-3" id="districtsContainer">
            <!-- Сюда будут подгружаться районы -->
            <div id="loadingSpinner" class="text-center py-2">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Загрузка районов...
            </div>
        </div>
    </div>
</div>


<!-- Скрипт 2: Восстановление значений district (множественный выбор) -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const districtsContainer = document.getElementById('districtsContainer');
        const currentCityElement = document.getElementById('currentCity');
        const loadingSpinner = document.getElementById('loadingSpinner');

        // Получаем город из сессии (можно передать через json в Blade)
        const userCity = @json(session('user_city', 'Санкт-Петербург'));
        currentCityElement.textContent = userCity;

        // Функция для загрузки районов
        async function loadDistricts() {
            try {
                loadingSpinner.style.display = 'block';
                districtsContainer.style.opacity = '0.5';

                const response = await fetch('{{ route("api.districts.by.city") }}');
                const data = await response.json();

                if (data.success) {
                    renderDistricts(data.districts);
                } else {
                    showError(data.message);
                }
            } catch (error) {
                console.error('Ошибка загрузки районов:', error);
                showError('Не удалось загрузить районы. Попробуйте позже.');
            } finally {
                loadingSpinner.style.display = 'none';
                districtsContainer.style.opacity = '1';
            }
        }

        // Отрисовка районов
        function renderDistricts(districts) {
            if (districts.length === 0) {
                districtsContainer.innerHTML = '<p class="text-muted px-3">Районы не найдены</p>';
                return;
            }

            let html = '';
            districts.forEach(district => {
                html += `
                <label class="form-check-label d-flex align-items-center mb-2">
                    <input type="checkbox" name="district[]" value="${district.name}" class="form-check-input me-2">
            <span>${district.name}</span>
        </label>
            `;
            });
            districtsContainer.innerHTML = html;

            // Восстанавливаем выбранные ранее районы
            restoreSelectedDistricts();
        }

        // Восстановление выбранных значений
        function restoreSelectedDistricts() {
            const sessionFilters = @json(session('selected_filters', []));

            if (sessionFilters && sessionFilters.district && Array.isArray(sessionFilters.district)) {
                sessionFilters.district.forEach(value => {
                    const checkbox = document.querySelector(`input[name="district[]"][value="${value}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                        const label = checkbox.closest('.form-check-label');
                        if (label) {
                            label.style.backgroundColor = '#e8f4fd';
                            label.style.fontWeight = '500';
                        }
                    }
                });

                // Вызываем функцию обновления кнопки, если она существует
                if (typeof updateDistrictButton === 'function') {
                    updateDistrictButton();
                }
            }
        }

        // Показ ошибки
        function showError(message) {
            districtsContainer.innerHTML = `<p class="text-danger px-3">${message}</p>`;
        }

        // Загружаем районы при открытии выпадающего списка
        document.getElementById('districtDropdown').addEventListener('click', function() {
            // Загружаем только если ещё не загружены
            if (!this.dataset.loaded) {
                loadDistricts();
                this.dataset.loaded = 'true';
            }
        });

        // Инициируем загрузку при загрузке страницы (если нужно)
        loadDistricts(); // Уберите эту строку, если хотите загружать только при клике
    });

</script>

