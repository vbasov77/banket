<style>
    .dropdown-container {
        position: relative;
    }

    .dropdown-menu-custom {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 1000;
        display: none;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,.1);
        border: 1px solid #e0e0e0;
        min-width: 100%;
        max-width: 400px;
        margin-top: 4px;
    }

    .dropdown-container.show .dropdown-menu-custom {
        display: block;
    }
</style>

<div class="dropdown-container">
    <button type="button"
            class="btn-festive-gradient btn-festive-gradient-white dropdown-toggle none-shadow"
            id="metroDropdown"
            aria-expanded="false"
            aria-haspopup="true"
            data-near-metro-id="{{ session('selected_filters.near_metro_id', '') }}"
            onclick="toggleMetroDropdown(event)">  <!-- <--- это единственное изменение в HTML -->
        Метро
        <span id="selectedMetroText" class="ms-2 text-muted small"></span>
    </button>

    <div class="dropdown-menu-custom" id="metroDropdownMenu">
        <h6 class="px-3 py-2 text-muted fw-semibold">Станция метро</h6>
        <div class="px-3" id="metrosContainer">
            <!-- Сюда JS будет вставлять станции или ошибку -->
        </div>
    </div>
</div>



<!-- Чистый JS, без Blade-синтаксиса внутри -->
<script>
    function toggleMetroDropdown(e) {
        e.preventDefault();
        const container = document.getElementById('metroDropdownContainer');
        const btn = document.getElementById('metroDropdown');
        if (!container || !btn) return;

        const isVisible = container.classList.contains('show');
        container.classList.toggle('show');
        btn.setAttribute('aria-expanded', !isVisible);
    }

    document.addEventListener('click', function(e) {
        const container = document.getElementById('metroDropdownContainer');
        if (!container) return;

        if (!e.target.closest('.dropdown-container')) {
            container.classList.remove('show');
            const btn = container.querySelector('#metroDropdown');
            if (btn) btn.setAttribute('aria-expanded', 'false');
        }
    });

    // обновление текста при выборе станции ---
    function updateMetroButtonText() {
        const selectedMetroText = document.getElementById('selectedMetroText');
        const checkedRadio = document.querySelector('input[name="near_metro_id"]:checked');

        if (selectedMetroText && checkedRadio && checkedRadio.nextElementSibling) {
            selectedMetroText.textContent = checkedRadio.nextElementSibling.textContent;
        } else if (selectedMetroText) {
            selectedMetroText.textContent = '';
        }
    }

    (async () => {
        const metrosContainer = document.getElementById('metrosContainer');
        const metroDropdownBtn = document.getElementById('metroDropdown');

        if (!metrosContainer || !metroDropdownBtn) {
            console.error('Элементы для выпадающего списка не найдены');
            return;
        }

        const currentSelectedMetro = metroDropdownBtn.dataset.nearMetroId || null;

        try {
            const response = await fetch('{{ route("api.metros.by.city") }}');
            const data = await response.json();

            if (!data.success) {
                metrosContainer.innerHTML = `<p class="text-danger px-3">${data.message || 'Не удалось загрузить станции'}</p>`;
                return;
            }

            const metros = data.metros || [];
            let html = '';

            if (metros.length === 0) {
                html = '<p class="text-muted px-3">Станции не найдены</p>';
            } else {
                metros.forEach(metro => {
                    const isChecked = metro.id == currentSelectedMetro ? 'checked' : '';
                    const labelClass = isChecked ? 'fw-bold' : '';

                    html += `
                    <label class="form-check-label d-flex align-items-center mb-2 ${labelClass}" style="cursor: pointer;">
                        <input type="radio"
                               name="near_metro_id"
                               value="${metro.id}"
                               class="form-check-input me-2"
                               ${isChecked}>
                        <span>${metro.name}</span>
                    </label>`;
                });
            }
            metrosContainer.innerHTML = html;

            // Обновляем текст кнопки сразу после отрисовки
            updateMetroButtonText();
        } catch (error) {
            console.error('Ошибка загрузки метро:', error);
            metrosContainer.innerHTML = '<p class="text-danger px-3">Не удалось загрузить станции. Проверьте консоль.</p>';
        }
    })();

    // Вешаем обработчик, чтобы текст обновлялся при каждом выборе
    document.addEventListener('change', function(e) {
        if (e.target && e.target.name === 'near_metro_id') {
            updateMetroButtonText();
        }
    }, true); // true — чтобы ловить событие даже если оно всплывает
</script>
