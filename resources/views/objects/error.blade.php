@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 mt-5 mb-5">
                <div class="card border-danger shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">Ошибка</h4>
                    </div>
                    <div class="card-body">
                        <p class="lead text-danger">
                            Как вы здесь оказались? Похоже, вы пытаетесь добавить объект второй раз.
                            По правилам сайта вы можете иметь только один объект.
                        </p>

                        <div class="mt-4 d-flex gap-3 justify-content-center flex-wrap">
                            <a href="{{ route('my.obj') }}" class="btn-festive-gradient btn-festive-gradient-white">
                                Перейти в панель
                            </a>
                            <a href="{{ route('obj.edit', ['id' => $id]) }}" class="btn-festive-gradient btn-festive-gradient-green">
                                Редактировать объект
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
