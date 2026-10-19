@extends('layouts.app')

@section('title', 'Редактировать: ' . $performer->name)

@section('content')
    <h1>Редактировать: {{ $performer->name }}</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <p>
        <small>
            Создано: {{ $performer->created_at->format('d.m.Y H:i') }}
            @if ($performer->user_id) — от пользователя #{{ $performer->user_id }} @endif
        </small>
    </p>

    @include('catalog.admin._form', ['performer' => $performer])

    <hr>

    @if ($performer->photos->isNotEmpty())
        <h2>Фото ({{ $performer->photos->count() }})</h2>
        <div class="gallery">
            @foreach ($performer->photos as $photo)
                <img src="{{ $photo->small_img ?? $photo->big_img }}" alt="{{ $performer->name }}">
            @endforeach
        </div>
    @else
        <p>Фото пока не добавлены.</p>
    @endif

    <p>
        <a href="{{ route('catalog.show', [$performer->category, $performer]) }}">Открыть публичную карточку</a>
    </p>
@endsection
