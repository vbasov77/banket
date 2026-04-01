<!-- resources/views/cities/index.blade.php -->
<div>
    <p>Текущий город: <strong>{{ $currentCity->name }}</strong></p>

    @guest
        <p>Для неавторизованных пользователей по умолчанию установлен Санкт‑Петербург</p>
    @endguest

    <form method="POST" action="{{ route('city.store') }}">
        @csrf
        <select name="city_id" required>
            <option value="">Выберите город</option>
            @foreach($cities as $city)
                <option value="{{ $city->id }}"
                        {{ $currentCity->id == $city->id ? 'selected' : '' }}>
                    {{ $city->name }}
                </option>
            @endforeach
        </select>
        <button type="submit">Сохранить город</button>
    </form>

    @if(session('success'))
        <div style="color: green;">{{ session('success') }}</div>
    @endif
</div>
