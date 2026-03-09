<div class="dropdown-container">
    <button type="button"
            class="btn-festive-gradient btn-festive-gradient-white dropdown-toggle none-shadow">
        Вместимость до
    </button>
    <div class="dropdown-menu-custom">
        <h6 class="px-3 py-2 text-muted fw-semibold">Укажите максимальную вместимость</h6>
        <div class="px-3">
            <div class="mb-3">
                <label for="capacity_to_input" class="form-label">Количество гостей</label>
                <input type="number"
                       id="capacity_to_input"
                       name="capacity_to"
                       class="form-control"
                       placeholder="Например: 50"
                       min="1"
                       onkeypress="return (this.value.length < 6)"
                       value="@(session('selected_filters.capacity_to') ?? ''">
            </div>
            <small class="text-muted">Максимальное количество гостей, которое может вместить объект</small>
        </div>
    </div>
</div>

<!-- Скрипт 3: Восстановление значений capacity_to (числовое поле) -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Безопасное получение данных сессии с гарантированным типом «объект»
        const sessionFilters = @json(session('selected_filters', []));
        const capacityInput = document.querySelector('input[name="capacity_to"]');

        // Проверяем, что sessionFilters — это объект и содержит ключ capacity_to
        if (sessionFilters &&
            typeof sessionFilters === 'object' &&
            sessionFilters.capacity_to !== undefined &&
            capacityInput) {

            // Проверяем, что значение — число или строка, которую можно преобразовать
            const value = sessionFilters.capacity_to;
            if (value !== null && value !== '' && !isNaN(Number(value))) {
                capacityInput.value = value;

                // Безопасный вызов функции с проверкой существования
                if (typeof updateNumericButton === 'function') {
                    updateNumericButton('capacity_to', 'Вместимость до', 'чел.');
                } else {
                    console.warn('Функция updateNumericButton не найдена. Убедитесь, что скрипт с функцией загружен.');
                }
            }
        }
    });
</script>

