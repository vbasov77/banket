document.addEventListener('DOMContentLoaded', function () {
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
    if (!confirm('Вы хотите снять с публикации?')) return;

    const id = button.getAttribute('data-id');
    const url = button.getAttribute('data-url');

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ id: id })
    })
        .then(response => {
            // ГЛАВНОЕ: сначала проверяем статус
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text(); // читаем как текст, а не JSON
        })
        .then(text => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Это не JSON! Это HTML:', text.substring(0, 200));
                alert('Сервер вернул ошибку (см. консоль). Проверьте логи Laravel.');
                return;
            }

            if (data.answer === 'ok') {
                updateUIForUnpublish(button, id);
            } else if (data.message) {
                alert(data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Произошла ошибка при запросе. Проверьте консоль и логи Laravel.');
        });
}

function handlePublish(button) {
    if (!confirm('Вы хотите опубликовать?')) return;

    const id = button.getAttribute('data-id');
    const url = button.getAttribute('data-url');

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ id: id })
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            console.log('Получен ответ:', text);

            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Это не JSON! Это HTML:', text.substring(0, 200));
                alert('Сервер вернул ошибку (см. консоль).');
                return;
            }

            if (data.answer === 'ok') {
                updateUIForPublish(button, id);
            } else if (data.message) {
                alert(data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Произошла ошибка при запросе.');
        });
}

function updateUIForUnpublish(button, id) {
    button.src = '../../icons/publish.svg';
    button.style.width = '15px';
    button.title = 'Опубликовать';
    button.classList.remove('takeOff');
    button.classList.add('publish');

    const statusDiv = document.querySelector(`.publication-status[data-id="${id}"]`);
    if (statusDiv) {
        statusDiv.innerHTML = `<span class="badge bg-danger">Не опубликовано</span>`;
    }

    const card = document.querySelector(`.opacity[data-id="${id}"]`);
    if (card) {
        card.style.opacity = '.7';
    }
}

function updateUIForPublish(button, id) {
    button.src = '../../icons/take_off.svg';
    button.style.width = '18px';
    button.title = 'Снять с публикации';
    button.classList.remove('publish');
    button.classList.add('takeOff');

    const statusDiv = document.querySelector(`.publication-status[data-id="${id}"]`);
    if (statusDiv) {
        statusDiv.innerHTML = `<span class="badge bg-success">Опубликовано</span>`;
    }

    const card = document.querySelector(`.opacity[data-id="${id}"]`);
    if (card) {
        card.style.opacity = '1';
    }
}
