<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        overflow-x: hidden; /* Убираем горизонтальный скролл из‑за навигации */
    }

    /* Стили для выдвигающейся навигации */
    .sidebar {
        position: fixed;
        top: 0;
        left: -250px; /* Изначально скрыта за левым краем */
        width: 250px;
        height: 100%;
        background: #333;
        transition: left 0.3s ease;
        z-index: 1000;
        line-height: 5px;
    }

    .sidebar.active {
        left: 0; /* Показывается при добавлении класса active */
    }

    .nav-list {
        list-style: none;
        padding-top: 60px;
        padding-bottom: 20px;
    }

    .nav-list li {
        padding: 15px 20px;
    }

    .nav-list a {
        color: white;
        text-decoration: none;
        font-size: 16px;
    }

    .nav-list a:hover {
        color: #4CAF50;
    }

    /* Основной контент */
    .content {
        min-height: 200vh; /* Для демонстрации скролла */
        padding: 20px;
    }

    .scroll-content {
        max-width: 800px;
        margin: 0 auto;
    }

    h1 {
        margin-bottom: 20px;
        font-size: clamp(1.5rem, 5vw, 2.5rem); /* Адаптивный размер заголовка */
    }

    p {
        line-height: 1.6;
        margin-bottom: 1em;
        font-size: clamp(0.9rem, 3vw, 1.1rem); /* Адаптивный размер текста */
    }

    /* Кнопка переключения навигации — фиксированная */
    .toggle-btn {
        position: fixed; /* Фиксированное позиционирование */
        top: 80px;
        right: 15px;
        background: #4CAF50;
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.3s;
        z-index: 1001; /* Выше навигации */
        touch-action: manipulation; /* Улучшение отклика на мобильных */
    }

    .toggle-btn:hover {
        transform: rotate(90deg);
    }

    /* Кнопка закрытия (крестик) внутри меню */
    .close-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        background: none;
        color: white;
        border: none;
        font-size: 28px;
        cursor: pointer;
        line-height: 1;
    }

    .close-btn:hover {
        color: #ff5722;
    }

    /* Адаптивные стили для мобильных устройств */
    @media (max-width: 768px) {
        /* Уменьшаем ширину навигации на мобильных */
        .sidebar {
            width: 220px;
            left: -220px;
        }

        .sidebar.active {
            left: 0;
        }

        /* Отступы и размеры для лучшего отображения на маленьких экранах */
        .nav-list {
            padding-top: 50px;
        }

        .nav-list li {
            padding: 12px 15px;
        }

        .nav-list a {
            font-size: 14px;
        }

        /* Позиционирование кнопки переключения на мобильных */
        .toggle-btn {
            top: 60px;
            right: 10px;
            width: 36px;
            height: 36px;
            font-size: 16px;
        }

        /* Позиция крестика на мобильных */
        .close-btn {
            top: 10px;
            right: 10px;
            font-size: 24px;
        }

        /* Отступы контента для мобильных */
        .content {
            padding: 15px;
        }

        .scroll-content {
            max-width: 100%;
            padding: 0 10px;
        }
    }

    /* Для очень маленьких экранов (смартфоны) */
    @media (max-width: 480px) {
        .sidebar {
            width: 85%; /* На очень маленьких экранах меню занимает всю ширину */
            left: -100%;
        }

        .logo-panel {
            display: none;
        }

        .sidebar.active {
            left: 0;
        }

        .nav-list {
            padding-top: 40px;
        }

        .nav-list li {
            padding: 10px 15px;
        }

        .nav-list a {
            font-size: 13px;
        }

        /* Кнопка переключения на очень маленьких экранах */
        .toggle-btn {
            top: 60px;
            right: 8px;
            width: 32px;
            height: 32px;
            font-size: 14px;
        }

        /* Крестик на очень маленьких экранах */
        .close-btn {
            top: 8px;
            right: 8px;
            font-size: 20px;
        }

        /* Дополнительные отступы для контента на маленьких экранах */
        .content {
            padding: 10px;
        }
    }

    .navi ul {
        padding-left: 0rem;
    }

    .logo-container {
        padding: 20px 15px;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .logo-link {
        display: inline-block;
        transition: transform 0.3s ease;
    }

    .logo-link:hover {
        transform: scale(1.05);
    }

    .circular-logo {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        border: 3px solid rgba(255, 255, 255, 0.3);
    }

    @media (max-width: 768px) {
        .circular-logo {
            width: 60px;
            height: 60px;
        }
    }

</style>
<!-- Выдвигающаяся навигация слева -->
<nav class="sidebar navi" id="sidebar">
    <div class="logo-container">

        <center>
            @if(!empty($data['img_obj']))
                <a title="Загрузить новое" href="{{ route('edit.img_obj', ['id' => $data['id']]) }}" class="logo-link">
                    <img src="{{ $data['img_obj']['path'] ?? asset('images/no_image/no_image.jpg') }}"
                         alt="Логотип предприятия"
                         class="circular-logo">
                </a>
            @else
                <img src="{{asset('images/no_image/no_image.jpg') }}"
                     alt="Логотип предприятия" title="Сначала добавьте объект"
                     class="circular-logo">
            @endif
        </center>


    </div>
    <!-- Кнопка закрытия (крестик) -->
    <button class="close-btn" id="closeBtn">×</button>

    <ul class="nav-list">
        @if(!empty($data['details_obj']['id']))
            <li><a class="dropdown-item" href="{{ route('create.subj') }}">Добавить субъект</a></li>
        @endif
        @if(!empty($data['id']))
            <li class="text-white">
                <b>Редактировать:</b>
            </li>
            <li><a class="dropdown-item" href="{{ route('obj.edit',['id' => $data['id']]) }}">
                    Объект</a>
            </li>
            @if(!empty($data['details_obj']['id']))
                <li><a class="dropdown-item"
                       href="{{ route('edit.details_obj', ['id' => $data['details_obj']['id']]) }}">Детали объекта</a>
                </li>
            @endif
        @else
            <li><a class="dropdown-item" href="{{ route('create.obj') }}">Добавить объект</a>
            </li>
        @endif
    </ul>
</nav>

<!-- Основная область контента -->

<button class="toggle-btn" id="toggleBtn">☰</button>

<script>
    // Элементы DOM
    const toggleBtn = document.getElementById('toggleBtn');
    const sidebar = document.getElementById('sidebar');
    const closeBtn = document.getElementById('closeBtn');

    // Открытие меню по кнопке переключения
    toggleBtn.addEventListener('click', () => {
        sidebar.classList.add('active');
    });

    // Закрытие меню по крестику
    closeBtn.addEventListener('click', () => {
        sidebar.classList.remove('active');
    });

    // Закрытие навигации при клике вне её области
    document.addEventListener('click', (e) => {
        if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    });
</script>
