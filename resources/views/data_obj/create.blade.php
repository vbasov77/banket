@extends('layouts.app', ['title' => 'Новый ресторан'])

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Новый ресторан</h1>
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

        <form action="{{ route('store.data_obj') }}" method="POST">
            @csrf
            @include('data_obj._form')
            <div class="text-end">
                <button type="submit" class="btn btn-primary btn-lg">
                    💾 Создать
                </button>
            </div>
        </form>
    </div>
@endsection
