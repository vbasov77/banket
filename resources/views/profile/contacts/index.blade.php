@extends('layouts.app')

@section('title', 'Мои контакты')

@section('content')
    <div class="max-w-xl mx-auto p-6 space-y-6">

        <h1 class="text-2xl font-bold">Мои контакты</h1>

        @if (session('success'))
            <div class="p-3 rounded bg-green-100 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-3 rounded bg-red-100 text-red-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 col-sm-12">
                <form method="POST" action="{{ route('contacts.sync') }}" class="space-y-4">
                    @csrf

                    @foreach (\App\Models\UserContact::TYPES as $type)
                        @php
                            $current = $contacts->firstWhere('type', $type)?->value;
                        @endphp
                        <div>
                            <label class="block text-sm mb-1 capitalize">
                                {{ ['whatsapp' => 'WhatsApp', 'telegram' => 'Telegram', 'max' => 'MAX', 'vk' => 'ВКонтакте'][$type] }}
                            </label>
                            <input type="text"
                                   name="{{ $type }}"
                                   value="{{ old($type, $current) }}"
                                   placeholder="{{ $type === 'whatsapp' ? '+7 900 000-00-00' : ($type === 'telegram' ? '@username' : 'Ссылка или ник') }}"
                                   class="border rounded p-2 w-full">
                        </div>
                    @endforeach
                    <br>
                    <br>
                    <button class="btn-festive-gradient btn-festive-gradient-blue">Сохранить</button>
                    <button type="button" class="btn-festive-gradient btn-festive-gradient-white"
                            onclick="history.back()">
                        Назад
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
