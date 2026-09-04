<!DOCTYPE html>
<html>
<body>
<h3>Здравствуйте, {{ $user->name }}!</h3>
<p>Вы зарегистрированы в системе.</p>
<p>Ваш логин: {{ $user->email }}</p>
<p>Ваш временный пароль: <strong>{{ $password }}</strong></p>
<p>Рекомендуем сразу сменить пароль в профиле.</p>
</body>
</html>
