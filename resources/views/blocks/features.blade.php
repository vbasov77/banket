<div class="dropdown-container">
    <button type="button"
            class="btn-festive-gradient btn-festive-gradient-white dropdown-toggle none-shadow">
        Особенности
    </button>
    <div class="dropdown-menu-custom">
        <h6 class="px-3 py-2 text-muted fw-semibold">Особенности</h6>
        <div class="px-3">
            <label class="form-check-label">
                <input name="features[]" class="features" type="checkbox"
                       value="У воды">
                <span ></span>
                У воды
            </label>
            <label class="form-check-label">
                <input name="features[]" class="features" type="checkbox"
                       value="У воды">
                <span ></span>
                За городом
            </label>
            <label class="form-check-label">
                <input name="features[]" class="features" type="checkbox"
                       value="Подарки за бронирование">
                <span ></span>
                Подарки за бронирование
            </label>
            <label class="form-check-label">
                <input name="features[]" class="features" type="checkbox"
                       value="Можно свои б/а напитки">
                <span ></span>
                Можно свои б/а напитки
            </label>
            <label class="form-check-label">
                <input name="features[]" class="features" type="checkbox"
                       value="Без пробкового сбора">
                <span ></span>
                Без "пробкового" сбора
            </label>
            <label class="form-check-label">
                <input name="features[]" class="features" type="checkbox"
                       value="Выездная регистрация">
                <span ></span>
                Выездная регистрация
            </label>
            <label class="form-check-label">
                <input name="features[]" class="features" type="checkbox"
                       value="Музыкальное оборудование">
                <span ></span>
                Музыкальное оборудование
            </label>
        </div>
    </div>
</div>

<!-- Скрипт 2: Восстановление значений features (множественный выбор) -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Безопасное получение данных сессии с гарантированным типом «объект»
        const sessionFilters = @json(session('selected_filters', []));

        // Проверяем, что sessionFilters — это объект и содержит ключ features
        if (sessionFilters &&
            typeof sessionFilters === 'object' &&
            sessionFilters.features !== undefined &&
            Array.isArray(sessionFilters.features)) {

            sessionFilters.features.forEach(value => {
                const checkbox = document.querySelector(`input[name="features[]"][value="${value}"]`);

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
            if (typeof updateFeaturesButton === 'function') {
                updateFeaturesButton();
            } else {
                console.warn('Функция updateFeaturesButton не найдена. Убедитесь, что скрипт с функцией загружен.');
            }
        }
    });
</script>

