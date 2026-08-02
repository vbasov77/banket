<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            @if($errors->has('name'))
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            @endif
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            @if($errors->has('email'))
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            @endif
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            @if($errors->has('password'))
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            @endif
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            @if($errors->has('password_confirmation'))
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            @endif
        </div>

        {{-- Капча с русским словом --}}
        <div class="mt-6">
            <x-input-label for="captcha" :value="__('Type the word below')" />

            <div class="flex items-center gap-3 mt-1">
                <span class="inline-block px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-bold rounded-lg shadow-sm tracking-wide">
                    {{ $word }}
                </span>
                <x-text-input
                        id="captcha"
                        class="block mt-1 w-full md:w-64"
                        type="text"
                        name="captcha"
                        :value="old('captcha')"
                        required
                        autocomplete="off"
                        placeholder="{{ __('Enter word') }}"
                />
            </div>

            @if($errors->has('captcha'))
                <x-input-error :messages="$errors->get('captcha')" class="mt-2" />
            @endif
        </div>
        {{-- Конец капчи --}}

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
