<style>
    /* Основной контейнер — сохраняем flex‑раскладку с переносом */
    .horizontal-dropdowns {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: flex-start;
        align-items: flex-start;
        gap: 20px;
        width: 100%;
    }

    /* Контейнер для каждого выпадающего меню */
    .dropdown-container {
        position: relative;
        display: inline-block;
        width: 100%; /* Занимает всю ширину родителя */
        max-width: 100%; /* Не выходит за пределы контейнера */
        box-sizing: border-box;
    }

    /* Кнопка выпадающего меню — растягиваем на всю ширину */
    .btn-festive-gradient {
        white-space: nowrap;
        border: none;
        font-weight: 500;
        text-align: left; /* Текст слева */
        padding: 12px 16px; /* Отступы для лучшей читаемости */
        overflow: hidden;
        text-overflow: ellipsis; /* Обрезаем длинный текст */
    }

    /* Стили для выпадающего списка — ограничиваем ширину */
    .dropdown-menu-custom {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 1000;
        display: none;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border: 1px solid #e0e0e0;
        min-width: 100%; /* Ширина как у кнопки */
        max-width: 100vw; /* Максимальная ширина — весь экран */
        margin-top: 4px;
    }

    /* Для мобильных устройств — дополнительные ограничения */
    @media (max-width: 768px) {
        .dropdown-menu-custom {
            max-width: calc(100vw - 30px); /* Отступы от краёв экрана */
            left: 0;
            right: 0;
            margin-left: auto;
            margin-right: auto;
        }
    }

    /* Показываем список при активном состоянии */
    .dropdown-container.show .dropdown-menu-custom {
        display: block;
    }

    /* Поле ввода внутри выпадающего меню */
    #capacity_to_input {
        width: 100%;
        padding: 8px 12px;
        font-size: 14px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        box-sizing: border-box;
        margin-bottom: 8px; /* Отступ снизу */
    }

    /* Метка и подсказка */
    .form-label {
        font-size: 14px;
        margin-bottom: 4px;
        display: block;
    }

    .text-muted {
        font-size: 12px;
        line-height: 1.4;
        color: #6b7280;
    }

    /* Корректное выравнивание элементов внутри выпадающего меню */
    .dropdown-menu-custom .px-3 {
        padding-left: 12px;
        padding-right: 12px;
        padding-top: 8px;
        padding-bottom: 12px;
    }

    /* Адаптация для очень маленьких экранов */
    @media (max-width: 480px) {
        .horizontal-dropdowns {
            gap: 15px;
        }

        .btn-festive-gradient {
            font-size: 14px;
            padding: 10px 14px;
        }

        .dropdown-menu-custom {
            max-width: calc(100vw - 20px);
            left: 10px;
            right: 10px;
        }
    }
</style>

<style>
    .filter-container {
        width: 100%;
        padding: 15px 0;

    }

    .buttons-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .button-left,
    .button-right {
        flex: 1;
    }

    .button-left {
        text-align: left;
    }

    .button-right {
        text-align: right;
    }

    .btn-apply,
    .btn-reset {
        background: linear-gradient(145deg, #ffffff, #f5f5f5);
        font-family: 'Georgia', serif;
        font-size: 14px;
        font-weight: 500;
        border-radius: 10px;
        padding: 10px 20px;
        border: none;
        box-shadow: 6px 6px 12px #d9d9d9,
        -6px -6px 12px #ffffff;
        transition: all 0.3s ease;
        cursor: pointer;
        white-space: nowrap;
    }

    /* Кнопка «Сбросить фильтры» (слева) — менее акцентная */
    .btn-reset {
        color: #6b7280;
        background: linear-gradient(145deg, #f9f9f9, #ededed);
    }

    .btn-reset:hover {
        background: linear-gradient(145deg, #f1f1f1, #e1e1e1);
        box-shadow: 3px 3px 6px #c8c8c8,
        -3px -3px 6px #f8f8f8;
        transform: translateY(-1px);
    }

    .btn-reset:active {
        box-shadow: inset 3px 3px 6px #c8c8c8,
        inset -3px -3px 6px #f8f8f8;
        transform: translateY(0);
        background: linear-gradient(145deg, #e1e1e1, #d1d1d1);
    }

    /* Кнопка «Применить фильтры» (справа) — акцентная */
    .btn-apply {
        color: #2d3748;
    }

    .btn-apply:hover {
        background: linear-gradient(145deg, #f8f8f8, #e8e8e8);
        box-shadow: 3px 3px 6px #d1d1d1,
        -3px -3px 6px #ffffff;
        transform: translateY(-2px);
    }

    .btn-apply:active {
        box-shadow: inset 3px 3px 6px #d1d1d1,
        inset -3px -3px 6px #ffffff;
        transform: translateY(0);
        background: linear-gradient(145deg, #e8e8e8, #d8d8d8);
    }

    .dropdown-menu-custom .px-3 {
        max-height: 250px; /* Фиксированная высота контейнера */
        overflow-y: auto; /* Вертикальная прокрутка, если элементы не помещаются */
        display: block;
    }

    /* Стили скроллбара для WebKit‑браузеров (Chrome, Edge, Safari) */
    .dropdown-menu-custom .px-3::-webkit-scrollbar {
        width: 6px;
    }

    .dropdown-menu-custom .px-3::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .dropdown-menu-custom .px-3::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .dropdown-menu-custom .px-3::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Для Firefox */
    .dropdown-menu-custom .px-3 {
        scrollbar-width: thin;
        scrollbar-color: #c1c1c1 #f1f1f1;
    }

</style>

<style>
    /* Базовые стили для кнопки "Показать фильтры" */
    .btn-show-filters {
        background: #4a6fa5;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 16px;
        width: 100%;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .btn-show-filters:hover {
        background: #3a5a8a;
    }

    /* Скрытие выпадающих списков на мобильных (до md breakpoint) */
    .horizontal-dropdowns {
        display: none;
    }

    /* Показываем выпадающие списки, когда у контейнера есть класс active */
    .horizontal-dropdowns.active {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
    }

    /* Стили для выпадающих контейнеров внутри */
    .horizontal-dropdowns .dropdown-container {
        flex: 1;
        min-width: 250px;
    }

    /* Кнопки сброса и применения на мобильных */
    .filter-container .buttons-wrapper {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .filter-container .button-left,
    .filter-container .button-right {
        width: 100%;
    }

    .btn-reset,
    .btn-apply {
        width: 100%;
        padding: 12px;
        text-align: center;
    }

    /* Десктопные стили — показываем всё как есть */
    @media (min-width: 992px) {
        .horizontal-dropdowns {
            display: flex !important;
            flex-wrap: wrap;
            gap: 15px;
        }

        .btn-show-filters {
            display: none !important;
        }

        .filter-container .buttons-wrapper {
            flex-direction: row;
            justify-content: space-between;
        }

        .filter-container .button-left,
        .filter-container .button-right {
            width: auto;
        }
    }
</style>


<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <!-- Кнопка для открытия фильтров на мобильных -->
            <button type="button" class="btn-show-filters mb-3 d-md-none">
                Показать фильтры
            </button>
            <form method="post" action="{{route('search.objs')}}">
                @csrf
                <!-- Горизонтальный контейнер для выпадающих меню -->
                <div class="horizontal-dropdowns">

                    <!-- Первое выпадающее меню: Для мероприятий -->
                    @include('blocks.for_events')


                    <!-- Выпадающее меню: Район -->
                    @include('blocks.district')

                    <!-- Выпадающее меню: Вместимость до -->
                    @include('blocks.capacity_to')

                    <!-- Выпадающее меню: Сумма на чел до -->
                    @include('blocks.per_person')

                    <!-- Выпадающее меню: Особенности -->
                    @include('blocks.features')

                </div> <!-- Закрытие horizontal-dropdowns -->

                <div class="filter-container">
                    <div class="buttons-wrapper">
                        <div class="button-left">
                            <button type="reset" class="btn-reset">Сбросить фильтры</button>
                        </div>
                        <div class="button-right">
                            <button type="submit" class="btn-apply">Применить фильтры</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Инициализация: сохраняем оригинальные тексты кнопок
        document.querySelectorAll('.dropdown-toggle').forEach(button => {
            button.setAttribute('data-original-text', button.textContent);
        });

        // Обработчик клика по документу для закрытия списков
        document.addEventListener('click', function (e) {
            // Если клик был ВНУТРИ выпадающего списка — ничего не делаем
            if (e.target.closest('.dropdown-container')) {
                return;
            }

            // Иначе — закрываем ВСЕ выпадающие списки
            document.querySelectorAll('.dropdown-container').forEach(container => {
                container.classList.remove('show');
            });
        });

        // Обработчики для кнопок открытия списков
        document.querySelectorAll('.dropdown-toggle').forEach(button => {
            button.addEventListener('click', function (e) {
                e.stopPropagation(); // Останавливаем распространение события

                const container = this.closest('.dropdown-container');

                // Закрываем все открытые списки, кроме текущего
                document.querySelectorAll('.dropdown-container.show').forEach(openContainer => {
                    if (openContainer !== container) {
                        openContainer.classList.remove('show');
                    }
                });

                // Переключаем состояние текущего списка
                container.classList.toggle('show');
            });
        });

        // Функция обновления кнопки for_events (одиночный выбор)
        function updateForEventsButton() {
            // Находим отмеченную радиокнопку
            const radio = document.querySelector('input[name="for_events"]:checked');

            // Ищем кнопку через контейнер for-events-dropdown
            const button = document.querySelector('.for-events-dropdown .dropdown-toggle');

            // Если кнопка не найдена — выходим без ошибки
            if (!button) {
                console.warn('Кнопка выпадающего списка для for_events не найдена');
                return;
            }

            const originalText = button.getAttribute('data-original-text') || 'Для мероприятий';

            if (radio) {
                // В вашей структуре текст находится в <span class="ms-2"> внутри того же label, что и радиокнопка
                const label = radio.closest('.form-check');
                const span = label?.querySelector('.ms-2');

                if (span && span.textContent.trim()) {
                    const labelText = span.textContent.trim();
                    button.textContent = `${labelText}`;
                } else {
                    console.warn('Текст мероприятия не найден для выбранной радиокнопки');
                    button.textContent = originalText;
                }
            } else {
                // Если ничего не выбрано — восстанавливаем исходный текст
                button.textContent = originalText;
            }
        }


        // Функция обновления кнопки district (количество выбранных)
        function updateDistrictButton() {
            const checked = document.querySelectorAll('input[name="district[]"]:checked');
            const button = document.querySelector('.dropdown-toggle[data-original-text*="Район"]');

            if (!button) return;

            const originalText = button.getAttribute('data-original-text');

            if (checked.length > 0) {
                button.textContent = `${originalText} (${checked.length})`;
            } else {
                button.textContent = originalText;
            }
        }

        // Функция обновления кнопки features (количество выбранных)
        function updateFeaturesButton() {
            const checked = document.querySelectorAll('input[name="features[]"]:checked');
            const button = document.querySelector('.dropdown-toggle[data-original-text*="Особенности"]');

            if (!button) return;

            const originalText = button.getAttribute('data-original-text');

            if (checked.length > 0) {
                button.textContent = `${originalText} (${checked.length})`;
            } else {
                button.textContent = originalText;
            }
        }

        // Функция обновления кнопок с числовыми значениями
        function updateNumericButton(inputName, prefix, suffix) {
            const input = document.querySelector(`input[name="${inputName}"]`);
            const button = input?.closest('.dropdown-container')?.querySelector('.dropdown-toggle');

            if (!input || !button) return;

            const originalText = button.getAttribute('data-original-text');
            const value = input.value.trim();

            if (value) {
                button.textContent = `${prefix} ${value} ${suffix}`;
            } else {
                button.textContent = originalText;
            }
        }

        // Обработчики изменений
        // Для радиокнопок for_events
        document.querySelectorAll('input[name="for_events"]').forEach(radio => {
            radio.addEventListener('change', updateForEventsButton);
        });

        // Для чекбоксов district
        document.querySelectorAll('input[name="district[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', updateDistrictButton);
        });

        // Для чекбоксов features
        document.querySelectorAll('input[name="features[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', updateFeaturesButton);
        });

        // Для полей capacity_to и per_person
        const capacityInput = document.querySelector('input[name="capacity_to"]');
        if (capacityInput) {
            capacityInput.addEventListener('input', () => {
                updateNumericButton('capacity_to', '👥👥', 'чел.');
            });
        }

        const perPersonInput = document.querySelector('input[name="per_person"]');
        if (perPersonInput) {
            perPersonInput.addEventListener('input', () => {
                updateNumericButton('per_person', '💵', '₽');
            });
        }

        // Первоначальное обновление кнопок (на случай, если есть значения по умолчанию)
        updateForEventsButton();
        updateDistrictButton();
        updateFeaturesButton();
        updateNumericButton('capacity_to', '👥👥', 'чел.');
        updateNumericButton('per_person', '💵', '₽');

        console.log('Инициализация фильтров завершена. Отображение выбранных значений активировано.');
    });
</script>
<script>
    // --------------------------------------------------------------- удаляем сессию, очищаем списки
    document.addEventListener('DOMContentLoaded', function () {
        // Находим кнопку «Сбросить фильтры»
        const resetButton = document.querySelector('.btn-reset');

        if (resetButton) {
            resetButton.addEventListener('click', function (e) {
                e.preventDefault(); // Отменяем стандартное действие кнопки reset

                // Получаем форму
                const form = this.closest('form');

                // Очищаем все поля формы
                form.reset();

                // Дополнительно очищаем чекбоксы и радиокнопки
                const checkboxes = form.querySelectorAll('input[type="checkbox"]');
                const radios = form.querySelectorAll('input[type="radio"]');

                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });

                radios.forEach(radio => {
                    radio.checked = false;
                });

                // Очищаем текстовые поля ввода
                const textInputs = form.querySelectorAll('input[type="text"], input[type="number"]');
                textInputs.forEach(input => {
                    input.value = '';
                });

                // Обновляем тексты кнопок выпадающих списков до исходных значений
                document.querySelectorAll('.dropdown-toggle').forEach(button => {
                    const originalText = button.getAttribute('data-original-text');
                    if (originalText) {
                        button.textContent = originalText;
                    }
                });

                // Показываем индикатор загрузки (опционально)
                resetButton.textContent = 'Сброс...';
                resetButton.disabled = true;

                const sessionFilters = @json(session('selected_filters', []));

                if (sessionFilters) {
                    // Отправляем AJAX‑запрос для очистки сессии
                    fetch('{{ route('clear.filters') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({})
                    })
                        .then(response => {
                            // Всегда пытаемся разобрать JSON, даже если статус не 200
                            return response.json().then(data => {
                                if (!response.ok) {
                                    throw new Error(data.message || 'Ошибка сервера');
                                }
                                return data;
                            });
                        })
                        .then(data => {
                            console.log('Ответ сервера:', data);

                            // Проверяем успех операции
                            if (data.success) {
                                console.log('Фильтры успешно сброшены');
                                // Выполняем редирект
                                window.location.href = '{{ route('front') }}';
                            } else {
                                console.warn('Ошибка сброса фильтров:', data.message);
                                alert('Не удалось сбросить фильтры: ' + data.message);
                                // Всё равно выполняем редирект, но с уведомлением
                                window.location.href = '{{ route('front') }}';
                            }
                        })
                        .catch(error => {
                            console.error('Критическая ошибка при сбросе фильтров:', error);
                            alert('Произошла ошибка при сбросе фильтров. Страница будет перезагружена.');
                            // В случае критической ошибки — просто редиректим
                            window.location.href = '{{ route('front') }}';
                        })
                        .finally(() => {
                            // Восстанавливаем текст кнопки в любом случае
                            resetButton.textContent = 'Сбросить фильтры';
                            resetButton.disabled = false;
                        });
                } else {
                    // Восстанавливаем текст кнопки в любом случае
                    resetButton.textContent = 'Сбросить фильтры';
                    resetButton.disabled = false;
                }
            });
        }
    });
</script>

{{--  --------------------------------------Проверка перед отправкой формы--}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        const applyButton = form.querySelector('.btn-apply');

        form.addEventListener('submit', function (event) {
            // Получаем все элементы формы, которые могут содержать значения фильтров
            const filterElements = form.querySelectorAll('select, input[type="text"], input[type="number"], input[type="checkbox"]:checked, input[type="radio"]:checked');

            let isFormEmpty = true;

            // Проверяем каждый элемент на наличие значения
            for (let element of filterElements) {
                if (element.tagName === 'SELECT') {
                    // Для выпадающих списков проверяем, выбран ли элемент (не пустой option)
                    if (element.value && element.value !== '' && element.value !== '0') {
                        isFormEmpty = false;
                        break;
                    }
                } else if (element.type === 'checkbox' || element.type === 'radio') {
                    // Для чекбоксов и радиокнопок достаточно факта выбора (элемент уже в querySelectorAll только если checked)
                    isFormEmpty = false;
                    break;
                } else {
                    // Для текстовых и числовых полей проверяем наличие непустого значения
                    if (element.value && element.value.trim() !== '') {
                        isFormEmpty = false;
                        break;
                    }
                }
            }

            // Если форма пустая, отменяем отправку и показываем сообщение
            if (isFormEmpty) {
                event.preventDefault(); // Отменяем отправку формы

                // Показываем сообщение пользователю
                alert('Пожалуйста, выберите хотя бы один фильтр перед применением.');
            }
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const showFiltersBtn = document.querySelector('.btn-show-filters');
        const dropdownsContainer = document.querySelector('.horizontal-dropdowns');

        if (showFiltersBtn && dropdownsContainer) {
            showFiltersBtn.addEventListener('click', function() {
                dropdownsContainer.classList.toggle('active');

                // Меняем текст кнопки
                if (dropdownsContainer.classList.contains('active')) {
                    showFiltersBtn.textContent = 'Скрыть фильтры';
                } else {
                    showFiltersBtn.textContent = 'Показать фильтры';
                }
            });
        }
    });
</script>
