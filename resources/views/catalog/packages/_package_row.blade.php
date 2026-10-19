@php
    $fieldOld = fn (string $field) => old("packages.{$i}.{$field}", $pkg[$field] ?? '');
@endphp

<div class="package-row border rounded-3 p-3 mb-3 bg-light">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong>Пакет <span class="package-num">{{ is_numeric($i) ? $i + 1 : '#' }}</span></strong>

        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-secondary package-up" title="Выше">↑</button>
            <button type="button" class="btn btn-outline-secondary package-down" title="Ниже">↓</button>
            <button type="button" class="btn btn-outline-danger package-remove" title="Удалить">✕</button>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-12">
            <input type="text"
                   class="form-control"
                   placeholder="Название пакета"
                   data-field="title"
                   value="{{ $fieldOld('title') }}">
        </div>
        <div class="col-6 col-md-4">
            <input type="number" min="0" class="form-control"
                   placeholder="Цена от, ₽"
                   data-field="price_min"
                   value="{{ $fieldOld('price_min') }}">
        </div>
        <div class="col-6 col-md-4">
            <input type="number" min="0" class="form-control"
                   placeholder="Цена до, ₽ (можно не указывать)"
                   data-field="price_max"
                   value="{{ $fieldOld('price_max') }}">
        </div>
        <div class="col-12 col-md-4 d-flex align-items-center">
            <div class="form-check">
                <input type="checkbox" class="form-check-input"
                       data-field="is_addon" value="1"
                       id="addon-{{ $i }}"
                        @checked($fieldOld('is_addon'))>
                <label class="form-check-label" for="addon-{{ $i }}">Дополнение</label>
            </div>
        </div>
        <div class="col-12">
            <textarea class="form-control" rows="2"
                      placeholder="Что входит в пакет"
                      data-field="description">{{ $fieldOld('description') }}</textarea>
        </div>
    </div>
</div>
