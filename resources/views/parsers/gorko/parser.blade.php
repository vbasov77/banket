@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <h3>Парсер горько.ру — тест залов с фото</h3>

                <form action="{{ route('parser.gorko.parse') }}" method="post">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Ссылка на ресторан горько.ру</label>
                        <input type="url" name="url_gorko" class="form-control"
                               value="{{ old('url_gorko', $url_gorko ?? '') }}"
                               placeholder="https://spb.gorko.ru/рестораны/30065/"
                               required>
                        @error('url_gorko')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <button class="btn btn-primary" type="submit">Парсить</button>
                </form>

                @if(!empty($result))
                    <hr class="my-4">

                    <h4>Залы ({{ count($result['_halls'] ?? []) }})</h4>

                    @foreach($result['_halls'] ?? [] as $hall)
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5>{{ $hall['name'] }}</h5>
                                <p class="mb-1">
                                    Вместимость: {{ $hall['capacity'] }} чел.
                                    | Фуршет: {{ $hall['furshet'] }} чел.
                                    | Цена: {{ number_format($hall['per_person'], 0, ' ', ' ') }} ₽/чел
                                    | Мин. стоимость: {{ number_format($hall['minimum_cost'], 0, ' ', ' ') }} ₽
                                </p>

                                @if(!empty($hall['photos']))
                                    <p class="text-success mb-1">Фото: {{ count($hall['photos']) }}</p>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        @foreach($hall['photos'] as $photo)
                                            <img src="{{ $photo }}" style="width: 150px; height: 100px; object-fit: cover;" class="rounded">
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted mt-2">Фото не найдены</p>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    @if(!empty($result['_debug']))
                        <details class="mt-3">
                            <summary>Отладка __NEXT_DATA__</summary>
                            <pre style="max-height: 300px; overflow: auto;">{{ implode("\n", $result['_debug']) }}</pre>
                        </details>
                    @endif

                    <details class="mt-3">
                        <summary>Текст страницы (первые 2000 символов)</summary>
                        <pre style="max-height: 300px; overflow: auto;">{{ $result['_fulltext_snippet'] ?? '' }}</pre>
                    </details>
                @endif
            </div>
        </div>
    </div>
@endsection
