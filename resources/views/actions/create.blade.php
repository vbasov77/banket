@extends('layouts.app', ['title' => 'Новая акция — ' . $obj->name_obj])

@section('content')
    <div class="container mt-5" style="padding-bottom: 50px">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-12">

                <h2 class="section-title mb-4">
                    Новая акция — «{{ $obj->name_obj }}»
                </h2>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="bg-light p-4 rounded-10 shadow-sm">
                    <form action="{{ route('action.store', ['obj' => $obj->id]) }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="actions" class="form-label fw-semibold">Текст акции</label>
                            <textarea name="actions" id="actions" class="form-control" rows="10"
                                      placeholder="Введите текст акции. Переносы строк сохраняются."
                                      required>{{ old('actions') }}</textarea>
                            <div class="form-text">Пустые строки будут автоматически удалены.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn-festive-gradient btn-festive-gradient-green">
                                Сохранить
                            </button>
                            <a href="{{ route('show.subj', ['id' => $obj->id]) }}"
                               class="btn btn-link text-decoration-none">
                                Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
