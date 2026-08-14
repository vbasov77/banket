@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-sm-12 col-md-10 col-lg-9">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0">Отправить сообщение 💌</h3>
                    </div>
                    <div class="card-body">

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('admin_mail.store') }}" method="POST">
                            @csrf

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">📧 Email (Кому)</label>
                                <input type="email"
                                       name="email"
                                       id="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}"
                                       required
                                       placeholder="example@mail.com">
                                @error('email')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <!-- Subject -->
                            <div class="mb-3">
                                <label for="subject" class="form-label">📢 Тема сообщения</label>
                                <input type="text"
                                       name="subject"
                                       id="subject"
                                       class="form-control @error('subject') is-invalid @enderror"
                                       value="{{ old('subject') }}"
                                       required
                                       maxlength="255"
                                       placeholder="Коротко о сути">
                                @error('subject')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <!-- Message -->
                            <div class="mb-4">
                                <label for="message" class="form-label">💬 Сообщение</label>
                                <textarea name="message"
                                          id="message"
                                          class="form-control @error('message') is-invalid @enderror"
                                          rows="5"
                                          required
                                          placeholder="Расскажите подробнее..."></textarea>
                                @error('message')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <!-- Submit -->
                            <button type="submit" class="btn-festive-gradient btn-festive-gradient-green">
                                📩 Отправить сообщение
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
