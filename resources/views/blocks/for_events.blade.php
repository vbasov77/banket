<div class="dropdown-container for-events-dropdown" >
    <button type="button"
            class="btn-festive-gradient btn-festive-gradient-white dropdown-toggle">
        Для мероприятий
    </button>
    <div class="dropdown-menu-custom">
        <h6 class="px-3 py-2 text-muted fw-semibold">Выберите тип мероприятия</h6>
        <div style="line-height: 2" class="px-3">
            <label class="form-check">
                <input type="radio" name="for_events" value="Свадьба" class="form-check-input">
                <span class="ms-2">Свадьба</span>
            </label>
            <label class="form-check">
                <input type="radio" name="for_events" value="День рождения"
                       class="form-check-input">
                <span class="ms-2">День рождения</span>
            </label>
            <!-- Добавьте другие типы мероприятий по аналогии -->
            <label class="form-check">
                <input type="radio" name="for_events" value="Корпоратив" class="form-check-input">
                <span class="ms-2">Корпоратив</span>
            </label>
            <label class="form-check">
                <input type="radio" name="for_events" value="Юбилей" class="form-check-input">
                <span class="ms-2">Юбилей</span>
            </label>
        </div>
    </div>
</div>

<!-- Скрипт 1: Восстановление значений for_events (одиночный выбор) -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Безопасное получение данных сессии с гарантированным типом «объект»
        const sessionFilters = @json(session('selected_filters', []));

        // Проверяем, что sessionFilters — это объект и содержит ключ for_events
        if (sessionFilters && typeof sessionFilters === 'object' && sessionFilters.for_events !== undefined) {
            // Находим радиокнопку с соответствующим значением
            const radio = document.querySelector(`input[name="for_events"][value="${sessionFilters.for_events}"]`);

            if (radio) {
                radio.checked = true;

                // Безопасный вызов функции с проверкой существования
                if (typeof updateForEventsButton === 'function') {
                    updateForEventsButton();
                } else {
                    console.warn('Функция updateForEventsButton не найдена. Убедитесь, что скрипт с функцией загружен.');
                }
            }
        }
    });
</script>

