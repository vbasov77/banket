@extends('layouts.app')

@section('content')
    <style>
        .role-card {
            transition: all 0.2s ease;
            border: 1px solid transparent;
            padding: 1.25rem;
        }
        .role-card:hover {
            border-color: rgba(0,0,0,.1);
        }
        /* Цвет выбора: тёплый градиент вместо зелёного */
        input[name="role"]:checked + .role-card {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(78, 171, 222, 0.15);
            border-color: #43c2de;
            background: #ebf1fd;
        }

        .check-badge {
            opacity: 0;
            transition: opacity 0.2s;
        }
        input[name="role"]:checked + .role-card .check-badge {
            opacity: 1;
        }
    </style>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="text-center mb-5">
                    <h2 class="fw-bold">Выберите вашу роль</h2>
                    <p class="text-muted">От этого зависит, какие возможности будут доступны в системе «BanKet»</p>
                </div>

                <form action="{{ route('role.store') }}" method="POST">
                    @csrf

                    <div class="row g-4">
                        @foreach($roles as $role)
                            @php
                                $isBanquet = $role->name === 'soon_banquet';
                                $isRestaurateur = $role->name === 'restaurateur';
                            @endphp

                            <div class="col-12">
                                <!-- Радио сначала, потом карточка — так работает CSS-выбор -->
                                <input
                                        type="radio"
                                        name="role"
                                        value="{{ $role->name }}"
                                        id="role_{{ $role->name }}"
                                        class="form-check-input position-absolute opacity-0"
                                        required
                                >

                                <label for="role_{{ $role->name }}" class="role-card d-flex align-items-center gap-4 cursor-pointer h-100">
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1 fw-bold">
                                            @if($isBanquet)
                                                Скоро банкет
                                            @elseif($isRestaurateur)
                                                Ресторатор
                                            @else
                                                {{ $role->name }}
                                            @endif
                                        </h5>
                                        <p class="mb-0 text-small text-muted">
                                            @if($isBanquet)
                                                Поиск и бронирование банкетных залов
                                            @elseif($isRestaurateur)
                                                Управление площадками и заявками
                                            @else
                                                {{ $role->name }}
                                            @endif
                                        </p>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    @error('role')
                    <div class="text-danger mt-3 fw-normal small d-block text-center">{{ $message }}</div>
                    @enderror

                    <div class="mt-4 text-center">
                        <button type="submit" class="btn-festive-gradient btn-festive-gradient-green m-3">
                            Подтвердить выбор
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
