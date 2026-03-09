<div class="dropdown-container">
    <button type="button"
            class="btn-festive-gradient btn-festive-gradient-white dropdown-toggle none-shadow">
        Цена на человека до
    </button>
    <div class="dropdown-menu-custom">
        <h6 class="px-3 py-2 text-muted fw-semibold">Укажите максимальную цену на человека</h6>
        <div class="px-3">
            <div class="mb-3">
                <label for="per_person_input" class="form-label">Сумма (руб.)</label>
                <input type="number"
                       id="per_person_input"
                       name="per_person"
                       class="form-control"
                       placeholder="Например: 1500"
                       min="1"
                       onkeypress="return (this.value.length < 6)"
                       value="@(session('selected_filters.per_person') ?? ''">
            </div>
            <small class="text-muted">Максимальная цена на человека, которую вы готовы заплатить</small>
        </div>
    </div>
</div>

<!-- Скрипт 4: Восстановление значений per_person (числовое поле) -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Безопасное получение данных сессии с гарантированным типом «объект»
        const sessionFilters = @json(session('selected_filters', []));
        const perPersonInput = document.querySelector('input[name="per_person"]');

        // Проверяем, что sessionFilters — это объект и содержит ключ per_person
        if (sessionFilters &&
            typeof sessionFilters === 'object' &&
            sessionFilters.per_person !== undefined &&
            perPersonInput) {

            // Проверяем, что значение — число или строка, которую можно преобразовать
            const value = sessionFilters.per_person;
            if (value !== null && value !== '' && !isNaN(Number(value))) {
                perPersonInput.value = value;

                // Безопасный вызов функции с проверкой существования
                if (typeof updateNumericButton === 'function') {
                    updateNumericButton('per_person', 'Цена до', 'руб.');
                } else {
                    console.warn('Функция updateNumericButton не найдена. Убедитесь, что скрипт с функцией загружен.');
                }
            }
        }
    });
</script>

