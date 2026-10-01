@extends('layouts.app')
@section('content')
    <link href="{{ asset('css/checkbox.css') }}" rel="stylesheet">
    <link href="{{ asset('css/tables.css') }}" rel="stylesheet">
    <style>
    </style>
    <section>
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5 justify-content-center">
                <div class="col-lg-10 mt-5">
                    <h3>Редактировать субъект</h3>
                    <span>
                        Субъект — это структурное подразделение вашей компании: например, банкетный зал, кафе или ресторан.
                    </span>
                    <br>
                    <br>
                    <form action="{{route('update.subj')}}" method="post">
                        @csrf
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
                        <input type="text" maxlength="25" class="form-control @error('name_subj') is-invalid @enderror"
                               name="name_subj"
                               value="{{$subj->name_subj ?? old('name_subj')}}"><br>
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
                                               placeholder="Минимальная сумма" autocomplete="off">
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
                                        <label for="capacity_to"><b>Вместимость (человек) до:</b></label>
                                        <input style="width: 50%" name="capacity_to" type="number"
                                               value="{{old('capacity_to') ?? $subj->capacity_to }}"
                                               class="form-control"
                                               onkeypress="return (event.charCode >= 48 && event.charCode <= 57 && /^\d{0,3}$/.test(this.value));"
                                               placeholder="Вместимость до" autocomplete="off" required>
                                    </div>
                                </td>
                                <td style="width: 49%">
                                    <div>
                                        <label for="furshet"><b>Вместимость на фуршет(человек) до:</b></label>
                                        <input style="width: 50%" name="furshet" type="number"
                                               value="{{old('furshet') ?? $subj->furshet }}"
                                               class="form-control"
                                               onkeypress="return (event.charCode >= 48 && event.charCode <= 57 && /^\d{0,3}$/.test(this.value));"
                                               placeholder="Вместимость на фуршет до" autocomplete="off">
                                        <br>
                                    </div>
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
                                                   value="Гостиница/Отель">
                                            <span class="checkmark"></span>
                                            Гостиница/Отель
                                        </label>
                                        <label class="checkbox-container">
                                            <input name="site_type[]" class="site_type" type="checkbox"
                                                   value="Загородный дом">
                                            <span class="checkmark"></span>
                                            Загородный дом
                                        </label>
                                        <label class="checkbox-container">
                                            <input name="site_type[]" class="site_type" type="checkbox"
                                                   value="Шатёр">
                                            <span class="checkmark"></span>
                                            Шатёр
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

                                </td>
                                <td style="width: 49%">
                                    <div>
                                        <label for="loud_music_until"><b>Громкая музыка разрешена до:</b></label>
                                        <select name="loud_music_until" id="loud_music_until" class="form-control">
                                            <option value="">— не указано —</option>
                                            <option value="22:00" {{ old('loud_music_until', $subj->loud_music_until ?? '') === '22:00' ? 'selected' : '' }}>22:00</option>
                                            <option value="23:00" {{ old('loud_music_until', $subj->loud_music_until ?? '') === '23:00' ? 'selected' : '' }}>23:00</option>
                                            <option value="00:00" {{ old('loud_music_until', $subj->loud_music_until ?? '') === '00:00' ? 'selected' : '' }}>00:00</option>
                                            <option value="01:00" {{ old('loud_music_until', $subj->loud_music_until ?? '') === '01:00' ? 'selected' : '' }}>01:00</option>
                                            <option value="morning" {{ old('loud_music_until', $subj->loud_music_until ?? '') === 'morning' ? 'selected' : '' }}>до утра</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <br>

                        <div>
                            <label for="features"><b>Особенности:</b></label><br>
                            <textarea class="form-control" name="features" id="features"
                                      rows="5" cols="85"
                                      placeholder="Введите текст...">{{ old('features', $subj->features ?? '') }}</textarea>
                        </div>
                        <br>
                        <br>
                        <a href="{{ route('edit.img_subj', ['id' => $subj->id]) }}"
                           class="btn-festive-gradient btn-festive-gradient-green">
                            Редактировать альбом
                        </a>
                        <br>
                        <br>
                        <input style="margin-bottom: 50px" class="btn-festive-gradient btn-festive-gradient-blue"
                               type="submit"
                               value="Сохранить">
                        <input id="office" style="margin-bottom: 50px; box-shadow: none;"
                               class="btn-festive-gradient btn-festive-gradient-white"
                               type="submit"
                               value="Перейти в панель">
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        let checkboxSiteType = document.getElementsByClassName('site_type');

        document.getElementById('office').addEventListener('click', function (e) {
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

        } else {
            const siteTypeArray = @json($subj->site_type);
            for (var i = 0; i < checkboxSiteType.length; i++) {
                if (siteTypeArray.includes(checkboxSiteType[i].value)) {
                    checkboxSiteType[i].checked = true;
                }
            }
        }
    </script>

@endsection
