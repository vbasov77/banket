@extends('layouts.app')
@section('content')

    <style>
        .styled-table input.form-control {
            width: 50%;
        }
        @media (max-width: 480px) {
            .styled-table input.form-control {
                width: 100%;
            }
        }
    </style>

    <link href="{{ asset('css/checkbox.css') }}" rel="stylesheet">

    <link href="{{ asset('css/tables.css') }}" rel="stylesheet">
    <section style="margin-top: 50px">
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5">
                <div class="col-lg-12">
                    <form action="{{route('store.subj')}}" method="post">
                        @csrf
                        <h3>Добавьте новый субъект</h3>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <input type="hidden" name="obj_id" value="{{$objId}}">
                        <label for="name_subj"><b>Название</b></label><br>
                        <input type="text" class="form-control @error('name_subj') is-invalid @enderror"
                               name="name_subj"
                               value="{{old('name_subj')}}"><br>
                        <br>
                        {{--                        Минимальная сумма, цена на человека--}}
                        <table class="styled-table">
                            <tr>
                                <td style="width: 49%">
                                    <div>
                                        <label for="minimum_cost"><b>Минимальная сумма:</b></label>
                                        <input name="minimum_cost" type="number"
                                               value="{{old('minimum_cost') }}"
                                               class="form-control"
                                               placeholder="Минимальная сумма" autocomplete="off" required>
                                        <br>
                                    </div>
                                </td>
                                <td style="width: 49%">
                                    <div>
                                        <label for="per_person"><b>Цена на человека:</b></label>
                                        <input name="per_person" type="number"
                                               value="{{old('per_person') }}"
                                               class="form-control"
                                               placeholder="Цена на человека" autocomplete="off" required>
                                        <br>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        {{--                        Вместимость от, вместимость до--}}
                        <table class="styled-table">
                            <tr>
                                <td style="width: 49%">
                                    <div>
                                        <label for="capacity_from"><b>Вместимость (человек) от:</b></label>
                                        <input name="capacity_from" type="number"
                                               value="{{old('capacity_from') }}"
                                               class="form-control"
                                               onkeypress="return (event.charCode >= 48 && event.charCode <= 57 && /^\d{0,3}$/.test(this.value));"
                                               placeholder="Вместимость от" autocomplete="off" required>
                                        <br>
                                    </div>
                                </td>
                                <td style="width: 49%">
                                    <div>
                                        <label for="capacity_to"><b>Вместимость (человек) до:</b></label>
                                        <input name="capacity_to" type="number"
                                               value="{{old('capacity_to') }}"
                                               class="form-control"
                                               onkeypress="return (event.charCode >= 48 && event.charCode <= 57 && /^\d{0,3}$/.test(this.value));"
                                               placeholder="Вместимость до" autocomplete="off" required>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        {{--                        На фуршет--}}
                        <table class="styled-table">
                            <tr>
                                <td style="width: 49%">
                                    <div>
                                        <label for="furshet"><b>Вместимость на фуршет(человек) до:</b></label>
                                        <input name="furshet" type="number"
                                               value="{{old('furshet') }}"
                                               class="form-control"
                                               onkeypress="return (event.charCode >= 48 && event.charCode <= 57 && /^\d{0,3}$/.test(this.value));"
                                               placeholder="Вместимость на фуршет до" autocomplete="off" required>
                                        <br>
                                    </div>
                                </td>
                                <td style="width: 49%">

                                </td>
                            </tr>
                        </table>

                        {{--                        Тип площадки--}}
                        <table class="styled-table">
                            <tr>
                                <td style="width: 49%">
                                    <div class="checkbox-group">
                                        <label><b>Тип площадки:</b></label>
                                        <div class="checkbox-group">
                                            <label class="checkbox-container">
                                                <input name="site_type[]" class="site_type" type="checkbox"
                                                       value="База отдыха">
                                                <span class="checkmark"></span>
                                                База отдыха
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="site_type[]" class="site_type" type="checkbox"
                                                       value="Банкетный зал">
                                                <span class="checkmark"></span>
                                                Банкетный зал
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="site_type[]" class="site_type" type="checkbox"
                                                       value="Кафе">
                                                <span class="checkmark"></span>
                                                Кафе
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="site_type[]" class="site_type" type="checkbox"
                                                       value="Коттедж">
                                                <span class="checkmark"></span>
                                                Коттедж
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="site_type[]" class="site_type" type="checkbox"
                                                       value="Ресторан">
                                                <span class="checkmark"></span>
                                                Ресторан
                                            </label>
                                        </div>
                                    </div>
                                </td>
                                <td style="width: 49%">
                                    <div class="checkbox-group">
                                        <label class="checkbox-container">
                                            <input name="site_type[]" class="site_type" type="checkbox"
                                                   value="Клуб">
                                            <span class="checkmark"></span>
                                            Клуб
                                        </label>
                                        <label class="checkbox-container">
                                            <input name="site_type[]" class="site_type" type="checkbox"
                                                   value="Загородный дом">
                                            <span class="checkmark"></span>
                                            Загородный дом
                                        </label>
                                        <label class="checkbox-container">
                                            <input name="site_type[]" class="site_type" type="checkbox"
                                                   value="Шатер">
                                            <span class="checkmark"></span>
                                            Шатер
                                        </label>
                                        <label class="checkbox-container">
                                            <input name="site_type[]" class="site_type" type="checkbox"
                                                   value="Лофт">
                                            <span class="checkmark"></span>
                                            Лофт
                                        </label>
                                        <label class="checkbox-container">
                                            <input name="site_type[]" class="site_type" type="checkbox"
                                                   value="Терраса">
                                            <span class="checkmark"></span>
                                            Терраса
                                        </label>
                                        <label class="checkbox-container">
                                            <input name="site_type[]" class="site_type" type="checkbox"
                                                   value="Яхта">
                                            <span class="checkmark"></span>
                                            Яхта
                                        </label>
                                        <label class="checkbox-container">
                                            <input name="site_type[]" class="site_type" type="checkbox"
                                                   value="Теплоход">
                                            <span class="checkmark"></span>
                                            Теплоход
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        {{--                        Для мероприятий, Особенности--}}
                        <table class="styled-table">
                            <tr>
                                <td style="width: 49%">
                                    <div>
                                        <label><b>Для мероприятий:</b></label>
                                        <div class="checkbox-group">
                                            <label class="checkbox-container">
                                                <input name="for_events[]" class="for_events" type="checkbox"
                                                       value="Свадьба">
                                                <span class="checkmark"></span>
                                                Свадьба
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="for_events[]" class="for_events" type="checkbox"
                                                       value="Корпоратив">
                                                <span class="checkmark"></span>
                                                Корпоратив
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="for_events[]" class="for_events" type="checkbox"
                                                       value="День рождения">
                                                <span class="checkmark"></span>
                                                День рождения
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="for_events[]" class="for_events" type="checkbox"
                                                       value="Выпускной">
                                                <span class="checkmark"></span>
                                                Выпускной
                                            </label>
                                        </div>
                                    </div>
                                </td>
                                <td style="width: 49%">
                                    <div>
                                        <label for="features"><b>Особенности:</b></label>
                                        <div class="checkbox-group">
                                            <label class="checkbox-container">
                                                <input name="features[]" class="features" type="checkbox"
                                                       value="У воды">
                                                <span class="checkmark"></span>
                                                У воды
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="features[]" class="features" type="checkbox"
                                                       value="За городом">
                                                <span class="checkmark"></span>
                                                За городом
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="features[]" class="features" type="checkbox"
                                                       value="Подарки за бронирование">
                                                <span class="checkmark"></span>
                                                Подарки за бронирование
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="features[]" class="features" type="checkbox"
                                                       value="Можно свои б/а напитки">
                                                <span class="checkmark"></span>
                                                Можно свои б/а напитки
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="features[]" class="features" type="checkbox"
                                                       value="Без пробкового сбора">
                                                <span class="checkmark"></span>
                                                Без "пробкового" сбора
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="features[]" class="features" type="checkbox"
                                                       value="Выездная регистрация">
                                                <span class="checkmark"></span>
                                                Выездная регистрация
                                            </label>
                                            <label class="checkbox-container">
                                                <input name="features[]" class="features" type="checkbox"
                                                       value="Музыкальное оборудование">
                                                <span class="checkmark"></span>
                                                Музыкальное оборудование
                                            </label>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <br>
                        <div>
                            <label for="text_subj"><b>Описание:</b></label><br>
                            <textarea class="form-control  @error('text_subj') is-invalid @enderror"
                                      placeholder="Введите текст..." name="text_subj"
                                      id="text_subj"
                                      rows="5" cols="85"> {{old('text_subj')}}</textarea><br>
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
        if (@json(old('site_type'))) {
            var checkboxSiteType = document.getElementsByClassName('site_type');
            const oldSiteTypeArray = @json(old('site_type'));

            for (var i = 0; i < checkboxSiteType.length; i++) {
                if (oldSiteTypeArray.includes(checkboxSiteType[i].value)) {
                    checkboxSiteType[i].checked = true;
                }
            }

            var checkboxForEvents = document.getElementsByClassName('for_events');
            const oldSiteForEvents = @json(old('for_events'));

            for (var i = 0; i < checkboxForEvents.length; i++) {
                if (oldSiteForEvents.includes(checkboxForEvents[i].value)) {
                    checkboxForEvents[i].checked = true;
                }
            }

            var checkboxFeatures = document.getElementsByClassName('features');
            const oldFeatures = @json(old('features'));

            for (var i = 0; i < checkboxFeatures.length; i++) {
                if (oldFeatures.includes(checkboxFeatures[i].value)) {
                    checkboxFeatures[i].checked = true;
                }
            }
        }

        let alcohol = document.getElementById('alcohol');
        const alcoholBool = document.getElementById('alcoholBool');

        function checkCheckboxAlcohol() {
            if (alcoholBool.checked) {
                alcohol.value = "";
                alcohol.disabled = true;
            } else {
                alcohol.disabled = false;
            }
        }
    </script>
@endsection
