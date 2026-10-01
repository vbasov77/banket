@extends('layouts.app')
@section('content')
    <link href="{{ asset('css/tables.css') }}" rel="stylesheet">
    <link href="{{ asset('css/checkbox.css') }}" rel="stylesheet">

    <section>
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5">
                <div class="col-lg-12 mt-5">
                    <h3>Добавьте/отредактируйте детали объекта «{{ $obj->name_obj ?? 'Объект' }}»</h3>
                    <span>Детали объекта — это более подробная информация о вашем объекте.</span>

                    <form action="{{ route('update.details_obj', ['id' => $obj->id]) }}" method="post">
                        @csrf
                        <!-- Скрытое поле ID объекта -->
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
                                        <label><b>Для мероприятий:</b></label>
                                        <div class="checkbox-group">
                                            @foreach (['Свадьба','День рождения','Корпоратив', 'Корпоратив на Новый Год','Выпускной','Детский праздник','Фуршет','Мальчишник/Девичник', 'Презентация'] as $event)
                                                <label class="checkbox-container">
                                                    <input name="for_events[]" class="for_events" type="checkbox"
                                                           value="{{ $event }}"
                                                            {{ in_array($event, old('for_events', $obj->for_events ?? [])) ? 'checked' : '' }}>
                                                    <span class="checkmark"></span>
                                                    {{ $event }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                                <td style="width: 49%">
                                    <div>
                                        <label><b>Кухня:</b></label>
                                        <div class="checkbox-group">
                                            @foreach (['Русская','Кавказская', 'Китайская', 'Японская', 'Азиатская','Европейская', 'Паназиатская', 'Смешенная'] as $k)
                                                <label class="checkbox-container">
                                                    <input name="kitchen[]" class="kitchen" type="checkbox"
                                                           value="{{ $k }}"
                                                            {{ in_array($k, old('kitchen', $obj->kitchen ?? [])) ? 'checked' : '' }}>
                                                    <span class="checkmark"></span>
                                                    {{ $k }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <table class="styled-table">
                            <tr>
                                <td style="width: 49%">
                                    <div>
                                        <label><b>Способы оплаты:</b></label>
                                        <div class="checkbox-group">
                                            @foreach (['Наличный', 'Безналичный','Банковская карта','Перевод'] as $p)
                                                <label class="checkbox-container">
                                                    <input name="payment_methods[]" class="payment_methods" type="checkbox"
                                                           value="{{ $p }}"
                                                            {{ in_array($p, old('payment_methods', $obj->payment_methods ?? [])) ? 'checked' : '' }}>
                                                    <span class="checkmark"></span>
                                                    {{ $p }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                                <td style="width: 49%">
                                    <div>
                                        <label><b>Сервис:</b></label>
                                        <div class="checkbox-group">
                                            @php
                                                $services = [
                                                    'Ведущий/Тамада','Диджей','Живая музыка','Фотограф/Видеооператор',
                                                    'Аниматоры','Украшение зала','Оформление фотозоны','Воздушные шары',
                                                    'Звуковое оборудование','Световое оборудование','Проекционное оборудование',
                                                    'Трансфер для гостей','Выездная регистрация','Фейерверк/Салют'
                                                ];
                                            @endphp
                                            @foreach ($services as $s)
                                                <label class="checkbox-container">
                                                    <input name="service[]" class="service" type="checkbox"
                                                           value="{{ $s }}"
                                                            {{ in_array($s, old('service', $obj->service ?? [])) ? 'checked' : '' }}>
                                                    <span class="checkmark"></span>
                                                    {{ $s }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <table class="styled-table">
                            <tr>
                                <td style="width: 49%">
                                    <label><b>Пробковый сбор:</b></label>
                                    <div class="radio-group">
                                        @php
                                            $alcohol = old('alcohol', $obj->alcohol ?? null);
                                        @endphp

                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="alcohol"
                                                   id="alcohol-free" value="0"
                                                   {{ $alcohol === '0' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="alcohol-free">Разрешено бесплатно</label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="alcohol"
                                                   id="alcohol-forbidden" value="1"
                                                   {{ $alcohol === '1' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="alcohol-forbidden">Запрещено</label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="alcohol"
                                                   id="alcohol-paid" value="2"
                                                   {{ $alcohol === '2' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="alcohol-paid">За отдельную плату</label>
                                        </div>

                                        @php
                                            $price = old('alcohol_price', $obj->alcohol_price ?? 0);
                                        @endphp
                                        <div id="alcoholPriceContainer" class="mt-2"
                                             style="display: {{ $alcohol === '2' ? 'block' : 'none' }};">
                                            <label for="alcohol_price">Цена доплаты за свой алкоголь за человека:</label>
                                            <input id="alcohol_price" name="alcohol_price" type="number"
                                                   min="0" max="100000" step="0.01"
                                                   value="{{ $price }}"
                                                   class="form-control" placeholder="Введите цену" autocomplete="off">
                                        </div>
                                    </div>
                                </td>
                                <td style="width: 49%">
                                    <label><b>Сервисный сбор:</b></label><br>
                                    <span>Процент за обслуживание. Оставьте пустым, если платы нет.</span>
                                    <input name="service_fee" type="number"
                                           oninput="
                                               if (this.value.length > 7) {
                                                   this.value = this.value.slice(0, 7);
                                                   this.style.borderColor = 'red';
                                                   setTimeout(() => this.style.borderColor = '', 1000);
                                               } else {
                                                   this.style.borderColor = '';
                                               }
                                           "
                                           value="{{ old('service_fee', $obj->service_fee ?? '') }}"
                                           class="form-control"
                                           placeholder="Сервисный сбор" autocomplete="off">
                                </td>
                            </tr>
                        </table>
                        <table class="styled-table">
                            <tr>
                                <td style="width: 49%">
                                    <label><b>Можно принести с собой:</b></label>
                                    <div class="checkbox-group">
                                        @foreach (['Безалкогольные напитки','Фрукты','Икра','Торт','Каравай','Другое'] as $item)
                                            <label class="checkbox-container">
                                                <input name="bring_with_you[]" class="bring_with_you" type="checkbox"
                                                       value="{{ $item }}"
                                                        {{ in_array($item, old('bring_with_you', $obj->bring_with_you ?? [])) ? 'checked' : '' }}>
                                                <span class="checkmark"></span>
                                                {{ $item }}
                                            </label>
                                        @endforeach
                                    </div>
                                </td>
                                <td style="width: 49%"></td>
                            </tr>
                        </table>

                        <br>
                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">Описание объекта (до 150 символов)</label>
                            <textarea name="description" id="description" class="form-control" rows="4"
                                      maxlength="150"
                                      placeholder="Например: банкетный зал на 120 гостей, панорамные окна, своя кухня"
                            >{{ old('description', $obj->description ?? '') }}</textarea>
                            <div class="form-text text-end">
                                <span id="description_counter">0</span> / 150 символов
                            </div>
                        </div>

                        <br>
                        <div>
                            <label for="text_obj"><b>Полное описание:</b></label><br>
                            <textarea class="form-control" name="text_obj" id="text_obj"
                                      rows="5" cols="85"
                                      placeholder="Введите текст...">{{ old('text_obj', $obj->text_obj ?? '') }}</textarea>
                        </div>
                        <br><br>

                        <button type="submit" class="btn-festive-gradient btn-festive-gradient-green"
                                style="margin-bottom: 50px;">Продолжить</button>

                        <a href="{{ route('my.obj') }}" class="btn-festive-gradient btn-festive-gradient-white"
                           style="margin-bottom: 50px; text-decoration: none; display: inline-block;">
                            Перейти в панель управления
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Подсчёт символов в description
        document.addEventListener('DOMContentLoaded', () => {
            const textarea = document.getElementById('description');
            const counter = document.getElementById('description_counter');

            if (textarea && counter) {
                const updateCounter = () => {
                    counter.textContent = textarea.value.length;
                };
                updateCounter();
                textarea.addEventListener('input', updateCounter);
            }
        });

        // Логика поля «Цена за алкоголь»
        document.addEventListener('DOMContentLoaded', function () {
            const alcoholRadios = document.querySelectorAll('input[name="alcohol"][type="radio"]');
            const alcoholPriceContainer = document.getElementById('alcoholPriceContainer');
            const alcoholPriceInput = document.getElementById('alcohol_price');

            function toggleAlcoholPriceField() {
                const selected = document.querySelector('input[name="alcohol"]:checked');
                if (selected && selected.value === '2') {
                    alcoholPriceContainer.style.display = 'block';
                } else {
                    alcoholPriceContainer.style.display = 'none';
                    alcoholPriceInput.value = '';
                }
            }

            alcoholRadios.forEach(radio => {
                radio.addEventListener('change', toggleAlcoholPriceField);
            });
            toggleAlcoholPriceField();
        });
    </script>
@endsection
