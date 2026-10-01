@extends('layouts.app', ['title' => 'Редактирование акции — ' . $obj->name_obj])

@section('content')
    <div class="container mt-5" style="padding-bottom: 50px">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-12">

                <h2 class="section-title mb-4">
                    Редактирование акции — «{{ $obj->name_obj }}»
                </h2>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

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
                    <form id="updateForm" action="{{ route('action.update', ['obj' => $obj->id]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="actions" class="form-label fw-semibold">Текст акции</label>
                            <textarea name="actions" id="actions" class="form-control" rows="10"
                                      required>{{ old('actions', $action->actions) }}</textarea>
                        </div>
                    </form>

                    <div class="d-flex gap-2">
                        <button type="submit" form="updateForm"
                                class="btn-festive-gradient btn-festive-gradient-green">
                            Обновить
                        </button>
                        <a href="{{ route('show.obj', ['id' => $obj->id]) }}"
                           class="btn-festive-gradient btn-festive-gradient-white">
                            Отмена
                        </a>
                        <form action="{{ route('action.destroy', ['obj' => $obj->id]) }}"
                              method="POST"
                              onsubmit="return confirm('Удалить акцию?')"
                              class="ms-auto">
                            @csrf
                            @method('DELETE')
                            <button class="btn-festive-gradient btn-festive-gradient-red">
                                <i class="bi bi-trash"></i> Удалить
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
