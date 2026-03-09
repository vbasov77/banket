@extends('layouts.app')
@section('content')
    <link href="{{ asset('css/tables.css') }}" rel="stylesheet">
    <link href="{{ asset('css/checkbox.css') }}" rel="stylesheet">
    <section style="margin-top: 50px">
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5">
                <div class="col-lg-12">
                    <form action="{{route('store.details_obj')}}" method="post">
                        @csrf
                        <h3>Добавьте детали вашего объекта {{$obj->name_obj}}</h3>
                        <br>
                        <input type="hidden" name="obj_id" value="{{$obj->id}}">
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
                                        </div>
                                    </div>
                                </td>
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
                                                       value="Азиатская">
                                                <span class="checkmark"></span>
                                                Азиатская
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="kitchen[]" class="kitchen" type="checkbox" value="Русская">
                                                <span class="checkmark"></span>
                                                Европейская
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
                                                       value="Наличные">
                                                <span class="checkmark"></span>
                                                Наличные
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="payment_methods[]"
                                                       class="payment_methods" type="checkbox"
                                                       value="Карта">
                                                <span class="checkmark"></span>
                                                Карта
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
                                                <label for="alcohol_price">Цена доплаты за свой алкоголь за человека:</label>
                                                <input id="alcohol_price"
                                                       name="alcohol_price"
                                                       type="number"
                                                       min="0"
                                                       max="100000"
                                                       step="0.01"
                                                       value="{{ old('alcohol_price') }}"
                                                       data-saved-price="{{ old('alcohol_price') }}"
                                                       class="form-control"
                                                       placeholder="Введите цену"
                                                       autocomplete="off">
                                            </div>
                                            <script>
                                                document.addEventListener('DOMContentLoaded', function() {
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
                                        <label><b>Свои фрукты, другое:</b></label>
                                        <div class="radio-group">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="more"
                                                       id="more-forbidden"
                                                       value="0"
                                                       {{ old('more') == '0' ? 'checked' : '' }}
                                                       required>
                                                <label class="form-check-label" for="more-forbidden">
                                                    Запрещено
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="more"
                                                       id="more-allowed"
                                                       value="1"
                                                       {{ old('more') == '1' ? 'checked' : '' }}
                                                       required>
                                                <label class="form-check-label" for="more-allowed">
                                                    Разрешено бесплатно
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="more"
                                                       id="more-paid"
                                                       value="2"
                                                       {{ old('more') == '2' ? 'checked' : '' }}
                                                       required>
                                                <label class="form-check-label" for="more-paid">
                                                    За отдельную плату
                                                </label>
                                            </div>

                                            <!-- Поле для цены — показывается только если выбран вариант «За отдельную плату» -->
                                            <div id="morePriceContainer" class="mt-2"
                                                 style="display: {{ old('more') == '2' ? 'block' : 'none' }};">
                                                <label for="more_price">Цена доплаты за свои фрукты/другое:</label>
                                                <input id="more_price"
                                                       name="more_price"
                                                       type="number"
                                                       min="0"
                                                       max="100000"
                                                       step="0.01"
                                                       value="{{ old('more_price') }}"
                                                       data-saved-price="{{ old('more_price') }}"
                                                       class="form-control"
                                                       placeholder="Введите цену"
                                                       autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            const moreRadios = document.querySelectorAll('input[name="more"][type="radio"]');
                                            const morePriceContainer = document.getElementById('morePriceContainer');
                                            const morePriceInput = document.getElementById('more_price');

                                            function toggleMorePriceField() {
                                                const selectedValue = document.querySelector('input[name="more"]:checked')?.value;

                                                if (selectedValue === '2') {
                                                    morePriceContainer.style.display = 'block';
                                                    // Если есть сохранённая цена — показываем её
                                                    const savedPrice = morePriceInput.getAttribute('data-saved-price');
                                                    if (savedPrice && savedPrice !== '0') {
                                                        morePriceInput.value = savedPrice;
                                                    }
                                                } else {
                                                    morePriceContainer.style.display = 'none';
                                                    morePriceInput.value = '';
                                                }
                                            }

                                            moreRadios.forEach(radio => {
                                                radio.addEventListener('change', toggleMorePriceField);
                                            });

                                            // Инициализация при загрузке — учитываем старые значения
                                            toggleMorePriceField();
                                        });
                                    </script>
                                </td>
                            </tr>
                        </table>
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
        let checkboxForEvents = document.getElementsByClassName('for_events');
        let checkboxKitchen = document.getElementsByClassName('kitchen');
        let checkboxPaymentMethods = document.getElementsByClassName('payment_methods');

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
