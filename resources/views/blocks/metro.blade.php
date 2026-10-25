{{-- ===== Метро: дропдаун фильтра ===== --}}
<div class="dropdown-container">
    <button type="button"
            class="btn-festive-gradient btn-festive-gradient-white dropdown-toggle none-shadow"
            id="metroDropdown"
            data-bs-toggle="dropdown"
            data-bs-display="static"
            data-near-metro-id="{{ session('selected_filters.near_metro_id', '') }}"
            aria-expanded="false">
        Метро
        <span id="selectedMetroText" class="ms-2 text-muted small"></span>
    </button>

    {{-- Класс dropdown-menu обязателен — по нему Bootstrap ищет меню --}}
    <div class="dropdown-menu dropdown-menu-custom" id="metroDropdownMenu">
        <h6 class="px-3 py-2 text-muted fw-semibold">Станция метро</h6>
        <div class="px-3" id="metrosContainer"></div>
    </div>
</div>

<style>
    /* display: none не нужен — показом управляет Bootstrap через .show */
    .dropdown-menu-custom {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 1000;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .1);
        border: 1px solid #e0e0e0;
        min-width: 100%;
        max-width: 400px;
        margin-top: 4px;
    }
</style>

<script>
    (function () {
        const metrosContainer = document.getElementById('metrosContainer');
        const metroDropdownBtn = document.getElementById('metroDropdown');

        if (!metrosContainer || !metroDropdownBtn) return;

        const currentSelectedMetro = metroDropdownBtn.dataset.nearMetroId || null;

        function updateMetroButtonText() {
            const selectedMetroText = document.getElementById('selectedMetroText');
            const checkedRadio = document.querySelector('input[name="near_metro_id"]:checked');

            if (selectedMetroText && checkedRadio && checkedRadio.nextElementSibling) {
                selectedMetroText.textContent = checkedRadio.nextElementSibling.textContent;
            } else if (selectedMetroText) {
                selectedMetroText.textContent = '';
            }
        }

        function loadMetros() {
            fetch('{{ route("api.metros.by.city") }}')
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        metrosContainer.innerHTML =
                            `<p class="text-danger px-3">${data.message || 'Не удалось загрузить станции'}</p>`;
                        return;
                    }

                    const metros = data.metros || [];

                    if (metros.length === 0) {
                        // закрываем дропдаун, если открыт, и прячем весь блок
                        const instance = bootstrap.Dropdown.getInstance(metroDropdownBtn);
                        if (instance) instance.hide();
                        metroDropdownBtn.closest('.dropdown-container').style.display = 'none';
                        return;
                    }

                    let html = '';
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
                    metrosContainer.innerHTML = html;

                    updateMetroButtonText();
                })
                .catch(error => {
                    console.error('Ошибка загрузки метро:', error);
                    metrosContainer.innerHTML =
                        '<p class="text-danger px-3">Не удалось загрузить станции. Проверьте консоль.</p>';
                });
        }

        // Грузим станции один раз — при первом открытии дропдауна
        let metrosLoaded = false;
        metroDropdownBtn.addEventListener('show.bs.dropdown', function () {
            if (!metrosLoaded) {
                loadMetros();
                metrosLoaded = true;
            }
        });

        // Обновляем текст кнопки при выборе станции
        document.addEventListener('change', function (e) {
            if (e.target && e.target.name === 'near_metro_id') {
                updateMetroButtonText();
            }
        }, true);
    })();
</script>
