@extends('layouts.app')

@section('title', 'Добавить исполнителя')

@section('content')
    <div class="container py-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Главная</a></li>
                <li class="breadcrumb-item active" aria-current="page">Заявка в каталог</li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-11 col-sm-12">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-1">Добавить исполнителя</h1>
                        <p class="text-muted mb-4">
                            Заполните заявку — после проверки карточка появится в каталоге.
                        </p>

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                Проверьте форму — есть ошибки.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Закрыть"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('catalog.request.store') }}">
                            @csrf

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="category_id" class="form-label">Категория <span
                                                class="text-danger">*</span></label>
                                    <select name="category_id" id="category_id"
                                            class="form-select @error('category_id') is-invalid @enderror" required>
                                        <option value="">— выберите —</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                @include('catalog.partials._city_field')

                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Телефон</label>
                                    <input type="tel"
                                           id="phone-input"
                                           name="phone"
                                           placeholder="+7 (___) ___ __ __"
                                           class="tel form-control @error('phone') is-invalid @enderror"
                                           value="{{ old('phone_obj') }}"
                                           autocomplete="tel"
                                           inputmode="numeric"
                                           required><br>
                                    @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="name" class="form-label">Имя или название <span
                                                class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                                           class="form-control @error('name') is-invalid @enderror"
                                           maxlength="255"
                                           placeholder="Например: Иван / Салон платьев «Милана»" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Для салонов и студий указывайте название, а не имя
                                        человека.
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="description" class="form-label">Описание</label>
                                    <textarea name="description" id="description" rows="6"
                                              class="form-control @error('description') is-invalid @enderror"
                                              placeholder="Что вы делаете, опыт, что входит в услугу, цены...">{{ old('description') }}</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>


                                <div class="col-12">
                                    <label class="form-label">Ссылки на видео (ВКонтакте)</label>

                                    <div id="video-fields">
                                        @php
                                            $videos = old('video_url', $performer->video_url ?? []);
                                            if (! is_array($videos)) { $videos = []; }
                                        @endphp

                                        @foreach ($videos as $url)
                                            <div class="input-group mb-2 video-row">
                                                <input type="url" name="video_url[]" value="{{ $url }}"
                                                       class="form-control @error('video_url.' . $loop->index) is-invalid @enderror"
                                                       maxlength="500" placeholder="https://vkvideo.ru/...">
                                                <button type="button" class="btn btn-outline-danger remove-video">🗑️</button>
                                                @error('video_url.' . $loop->index)
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endforeach
                                    </div>

                                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-video">
                                        <i class="bi bi-plus-lg"></i> Добавить видео
                                    </button>
                                    <div class="form-text">Видео покажем на карточке как портфолио. Можно добавить несколько.</div>
                                </div>

                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn-festive-gradient btn-festive-gradient-blue">Сохранить</button>
                                <a href="{{ route('catalog.request.my_category') }}" class="btn-festive-gradient btn-festive-gradient-white">Отмена</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('video-fields');

            document.getElementById('add-video').addEventListener('click', function () {
                const row = document.createElement('div');
                row.className = 'input-group mb-2 video-row';
                row.innerHTML = `
            <input type="url" name="video_url[]" class="form-control"
                   maxlength="500" placeholder="https://vkvideo.ru/...">
            <button type="button" class="btn btn-outline-danger remove-video">🗑️</button>`;
                container.appendChild(row);
            });

            container.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-video')) {
                    e.target.closest('.video-row').remove();
                }
            });
        });
    </script>
    <script src="{{asset('js/masks/phone_mask.js')}}" defer></script>
@endsection
