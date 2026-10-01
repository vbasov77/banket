@extends('layouts.app')
@section('content')
    <link href="{{ asset('css/tables.css') }}" rel="stylesheet">
    <link href="{{ asset('css/checkbox.css') }}" rel="stylesheet">

    <section>
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5">
                <div class="col-lg-12 mt-5">
                    <h3>Особенности объекта «{{ $obj->name_obj ?? 'Объект' }}»</h3>
                    <span>Отредактируйте дополнительные параметры объекта.</span>

                    <form action="{{ route('update.obj_features', ['id' => $feature->id]) }}" method="post">
                        @csrf
                        @method('PUT')
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

                        <!-- Текстовые поля -->
                        <table class="styled-table">
                            <tr>
                                <td style="width: 49%">
                                    <div>
                                        <label for="banquet_note"><b>Примечание к банкету:</b></label>
                                        <textarea name="banquet_note" id="banquet_note"
                                                  class="form-control" rows="3"
                                                  placeholder="Например: банкет начинается после 18:00">{{ old('banquet_note', $feature->banquet_note ?? '') }}</textarea>
                                    </div>
                                </td>
                                <td style="width: 49%">
                                    <div>
                                        <label for="prepayment"><b>Предоплата:</b></label>
                                        <input name="prepayment" id="prepayment" type="text"
                                               class="form-control"
                                               value="{{ old('prepayment', $feature->prepayment ?? '') }}"
                                               placeholder="Например: 30% или 5000 ₽"
                                               autocomplete="off">
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <!-- Текстиль и столы (чекбоксы) -->
                        <table class="styled-table">
                            <tr>
                                <td style="width: 49%">
                                    <div>
                                        <label><b>Текстильный пакет:</b></label>
                                        <div class="checkbox-group">
                                            @php
                                                $textileList = [
                                                    'Юбки',
                                                    'Скатерти',
                                                    'Скатерти и напероны на столы',
                                                    'Салфетки',
                                                    'Бегуны',
                                                    'Банты/ленты',
                                                    'Чехлы и банты на стулья',
                                                    'Дорожки на столы',
                                                    'Пледы/покрывала',
                                                    'Декоративные подушки',
                                                ];
                                                $selectedTextile = old('textile_package', $feature->textile_package ?? []);
                                                if (!is_array($selectedTextile)) {
                                                    $selectedTextile = is_string($selectedTextile)
                                                        ? explode(',', $selectedTextile)
                                                        : (json_decode($selectedTextile, true) ?? []);
                                                }
                                            @endphp
                                            @foreach ($textileList as $textile)
                                                <label class="checkbox-container">
                                                    <input name="textile_package[]" class="textile" type="checkbox"
                                                           value="{{ $textile }}"
                                                            {{ in_array($textile, $selectedTextile) ? 'checked' : '' }}>
                                                    <span class="checkmark"></span>
                                                    {{ $textile }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                                <td style="width: 49%">
                                    <div>
                                        <label for="textile_colors"><b>Цвета текстиля:</b></label>
                                        <input name="textile_colors" id="textile_colors" type="text"
                                               class="form-control"
                                               value="{{ old('textile_colors', $feature->textile_colors ?? '') }}"
                                               placeholder="Например: белый, бордовый, кремовый"
                                               autocomplete="off">
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <table class="styled-table">
                            <tr>
                                <td style="width: 49%">
                                    <div>
                                        <label><b>Столы:</b></label>
                                        <div class="checkbox-group">
                                            @php
                                                $tablesList = [
                                                    'Круглые',
                                                    'Прямоугольные',
                                                    'Квадратные',
                                                    'Овальные',
                                                ];
                                                $selectedTables = old('tables', $feature->tables ?? []);
                                                if (!is_array($selectedTables)) {
                                                    $selectedTables = is_string($selectedTables)
                                                        ? explode(',', $selectedTables)
                                                        : (json_decode($selectedTables, true) ?? []);
                                                }
                                            @endphp
                                            @foreach ($tablesList as $table)
                                                <label class="checkbox-container">
                                                    <input name="tables[]" class="tables" type="checkbox"
                                                           value="{{ $table }}"
                                                            {{ in_array($table, $selectedTables) ? 'checked' : '' }}>
                                                    <span class="checkmark"></span>
                                                    {{ $table }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                                <td style="width: 49%">
                                </td>
                            </tr>
                        </table>

                        <!-- Локация -->
                        <table class="styled-table">
                            <tr>
                                <td style="width: 49%">
                                    <div>
                                        <label for="parking"><b>Парковка:</b></label>
                                        <input name="parking" id="parking" type="text"
                                               class="form-control"
                                               value="{{ old('parking', $feature->parking ?? '') }}"
                                               placeholder="Например: 50 машин, бесплатно"
                                               autocomplete="off">
                                    </div>
                                </td>
                                <td style="width: 49%">
                                    <div>
                                        <label for="pier"><b>Пирс / Причал:</b></label>
                                        <input name="pier" id="pier" type="text"
                                               class="form-control"
                                               value="{{ old('pier', $feature->pier ?? '') }}"
                                               placeholder="Например: есть, 3 места"
                                               autocomplete="off">
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <table class="styled-table">
                            <tr>
                                <td style="width: 49%">
                                    <div>
                                        <label><b>Расположение:</b></label>
                                        <div class="checkbox-group">
                                            @php
                                                $locationList = [
                                                    'Центр города',
                                                    'Шатёр',
                                                    'У воды',
                                                    'На высоте',
                                                    'В парке',
                                                    'Зелёная зона',
                                                    'Рядом с метро',
                                                    'На набережной',
                                                    'При гостинице',
                                                    'Панорамный вид на улице',
                                                    'С верандой/террасой',
                                                    'На закрытой территории',
                                                    'Территория для регистрации',
                                                ];
                                                $selectedLocation = old('location', $feature->location ?? []);
                                                if (!is_array($selectedLocation)) {
                                                    $selectedLocation = is_string($selectedLocation)
                                                        ? explode(',', $selectedLocation)
                                                        : (json_decode($selectedLocation, true) ?? []);
                                                }
                                            @endphp
                                            @foreach ($locationList as $loc)
                                                <label class="checkbox-container">
                                                    <input name="location[]" class="location" type="checkbox"
                                                           value="{{ $loc }}"
                                                            {{ in_array($loc, $selectedLocation) ? 'checked' : '' }}>
                                                    <span class="checkmark"></span>
                                                    {{ $loc }}
                                                </label>
                                            @endforeach

                                        </div>
                                    </div>
                                </td>
                                <td style="width: 49%">
                                    <div>
                                        <label for="interior"><b>Интерьер:</b></label>
                                        <textarea name="interior" id="interior"
                                                  class="form-control" rows="3"
                                                  placeholder="Например: классический, панорамные окна, мраморные колонны">{{ old('interior', $feature->interior ?? '') }}</textarea>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <!-- Оборудование и дети (чекбоксы) -->
                        <table class="styled-table">
                            <tr>
                                <td style="width: 49%">
                                    <div>
                                        <label for="equipment"><b>Оборудование:</b></label>
                                        <div class="checkbox-group">
                                            @php
                                                $equipmentList = [
                                                    'Звуковое оборудование',
                                                    'Световое оборудование',
                                                    'Своё оборудование',
                                                    'Проекционное оборудование',
                                                    'Проекторный экран',
                                                    'Микрофоны',
                                                    'Сцена',
                                                    'Танцпол',
                                                    'Кондиционер',
                                                    'Гардероб',
                                                    'Лифт для гостей',
                                                    'Wi-Fi',
                                                    'Гримёрки для артистов',
                                                ];
                                                $selectedEquipment = old('equipment', $feature->equipment ?? []);
                                                if (!is_array($selectedEquipment)) {
                                                    $selectedEquipment = is_string($selectedEquipment)
                                                        ? explode(',', $selectedEquipment)
                                                        : (json_decode($selectedEquipment, true) ?? []);
                                                }
                                            @endphp
                                            @foreach ($equipmentList as $eq)
                                                <label class="checkbox-container">
                                                    <input name="equipment[]" class="equipment" type="checkbox"
                                                           value="{{ $eq }}"
                                                            {{ in_array($eq, $selectedEquipment) ? 'checked' : '' }}>
                                                    <span class="checkmark"></span>
                                                    {{ $eq }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                                <td style="width: 49%">
                                    <div>
                                        <label for="kids"><b>Для детей:</b></label>
                                        <div class="checkbox-group">
                                            @php
                                                $kidsList = [
                                                    'Детское меню',
                                                    'Аниматоры',
                                                    'Детская комната',
                                                    'Детская площадка',
                                                    'Колясочное место',
                                                    'Высокие стульчики',
                                                    'Игровая зона',
                                                ];
                                                $selectedKids = old('kids', $feature->kids ?? []);
                                                if (!is_array($selectedKids)) {
                                                    $selectedKids = is_string($selectedKids)
                                                        ? explode(',', $selectedKids)
                                                        : (json_decode($selectedKids, true) ?? []);
                                                }
                                            @endphp
                                            @foreach ($kidsList as $kid)
                                                <label class="checkbox-container">
                                                    <input name="kids[]" class="kids" type="checkbox"
                                                           value="{{ $kid }}"
                                                            {{ in_array($kid, $selectedKids) ? 'checked' : '' }}>
                                                    <span class="checkmark"></span>
                                                    {{ $kid }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <br>
                        <button type="submit" class="btn-festive-gradient btn-festive-gradient-green"
                                style="margin-bottom: 50px;">Сохранить
                        </button>

                        <a href="{{ route('my.obj') }}"
                           class="btn-festive-gradient btn-festive-gradient-white"
                           style="margin-bottom: 50px; text-decoration: none; display: inline-block;">
                            Перейти в панель управления
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection