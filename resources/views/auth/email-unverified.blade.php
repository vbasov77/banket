<div class="container">
    <div class="alert alert-warning">
        <h4>Внимание!</h4>
        <p>Ваш email <strong>{{ $email }}</strong> еще не подтвержден. Пожалуйста, проверьте вашу почту.</p>
    </div>

    <form method="POST" action="{{ route('verification.resend') }}">
        @csrf
        <button type="submit" class="btn btn-primary">
            Отправить подтверждение еще раз
        </button>
    </form>
</div>