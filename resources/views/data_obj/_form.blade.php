@php
    // Гарантируем, что переменная существует всегда
    $restaurant = $restaurant ?? null;
    $editMode = isset($restaurant) && $restaurant !== null;

    $val = function ($field) use ($editMode, $restaurant) {
        if ($editMode) {
            return old($field, $restaurant->{$field} ?? '');
        }
        return old($field, '');
    };

    $checked = function ($field) use ($editMode, $restaurant) {
        $v = $editMode ? old($field, $restaurant->{$field} ?? '') : old($field, '');
        return $v === 'в наличии' || $v === '1' || $v === true;
    };
@endphp



<div class="card mb-4 p-4 shadow-sm">
    <div class="row mb-3">
        <div class="col-md-8">
            <label class="form-label fw-bold">Название объекта</label>
            <input type="text" name="name" class="form-control" value="{{ $val('name') }}" required>
        </div>
    </div>
</div>

<div class="card mb-4 p-4 shadow-sm">
    <h5 class="fw-bold mb-3 border-bottom pb-2">Условия банкета</h5>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Банкетное меню</label>
            <input type="text" name="banquet_menu" class="form-control" value="{{ $val('banquet_menu') }}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Проценты за обслуживание</label>
            <input type="text" name="service_fee" class="form-control" value="{{ $val('service_fee') }}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Свой алкоголь</label>
            <input type="text" name="own_alcohol" class="form-control" value="{{ $val('own_alcohol') }}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Можно принести с собой</label>
            <input type="text" name="bring_with_you" class="form-control" value="{{ $val('bring_with_you') }}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Предоплата</label>
            <textarea name="prepayment" class="form-control" rows="2">{{ $val('prepayment') }}</textarea>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Примечание</label>
            <textarea name="banquet_note" class="form-control" rows="2">{{ $val('banquet_note') }}</textarea>
        </div>
        <div class="col-12 mb-3">
            <label class="form-label fw-bold">Акции, скидки и подарки</label>
            <textarea name="actions" class="form-control" rows="6">{{ $val('actions') }}</textarea>
        </div>
    </div>
</div>

{{-- Дополнительная информация --}}
<div class="card mb-4 p-4 shadow-sm">
    <h5 class="fw-bold mb-3 border-bottom pb-2">Дополнительная информация</h5>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Текстильный пакет</label>
            <textarea name="textile_package" class="form-control" rows="2">{{ $val('textile_package') }}</textarea>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Расцветки текстильного пакета</label>
            <textarea name="textile_colors" class="form-control" rows="2">{{ $val('textile_colors') }}</textarea>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label fw-bold">Столы</label>
            <input type="text" name="tables" class="form-control" value="{{ $val('tables') }}">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label fw-bold">Громкая музыка</label>
            <input type="text" name="loud_music" class="form-control" value="{{ $val('loud_music') }}">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label fw-bold">Парковка</label>
            <input type="text" name="parking" class="form-control" value="{{ $val('parking') }}">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label fw-bold">Причал</label>
            <input type="text" name="pier" class="form-control" value="{{ $val('pier') }}">
        </div>
    </div>
</div>

{{-- Оборудование --}}
<div class="card mb-4 p-4 shadow-sm">
    <h5 class="fw-bold mb-3 border-bottom pb-2">Оборудование</h5>

    {{-- Поля-селекты --}}
    <div class="row mb-3">
        @php
            $equipSelects = [
                'own_equipment' => 'Своё оборудование',
                'projector_screen' => 'Проекторный экран',
                'music_stage' => 'Музыкальная сцена',
                'sound_equipment' => 'Звуковое оборудование',
                'light_equipment' => 'Световое оборудование',
                'dimming_system' => 'Система затемнения',
            ];
            $ownEquipOptions = ['', 'можно, бесплатно', 'можно, платно', 'нет', 'в наличии'];
            $standardOptions = ['', 'в наличии', 'отсутствует', 'по запросу'];
        @endphp
        @foreach($equipSelects as $field => $label)
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">{{ $label }}</label>
                <select name="{{ $field }}" class="form-select">
                    @php
                        $options = $field === 'own_equipment' ? $ownEquipOptions : $standardOptions;
                        $current = $val($field);
                    @endphp
                    @foreach($options as $opt)
                        <option value="{{ $opt }}" @if($current === $opt) selected @endif>
                            {{ $opt === '' ? '— не указано —' : $opt }}
                        </option>
                    @endforeach
                    @if($current && !in_array($current, $options))
                        <option value="{{ $current }}" selected>{{ $current }}</option>
                    @endif
                </select>
            </div>
        @endforeach
    </div>

    {{-- Чекбоксы --}}
    <div class="row">
        @php
            $checkboxes = [
                'karaoke' => 'Караоке',
                'wifi' => 'Wi-Fi',
                'dance_floor' => 'Танцпол',
                'air_conditioner' => 'Кондиционер',
                'wardrobe' => 'Гардероб',
                'dressing_rooms' => 'Гримёрки для артистов',
                'kids_room' => 'Детская комната',
                'kids_menu' => 'Детское меню',
                'kids_corner' => 'Детский уголок',
            ];
        @endphp
        @foreach($checkboxes as $field => $label)
            <div class="col-md-4 mb-2">
                <div class="form-check">
                    <input
                            type="checkbox"
                            name="{{ $field }}"
                            value="в наличии"
                            class="form-check-input"
                            id="chk_{{ $field }}"
                            @if($checked($field)) checked @endif
                    >
                    <label for="chk_{{ $field }}" class="form-check-label">{{ $label }}</label>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Особенности заведения --}}
<div class="card mb-4 p-4 shadow-sm">
    <h5 class="fw-bold mb-3 border-bottom pb-2">Особенности заведения</h5>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Интерьер</label>
            <input type="text" name="interior" class="form-control" value="{{ $val('interior') }}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Месторасположение</label>
            <textarea name="location" class="form-control" rows="2">{{ $val('location') }}</textarea>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Режим работы</label>
            <input type="text" name="working_hours" class="form-control" value="{{ $val('working_hours') }}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Сайт</label>
            <input type="text" name="website" class="form-control" value="{{ $val('website') }}">
        </div>
    </div>
</div>
