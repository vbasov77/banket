@extends('layouts.app')
@section('content')
    <link href="{{ asset('css/checkbox.css') }}" rel="stylesheet">
    <link href="{{ asset('css/tables.css') }}" rel="stylesheet">
    <style>
    </style>
    <section>
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5 justify-content-center">
                <div class="col-lg-10">
                    <form action="{{route('update.subj')}}" method="post">
                        @csrf
                        <h3>Новый объект</h3>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <input type="hidden" name="subj_id" value="{{$subj->id}}">
                        <label for="name_subj"><b>Название</b></label><br>
                        <input type="text" class="form-control @error('name_subj') is-invalid @enderror"
                               name="name_subj"
                               value="{{$subj->name_subj ?? old('name_subj')}}"><br>
                        <br>
                        <div>
                            <label for="address_subj"><b>Адрес:</b></label><br>
                            <input id="address_subj" name="address_subj" type="text"
                                   value="{{$subj->address_subj ?? old('address_subj')}}"
                                   class="form-control"
                                   placeholder="Адрес субъекта {{$subj->name_obj}}" autocomplete="off" required>
                        </div>
                        <br>
                        {{--                        Минимальная сумма, цена на человека--}}
                        <table class="styled-table">
                            <tr>
                                <td style="width: 49%">
                                    <div>
                                        <label for="minimum_cost"><b>Минимальная сумма:</b></label>
                                        <input style="width: 50%" name="minimum_cost" type="number"
                                               value="{{old('minimum_cost') ?? $subj->minimum_cost }}"
                                               class="form-control"
                                               placeholder="Минимальная сумма" autocomplete="off" required>
                                        <br>
                                    </div>
                                </td>
                                <td style="width: 49%">
                                    <div>
                                        <label for="per_person"><b>Цена на человека:</b></label>
                                        <input style="width: 50%" name="per_person" type="number"
                                               value="{{old('per_person') ?? $subj->per_person }}"
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
                                        <input style="width: 50%" name="capacity_from" type="number"
                                               value="{{old('capacity_from') ?? $subj->capacity_from }}"
                                               class="form-control"
                                               onkeypress="return (event.charCode >= 48 && event.charCode <= 57 && /^\d{0,3}$/.test(this.value));"
                                               placeholder="Вместимость от" autocomplete="off" required>
                                        <br>
                                    </div>
                                </td>
                                <td style="width: 49%">
                                    <div>
                                        <label for="capacity_to"><b>Вместимость (человек) до:</b></label>
                                        <input style="width: 50%" name="capacity_to" type="number"
                                               value="{{old('capacity_to') ?? $subj->capacity_to }}"
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
                                        <input style="width: 50%" name="furshet" type="number"
                                               value="{{old('furshet') ?? $subj->furshet }}"
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
                                <td style="width: 49%">

                                </td>
                            </tr>
                        </table>


                        <br>
                        <div>
                            <label for="text_subj"><b>Описание:</b></label><br>
                            <textarea class="form-control  @error('text_subj') is-invalid @enderror"
                                      placeholder="Введите текст..." name="text_subj"
                                      id="text_subj"
                                      rows="5"
                                      cols="85"> {{$subj->text_subj ?? old('text_subj')}}</textarea><br>
                        </div>
                        <a href="{{ route('edit.img_subj', ['id' => $subj->id]) }}"
                           class="btn-festive-gradient btn-festive-gradient-green">
                            @if($images)
                                Редактировать альбом
                            @else
                                Создайте альбом
                            @endif
                        </a>

                        <br>
                        <br>
                        <input style="margin-bottom: 50px" class="btn-festive-gradient btn-festive-gradient-blue"
                               type="submit"
                               value="Сохранить">
                        <input id="office" style="margin-bottom: 50px; box-shadow: none;" class="btn-festive-gradient btn-festive-gradient-white"
                               type="submit"
                               value="Перейти в панель">
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>

        let checkboxFeatures = document.getElementsByClassName('features');
        var checkboxForEvents = document.getElementsByClassName('for_events');
        let checkboxSiteType = document.getElementsByClassName('site_type');

        document.getElementById('office').addEventListener('click', function(e) {
            e.preventDefault()
            window.location.href = '{{route('my.obj')}}';
        });


        if (@json(old('site_type'))) {

            const oldSiteTypeArray = @json(old('site_type'));
            for (var i = 0; i < checkboxSiteType.length; i++) {
                if (oldSiteTypeArray.includes(checkboxSiteType[i].value)) {
                    checkboxSiteType[i].checked = true;
                }
            }

            const oldFeatures = @json(old('features'));
            for (var i = 0; i < checkboxFeatures.length; i++) {
                if (oldFeatures.includes(checkboxFeatures[i].value)) {
                    checkboxFeatures[i].checked = true;
                }
            }
        } else {
            const siteTypeArray = @json($subj->site_type);
            for (var i = 0; i < checkboxSiteType.length; i++) {
                if (siteTypeArray.includes(checkboxSiteType[i].value)) {
                    checkboxSiteType[i].checked = true;
                }
            }

            const featuresArray = @json($subj->features);
            for (var i = 0; i < checkboxFeatures.length; i++) {
                if (featuresArray.includes(checkboxFeatures[i].value)) {
                    checkboxFeatures[i].checked = true;
                }
            }


        }
    </script>

@endsection
