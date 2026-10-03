@extends('layouts.app')
@section('content')
    <link href="{{ asset('css/tables.css') }}" rel="stylesheet">
    <link href="{{ asset('css/checkbox.css') }}" rel="stylesheet">
    <section>
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5">
                <div class="col-lg-12 mt-5">
                    @if($obj)
                        <h3>Добавьте детали вашего объекта {{ $obj->name_obj }}</h3>

                    @else
                        <h3>Добавьте детали нового объекта</h3>
                    @endif
                    <span>
                        Детали объекта — это более подробная информация о вашем объекте.
                        </span>
                    <form action="{{route('store.details_obj')}}" method="post">
                        @csrf
                        <input type="hidden" name="obj_id" value="{{ $obj->id }}">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <table class="styled-table">
                            <tr>
                                <td style="width: 49%">
                                    <div>
                                        <label for="kitchen"><b>Кухня:</b></label>
                                        <div class="checkbox-group">
                                            <label class="checkbox-container">
                                                <input name="kitchen[]" class="kitchen" type="checkbox" value="Русская">
                                                <span class="checkmark"></span>
                                                Русская
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="kitchen[]" class="kitchen" type="checkbox"
                                                       value="Кавказская">
                                                <span class="checkmark"></span>
                                                Кавказская
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="kitchen[]" class="kitchen" type="checkbox"
                                                       value="Китайская">
                                                <span class="checkmark"></span>
                                                Китайская
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="kitchen[]" class="kitchen" type="checkbox"
                                                       value="Японская">
                                                <span class="checkmark"></span>
                                                Японская
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="kitchen[]" class="kitchen" type="checkbox"
                                                       value="Азиатская">
                                                <span class="checkmark"></span>
                                                Азиатская
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="kitchen[]" class="kitchen" type="checkbox"
                                                       value="Европейская">
                                                <span class="checkmark"></span>
                                                Европейская
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="kitchen[]" class="kitchen" type="checkbox"
                                                       value="Паназиатская">
                                                <span class="checkmark"></span>
                                                Паназиатская
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="kitchen[]" class="kitchen" type="checkbox"
                                                       value="Смешенная">
                                                <span class="checkmark"></span>
                                                Смешенная
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="kitchen[]" class="kitchen" type="checkbox"
                                                       value="Средиземноморская">
                                                <span class="checkmark"></span>
                                                Средиземноморская
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="kitchen[]" class="kitchen" type="checkbox"
                                                       value="Французская">
                                                <span class="checkmark"></span>
                                                Французская
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="kitchen[]" class="kitchen" type="checkbox"
                                                       value="Греческая">
                                                <span class="checkmark"></span>
                                                Греческая
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="kitchen[]" class="kitchen" type="checkbox"
                                                       value="Испанская">
                                                <span class="checkmark"></span>
                                                Испанская
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="kitchen[]" class="kitchen" type="checkbox"
                                                       value="Турецкая">
                                                <span class="checkmark"></span>
                                                Турецкая
                                            </label>
                                        </div>
                                    </div>
                                </td>
                                <td style="width: 49%">
                                    <div>
                                        <label for="for_events"><b>Для мероприятий:</b></label>
                                        <div class="checkbox-group">
                                            <label class="checkbox-container">
                                                <input name="for_events[]" class="for_events" type="checkbox"
                                                       value="Свадьба">
                                                <span class="checkmark"></span>
                                                Свадьба
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="for_events[]" class="for_events" type="checkbox"
                                                       value="День рождения">
                                                <span class="checkmark"></span>
                                                День рождения
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="for_events[]" class="for_events" type="checkbox"
                                                       value="Корпоратив">
                                                <span class="checkmark"></span>
                                                Корпоратив
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="for_events[]" class="for_events" type="checkbox"
                                                       value="Корпоратив">
                                                <span class="checkmark"></span>
                                                Корпоратив на Новый Год
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="for_events[]" class="for_events" type="checkbox"
                                                       value="Выпускной">
                                                <span class="checkmark"></span>
                                                Выпускной
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="for_events[]" class="for_events" type="checkbox"
                                                       value="Детский праздник">
                                                <span class="checkmark"></span>
                                                Детский праздник
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="for_events[]" class="for_events" type="checkbox"
                                                       value="Фуршет">
                                                <span class="checkmark"></span>
                                                Фуршет
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="for_events[]" class="for_events" type="checkbox"
                                                       value="Мальчишник/Девичник">
                                                <span class="checkmark"></span>
                                                Мальчишник/Девичник
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="for_events[]" class="for_events" type="checkbox"
                                                       value="Мальчишник/Девичник">
                                                <span class="checkmark"></span>
                                                Презентация
                                            </label>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <table class="styled-table">
                            <tr>
                                <td style="width: 49%">
                                    <div>
                                        <label for="payment_methods"><b>Способы оплаты:</b></label>
                                        <div class="checkbox-group">
                                            <label class="checkbox-container">
                                                <input name="payment_methods[]"
                                                       class="payment_methods" type="checkbox"
                                                       value="Наличный">
                                                <span class="checkmark"></span>
                                                Наличный
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="payment_methods[]"
                                                       class="payment_methods" type="checkbox"
                                                       value="Безналичный">
                                                <span class="checkmark"></span>
                                                Безналичный
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="payment_methods[]"
                                                       class="payment_methods" type="checkbox"
                                                       value="Банковская карта">
                                                <span class="checkmark"></span>
                                                Банковская карта
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="payment_methods[]"
                                                       class="payment_methods" type="checkbox"
                                                       value="Перевод">
                                                <span class="checkmark"></span>
                                                Перевод
                                            </label>
                                        </div>
                                    </div>
                                </td>
                                <td style="width: 49%">
                                    <div>
                                        <label for="service"><b>Сервис:</b></label>
                                        <div class="checkbox-group">
                                            <label class="checkbox-container">
                                                <input name="service[]"
                                                       class="service" type="checkbox"
                                                       value="Ведущий/Тамада">
                                                <span class="checkmark"></span>
                                                Ведущий/Тамада
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="service[]"
                                                       class="service" type="checkbox"
                                                       value="Диджей">
                                                <span class="checkmark"></span>
                                                Диджей
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="service[]"
                                                       class="service" type="checkbox"
                                                       value="Живая музыка">
                                                <span class="checkmark"></span>
                                                Живая музыка
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="service[]"
                                                       class="service" type="checkbox"
                                                       value="Фотограф/Видеооператор">
                                                <span class="checkmark"></span>
                                                Фотограф/Видеооператор
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="service[]"
                                                       class="service" type="checkbox"
                                                       value="Аниматоры">
                                                <span class="checkmark"></span>
                                                Аниматоры
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="service[]"
                                                       class="service" type="checkbox"
                                                       value="Украшение зала">
                                                <span class="checkmark"></span>
                                                Украшение зала
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="service[]"
                                                       class="service" type="checkbox"
                                                       value="Оформление фотозоны">
                                                <span class="checkmark"></span>
                                                Оформление фотозоны
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="service[]"
                                                       class="service" type="checkbox"
                                                       value="Воздушные шары">
                                                <span class="checkmark"></span>
                                                Воздушные шары
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="service[]"
                                                       class="service" type="checkbox"
                                                       value="Звуковое оборудование">
                                                <span class="checkmark"></span>
                                                Звуковое оборудование
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="service[]"
                                                       class="service" type="checkbox"
                                                       value="Световое оборудование">
                                                <span class="checkmark"></span>
                                                Световое оборудование
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="service[]"
                                                       class="service" type="checkbox"
                                                       value="Проекционное оборудование">
                                                <span class="checkmark"></span>
                                                Проекционное оборудование
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="service[]"
                                                       class="service" type="checkbox"
                                                       value="Трансфер для гостей">
                                                <span class="checkmark"></span>
                                                Трансфер для гостей
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="service[]"
                                                       class="service" type="checkbox"
                                                       value="Выездная регистрация">
                                                <span class="checkmark"></span>
                                                Выездная регистрация
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="service[]"
                                                       class="service" type="checkbox"
                                                       value="Фейерверк/Салют">
                                                <span class="checkmark"></span>
                                                Фейерверк/Салют
                                            </label>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <table class="styled-table">
                            <tr>
                                <td style="width: 49%">
                                    <div>
                                        <label><b>Пробковый сбор:</b></label>
                                        <div class="radio-group">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="alcohol"
                                                       id="alcohol-allowed"
                                                       value="0"
                                                       {{ old('alcohol') == '0' ? 'checked' : '' }}
                                                       required>
                                                <label class="form-check-label" for="alcohol-allowed">
                                                    Разрешено бесплатно
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="alcohol"
                                                       id="alcohol-er"
                                                       value="1"
                                                       {{ old('alcohol') == '1' ? 'checked' : '' }}
                                                       required>
                                                <label class="form-check-label" for="alcohol-er">
                                                    Запрещено
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="alcohol"
                                                       id="alcohol-paid"
                                                       value="2"
                                                       {{ old('alcohol') == '2' ? 'checked' : '' }}
                                                       required>
                                                <label class="form-check-label" for="alcohol-paid">
                                                    За отдельную плату
                                                </label>
                                            </div>

                                            <!-- Поле для цены — показывается только если выбран вариант «За отдельную плату» -->
                                            <div id="alcoholPriceContainer" class="mt-2"
                                                 style="display: {{ old('alcohol') == '2' ? 'block' : 'none' }};">
                                                <label for="alcohol_price">Цена доплаты за свой алкоголь за
                                                    человека:</label>
                                                <input id="alcohol_price"
                                                       name="alcohol_price"
                                                       type="number"
                                                       oninput="
         if (this.value.length > 7) {
           this.value = this.value.slice(0, 7);
           this.style.borderColor = 'red';
           setTimeout(() => this.style.borderColor = '', 1000);
         } else {
           this.style.borderColor = '';
         }"
                                                       step="0.01"
                                                       value="{{ old('alcohol_price') }}"
                                                       data-saved-price="{{ old('alcohol_price') }}"
                                                       class="form-control"
                                                       placeholder="Введите цену"
                                                       autocomplete="off">
                                            </div>
                                            <script>
                                                document.addEventListener('DOMContentLoaded', function () {
                                                    const alcoholRadios = document.querySelectorAll('input[name="alcohol"][type="radio"]');
                                                    const alcoholPriceContainer = document.getElementById('alcoholPriceContainer');
                                                    const alcoholPriceInput = document.getElementById('alcohol_price');

                                                    function toggleAlcoholPriceField() {
                                                        const selectedValue = document.querySelector('input[name="alcohol"]:checked')?.value;

                                                        if (selectedValue === '2') {
                                                            alcoholPriceContainer.style.display = 'block';
                                                            // Если есть сохранённая цена — показываем её
                                                            const savedPrice = alcoholPriceInput.getAttribute('data-saved-price');
                                                            if (savedPrice && savedPrice !== '0') {
                                                                alcoholPriceInput.value = savedPrice;
                                                            }
                                                        } else {
                                                            alcoholPriceContainer.style.display = 'none';
                                                            alcoholPriceInput.value = '';
                                                        }
                                                    }

                                                    alcoholRadios.forEach(radio => {
                                                        radio.addEventListener('change', toggleAlcoholPriceField);
                                                    });

                                                    // Инициализация при загрузке — учитываем старые значения
                                                    toggleAlcoholPriceField();
                                                });

                                            </script>
                                        </div>
                                    </div>
                                </td>
                                <td style="width: 49%">
                                    <div>
                                        <label for="service_fee"><b>Сервисный сбор:</b></label><br>
                                        <span> Процент за обслуживание. Оставьте пустым, если платы нет </span>
                                        <input name="service_fee" type="number"
                                               oninput="
         if (this.value.length > 7) {
           this.value = this.value.slice(0, 7);
           this.style.borderColor = 'red';
           setTimeout(() => this.style.borderColor = '', 1000);
         } else {
           this.style.borderColor = '';
         }"
                                               value="{{old('service_fee') }}"
                                               class="form-control"
                                               placeholder="Сервисный сбор" autocomplete="off">
                                        <br>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <table class="styled-table">
                            <tr>
                                <td style="width: 49%">
                                    <div>
                                        <label for="bring_with_you"><b>Можно принести с собой:</b></label>
                                        <div class="checkbox-group">
                                            <label class="checkbox-container">
                                                <input name="bring_with_you[]" class="bring_with_you" type="checkbox" value="Безалкогольные напитки">
                                                <span class="checkmark"></span>
                                                Безалкогольные напитки
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="bring_with_you[]" class="bring_with_you" type="checkbox" value="Фрукты">
                                                <span class="checkmark"></span>
                                                Фрукты
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="bring_with_you[]" class="bring_with_you" type="checkbox" value="Икра">
                                                <span class="checkmark"></span>
                                                Икра
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="bring_with_you[]" class="bring_with_you" type="checkbox" value="Торт">
                                                <span class="checkmark"></span>
                                                Торт
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="bring_with_you[]" class="bring_with_you" type="checkbox" value="Каравай">
                                                <span class="checkmark"></span>
                                                Каравай
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="bring_with_you[]" class="bring_with_you" type="checkbox" value="Другое">
                                                <span class="checkmark"></span>
                                                Другое
                                            </label>
                                        </div>
                                    </div>
                                </td>
                                <td style="width: 49%">

                                </td>
                            </tr>
                        </table>
                        <br>
                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">Описание коротко (до 150 символов)</label>
                            <textarea
                                    name="description"
                                    id="description"
                                    class="form-control"
                                    rows="4"
                                    maxlength="150"
                                    placeholder="Например: банкетный зал на 120 гостей, панорамные окна, своя кухня"
                            >{{ old('description', $obj->description ?? '') }}</textarea>
                            <div class="form-text text-end">
                                <span id="description_counter">0</span> / 150 символов
                            </div>
                        </div>
                        <br>
                        <div>
                            <label for="text_obj"><b>Описание:</b></label><br>
                            <textarea class="form-control" placeholder="Введите текст..." name="text_obj" id="text_obj"
                                      rows="5" cols="85"> {{old('text_obj')}}</textarea><br>
                        </div>
                        <br>
                        <br>
                        <input style="margin-bottom: 50px" class="btn-festive-gradient btn-festive-gradient-green"
                               type="submit" value="Продолжить">
                    </form>
                </div>
            </div>
        </div>
    </section>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const textarea = document.getElementById('description');
            const counter = document.getElementById('description_counter');

            if (textarea && counter) {
                const updateCounter = () => {
                    const length = textarea.value.length;
                    counter.textContent = length;
                };

                updateCounter();
                textarea.addEventListener('input', updateCounter);
            }
        });

    </script>
    <script>
        let checkboxForEvents = document.getElementsByClassName('for_events');
        let checkboxKitchen = document.getElementsByClassName('kitchen');
        let checkboxPaymentMethods = document.getElementsByClassName('payment_methods');
        let checkboxServices = document.getElementsByClassName('service');
        let checkboxBringWithYou = document.getElementsByClassName('bring_with_you');

        if (@json(old('bring_with_you'))) {
            const oldBringWithYou = @json(old('bring_with_you'));
            for (var index = 0; index < checkboxBringWithYou.length; index++) {
                if (oldBringWithYou.includes(checkboxBringWithYou[index].value)) {
                    checkboxBringWithYou[index].checked = true;
                }
            }
        }


        if (@json(old('for_events'))) {
            //----------------- Для мероприятий:
            const oldForEventsArray = @json(old('for_events'));
            for (var index = 0; index < checkboxForEvents.length; index++) {
                if (oldForEventsArray.includes(checkboxForEvents[index].value)) {
                    checkboxForEvents[index].checked = true;
                }
            }

            const oldKitchen = @json(old('kitchen'));
            for (var index = 0; index < checkboxKitchen.length; index++) {
                if (oldKitchen.includes(checkboxKitchen[index].value)) {
                    checkboxKitchen[index].checked = true;
                }
            }

            const oldService = @json(old('service'));
            for (var index = 0; index < checkboxServices.length; index++) {
                if (oldService.includes(checkboxServices[index].value)) {
                    checkboxServices[index].checked = true;
                }
            }

            //----------------- Оплата:
            const oldPaymentMethods = @json(old('payment_methods'));
            for (var index = 0; index < checkboxPaymentMethods.length; index++) {
                if (oldPaymentMethods.includes(checkboxPaymentMethods[index].value)) {
                    checkboxPaymentMethods[index].checked = true;
                }
            }
        }


    </script>
    {{--        <script src="{{ asset('js/checkbox/checkbox.js') }}" defer></script>--}}
@endsection
