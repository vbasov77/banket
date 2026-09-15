@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <h3>Тест DaData: Поиск адреса и координат</h3>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group position-relative">
                    <label for="address-input">Начните вводить адрес:</label>
                    <input type="text" id="address-input" class="form-control" placeholder="Например: Санкт-Петербург, Большая П" autocomplete="off">
                    <ul id="suggestions-list" class="list-group position-absolute w-100" style="z-index: 1000; display: none;"></ul>
                </div>
                <button type="button" id="get-data-btn" class="btn btn-primary mt-2" disabled>Получить данные</button>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5>Данные выбранного адреса:</h5>
                        <p><strong>Город:</strong> <span id="data-city"></span></p>
                        <p><strong>Район:</strong> <span id="data-area"></span></p>
                        <p><strong>Улица:</strong> <span id="data-street"></span></p>
                        <p><strong>Дом:</strong> <span id="data-house"></span></p>
                        <p><strong>Широта:</strong> <span id="data-lat"></span></p>
                        <p><strong>Долгота:</strong> <span id="data-lon"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let dadataAbortController = null;
        let selectedDadata = null;

        const addressInput = document.getElementById('address-input');
        const suggestionsList = document.getElementById('suggestions-list');
        const getDataBtn = document.getElementById('get-data-btn');

        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        addressInput.addEventListener('input', debounce(async function() {
            const query = addressInput.value;
            if (query.length < 3) {
                suggestionsList.style.display = 'none';
                return;
            }

            if (dadataAbortController) dadataAbortController.abort();
            dadataAbortController = new AbortController();

            try {
                const response = await fetch(`/test/dadata/suggest?q=${encodeURIComponent(query)}`, {
                    signal: dadataAbortController.signal
                });

                if (!response.ok) return;
                const data = await response.json();

                if (data.error || data.length === 0) {
                    suggestionsList.innerHTML = '<li class="list-group-item text-muted">Ничего не найдено</li>';
                    suggestionsList.style.display = 'block';
                    return;
                }

                suggestionsList.innerHTML = '';
                data.forEach(item => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item list-group-item-action';
                    li.style.cursor = 'pointer';
                    li.textContent = item.value;

                    li.addEventListener('click', function() {
                        addressInput.value = item.value;
                        selectedDadata = item;
                        getDataBtn.disabled = false;
                        suggestionsList.style.display = 'none';
                    });
                    suggestionsList.appendChild(li);
                });
                suggestionsList.style.display = 'block';

            } catch (error) {
                if (error.name === 'AbortError') return;
            }
        }, 400));

        getDataBtn.addEventListener('click', function() {
            if (!selectedDadata) return;
            document.getElementById('data-city').textContent = selectedDadata.city || '—';
            document.getElementById('data-area').textContent = selectedDadata.area || '—';
            document.getElementById('data-street').textContent = selectedDadata.street || '—';
            document.getElementById('data-house').textContent = selectedDadata.house || '—';
            document.getElementById('data-lat').textContent = selectedDadata.lat || '—';
            document.getElementById('data-lon').textContent = selectedDadata.lon || '—';
        });

        document.addEventListener('click', function(e) {
            if (!addressInput.contains(e.target) && !suggestionsList.contains(e.target)) {
                suggestionsList.style.display = 'none';
            }
        });
    </script>
@endsection
