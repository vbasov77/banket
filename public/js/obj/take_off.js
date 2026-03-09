document.addEventListener('DOMContentLoaded', function () {
    // Делегируем события на документ (работает для динамических элементов)
    document.addEventListener('click', function (e) {

        if (e.target.closest('.takeOff')) {
            handleTakeOff(e.target);
        }
        if (e.target.closest('.publish')) {
            handlePublish(e.target);
        }
    });
});

function handleTakeOff(button) {
    const id = button.getAttribute('data-id');
    const url = button.getAttribute('data-url');

    fetch(url, { /* ... */})
        .then(response => response.json())
        .then(data => {
            if (data.answer === 'ok') {
                // Получаем шаблон URL для публикации
                const publishRoute = button.getAttribute('data-route-publish');
                const newUrl = publishRoute.replace('__id__', id);

                // Обновляем атрибуты
                button.src = '../../icons/publish.svg';
                button.style.width = '15px';
                button.title = 'Опубликовать';
                button.classList.remove('takeOff');
                button.classList.add('publish');
                button.setAttribute('data-url', newUrl);

                // 2. Обновляем статус на "Не опубликовано"
                const statusDiv = document.querySelector(`.publication-status[data-id="${id}"]`);
                if (statusDiv) {
                    statusDiv.innerHTML = `<div data-id="${id}" class="d-inline-block">
                            <span class="badge bg-danger">Не опубликовано</span>                        
                        </div>`;
                }

                const opacity = document.querySelector(`.opacity[data-id="${id}"]`);
                if (opacity) {
                    opacity.style.opacity = '.7';
                }
            }
        });
}

function handlePublish(button) {
    const id = button.getAttribute('data-id');
    const url = button.getAttribute('data-url');

    fetch(url, { /* ... */})
        .then(response => response.json())
        .then(data => {
            if (data.answer === 'ok') {
                // Получаем шаблон URL для снятия с публикации
                const takeoffRoute = button.getAttribute('data-route-takeoff');
                const newUrl = takeoffRoute.replace('__id__', id);

                // Обновляем атрибуты
                button.src = '../../icons/take_off.svg';
                button.style.width = '18px';
                button.title = 'Снять с публикации';
                button.classList.remove('publish');
                button.classList.add('takeOff');
                button.setAttribute('data-url', newUrl);

                // 2. Обновляем статус на "Опубликовано"
                const statusDiv = document.querySelector(`.publication-status[data-id="${id}"]`);
                if (statusDiv) {
                    statusDiv.innerHTML = `<div data-id="${id}" class="d-inline-block">
                            <span class="badge bg-success">Опубликовано</span>
                        </div>`;
                }

                const opacity = document.querySelector(`.opacity[data-id="${id}"]`);
                if (opacity) {
                    opacity.style.opacity = '1';
                }
            }
        });
}
