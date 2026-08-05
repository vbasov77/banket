<div class="dropdown-container for-events-dropdown">
    <button type="button"
            class="btn-festive-gradient btn-festive-gradient-white dropdown-toggle"
            id="zagsDropdown"
            aria-expanded="false"
            aria-haspopup="true"
            data-near-zags-id="{{ session('selected_filters.near_zags_id', '') }}">
        ЗАГС
        <span id="selectedZagsText" class="ms-2 text-muted small"></span>
    </button>

    <div class="dropdown-menu-custom">
        <h6 class="px-3 py-2 text-muted fw-semibold">Выберите ЗАГС</h6>
        <div style="line-height: 2" class="px-3" id="zagsContainer">
            <p class="text-muted small">Загрузка...</p>
        </div>
    </div>
</div>

<script>
    function toggleZagsDropdown(e) {
        e.preventDefault();
        e.stopPropagation();

        const container = document.querySelector('.dropdown-container.for-events-dropdown');
        const btn = document.getElementById('zagsDropdown');

        if (!container || !btn) return;

        const isVisible = container.classList.contains('show');
        container.classList.toggle('show');
        btn.setAttribute('aria-expanded', !isVisible);
    }

    function updateZagsButtonText() {
        const selectedZagsText = document.getElementById('selectedZagsText');
        // Ищем отмеченную радио‑кнопку
        const checkedRadio = document.querySelector('input[name="near_zags_id"]:checked');

        if (selectedZagsText) {
            if (checkedRadio && checkedRadio.nextElementSibling) {
                selectedZagsText.textContent = checkedRadio.nextElementSibling.textContent;
            } else {
                selectedZagsText.textContent = '';
            }
        }
    }

    document.addEventListener('click', function(e) {
        const container = document.querySelector('.dropdown-container.for-events-dropdown');
        if (!container) return;

        if (!e.target.closest('.dropdown-container')) {
            container.classList.remove('show');
            const btn = container.querySelector('#zagsDropdown');
            if (btn) btn.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('change', function(e) {
        if (e.target && e.target.name === 'near_zags_id') {
            updateZagsButtonText();
        }
    }, true);

    document.addEventListener('DOMContentLoaded', async () => {
        const zagsContainer = document.getElementById('zagsContainer');
        const zagsDropdownBtn = document.getElementById('zagsDropdown');

        if (!zagsContainer || !zagsDropdownBtn) return;

        zagsDropdownBtn.addEventListener('click', toggleZagsDropdown);

        // Получаем ID из data-атрибута (это строка)
        const currentSelectedZags = zagsDropdownBtn.dataset.nearZagsId;

        try {
            const url = '{{ route("api.zags.by.city") }}';
            const response = await fetch(url);
            if (!response.ok) throw new Error('HTTP ' + response.status);

            const data = await response.json();
            if (!data.success) {
                zagsContainer.innerHTML = `<p class="text-danger px-3 small">${data.message || 'Не удалось загрузить ЗАГСы'}</p>`;
                return;
            }

            const zagsList = data.zags || [];
            let html = '';

            if (zagsList.length === 0) {
                html = '<p class="text-muted px-3 small">ЗАГСы не найдены</p>';
            } else {
                zagsList.forEach(zags => {
                    // Ключевое исправление: сравниваем как строки — это надёжно
                    const isChecked = String(zags.id) === String(currentSelectedZags) ? 'checked' : '';
                    const labelClass = isChecked ? 'fw-bold' : '';

                    html += `
                    <label class="form-check ${labelClass}" style="cursor: pointer;">
                        <input type="radio"
                               name="near_zags_id"
                               value="${zags.id}"
                               class="form-check-input"
                               ${isChecked}>
                        <span class="ms-2">${zags.name}</span>
                    </label>`;
                });
            }
            zagsContainer.innerHTML = html;

            // Сразу обновляем текст на кнопке
            updateZagsButtonText();
        } catch (error) {
            console.error('[ZAGS] Error:', error);
            zagsContainer.innerHTML = `
                <p class="text-danger px-3 small">
                    Ошибка загрузки: ${error.message}<br>
                    Проверьте консоль.
                </p>`;
        }
    });
</script>
