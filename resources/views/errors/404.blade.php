
@extends('layouts.app')
@section('content')
    <div class="container text-center py-5">
        <h1>404</h1>
        <p>Страница не найдена.</p>
        <a href="{{ url('/') }}" class="btn btn-primary">На главную</a>
    </div>
@endsection
