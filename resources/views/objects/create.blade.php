@extends('layouts.app')
@section('content')
    <section style="margin-top: 50px" class="about-section" id="about">
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5 justify-content-center">
                <div class="col-lg-8">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('store.obj') }}" method="post">
                        @csrf
                        <h3>Добавьте объект</h3>
                        <small>
                            (Объект - это обобщённое название вашей организации, в которой есть филиалы со своими
                            названиями.)</small>
                        <br>
                        <br>
                        <input type="hidden" name="user_id" value="{{ $user }}">

                        <label for="name_obj"><b>Название объекта</b></label><br>
                        <input type="text"
                               class="form-control @error('name_obj') is-invalid @enderror"
                               name="name_obj"
                               value="{{ old('name_obj') }}"
                               placeholder="Название"
                               required><br><br>

                        <label for="address_obj"><b>Адрес объекта</b></label><br>
                        <div>
                            <input type="text"
                                   name="address_obj"
                                   placeholder="Адрес"
                                   class="form-control @error('address_obj') is-invalid @enderror"
                                   value="{{ old('address_obj') }}"
                                   required><br>
                        </div>
                        <br>

                        <label for="phone_obj"><b>Телефон объекта</b></label><br>
                        <div>
                            <!-- Добавляем ID для более точного селектора -->
                            <input type="tel"
                                   id="phone-input"
                                   name="phone_obj"
                                   placeholder="+7 (___) ___ __ __"
                                   class="tel form-control @error('phone_obj') is-invalid @enderror"
                                   value="{{ old('phone_obj') }}"
                                   autocomplete="tel"
                                   inputmode="numeric"
                                   required><br>
                        </div>
                        <br><br>

                        <center>
                            <input style="margin-bottom: 50px;"
                                   class="btn-festive-gradient btn-festive-gradient-green"
                                   type="submit"
                                   value="Продолжить">
                        </center>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <script src="{{asset('js/masks/phone_mask.js')}}" defer></script>
@endsection
