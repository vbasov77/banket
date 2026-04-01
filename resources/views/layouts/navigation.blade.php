<style>
    a {
        text-decoration: none;
    }
</style>

<style>
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
    }

    .modal.hidden {
        display: none;
    }

    .modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 20px;
        border-radius: 8px;
        max-width: 90%;
        max-height: 90vh;
        overflow-y: auto;
    }

    .city-selector {
        margin-left: 10px;
    }
</style>

<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('front') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"/>
                    </a>
                    <div class="city-selector">
                        <button type="button" id="openCityModal" class="city-btn">
                            {{ session('user_city', "Санкт-Петербург")}}
                        </button>
                    </div>
                </div>
                <!-- Кнопка для открытия модального окна -->

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('front')" :active="request()->routeIs('front')">
                        Banquet
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown or Login Link -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @if (Auth::check())
                    <!-- Show dropdown for authenticated users -->
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                         viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                              d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                              clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('my.obj')">
                                Панель управления
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('favorites.subjs')">
                                Избранное
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                                 onclick="event.preventDefault();
                            this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <!-- Show login link for guest users -->
                    <x-nav-link :href="route('login')">
                        {{ __('Log In') }}
                    </x-nav-link>
                @endif
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                              stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <!-- Responsive Settings Options -->
        @if (Auth::check())
            <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('favorites.subjs')">
                        Избранное
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('my.obj')">
                        Панель управления
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                                               onclick="event.preventDefault();
                    this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4 space-y-1">
                    <x-responsive-nav-link :href="route('login')">
                        {{ __('Log In') }}
                    </x-responsive-nav-link>
                </div>
            </div>
        @endif
    </div>
</nav>

<!-- Модальное окно -->
<!-- Модальное окно (вне навигации) -->
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
            modal.style.display = 'block'; // Гарантированно показываем
            loadCities();
        }
    }

    function closeCityModal() {
        const modal = document.getElementById('cityModal');
        if (modal) {
            modal.style.display = 'none'; // Гарантированно скрываем
        }
    }

    function loadCities() {
        const citiesList = document.getElementById('citiesList');
        citiesList.innerHTML = '<div class="loading">Загрузка городов...</div>';

        fetch(cityModalData.getCitiesUrl)
            .then(response => response.json())
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
            body: JSON.stringify({
                city: cityName,
                city_id: cityId
            })
        })
            .then(() => {
                document.querySelector('.city-btn').innerHTML = `${cityName}`;
                closeCityModal();
                window.location.href = '/';
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const openButton = document.getElementById('openCityModal');
        if (openButton) {
            openButton.addEventListener('click', openCityModal);
        }

        document.addEventListener('click', event => {
            const modal = document.getElementById('cityModal');
            const openButton = document.getElementById('openCityModal');
            if (modal && !modal.contains(event.target) && openButton !== event.target) {
                closeCityModal();
            }
        });
    });
</script>
