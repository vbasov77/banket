document.querySelector('body').addEventListener('click', function(event) {
    event.preventDefault(); // отменяем стандартное действие (переход)
    // Проверяем, что клик был по элементу с id="publish"

    if (event.target.id !== 'publish') return;

    // Подтверждение действия
    if (!confirm('Вы хотите опубликовать?')) return;

    const $this = event.target;
    const id = $this.dataset.id;
    const data = { id: id };

    // HTML для прелоадера
    const preload = `
        <div class="preload">
            <img src="/images/loader/preloader.svg" width="35px">
        </div>
    `;

    // Создаем и настраиваем XMLHttpRequest
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/user/publish', true);
    xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
    xhr.responseType = 'json';

    // Обработчик успешного ответа
    xhr.onload = function() {

        if (xhr.status === 200 && xhr.response.answer === 'ok') {
            // Функция для удаления элементов по data-id
            const removeItems = (number) => {
                const elements = document.querySelectorAll(`div[data-id="${number}"]`);
                elements.forEach(e => e.remove());
            };

            removeItems(id);

            // Находим контейнер для прелоадера
            const preloadContainer = document.querySelector(`div[data-preload="${id}"]`);
            if (preloadContainer) {
                preloadContainer.innerHTML = preload;

                // Через 2 секунды скрываем прелоадер и обновляем контент
                setTimeout(() => {
                    preloadContainer.style.display = 'none';

                    // Новый HTML для кнопки "Снять с публикации"
                    const inHtml = `
                        <div data-id="${id}" class="d-inline-block">
                            <a id="takeOff" title="Снять с публикации" class="rem" data-id="${id}">
                                <img src="/icons/take_off.svg" style="width: 18px;">
                            </a>
                        </div>
                    `;

                    // Обновляем содержимое контейнера data-inn
                    const innContainer = document.querySelector(`div[data-inn="${id}"]`);
                    if (innContainer) {
                        innContainer.innerHTML = inHtml;
                    }

                    // Новый HTML для footer
                    const inHtmlFooter = `
                        <div data-id="${id}" class="d-inline-block">
                            <span class="badge bg-success">Опубликовано</span>
                        </div>
                    `;

                    // Обновляем содержимое footer-контейнера
                    const footerContainer = document.querySelector(`div[data-footer="${id}"]`);
                    if (footerContainer) {
                        footerContainer.innerHTML = inHtmlFooter;
                    }
                }, 2000);
            }
        }
    };

    // Обработчик ошибки
    xhr.onerror = function() {
        alert('Ошибка!!!');
    };

    // Отправляем запрос (данные в формате JSON)
    xhr.send(JSON.stringify(data));
});
