<!DOCTYPE html>
<html>
<head>
    <title>Leaflet + Геолокация</title>
    <!-- Исправленные ссылки с HTTPS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 400px; }
    </style>
</head>
<body>
<button onclick="getLocation()">Определить моё местоположение</button>
<div id="map"></div>

<!-- Скрипт Leaflet загружается перед нашим кодом -->
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Инициализация карты после загрузки Leaflet
    const map = L.map('map').setView([0, 0], 2);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    function getLocation() {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const { latitude, longitude } = position.coords;
                    const userPos = [latitude, longitude];

                    map.setView(userPos, 15);
                    L.marker(userPos).addTo(map).bindPopup('Вы здесь!');
                },
                (error) => {
                    alert('Не удалось получить местоположение: ' + error.message);
                },
                { enableHighAccuracy: true }
            );
        } else {
            alert('Геолокация недоступна');
        }
    }
</script>
</body>
</html>
