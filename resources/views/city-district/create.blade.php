@extends('layouts.app')
@section('content')
    <div class="container mt-5">
        <h1>Добавление города и районов</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('city-district.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="city_name" class="form-label">Название города</label>
                <input type="text" class="form-control" id="city_name" name="city_name"
                       value="{{ old('city_name') }}" required>
            </div>
            <div class="mb-3">
                <label for="districts" class="form-label">Районы (каждый с новой строки, с ";" в конце)</label>
                <textarea class="form-control" id="districts" name="districts" rows="6"
                          placeholder="Адмиралтейский;&#10;Василеостровский;&#10;Выборгский;&#10;" required>{{ old('districts') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Добавить</button>
            <br>
        </form>
    </div>

@endsection