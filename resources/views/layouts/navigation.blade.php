<style>
    a { text-decoration: none; }

    .modal {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.5); z-index: 1000;
    }
    .modal.hidden { display: none; }
    .modal-content {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
        background: white; padding: 20px; border-radius: 8px;
        max-width: 90%; max-height: 90vh; overflow-y: auto;
    }
    .city-selector { margin-left: 10px; font-size: 13px; }
    .close-btn {
        position: absolute; top: 15px; right: 15px; background: none;
        color: #ff5722; border: none; font-size: 28px; cursor: pointer; line-height: 1;
    }
    @media (max-width: 480px) {
        .close-btn { top: 8px; right: 8px; font-size: 20px; }
    }
</style>

<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- ЛЕВАЯ ЧАСТЬ: Лого + Город -->
            <div class="flex items-center">
                <a href="{{ route('front') }}" class="shrink-0 flex items-center">
                    <img src="{{ asset('icons/restaurant.svg') }}" alt="feast boom" class="h-8 w-auto">
                </a>
                <div class="city-selector ml-4">
                    <button type="button" id="openCityModal" class="city-btn text-sm font-medium text-gray-700 hover:text-red-600">
                        {{ session('user_city', 'Санкт-Петербург') }}
                    </button>
                </div>
            </div>

            <!-- ПРАВАЯ ЧАСТЬ: Кнопки (все в одном ряду) -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-6">
                @if (Auth::check())
                    <!-- Кнопка «Админ» (только для админов) -->
                    @if(Auth::user()?->isAdmin())
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-red-600 focus:outline-none transition ease-in-out duration-150">
                                    <div>Админ</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('show.admin_panel')">
                                    Админ панель
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    @endif

                    <!-- Кнопка профиля (всегда для авторизованных) -->
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.show')">Профиль</x-dropdown-link>
                            <x-dropdown-link :href="route('my.obj')">Панель управления</x-dropdown-link>
                            <x-dropdown-link :href="route('favorites.subjs')">Избранное</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <!-- Кнопка входа (для гостей) -->
                    <x-nav-link :href="route('login')" class="text-sm font-medium text-gray-500 hover:text-gray-900">
                        {{ __('Log In') }}
                    </x-nav-link>
                @endif
            </div>

            <!-- Гамбургер (мобильное меню) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Мобильное меню -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                @if(Auth::check())
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                @endif
            </div>
            <div class="mt-3 space-y-1">
                @if(Auth::check())
                    @if(Auth::user()?->isAdmin())
                        <x-responsive-nav-link :href="route('show.admin_panel')">Админ панель</x-responsive-nav-link>
                    @endif
                    <x-responsive-nav-link :href="route('favorites.subjs')">Избранное</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('my.obj')">Панель управления</x-responsive-nav-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                @else
                    <x-responsive-nav-link :href="route('login')">Вход</x-responsive-nav-link>
                @endif
            </div>
        </div>
    </div>
</nav>

<!-- Модальное окно выбора города -->
<div id="cityModal" class="modal hidden">
    <div class="modal-overlay" onclick="closeCityModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Выберите город</h3>
            <button type="button" class="close-btn" onclick="closeCityModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="cities-list" id="citiesList">
                <div class="loading">Загрузка городов...</div>
            </div>
        </div>
    </div>
</div>

<script>
    const cityModalData = {
        getCitiesUrl: '{{ route("get-cities") }}',
        setCityUrl: '{{ route("set-city") }}',
        csrfToken: document.querySelector('meta[name="csrf-token"]').content
    };

    function openCityModal() {
        const modal = document.getElementById('cityModal');
        if (modal) {
            modal.style.display = 'block';
            loadCities();
        }
    }

    function closeCityModal() {
        const modal = document.getElementById('cityModal');
        if (modal) modal.style.display = 'none';
    }

    function loadCities() {
        const citiesList = document.getElementById('citiesList');
        citiesList.innerHTML = '<div class="loading">Загрузка городов...</div>';
        fetch(cityModalData.getCitiesUrl)
            .then(r => r.json())
            .then(data => renderCities(data.cities));
    }

    function renderCities(cities) {
        const currentCity = '{{ session("city", "Санкт-Петербург") }}';
        const citiesList = document.getElementById('citiesList');
        citiesList.innerHTML = cities.map(city => `
            <div class="city-item ${city.name === currentCity ? 'selected' : ''}"
                data-city-id="${city.id}"
                data-city-name="${city.name}">
                ${city.name}
            </div>`
        ).join('');
        citiesList.addEventListener('click', handleCityClick);
    }

    function handleCityClick(event) {
        const cityItem = event.target.closest('.city-item');
        if (cityItem) {
            selectCity(cityItem.dataset.cityId, cityItem.dataset.cityName);
        }
    }

    function selectCity(cityId, cityName) {
        fetch(cityModalData.setCityUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': cityModalData.csrfToken
            },
            body: JSON.stringify({ city: cityName, city_id: cityId })
        })
            .then(() => {
                document.querySelector('.city-btn').textContent = cityName;
                closeCityModal();
                window.location.href = '/';
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const openButton = document.getElementById('openCityModal');
        if (openButton) openButton.addEventListener('click', openCityModal);

        document.addEventListener('click', e => {
            const modal = document.getElementById('cityModal');
            const openButton = document.getElementById('openCityModal');
            if (modal && !modal.contains(e.target) && openButton !== e.target) {
                closeCityModal();
            }
        });
    });
</script>
