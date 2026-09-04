<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body>
<h2>Подтвердите новый email</h2>
<p>Здравствуйте, {{ $user->name }}!</p>
<p>Вы изменили адрес почты на <b>{{ $user->email }}</b>.</p>
<p><a href="{{ $url }}">Подтвердить email</a></p>
<p>Ссылка действительна 60 минут.</p>
</body>
</html>
