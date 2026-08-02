@extends('layouts.app', ['title' => "Фильтры: банкетные залы"])

@section('content')
    <style>
        .filter-section {
            max-width: 1200px;
            margin: 0 auto;
        }

        .styled-table input.form-control {
            width: 50%;
        }

        @media (max-width: 480px) {
            .styled-table input.form-control {
                width: 100%;
            }
        }

        /* Сетка фильтров */
        .filters-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .filters-grid {
                grid-template-columns: 1fr;
            }
        }

        .filter-group {
            border: 1px solid #e0e0e0;
            padding: 16px;
            border-radius: 8px;
            background: #fafafa;
        }

        .filter-group h4 {
            margin-top: 0;
            margin-bottom: 12px;
            font-size: 1.1rem;
        }

        /* Стили для селектов */
        select.form-select {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        /* Для множественного выбора (районы) — прокрутка */
        select[multiple] {
            height: auto;
            max-height: 220px; /* фиксируем высоту, чтобы была прокрутка */
            overflow-y: auto; /* включаем вертикальную прокрутку */
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 5px;
            font-size: 14px;
        }

        /* Стили для ошибок */
        .input-error {
            border-color: red !important;
        }
    </style>

    <link href="{{ asset('css/checkbox.css') }}" rel="stylesheet">
    <link href="{{ asset('css/tables.css') }}" rel="stylesheet">

    <section>
        <div class="container px-4 px-lg-5 filter-section">
            <div class="row gx-4 gx-lg-5">
                <div class="col-lg-12 mt-5">
                    <h3>Фильтры</h3>
                    <span>Заполните фильтры для более точного поиска</span>

                    @if ($errors->any())
                        <div class="alert alert-danger mt-3">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="post" action="{{ route('search.objs') }}">
                        @csrf

                        <div class="filters-grid">
                            <div class="filter-group">
                                <h4>Вместимость (человек) от:</h4>
                                <input name="capacity_to" type="number"
                                       value="{{ old('capacity_to') }}"
                                       class="form-control {{ $errors->has('capacity_to') ? 'input-error' : '' }}"
                                       oninput="
                                                   if (this.value.length > 7) {
                                                       this.value = this.value.slice(0, 7);
                                                       this.style.borderColor = 'red';
                                                       setTimeout(() => this.style.borderColor = '', 1000);
                                                   } else {
                                                       this.style.borderColor = '';
                                                   }"
                                       placeholder="Вместимость от" autocomplete="off">
                            </div>
                            <div class="filter-group">
                                <h4> Цена на человека до:</h4>
                                <input name="per_person" type="number"
                                       value="{{ old('per_person') }}"
                                       class="form-control {{ $errors->has('per_person') ? 'input-error' : '' }}"
                                       oninput="
                                                   if (this.value.length > 6) {
                                                       this.value = this.value.slice(0, 6);
                                                       this.style.borderColor = 'red';
                                                       setTimeout(() => this.style.borderColor = '', 1000);
                                                   } else {
                                                       this.style.borderColor = '';
                                                   }"
                                       placeholder="Цена на человека до" autocomplete="off">
                            </div>
                            <!-- Районы: select multiple с прокруткой -->
                            <div class="filter-group">
                                <h4>Район</h4>
                                @if (empty($districtsOptions))
                                    <p>Нет доступных районов для выбранного города.</p>
                                @else
                                    <select name="district[]" multiple class="form-select"
                                            size="{{ min(count($districtsOptions), 8) }}">
                                        <option value="">Любой район</option>
                                        @foreach ($districtsOptions as $id => $name)
                                            <option value="{{ $id }}"
                                                    @if(in_array($id, (array)old('district', session('selected_filters.district', [])))) selected @endif>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Зажмите Ctrl/Cmd и кликайте, чтобы выбрать несколько
                                        районов.</small>
                                @endif
                            </div>

                            <!-- Метро: select (один выбор) -->
                            <div class="filter-group">
                                <h4>Метро</h4>
                                <label class="small">
                                    Список будет отсортирован по удалённости от станции метро: от ближнего ресторана к
                                    дальнему всего города.
                                </label>
                                @if (empty($metrosOptions))
                                    <p>Нет станций метро для выбранного города.</p>
                                @else
                                    <select name="near_metro_id" class="form-select">
                                        <option value="">Любой</option>
                                        @foreach ($metrosOptions as $metro)
                                            <option value="{{ $metro['id'] }}"
                                                    @if((int)old('near_metro_id', session('selected_filters.near_metro_id', '')) === (int)$metro['id']) selected @endif>
                                                {{ $metro['display'] ?? $metro['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>

                            <!-- ЗАГСы: select (один выбор) -->
                            <div class="filter-group">
                                <h4>ЗАГС</h4>
                                <span>
                                    Список будет отсортирован по удалённости от ЗАГСа: от ближнего ресторана к дальнему
                                    всего города.
                                </span>
                                @if (empty($zagsOptions))
                                    <p>Нет ЗАГСов для выбранного города.</p>
                                @else
                                    <select name="near_zags_id" class="form-select">
                                        <option value="">Любой</option>
                                        @foreach ($zagsOptions as $id => $name)
                                            <option value="{{ $id }}"
                                                    @if((int)old('near_zags_id', session('selected_filters.near_zags_id', '')) === (int)$id) selected @endif>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>

                            <!-- Пустая ячейка для баланса сетки -->
                            <div class="filter-group">
                                <div>
                                    <h4>Для мероприятий:</h4>

                                    <select name="for_events" id="for_events" class="form-select">
                                        <option value="">Любой тип мероприятия</option>
                                        <option value="Свадьба"
                                                @if(old('for_events') === 'Свадьба') selected @endif>
                                            Свадьба
                                        </option>
                                        <option value="День рождения"
                                                @if(old('for_events') === 'День рождения') selected @endif>
                                            День рождения
                                        </option>
                                        <option value="Корпоратив"
                                                @if(old('for_events') === 'Корпоратив') selected @endif>
                                            Корпоратив
                                        </option>
                                        <option value="Выпускной"
                                                @if(old('for_events') === 'Выпускной') selected @endif>
                                            Выпускной
                                        </option>
                                        <option value="Детский праздник"
                                                @if(old('for_events') === 'Детский праздник') selected @endif>
                                            Детский праздник
                                        </option>
                                        <option value="Фуршет"
                                                @if(old('for_events') === 'Фуршет') selected @endif>
                                            Фуршет
                                        </option>
                                        <option value="Мальчишник/Девичник"
                                                @if(old('for_events') === 'Мальчишник/Девичник') selected @endif>
                                            Мальчишник/Девичник
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <!-- Особенности: select multiple с прокруткой (как районы) -->
                            <div class="filter-group">
                                <h4>Особенности</h4>

                                @php
                                    $featuresList = [
                                        'У воды'                  => 'У воды',
                                        'За городом'              => 'За городом',
                                        'Подарки за бронирование' => 'Подарки за бронирование',
                                        'Можно свои б/а напитки'  => 'Можно свои б/а напитки',
                                        'Без пробкового сбора'    => 'Без "пробкового" сбора',
                                        'Выездная регистрация'    => 'Выездная регистрация',
                                        'Музыкальное оборудование' => 'Музыкальное оборудование',
                                    ];
                                @endphp

                                <select name="features[]" multiple class="form-select" size="{{ min(count($featuresList), 7) }}">
                                    <option value="">Любые особенности</option>
                                    @foreach ($featuresList as $value => $label)
                                        <option value="{{ $value }}"
                                                @if(in_array($value, (array)old('features', session('selected_filters.features', [])))) selected @endif>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Зажмите Ctrl/Cmd и кликайте, чтобы выбрать несколько особенностей.</small>
                            </div>

                        </div>


                        <br>
                        <button type="submit" class="btn-festive-gradient btn-festive-gradient-green mb-5">Найти залы
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
