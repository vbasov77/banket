<style>
    .favorite-btn {
        background: none;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
        border-radius: 50%; /* Круглая кнопка */
        transition: all 0.3s ease;
    }

    /* Эффект при наведении на кнопку */
    .favorite-btn:hover {
        background-color: rgba(231, 76, 60, 0.1);
        transform: scale(1.1); /* Лёгкое увеличение кнопки */
    }

    .favorite-icon {
        font-size: 40px; /* Уменьшили размер для баланса */
        color: #95a5a6; /* Серый цвет для нелюбимых */
        transition: all 0.3s ease;
        line-height: 1;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .favorite-icon.active {
        color: #e74c3c; /* Красный цвет для любимых */
        transform: scale(1.1); /* Увеличение иконки при активации */
    }

    /* Отдельный эффект наведения для иконки */
    .favorite-btn:hover .favorite-icon:not(.active) {
        color: #7f8c8d; /* Тёмно‑серый при наведении */
        transform: scale(1.05);
    }

    /* Для неавторизованных пользователей */
    .favorite-icon-login {
        font-size: 40px;
        color: #95a5a6;
        transition: color 0.3s ease;
    }

    .favorite-icon-login:hover {
        color: #7f8c8d;
    }

</style>
<div style="float: right !important;">
    @auth
        <button type="button" class="favorite-btn"
                data-restaurant-id="{{ $subj['subj_id'] }}"
                title="{{ ($subj['is_favorite'] ?? false) ? 'Удалить из избранного' : 'Добавить в избранное' }}">
            <span class="favorite-icon {{ ($subj['is_favorite'] ?? false) ? 'active' : '' }}">
                {{ ($subj['is_favorite'] ?? false) ? '❤️' : '♡' }}
            </span>
        </button>
    @else
        <x-nav-link :href="route('login')"
                    title="Авторизуйтесь, чтобы добавить в избранное">
            <span class="favorite-icon-login">♡</span>
        </x-nav-link>
    @endauth
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.favorite-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const icon = this.querySelector('.favorite-icon');
                const restaurantId = this.getAttribute('data-restaurant-id');
                const isFavorite = icon.classList.contains('active');

                // URL для запроса (должны быть определены в глобальном контексте)
                const url = isFavorite
                    ? window.favDestroy.replace(':id', restaurantId)
                    : window.favStore.replace(':id', restaurantId);
                const method = isFavorite ? 'DELETE' : 'POST';

                // Получаем CSRF‑токен
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: method === 'POST'
                        ? JSON.stringify({subj_id: restaurantId})
                        : undefined
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Обновляем состояние иконки
                        icon.classList.toggle('active');

                        // Обновляем текст иконки
                        const newText = isFavorite ? '♡' : '❤️';
                        icon.textContent = newText;

                        // Обновляем title кнопки
                        this.setAttribute(
                            'title',
                            isFavorite ? 'Добавить в избранное' : 'Удалить из избранного'
                        );

                        // Показываем сообщение пользователю
                        alert(data.message);
                    })
                    .catch(error => {
                        console.error('Ошибка:', error);
                        alert('Ошибка: ' + (error.message || 'Неизвестная ошибка'));

                        // В случае ошибки откатываем изменение состояния
                        icon.classList.toggle('active');
                        const currentText = icon.textContent;
                        icon.textContent = currentText === '❤️' ? '♡' : '❤️';
                    });
            });
        });
    });

</script>

