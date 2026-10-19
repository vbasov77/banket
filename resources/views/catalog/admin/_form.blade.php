@php
    $action = isset($performer)
        ? route('admin.catalog.update', $performer)
        : route('admin.catalog.store');
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @isset($performer) @method('PUT') @endisset

    <label>
        Категория
        <select name="category_id" required>
            <option value="">— выберите —</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}"
                        @selected(old('category_id', $performer->category_id ?? null) == $cat->id)>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
        @error('category_id') <span class="error">{{ $message }}</span> @enderror
    </label>

    <label>
        Имя / название
        <input type="text" name="name" value="{{ old('name', $performer->name ?? '') }}" required>
        @error('name') <span class="error">{{ $message }}</span> @enderror
    </label>

    <label>
        Город
        <input type="text" name="city" value="{{ old('city', $performer->city ?? '') }}" required>
        @error('city') <span class="error">{{ $message }}</span> @enderror
    </label>

    <label>
        Описание
        <textarea name="description" rows="6">{{ old('description', $performer->description ?? '') }}</textarea>
        @error('description') <span class="error">{{ $message }}</span> @enderror
    </label>

    <label>
        Телефон
        <input type="text" name="phone" value="{{ old('phone', $performer->phone ?? '') }}">
        @error('phone') <span class="error">{{ $message }}</span> @enderror
    </label>

    <label>
        Ссылка на соцстраницу
        <input type="url" name="social_url" value="{{ old('social_url', $performer->social_url ?? '') }}">
        @error('social_url') <span class="error">{{ $message }}</span> @enderror
    </label>

    <label>
        Ссылка на видео (ВК)
        <input type="url" name="video_url" value="{{ old('video_url', $performer->video_url ?? '') }}">
        @error('video_url') <span class="error">{{ $message }}</span> @enderror
    </label>

    <label>
        <input type="hidden" name="published" value="0">
        <input type="checkbox" name="published" value="1"
                @checked(old('published', $performer->published ?? false))>
        Опубликован
    </label>

    <button type="submit">Сохранить</button>
    <a href="{{ route('admin.catalog.index') }}">Отмена</a>
</form>
