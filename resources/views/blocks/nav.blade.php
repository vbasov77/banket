<style>
    /* Основной контейнер — выравнивание элементов вправо */
    .horizontal-dropdowns {
        display: inline-flex;
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: flex-end; /* Выравнивание всех элементов по правому краю */
        align-items: flex-start;
        gap: 20px;
        margin-left: auto; /* Прижимаем контейнер вправо */
        padding: 0;
        box-sizing: border-box;
    }

    /* Контейнер для каждого выпадающего меню */
    .dropdown-container {
        position: relative;
        display: inline-block;
        max-width: 300px; /* Жёстко ограничиваем ширину контейнера */
        min-width: 180px; /* Минимальная ширина для читаемости */
        width: auto; /* Ширина определяется содержимым */
        box-sizing: border-box;
    }

    /* Кнопка выпадающего меню — центрирование текста */
    .btn-festive-gradient {
        white-space: nowrap;
        border: none;
        font-weight: 500;
        text-align: center; /* ЦЕНТР текста — основное изменение */
        padding: 12px 16px;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 100%; /* Кнопка занимает всю ширину своего контейнера */
        min-width: 0; /* Разрешаем сжиматься при переносе */
    }

    /* Стили для выпадающего списка */
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
        min-width: 100%;
        max-width: 400px;
        margin-top: 4px;
    }

    /* Показываем список при активном состоянии */
    .dropdown-container.show .dropdown-menu-custom {
        display: block;
    }

    /* Корректное выравнивание чекбоксов */
    .form-check-label {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        cursor: pointer;
        white-space: nowrap;
    }

    .form-check:hover {
        background-color: #f8f9fa;
    }

    /* Стиль для чекбокса */
    .form-check input[type="checkbox"] {
        margin: 0;
        flex-shrink: 0;
        width: 16px;
        height: 16px;
    }

    /* Поле ввода внутри выпадающего меню */
    #capacity_to_input {
        width: 100%;
        padding: 8px 12px;
        font-size: 14px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        box-sizing: border-box;
        margin-bottom: 8px;
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

    /* Адаптация для мобильных устройств */
    @media (max-width: 768px) {
        .horizontal-dropdowns {
            justify-content: flex-end;
            gap: 15px;
            margin-left: auto;
        }

        .dropdown-container {
            max-width: 280px;
            min-width: 160px;
        }

        .btn-festive-gradient {
            font-size: 14px;
            padding: 10px 14px;
            text-align: center; /* Центрирование текста на мобильных */
        }
    }

    /* Адаптация для очень маленьких экранов */
    @media (max-width: 480px) {
        .horizontal-dropdowns {
            gap: 12px;
            margin-left: auto;
        }

        .dropdown-container {
            max-width: 240px;
            min-width: 140px;
        }

        .btn-festive-gradient {
            font-size: 13px;
            padding: 9px 12px;
            text-align: center; /* Центрирование текста на очень маленьких экранах */
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
    .horizontal-dropdowns {
        display: none; /* Изначальная скрытость контейнера фильтров */
    }
</style>
<style>
    .btn-show-filters {
        background: linear-gradient(145deg, #ffffff, #f5f5f5);
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 500;
        color: #2d3748;
        cursor: pointer;
        box-shadow: 4px 4px 8px #d9d9d9, -4px -4px 8px #ffffff;
        transition: all 0.2s ease;
        outline: none;
    }

    .btn-show-filters:hover {
        box-shadow: 2px 2px 4px #c8c8c8, -2px -2px 4px #f8f8f8;
        transform: translateY(-1px);
    }

    .btn-show-filters:active {
        box-shadow: inset 2px 2px 4px #c8c8c8, inset -2px -2px 4px #f8f8f8;
        transform: translateY(0);
    }

    .horizontal-dropdowns .dropdown-container {
        min-width: 250px;
    }
</style>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <!-- Кнопка для открытия фильтров на мобильных -->
            <button type="button" class="btn-show-filters mb-3">
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

                </div> <!-- Закрытие horizontal-dropdowns -->


            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const showFiltersButton = document.querySelector('.btn-show-filters');
        if (showFiltersButton) {
            showFiltersButton.addEventListener('click', function () {
                const dropdowns = document.querySelector('.horizontal-dropdowns');
                const isCurrentlyHidden = dropdowns.style.display === 'none' || !dropdowns.style.display;

                // Переключаем видимость контейнера с фильтрами
                dropdowns.style.display = isCurrentlyHidden ? 'flex' : 'none';

                // Обновляем текст кнопки
                if (isCurrentlyHidden) {
                    this.textContent = 'Скрыть фильтры';
                } else {
                    this.textContent = 'Показать фильтры';
                }

                // Переключаем класс для иконки (если используется Вариант 3)
                this.classList.toggle('open', isCurrentlyHidden);
            });
        }
    });

</script>


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

            console.log(isFormEmpty);
            return;
            // Если форма пустая, отменяем отправку и показываем сообщение
            if (isFormEmpty) {
                event.preventDefault(); // Отменяем отправку формы

                // Показываем сообщение пользователю
                alert('Пожалуйста, выберите хотя бы один фильтр перед применением.');
            }
        });
    });
</script>

