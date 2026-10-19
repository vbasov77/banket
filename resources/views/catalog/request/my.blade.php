@extends('layouts.app', ['title' => "Мои категории"])

@section('content')
    <script src="{{ asset('js/preloader/preloader.js') }}"></script>
    <link href="{{ asset('css/subj/card_subj.css') }}" rel="stylesheet">

    <section class="section py-2">
        <div class="container px-4">
            <div class="row">
                <div class="col-12">
                    <div class="row mb-4">
                        <div class="col-12">
                            <h1 class="section-title display-5">Мои категории</h1>
                        </div>
                    </div>

                    <div class="row">
                        @if(count($objects) != 0)
                            <h2 class="h4 text-muted mb-3">Выберите категорию для работы</h2>
                            @foreach($objects as $obj)
                                <div class="col-12 col-sm-8 col-md-6 col-lg-4 restaurants-grid">
                                    <div class="restaurant-card opacity"
                                         style="@if(!$obj->published) opacity: .7; @endif"
                                         data-id="{{ $obj->id }}">

                                        <!-- Изображение -->
                                        <div class="position-relative">
                                            <div class="restaurant-image">
                                                @if($obj->firstPhoto && $obj->firstPhoto->small_img)
                                                    <img src="{{ $obj->firstPhoto->small_img }}"
                                                         class="card-img-top"
                                                         alt="Фото категории"
                                                         style="height: 200px; object-fit: cover;">
                                                @else
                                                    <img src="{{ asset('images/no_image/no_image.jpg') }}"
                                                         class="card-img-top"
                                                         alt="Нет фото"
                                                         style="height: 200px; object-fit: cover;">
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Тело карточки -->
                                        <div class="restaurant-content">
                                            <h5 class="card-title fw-bold mb-3">{{ $obj->name }}</h5>

                                            <div class="mt-auto">
                                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3 border-top">
                                                    <!-- Контейнер для иконок -->
                                                    <div class="actions d-flex align-items-center gap-3">
                                                        <!-- Открыть -->
                                                        <a title="Открыть {{ $obj->name }}"
                                                           class="text-decoration-none text-muted"
                                                           href="{{ route('catalog.show', [
                                                               'category' => $obj->category?->slug ?? 'performers',
                                                               'performer' => $obj->id
                                                           ]) }}">
                                                            <img src="{{ asset('icons/eye.svg') }}"
                                                                 style="width: 18px; height: 18px;"
                                                                 alt="Открыть">
                                                        </a>

                                                        <!-- Редактировать -->
                                                        <a title="Редактировать {{ $obj->name }}"
                                                           class="text-decoration-none text-primary fs-5 line-height-1"
                                                           href="{{ route('catalog.request.edit', ['performer' => $obj->id]) }}">
                                                            ✏️
                                                        </a>

                                                        <!-- Удалить -->
                                                        <a title="Удалить {{ $obj->name }}"
                                                           class="text-decoration-none text-danger"
                                                           href="{{ route('performer.category.delete_confirm', ['id' => $obj->id]) }}">
                                                            🗑️
                                                        </a>

                                                        <!-- Опубликовать / Снять с публикации -->
                                                        <img class="{{ $obj->published ? 'takeOff' : 'publish' }}"
                                                             data-id="{{ $obj->id }}"
                                                             data-url="{{ $obj->published
                                                                 ? route('catalog.request.take_off', ['id' => $obj->id])
                                                                 : route('catalog.request.published', ['id' => $obj->id]) }}"
                                                             src="{{ asset('icons/' . ($obj->published ? 'take_off.svg' : 'publish.svg')) }}"
                                                             style="width: {{ $obj->published ? '18' : '15' }}px; cursor: pointer;"
                                                             title="{{ $obj->published ? 'Снять с публикации' : 'Опубликовать' }}"
                                                             alt="{{ $obj->published ? 'Снять с публикации' : 'Опубликовать' }}">

                                                        <!-- Редактировать фотоальбом -->
                                                        <a title="Фотоальбом {{ $obj->name }}"
                                                           href="{{ route('catalog.request.photo.create', ['id' => $obj->id]) }}">
                                                            <img src="{{ asset('icons/photo_st.svg') }}"
                                                                 style="width: 18px; height: 18px;"
                                                                 alt="Фотоальбом">
                                                        </a>
                                                    </div>

                                                    <!-- Статус публикации -->
                                                    <div class="publication-status" data-id="{{ $obj->id }}">
                                                        @if(!$obj->published)
                                                            <span class="badge bg-danger">Не опубликовано</span>
                                                        @else
                                                            <span class="badge bg-success">Опубликовано</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
                        <a href="{{ route('catalog.request.create') }}"
                           class="btn-festive-gradient btn-festive-gradient-blue">
                            <i class="bi bi-plus-lg me-2"></i>Добавить категорию
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Подключаем JS для публикации/снятия (как в шаблоне субъектов) -->
    <script src="{{ asset('js/catalog/take_off.js') }}" defer></script>
@endsection
