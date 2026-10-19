@extends('layouts.app')

@section('title', 'Каталог — модерация исполнителей')

@section('content')
    <h1>Исполнители</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" class="search-form">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Имя или город">
        <button type="submit">Найти</button>
    </form>

    <table class="table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Имя</th>
            <th>Категория</th>
            <th>Город</th>
            <th>Телефон</th>
            <th>Просмотры</th>
            <th>Статус</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @forelse ($performers as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>
                    {{ $item->name }}
                    @if ($item->user)
                        <br><small>от user #{{ $item->user_id }}</small>
                    @endif
                </td>
                <td>{{ $item->category->name }}</td>
                <td>{{ $item->city }}</td>
                <td>{{ $item->phone ?? '—' }}</td>
                <td>{{ $item->views }}</td>
                <td>
                    @if ($item->published)
                        <form action="{{ route('admin.catalog.reject', $item) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning">Снять с публикации</button>
                        </form>
                    @else
                        <form action="{{ route('admin.catalog.approve', $item) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success">Опубликовать</button>
                        </form>
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.catalog.edit', $item) }}">Редактировать</a>

                    <form action="{{ route('admin.catalog.destroy', $item) }}"
                          method="POST"
                          onsubmit="return confirm('Удалить {{ $item->name }}? Отменить будет нельзя.')"
                          style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Удалить</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8">Пока пусто.</td></tr>
        @endforelse
        </tbody>
    </table>

    {{ $performers->withQueryString()->links() }}
@endsection
