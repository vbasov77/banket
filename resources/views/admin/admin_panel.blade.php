@extends('layouts.app')

@section('content')
    <style>

        .icon-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            font-size: 1.5rem;
        }
    </style>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-5 pt-5">
        <div class="mb-10">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Панель управления и тесты</h2>
            <p class="text-gray-500 mt-2">Выберите нужный раздел для работы</p>
        </div>

        <!-- Сетка карточек: на мобильных 1 в ряд, на планшетах 2, на десктопе 3 -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- Блок: Тесты -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm hover:border-indigo-200 dark:hover:border-indigo-700 card-hover">
                <div class="flex items-center mb-4">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Тесты и утилиты</h3>
                </div>
                <div class="space-y-3">
                    <a href="{{route('send.mail')}}" class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-800 transition-colors">
                        ✉️ Послать письмо
                    </a>
                    <a href="#" class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-800 transition-colors">
                        🖼 Загрузить фото
                    </a>
                    <a href="#" class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-800 transition-colors">
                        🏷 Добавить метку
                    </a>
                </div>
            </div>

            <!-- Блок: Пользователи -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm hover:border-green-200 dark:hover:border-green-700 card-hover">
                <div class="flex items-center mb-4">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Пользователи</h3>
                </div>
                <div class="space-y-3">
                    <a href="#" class="inline-flex items-center px-4 py-2 bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300 border border-green-100 dark:border-green-800 rounded-lg hover:bg-green-100 dark:hover:bg-green-800 transition-colors">
                        👥 Все пользователи
                    </a>
                    <a href="#" class="inline-flex items-center px-4 py-2 bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300 border border-green-100 dark:border-green-800 rounded-lg hover:bg-green-100 dark:hover:bg-green-800 transition-colors">
                        🔍 Искать пользователя
                    </a>
                </div>
            </div>

            <!-- Блок: Рестораны -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm hover:border-orange-200 dark:hover:border-orange-700 card-hover">
                <div class="flex items-center mb-4">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Рестораны</h3>
                </div>
                <div class="space-y-3">
                    <a href="#" class="inline-flex items-center px-4 py-2 bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300 border border-orange-100 dark:border-orange-800 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-800 transition-colors">
                        🍽 Поиск ресторана
                    </a>
                    <!-- Сюда можно добавить ещё кнопки, если нужно -->
                </div>
            </div>

        </div>
    </div>
@endsection
