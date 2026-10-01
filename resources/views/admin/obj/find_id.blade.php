@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h3>Поиск объекта по ID</h3>
                <form action="/admin/obj" method="get" class="d-flex gap-2">
                    @csrf
                    <input type="number" name="id" class="form-control" placeholder="ID объекта" value="{{ $lastId ?? '' }}" required>
                    <button type="submit" class="btn btn-primary">Найти</button>
                </form>
            </div>
        </div>
    </div>
@endsection
