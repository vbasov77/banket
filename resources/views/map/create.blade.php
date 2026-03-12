@extends('layouts.app')
@section('content')
    @push('styles')
        <link href="{{ asset('subjs/css/search_address.css') }}" rel="stylesheet">
    @endpush
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <div id="map" style="height: 500px; width: 100%; border: 1px solid #ddd;"></div>
            </div>
            <div class="col-md-4">
                <h4>Добавить новую точку</h4>
                <form id="addPointForm">
                    @csrf
                    <div class="mb-2">
                        <div class="form-group">
                            <label for="city">Город:</label>
                            <input type="text" id="city" name="city" autocomplete="false" class="form-control"
                                   placeholder="Начните вводить город">
                            <div id="city-suggestions" class="suggestions-list"></div>
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="street">Улица:</label>
                            <input autocomplete="false" type="text" id="street" name="street" class="form-control"
                                   placeholder="Начните вводить улицу" disabled>
                            <div id="street-suggestions" class="suggestions-list"></div>
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="house-number">Номер дома:</label>
                            <input autocomplete="false" type="text" id="house-number" name="house_number"
                                   class="form-control"
                                   placeholder="Введите номер дома"
                                   disabled>
                        </div>
                        <br>
                    </div>

                    <button type="button" id="save-address" class="btn btn-outline-secondary btn-sm mb-2"
                            onclick="geocodeAddress()">Найти
                        на карте
                    </button>
                </form>

                <p class="text-muted mt-2">Кликните на карте для быстрого добавления точки</p>
            </div>
        </div>
    </div>
    <script>
        // Передаём данные из Blade в JavaScript
        window.csrfToken = "{{ csrf_token() }}";
        window.subjId = "{{ $subj->id }}";
        window.nameSubj = "{{ $subj->name_subj }}";

        // Статические URL маршрутов
        window.savePointUrl = "{{ route('map_subj.points.store') }}";
        window.myObjUrl = "{{ route('my.obj') }}";

    </script>
    <link rel="stylesheet" href="{{asset('map/leaflet/css/leaflet.css')}}"/>
    <script src="{{asset('map/leaflet/js/leaflet.js')}}" defer></script>
    <script src="{{asset('map/js/search_address_subj.js')}}" defer></script>
    <script src="{{asset('map/js/main_subj.js')}}" defer></script>
@endsection