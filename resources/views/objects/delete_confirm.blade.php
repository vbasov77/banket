@extends('layouts.app', ['title' => "Удаление объекта"])
@section('content')
    <script src="{{ asset('js/preloader/preloader.js') }}"></script>
    <link href="{{ asset('css/subj/card_subj.css') }}" rel="stylesheet">
    @include('blocks.nav_main_menu')

    <section class="section py-2">
        <div class="container px-4">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="card border-danger mt-4">
                        <div class="card-header bg-danger text-white">
                            <h4 class="mb-0">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                Удаление объекта
                            </h4>
                        </div>
                        <div class="card-body">
                            <h5 class="fw-bold">{{ $obj->name_obj }}</h5>
                            <p class="text-muted mb-4">
                                Это действие <strong>необратимо</strong>. Будут удалены все субъекты,
                                изображения, особенности и детали, связанные с этим объектом.
                            </p>

                            <div class="alert alert-danger border-danger">
                                <p class="mb-2 fw-bold">Чтобы подтвердить удаление, скопируйте и вставьте следующий текст:</p>
                                <p class="mb-0">
                                    <code class="text-danger fs-5 fw-bold user-select-all"
                                          id="confirmText">{{ $confirmText }}</code>
                                </p>
                            </div>

                            <form method="POST" action="{{ route('obj.destroy', ['id' => $obj->id]) }}">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="confirm_id" value="{{ $obj->id }}">

                                <div class="mb-3">
                                    <label for="confirmInput" class="form-label fw-bold">
                                        Введите текст подтверждения
                                    </label>
                                    <input type="text"
                                           id="confirmInput"
                                           name="confirm_input"
                                           class="form-control @error('confirm_input') is-invalid @enderror"
                                           autocomplete="off"
                                           placeholder="{{ $confirmText }}"
                                           required>
                                    @error('confirm_input')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('my.obj') }}"
                                       class="btn btn-outline-secondary">
                                        Отмена
                                    </a>
                                    <button type="submit"
                                            class="btn btn-danger"
                                            id="deleteBtn"
                                            disabled>
                                        <i class="bi bi-trash me-1"></i>
                                        Удалить объект
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const confirmText = document.getElementById('confirmText').textContent.trim();
            const confirmInput = document.getElementById('confirmInput');
            const deleteBtn = document.getElementById('deleteBtn');

            confirmInput.addEventListener('input', function() {
                deleteBtn.disabled = this.value.trim() !== confirmText;
            });
        });
    </script>
@endsection
