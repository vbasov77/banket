<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Загрузка фото на imageban.ru</title>
</head>
<body>
<h1>Загрузка изображения на imageban.ru</h1>

@if(session('success'))
    <div style="color: green; margin-bottom: 15px;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="color: red; margin-bottom: 15px;">
        {{ session('error') }}
    </div>
@endif

<form action="{{ route('upload.image') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div>
        <label for="image">Выберите изображение:</label>
        <input type="file" name="image" id="image" accept="image/*" required>
    </div>
    <br>
    <button type="submit">Загрузить на imageban.ru</button>
</form>
</body>
</html>
