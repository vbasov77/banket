@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h3 class="mb-0 text-dark fw-bold">➕ Создать пользователя</h3>
                    </div>

                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                ✅ {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('admin.users.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label text-secondary fw-normal">Имя</label>
                                <input type="text"
                                       name="name"
                                       id="name"
                                       class="form-control form-control-lg"
                                       value="{{ old('name') }}"
                                       required
                                       placeholder="Введите имя пользователя">
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label text-secondary fw-normal">Email</label>
                                <input type="email"
                                       name="email"
                                       id="email"
                                       class="form-control form-control-lg"
                                       value="{{ old('email') }}"
                                       required
                                       placeholder="name@example.com">
                            </div>

                            <button type="submit" class="btn-festive-gradient btn-festive-gradient-green">
                                Создать пользователя
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
