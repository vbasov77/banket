@extends('layouts.app', ['title' => "Редактировать логотип"])
@section('content')
    <style>
        .preloader-img {
            display: none;
        }
    </style>
    <link href="{{asset('css/croppie/croppie.css')}}" rel="stylesheet">
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h4>Ваш логотип</h4>
            </div>
            <div class="card-body">
                <form id="crop-form" action="{{ route('img_obj.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="photo-id" name="id" value="{{$img->id}}">
                    <div class="row">
                        <div class="col-sm-12 col-md-12 col-lg-6 text-center">
                            <div id="upload-logo"></div>
                        </div>
                        <div class="col-sm-12 col-md-12 col-lg-6" style="padding-top:30px;">
                            <strong>Выберите изображение:</strong>
                            <br/>
                            <input type="file" id="upload" name="img" class="form-control mb-3" accept="image/*">
                            <div style="display: inline-block">
                                <div style="display: inline-block; vertical-align: middle">
                                    <button type="button"
                                            class="btn-festive-gradient btn-festive-gradient-green upload-result">
                                        Сохранить
                                    </button>&ensp;
                                </div>
                                <div style="display: inline-block; vertical-align: middle">
                                    <img id="clear" src="{{ asset('images/loader/preloader.svg') }}"
                                         width="35px" height="auto" alt="" class="preloader-img"/>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{asset('js/jquery/3.6.0/jquery.min.js')}}"></script>
    <script src="{{asset('js/croppie/croppie.js')}}"></script>
    <script src="{{asset('js/croppie/main_edit.js')}}" defer></script>

@endsection