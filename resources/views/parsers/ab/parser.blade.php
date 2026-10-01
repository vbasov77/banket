@extends('layouts.app', ['title' => 'Парсер ресторанов'])

@section('content')
    <div class="container">
        <h1 class="mb-4">Парсер ресторанов avtobanket.ru</h1>

        {{-- Форма парсинга --}}
        <div class="card p-4 shadow-sm">
            <form action="{{ route('parser.parse') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="url" class="form-label fw-bold">Ссылка на avtobanket.ru</label>
                        <input type="url" id="url" name="url" class="form-control"
                               placeholder="https://www.avtobanket.ru/restaurants/graf-zeppelin"
                               value="{{ $url ?? '' }}" required>
                        @error('url')
                        <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="url_gorko" class="form-label fw-bold">Ссылка на gorko.ru (необязательно)</label>
                        <input type="url" id="url_gorko" name="url_gorko" class="form-control"
                               placeholder="https://spb.gorko.ru/restaurant/..."
                               value="{{ $url_gorko ?? '' }}">
                        @error('url_gorko')
                        <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-2">Спарсить</button>
            </form>
        </div>

        @isset($result)
            <form id="parser-store-form" action="{{ route('parser.save') }}" method="POST">
                @csrf
                <input type="hidden" name="source_url" value="{{ $url }}">

                {{-- Основные данные --}}
                <div class="card mt-4 p-4 shadow-sm">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Основные данные</h5>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Название объекта</label>
                            <input type="text" name="name_obj" class="form-control"
                                   value="{{ $result['name_obj'] ?? '' }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Телефон</label>
                            <input type="text" name="phone_obj" class="form-control"
                                   value="{{ $result['phone_obj'] ?? '' }}"
                                   placeholder="+7 (___) ___-__-__" required>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Описание</label>
                            <textarea name="text_obj" class="form-control"
                                      rows="6">{{ $result['text_obj'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Условия банкета --}}
                <div class="card mt-4 p-4 shadow-sm">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Условия банкета</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Цена на человека</label>
                            <input type="text" name="per_person" class="form-control"
                                   value="{{ $result['banquet_menu'] ?? '' }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Проценты за обслуживание</label>
                            <input type="text" name="service_fee" class="form-control"
                                   value="{{ $result['service_fee'] ?? '' }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Свой алкоголь</label>
                            <input type="text" name="alcohol" class="form-control"
                                   value="{{ $result['alcohol'] ?? '' }}"
                                   placeholder="0, 1 или 2:500">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Можно принести с собой</label>
                            <input type="text" name="bring_with_you" class="form-control"
                                   value="{{ $result['bring_with_you'] ?? '' }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Предоплата</label>
                            <textarea name="prepayment" class="form-control"
                                      rows="2">{{ $result['prepayment'] ?? '' }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Примечание</label>
                            <textarea name="banquet_note" class="form-control"
                                      rows="2">{{ $result['banquet_note'] ?? '' }}</textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Акции, скидки и подарки</label>
                            <textarea name="actions" class="form-control"
                                      rows="6">{{ $result['actions'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Дополнительная информация --}}
                <div class="card mt-4 p-4 shadow-sm">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Дополнительная информация</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Текстильный пакет</label>
                            <textarea name="textile_package" class="form-control"
                                      rows="2">{{ $result['textile_package'] ?? '' }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Расцветки текстильного пакета</label>
                            <textarea name="textile_colors" class="form-control"
                                      rows="2">{{ $result['textile_colors'] ?? '' }}</textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Столы</label>
                            <input type="text" name="tables" class="form-control"
                                   value="{{ $result['tables'] ?? '' }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Громкая музыка</label>
                            <input type="text" name="loud_music" class="form-control"
                                   value="{{ $result['loud_music'] ?? '' }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Парковка</label>
                            <input type="text" name="parking" class="form-control"
                                   value="{{ $result['parking'] ?? '' }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Причал</label>
                            <input type="text" name="pier" class="form-control"
                                   value="{{ $result['pier'] ?? '' }}">
                        </div>
                    </div>
                </div>

                {{-- Оборудование --}}
                <div class="card mt-4 p-4 shadow-sm">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Оборудование</h5>
                    <div class="row">
                        @php
                            $equipSelects = [
                                'own_equipment' => 'Своё оборудование',
                                'projector_screen' => 'Проекторный экран',
                                'music_stage' => 'Музыкальная сцена',
                                'sound_equipment' => 'Звуковое оборудование',
                                'light_equipment' => 'Световое оборудование',
                                'dimming_system' => 'Система затемнения',
                            ];
                            $standardOpts = ['', 'в наличии', 'отсутствует', 'по запросу'];
                            $ownEquipOpts = ['', 'можно, бесплатно', 'можно, платно', 'нет'];

                            $checkboxes = [
                                'karaoke' => 'Караоке',
                                'wifi' => 'Wi-Fi',
                                'dance_floor' => 'Танцпол',
                                'air_conditioner' => 'Кондиционер',
                                'wardrobe' => 'Гардероб',
                                'dressing_rooms' => 'Гримёрки для артистов',
                            ];
                            $kidsCheckboxes = [
                                'kids_room' => 'Детская комната',
                                'kids_menu' => 'Детское меню',
                                'kids_corner' => 'Детский уголок',
                            ];

                            $equipArr = $result['equipment'] ?? [];
                            $kidsArr  = $result['kids'] ?? [];
                        @endphp

                        {{-- Селекты --}}
                        @foreach($equipSelects as $field => $label)
                            @php
                                $opts = $field === 'own_equipment' ? $ownEquipOpts : $standardOpts;
                                $cur = $result[$field] ?? '';
                                // поддержка нового формата: метка есть в массиве → «в наличии»
                                if (is_array($equipArr) && in_array($label, $equipArr)) {
                                    $cur = 'в наличии';
                                }
                            @endphp
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">{{ $label }}</label>
                                <select name="equipment_sel[{{ $label }}]" class="form-select">
                                    @foreach($opts as $opt)
                                        <option value="{{ $opt }}" @if($cur === $opt) selected @endif>
                                            {{ $opt === '' ? '— не указано —' : $opt }}
                                        </option>
                                    @endforeach
                                    @if($cur && !in_array($cur, $opts))
                                        <option value="{{ $cur }}" selected>{{ $cur }}</option>
                                    @endif
                                </select>
                            </div>
                        @endforeach

                        {{-- Чекбоксы → equipment --}}
                        @foreach($checkboxes as $field => $label)
                            @php
                                $checked = ($result[$field] ?? '') === 'в наличии'
                                    || (is_array($equipArr) && in_array($label, $equipArr));
                            @endphp
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input type="checkbox" name="equipment_chk[]" value="{{ $label }}"
                                           class="form-check-input" id="chk_{{ $field }}"
                                           @if($checked) checked @endif>
                                    <label for="chk_{{ $field }}" class="form-check-label">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach

                        {{-- Детские чекбоксы → kids --}}
                        @foreach($kidsCheckboxes as $field => $label)
                            @php
                                $checked = ($result[$field] ?? '') === 'в наличии'
                                    || (is_array($kidsArr) && in_array($label, $kidsArr));
                            @endphp
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input type="checkbox" name="kids_chk[]" value="{{ $label }}"
                                           class="form-check-input" id="chk_{{ $field }}"
                                           @if($checked) checked @endif>
                                    <label for="chk_{{ $field }}" class="form-check-label">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Особенности заведения --}}
                <div class="card mt-4 p-4 shadow-sm">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Особенности заведения</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Интерьер</label>
                            <input type="text" name="interior" class="form-control"
                                   value="{{ $result['interior'] ?? '' }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Месторасположение</label>
                            <textarea name="location" class="form-control"
                                      rows="2">{{ $result['location'] ?? '' }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Режим работы</label>
                            <input type="text" name="working_hours" class="form-control"
                                   value="{{ $result['working_hours'] ?? '' }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Сайт</label>
                            <input type="text" name="website" class="form-control"
                                   value="{{ $result['website'] ?? '' }}">
                        </div>
                    </div>
                </div>
                {{-- Данные с gorko.ru --}}
                @if(!empty($result['site_type']) || !empty($result['kitchen']) || !empty($result['gorko_features']) || !empty($result['service']) || !empty($result['for_events']) || !empty($result['payment_methods']))
                    <div class="card mt-4 p-4 shadow-sm border-primary">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">Данные с gorko.ru</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Тип площадки</label>
                                <input type="text" name="site_type" class="form-control"
                                       value="{{ $result['site_type'] ?? '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Кухня</label>
                                <input type="text" name="kitchen" class="form-control"
                                       value="{{ $result['kitchen'] ?? '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Подходит для мероприятий</label>
                                <textarea name="for_events" class="form-control"
                                          rows="2">{{ $result['for_events'] ?? '' }}</textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Сервисы за отдельную плату</label>
                                <textarea name="service" class="form-control"
                                          rows="2">{{ $result['service'] ?? '' }}</textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Способы оплаты</label>
                                <input type="text" name="payment_methods" class="form-control"
                                       value="{{ $result['payment_methods'] ?? '' }}">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Особенности (с gorko.ru)</label>
                                <textarea name="gorko_features" class="form-control"
                                          rows="3">{{ $result['gorko_features'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Дебаг gorko (только если есть) --}}
                @if(!empty($result['_gorko_debug']))
                    <div class="card mt-4 p-3 bg-dark text-light">
                        <details>
                            <summary class="fw-bold mb-2" style="cursor:pointer">Отладка gorko.ru</summary>
                            <pre class="small mt-2"
                                 style="white-space:pre-wrap">{{ implode("\n", $result['_gorko_debug']) }}</pre>
                        </details>
                    </div>
                @endif

                {{-- Залы --}}
                @if(!empty($result['_halls']))
                    <div class="card mt-4 p-4 shadow-sm">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">Залы заведения</h5>
                        @foreach($result['_halls'] as $i => $hall)
                            <div class="border rounded p-3 mb-4 bg-light">
                                <h6 class="fw-bold mb-3">Зал {{ $i + 1 }}</h6>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Название зала</label>
                                        <input type="text" name="halls[{{ $i }}][name]" class="form-control"
                                               value="{{ $hall['name'] ?? '' }}">
                                    </div>
                                </div>
                                @if(!empty($hall['photos']))
                                    <label class="form-label fw-bold">Ссылки на фото (до 10)</label>
                                    <div class="row">
                                        @foreach($hall['photos'] as $pi => $photoUrl)
                                            <div class="col-md-3 col-sm-4 col-6 mb-2">
                                                <img src="{{ $photoUrl }}" alt="Фото {{ $pi + 1 }}"
                                                     class="img-thumbnail mb-1"
                                                     style="height: 80px; object-fit: cover; width: 100%;">
                                                <input type="text" name="halls[{{ $i }}][photos][{{ $pi }}]"
                                                       class="form-control form-control-sm" value="{{ $photoUrl }}">
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="row mt-3">
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label fw-bold">Вместимость</label>
                                        <input type="text" name="halls[{{ $i }}][capacity]" class="form-control"
                                               value="{{ $hall['capacity'] ?? '' }}">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label fw-bold">Аренда</label>
                                        <textarea name="halls[{{ $i }}][rent]" class="form-control"
                                                  rows="2">{{ $hall['rent'] ?? '' }}</textarea>
                                    </div>
                                </div>
                                {{-- Gorko-поля — отдельная строка --}}
                                <div class="row mt-2">
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label fw-bold">Вместимость (gorko)</label>
                                        <input type="text" name="halls[{{ $i }}][capacity_to]" class="form-control"
                                               value="{{ $hall['capacity_to'] ?? '' }}">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label fw-bold">Фуршет (gorko)</label>
                                        <input type="number" name="halls[{{ $i }}][furshet]" class="form-control"
                                               value="{{ $hall['furshet'] ?? 0 }}">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label fw-bold">Мин. стоимость банкета (gorko)</label>
                                        <input type="number" name="halls[{{ $i }}][minimum_cost]" class="form-control"
                                               value="{{ $hall['minimum_cost'] ?? 0 }}">
                                    </div>

                                    <div class="col-md-3 mb-2">
                                        <label class="form-label fw-bold">Особенности зала (gorko)</label>
                                        <textarea name="halls[{{ $i }}][gorko_features]" class="form-control"
                                                  rows="3">{{ $hall['gorko_features'] ?? '' }}</textarea>
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-success btn-lg">💾 Сохранить в БД</button>
                </div>
            </form>
        @endisset
    </div>
    @push('scripts')
        <script src="{{ asset('js/preloader/preloader.js') }}"></script>
        <script src="{{ asset('js/preloader/add_obj_preloader.js') }}"></script>
    @endpush
@endsection
