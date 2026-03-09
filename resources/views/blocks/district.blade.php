<div class="dropdown-container">
    <button type="button"
            class="btn-festive-gradient btn-festive-gradient-white dropdown-toggle none-shadow">
        Район
    </button>
    <div class="dropdown-menu-custom">
        <h6 class="px-3 py-2 text-muted fw-semibold">Район Санкт‑Петербурга</h6>
        <div class="px-3">
            <label class="form-check-label">
                <input type="checkbox" name="district[]" value="Адмиралтейский">
                <span>Адмиралтейский</span>
            </label>
            <label class="form-check-label">
                <input type="checkbox" name="district[]" value="Василеостровский">
                <span>Василеостровский</span>
            </label>
            <label class="form-check-label">
                <input type="checkbox" name="district[]" value="Выборгский">
                <span>Выборгский</span>
            </label>
            <label class="form-check-label">
                <input type="checkbox" name="district[]" value="Калининский">
                <span>Калининский</span>
            </label>
            <label class="form-check-label">
                <input type="checkbox" name="district[]" value="Кировский">
                <span>Кировский</span>
            </label>
            <label class="form-check-label">
                <input type="checkbox" name="district[]" value="Колпинский">
                <span>Колпинский</span>
            </label>
            <label class="form-check-label">
                <input type="checkbox" name="district[]" value="Красногвардейский">
                <span>Красногвардейский</span>
            </label>
            <label class="form-check-label">
                <input type="checkbox" name="district[]" value="Красносельский">
                <span>Красносельский</span>
            </label>
            <label class="form-check-label">
                <input type="checkbox" name="district[]" value="Кронштадтский">
                <span>Кронштадтский</span>
            </label>
            <label class="form-check-label">
                <input type="checkbox" name="district[]" value="Курортный">
                <span>Курортный</span>
            </label>
            <label class="form-check-label">
                <input type="checkbox" name="district[]" value="Московский">
                <span>Московский</span>
            </label>
            <label class="form-check-label">
                <input type="checkbox" name="district[]" value="Невский">
                <span>Невский</span>
            </label>
            <label class="form-check-label">
                <input type="checkbox" name="district[]" value="Петроградский">
                <span>Петроградский</span>
            </label>
            <label class="form-check-label">
                <input type="checkbox" name="district[]" value="Петродворцовый">
                <span>Петродворцовый</span>
            </label>
            <label class="form-check-label">
                <input type="checkbox" name="district[]" value="Приморский">
                <span>Приморский</span>
            </label>
            <label class="form-check-label">
                <input type="checkbox" name="district[]" value="Пушкинский">
                <span>Пушкинский</span>
            </label>
            <label class="form-check-label">
                <input type="checkbox" name="district[]" value="Фрунзенский">
                <span>Фрунзенский</span>
            </label>
            <label class="form-check-label">
                <input type="checkbox" name="district[]" value="Центральный">
                <span>Центральный</span>
            </label>
        </div>
    </div>
</div>

<!-- Скрипт 2: Восстановление значений district (множественный выбор) -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Безопасное получение данных сессии с гарантированным типом «объект»
        const sessionFilters = @json(session('selected_filters', []));

        // Проверяем, что sessionFilters — это объект и содержит ключ district
        if (sessionFilters &&
            typeof sessionFilters === 'object' &&
            sessionFilters.district !== undefined &&
            Array.isArray(sessionFilters.district)) {

            sessionFilters.district.forEach(value => {
                const checkbox = document.querySelector(`input[name="district[]"][value="${value}"]`);

                if (checkbox) {
                    checkbox.checked = true;

                    // Визуальное выделение выбранного элемента
                    const label = checkbox.closest('.form-check-label');
                    if (label) {
                        label.style.backgroundColor = '#e8f4fd';
                        label.style.fontWeight = '500';
                    }
                }
            });

            // Безопасный вызов функции с проверкой существования
            if (typeof updateDistrictButton === 'function') {
                updateDistrictButton();
            } else {
                console.warn('Функция updateDistrictButton не найдена. Убедитесь, что скрипт с функцией загружен.');
            }
        }
    });
</script>

