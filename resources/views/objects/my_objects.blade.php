@extends('layouts.app', ['title' => "Мои объекты"])
@section('content')
    <script src="{{ asset('js/preloader/preloader.js') }}"></script>
    <link href="{{ asset('css/subj/card_subj.css') }}" rel="stylesheet">
    @include('blocks.nav_main_menu')

    <section class="section py-2">
        <div class="container px-4">
            <div class="row">
                <div class="col-12">
                    @if(!empty($error))
                        <div class="alert alert-danger">{{ $error }}</div>
                    @endif
                    @if(!empty($message))
                        <div class="alert alert-success">{{ $message }}</div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-12">
                            <h1 class="section-title display-5">Мои объекты</h1>
                            <h2 class="h4 text-muted">Выберите объект для работы</h2>
                        </div>
                    </div>

                    <div class="row">
                        @foreach($objects as $obj)
                            <div class="col-12 col-sm-8 col-md-6 col-lg-4 restaurants-grid">
                                <div class="restaurant-card opacity">
                                    <!-- Изображение -->
                                    <div class="position-relative">
                                        <div class="restaurant-image">
                                            @if($obj->firstImgSubj && $obj->firstImgSubj->small_img)
                                                <img src="{{ $obj->firstImgSubj->small_img }}"
                                                     class="card-img-top" alt="Фото объекта"
                                                     style="height: 200px; object-fit: cover;">
                                            @else
                                                <img src="{{ asset('images/no_image/no_image.jpg') }}"
                                                     class="card-img-top" alt="Нет фото"
                                                     style="height: 200px; object-fit: cover;">
                                            @endif

                                        </div>
                                    </div>

                                    <!-- Тело карточки -->
                                    <div class="restaurant-content">
                                        <h5 class="card-title fw-bold mb-3">{{ $obj->name_obj }}</h5>

                                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                            <div class="actions d-flex align-items-center gap-3">
                                                <a title="Открыть {{ $obj->name_obj }}"
                                                   class="text-decoration-none text-muted"
                                                   href="{{ route('obj.view', ['id' => $obj->id]) }}">
                                                    <img src="{{ asset('icons/eye.svg') }}"
                                                         style="width: 18px; height: 18px;"
                                                         alt="Открыть">
                                                </a>
                                                <a title="Удалить {{ $obj->name_obj }}"
                                                   class="text-decoration-none text-danger"
                                                   href="{{ route('obj.delete_confirm', ['id' => $obj->id]) }}">
                                                    🗑️
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Кнопка создания нового объекта -->
                    <div class="col-12">
                        <div class="row mt-5">
                            <div class="col-12 text-center">
                                <a class="btn-festive-gradient btn-festive-gradient-green"
                                   href="{{ route('create.obj') }}">
                                    <i class="bi bi-pencil-square me-2"></i>
                                    Добавить объект
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
