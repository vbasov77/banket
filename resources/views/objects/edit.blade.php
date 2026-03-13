@extends('layouts.app')
@section('content')
    <section class="about-section text-center" id="about">
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5 justify-content-center">
                <div class="col-lg-8">
                    <form action="{{route('update.obj')}}" method="post">
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
                        <input type="hidden" name="user_id" value="{{$obj->user_id}}">
                        <input type="hidden" name="id" value="{{$obj->id}}">
                        <label for="name_obj"><b>Название</b></label><br>
                        <input type="text" class="form-control @error('name_obj') is-invalid @enderror" name="name_obj"
                               value="{{$obj->name_obj ?? old('name_obj')}}"
                               required><br>
                        <br>
                        <label for="phone_obj"><b>Телефон</b></label><br>
                        <div>
                            <input type="phone" name="phone_obj"
                                   class="tel form-control @error('phone_obj') is-invalid @enderror"
                                   value="{{$obj->phone_obj ??old('phone_obj')}}" required><br>
                        </div>
                        <br>
                        <br>
                        <input style="margin-bottom: 50px" class="btn-festive-gradient btn-festive-gradient-green" type="submit" value="Продолжить">
                        <br>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <script src="{{asset('js/masks/phone_mask.js')}}" defer></script>
@endsection
