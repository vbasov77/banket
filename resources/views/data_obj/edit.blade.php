@extends('layouts.app', ['title' => 'Редактировать ресторан'])

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Редактировать: {{ $restaurant->name ?? '' }}</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.restaurants.halls', $restaurant->id) }}" class="btn btn-outline-info">
                    Залы
                </a>
                <a href="{{ route('admin.restaurants.index') }}" class="btn btn-outline-secondary">
                    ← Назад
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('restaurants.update', $restaurant->id) }}" method="POST">
            @csrf
            @method('PATCH')
            @include('data_obj._form', ['restaurant' => $restaurant])
            <div class="text-end">
                <button type="submit" class="btn btn-success btn-lg">
                    💾 Сохранить
                </button>
            </div>
        </form>
    </div>
@endsection
