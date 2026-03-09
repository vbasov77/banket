@extends('layouts.app')
@section('content')
    <form id="crop-form" action="{{ route('img_obj.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="photo-id" name="id" value="16">
        <div class="row">
            <div class="col-12 col-sm-8 col-md-6 text-center">
                <div id="upload-logo"></div>
            </div>
            <div class="col-12 col-sm-8 col-md-6" style="padding-top:30px;">
                <strong>Выберите изображение:</strong>
                <br/>
                <input type="file" id="upload" name="img" class="form-control mb-3" accept="image/*">
                <div style="display: inline-block">
                    <div style="display: inline-block; vertical-align: middle">
                        <button type="submit"
                                class="btn-festive-gradient btn-festive-gradient-green upload-result">
                            Сохранить
                        </button>&ensp;
                    </div>
                </div>

            </div>
        </div>
    </form>

@endsection