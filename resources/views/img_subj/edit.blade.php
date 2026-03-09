<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Добавить фото</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css"
          integrity="sha512-3pIirOrwegjM6erE5gPSwkUzO+3cTjpnV9lexlNZqvupR64iZBnOOTiiLPb9M36zpMScbmUNIcHUqKD47M719g=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"
            integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>

    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"
            integrity="sha256-lSjKY0/srUM9BE3dPm+c4fBo1dky2v27Gdjm2uoZaL0=" crossorigin="anonymous"></script>
    <!-- В <head> после jQuery и jQuery UI -->
    <script src="{{ asset('js/jquery/jquery.ui.touch-punch.min.js') }}"></script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"
            integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <style>
        /* Список изображений — сетка без переполнения */
        .post_list_ul {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 24px;
            padding: 0;
            margin: 0;
            overflow-x: hidden; /* Блокируем горизонтальную прокрутку */
            width: 100%;
        }

        /* Элементы списка — жёсткие границы */
        .post_list_ul li {
            position: relative;
            border-radius: 12px;
            overflow: hidden; /* Обрезаем всё лишнее */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            background: #f8f9fa;
            transition: box-shadow 0.3s ease;
            width: 100%;
            /*height: 200px; !* Фиксированная высота *!*/
            max-width: 100%;
        }

        /* Изображение — строго в контейнере */
        .zoom {
            display: block;
            width: 100%;
            height: 150px;
            object-fit: cover;
            transition: filter 0.3s ease;
        }

        /* Эффект при наведении (без масштабирования) */
        .post_list_ul li:hover {
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.12);
        }

        .zoom:hover {
            filter: brightness(0.95) saturate(1.05);
        }

        /* Номер изображения — в углу, без выхода за границы */
        .pos_num {
            position: absolute;
            top: 12px;
            left: 12px;
            background: #ff6b6b;
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 6px 10px;
            border-radius: 20px;
            z-index: 10;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 5px rgba(255, 107, 107, 0.2);
            /* Ограничиваем размер */
            max-width: calc(100% - 24px);
            max-height: calc(100% - 24px);
        }

        /* Кнопка удаления — появляется при наведении */
        .round-popup {
            position: absolute;
            top: 12px;
            right: 12px;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
            width: 32px;
            height: 32px;
        }

        .post_list_ul li:hover .round-popup {
            opacity: 1;
            pointer-events: auto;
        }

        .close {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: rgba(255, 107, 107, 0.9);
            color: white;
            font-size: 22px;
            display: flex;
            justify-content: center;
            align-items: center;
            border: none;
            cursor: pointer;
            transition: background 0.3s ease;
            box-shadow: 0 2px 5px rgba(255, 107, 107, 0.3);
        }

        .close:hover {
            background: #e55039;
        }

        /* Поле загрузки файлов */
        #files {
            width: 100%;
            border: 2px dashed #4caf50;
            border-radius: 12px;
            padding: 25px 20px;
            text-align: center;
            font-size: 1rem;
            color: #4caf50;
            background: transparent;
            transition: border-color 0.3s, background-color 0.3s;
            cursor: pointer;
            font-weight: 500;
            min-height: 60px;
        }

        #files:hover {
            border-color: #4caf50;
            background-color: rgba(110, 68, 255, 0.05);
        }

        #files:focus {
            outline: none;
            border-color: #4caf50;
            box-shadow: 0 0 0 3px rgba(196, 253, 190, 0.3);
        }

        label[for="files"] {
            display: block;
            margin-bottom: 16px;
            color: #4caf50;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-size: 0.9rem;
            transition: color 0.3s;
        }

        /* Кнопка «Вернуться» */
        .btn-outline-success {
            display: block;
            margin: 30px auto 0;
            width: fit-content;
            padding: 12px 30px;
            border: 2px solid #4caf50;
            color: #4caf50;
            background: transparent;
            border-radius: 50px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: background-color 0.3s, color 0.3s, transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        .btn-outline-success:hover {
            background-color: #4caf50;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.3);
        }

        .btn-outline-success:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.4);
        }

        /* Глобальные меры против прокрутки */
        html, body, .container {
            overflow-x: hidden;
            margin: 0;
            padding: 0;
            width: 100%;
            max-width: 100%;
        }

        * {
            box-sizing: border-box;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }


        /* Макет загружаемого фото */
        .upload-placeholder {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 150px;
            background: linear-gradient(45deg, #f0f0f0 25%, #e0e0e0 25%, #e0e0e0 50%, #f0f0f0 50%, #f0f0f0 75%, #e0e0e0 75%);
            background-size: 40px 40px;
            animation: placeholder-shine 1.5s linear infinite;
            border-radius: 12px;
            margin: 0;
        }

        @keyframes placeholder-shine {
            0% {
                background-position: -40px 0;
            }
            100% {
                background-position: 40px 0;
            }
        }

        /* Скрываем реальное изображение до загрузки */
        .zoom.loading {
            display: none;
        }
    </style>

{{--    // Рассеивание как в ТГ--}}
<style>
    .delete-animation {
        position: relative;
        overflow: hidden;
    }

    .particle {
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
        z-index: 9999;
        background: currentColor;
        filter: blur(0.5px); /* Лёгкое размытие для мягкости */
    }


</style>

</head>
<link href="{{ asset('css/messages/messages.css') }}" rel="stylesheet">
<body>
<div class="container p-3">
    <div class="row">
        <div class="col-md-12">
            @if (session('message'))
                <h3 style="color:green">{{ session('message') }}</h3>
            @endif
        </div>

        <section>
            <div class="container px-4 px-lg-5">
                <div class="row gx-4 gx-lg-5 justify-content-center">
                    <div class="col-lg-10">
                        <br>
                        @if (!empty($message))
                            <div class="alert alert-success">
                                {{$message}}
                            </div>
                        @endif

                        <div class="col-md-12">
                            @if(count($images) > 0)
                                <h3 style="margin: 40px 0 60px 0">Редактировать фото</h3>
                            @else
                                <h3 style="margin: 40px 0 60px 0">Добавьте фото</h3>
                            @endif
                            <div class="justify-content-center">
                                <div class="table-responsive">
                                    <div id="example1_wrapper"
                                         class="dataTables_wrapper container-fluid dt-bootstrap4">
                                        <div class="row justify-content-center">
                                            <div class="col-sm-10">
                                                <ul id="post_sortable" class="post_list_ul">
                                                    @foreach ($images as $image)
                                                        <li class="ui-state-default myClass" id="{{$image->id}}"
                                                            data-id="{{$image->id}}">
                                                            <span class="pos_num">{{$loop->index + 1}}</span>
                                                            <img class="zoom img-fluid box3 del"
                                                                 src="{{$image->path . '&cs=240x0'}}"
                                                                 data-file="{{$image->id}}">
                                                            <div class="round-popup">
                                                                <button

                                                                        type="button"
                                                                        class="close"
                                                                ><span data-id="{{$image->id}}">&times;</span>
                                                                </button>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.card-body -->
                            </div>
                        </div>

                        <br>
                        <br>
                        <label for="files">Фото</label>
                        <input class="form-control" type="file" multiple="multiple" accept="image/*" id="files"
                               name="img[]"/>
                        <br>
                        <a href="{{route('edit.subj', ['id' => $subj])}}" class="btn btn-outline-success btn-sm">Вернуться
                            в редактирование</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
    function del() {
        // Удаляем предыдущие обработчики, чтобы избежать дублирования
        let buttons = document.querySelectorAll('.close');
        buttons.forEach(button => {
            button.removeEventListener('click', handleDeleteClick);
        });

        // Назначаем обработчики заново
        buttons.forEach(button => {
            button.addEventListener('click', handleDeleteClick);
        });
    }

    function handleDeleteClick(e) {
        if (confirm('Подтвердите удаление')) {
            const id = e.target.getAttribute('data-id');
            sendDel('/delete_subj_img/id' + id, id);
        } else {
            alert('Удаление отменено');
        }
    }

    function createParticleEffect(element) {
        const rect = element.getBoundingClientRect();
        const container = document.body;
        const particlesCount = 80; // Увеличил с 30 до 80 частиц

        // Получаем цвет элемента для частиц (можно заменить на фиксированный)
        const elementColor = window.getComputedStyle(element).color;

        for (let i = 0; i < particlesCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';

            // Случайный размер частицы (1–8 px)
            const size = Math.random() * 7 + 1;
            particle.style.width = `${size}px`;
            particle.style.height = `${size}px`;

            // Позиция частицы — случайная точка внутри элемента
            const x = Math.random() * rect.width;
            const y = Math.random() * rect.height;

            particle.style.left = `${rect.left + x}px`;
            particle.style.top = `${rect.top + y}px`;
            particle.style.color = elementColor; // Цвет из элемента

            // Анимация движения
            const angle = Math.random() * Math.PI * 2; // Случайный угол
            const speed = Math.random() * 200 + 100; // Скорость
            const distance = Math.random() * 150 + 120; // Дистанция

            const endX = x + Math.cos(angle) * distance;
            const endY = y + Math.sin(angle) * distance;

            container.appendChild(particle);

            // Анимируем частицу с вариациями
            particle.animate([
                {
                    transform: `translate(0, 0) scale(1)`,
                    opacity: 1
                },
                {
                    transform: `translate(${endX}px, ${endY}px) scale(${Math.random() * 0.3 + 0.1})`,
                    opacity: Math.random() * 0.3
                }
            ], {
                duration: Math.random() * 400 + 500, // Длительность 500–900 мс
                easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
                fill: 'forwards'
            });

            // Удаляем частицу после анимации
            setTimeout(() => particle.remove(), 900);
        }

        // Плавно скрываем исходный элемент
        element.style.transition = 'opacity 0.2s ease-out';
        element.style.opacity = '0';
    }

    async function sendDel(url, id) {
        let response = await fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        let result = await response.json();
        if (result.answer === 'ok') {
            const elementToDelete = document.getElementById(id);
            createParticleEffect(elementToDelete);

            setTimeout(() => {
                elementToDelete.remove();
                sortable();
            }, 900); // 900 мс = максимальная длительность анимации частиц
        } else {
            alert(result);
        }
    }



    del();

    function sortable() {
        // Получаем все элементы списка в текущем порядке (включая перетащенные)
        const items = document.querySelectorAll('.post_list_ul li');

        items.forEach((item, index) => {
            // Находим элемент с номером
            const posNum = item.querySelector('.pos_num');

            if (posNum) {
                // Присваиваем номер по порядку (начиная с 1)
                posNum.textContent = index + 1;
            }
        });
    }

    function createUploadPlaceholder(fileId) {
        const li = document.createElement('li');
        li.className = 'ui-state-default myClass';
        li.id = `upload-${fileId}`;
        li.dataset.id = fileId;

        li.innerHTML = `
            <div class="upload-placeholder">Загружается...</div>
            <img class="zoom img-fluid box3 del loading" src="" alt="Фото">
            <span class="pos_num"></span> <!-- ДОБАВЛЕНО! -->
            <div class="round-popup">
                <button type="button" class="close"><span data-id="${fileId}">&times;</span></button>
            </div>
        `;

        document.querySelector('.post_list_ul').appendChild(li);
        return li;
    }

    // Обновляет макет реальным фото
    function updateWithRealImage(placeholderLi, photoData) {
        const img = placeholderLi.querySelector('.zoom');
        const placeholder = placeholderLi.querySelector('.upload-placeholder');

        // Сохраняем .pos_num (он нужен для нумерации)
        const posNum = placeholderLi.querySelector('.pos_num');

        // Обновляем данные изображения
        img.src = photoData.path + '&cs=240x0';
        img.alt = photoData.name || 'Фото';
        img.setAttribute('data-file', photoData.id);

        // Удаляем ТОЛЬКО макет-заглушку, а не весь контент
        if (placeholder) {
            placeholder.remove();
        }

        // Восстанавливаем .pos_num, если он был удалён
        if (!posNum) {
            const newPosNum = document.createElement('span');
            newPosNum.className = 'pos_num';
            placeholderLi.insertBefore(newPosNum, img); // Вставляем перед img
        }

        img.classList.remove('loading');

        // Остальные обновления
        placeholderLi.id = photoData.id;
        placeholderLi.dataset.id = photoData.id;

        const closeBtn = placeholderLi.querySelector('.close span');
        closeBtn.dataset.id = photoData.id;

        sortable();
        del();
    }

    async function sendStoreImg(url, files) {
        let response = await fetch(url, {
            method: 'POST',
            body: files,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        let result = await response.json();

        if (result.path) {
            const text = `<li class="ui-state-default myClass" id="${result.id}" data-id="${result.id}">
                <span class="pos_num">${result.id}</span>
                <img class="zoom img-fluid box3 del" src="${result.path}/" data-file="${result.id}">
                <div class="round-popup">
                    <button type="button" class="close"><span data-id="${result.id}">&times;</span></button>
                </div>
            </li>`;

            document.querySelector('.post_list_ul').insertAdjacentHTML('beforeend', text);

            // Вызываем updateWithRealImage для заполнения data-file
            const newLi = document.getElementById(result.id);
            updateWithRealImage(newLi, {
                id: result.id,
                path: result.path,
                name: 'Новое фото' // Можно взять из File.name
            });

            sortable();
            del();
        }
    }

    let input = document.getElementById('files');

    input.addEventListener('change', function (e) {
        const files = e.target.files;
        const url = '/img_subj_store';

        for (const file of files) {
            // Генерируем уникальный ID для макета
            const fileId = `temp_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;

            // Создаём макет
            const placeholderLi = createUploadPlaceholder(fileId);

            // Отправляем файл
            const formData = new FormData();
            formData.append('img', file);
            formData.append('id', {{ $subj }});

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
                .then(response => response.json())
                .then(result => {
                    if (result.path) {
                        // Обновляем макет реальным фото
                        updateWithRealImage(placeholderLi, {
                            id: result.id,
                            path: result.path,
                            name: file.name
                        });
                    } else {
                        alert('Ошибка загрузки: ' + (result.message || 'Неизвестный ответ'));
                        placeholderLi.remove(); // Удаляем макет при ошибке
                    }
                })
                .catch(error => {
                    console.error('Ошибка отправки:', error);
                    alert('Не удалось отправить файл');
                    placeholderLi.remove();
                });
        }

        this.value = ''; // Очищаем input
    });

    $(document).ready(function () {
        // Инициализируем Sortable с поддержкой тач
        $('#post_sortable').sortable({
            placeholder: 'ui-state-highlight',
            forcePlaceholderSize: true, // Улучшает визуал на мобильных
            tolerance: 'pointer',     // Более точное срабатывание на касание
            update: function (event, ui) {
                var post_order_ids = [];
                $('#post_sortable li').each(function () {
                    post_order_ids.push($(this).data('id'));
                });

                $.ajax({
                    type: 'POST',
                    url: '{{ route('img_subj.order_change') }}',
                    dataType: 'json',
                    data: {
                        order: post_order_ids,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        toastr.success(response.message);
                        sortable(); // Обновляем номера позиций
                    },
                    error: function (xhr, status, error) {
                        console.log(xhr.responseText);
                        toastr.error('Ошибка сохранения порядка');
                    }
                });
            }
        }).disableSelection(); // Защищает от выделения текста при перетаскивании
    });

</script>
{{--<script>--}}
{{--    function del() {--}}
{{--        let button = document.querySelectorAll('.close');--}}
{{--        for (var i = 0; i < button.length; i++) {--}}
{{--            button[i].addEventListener('click', function (e) {--}}

{{--                if (confirm('Подтвердите удаление')) {--}}
{{--                    const id = e.target.getAttribute('data-id');--}}
{{--                    sendDel('/delete_subj_img/id' + id, id);--}}
{{--                } else {--}}
{{--                    alert('Удаление отменено');--}}
{{--                }--}}
{{--            });--}}
{{--        }--}}
{{--    }--}}


{{--    async function sendDel(url, id) {--}}
{{--        let response = await fetch(url, {--}}
{{--            method: 'DELETE',--}}
{{--            headers: {--}}
{{--                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')--}}
{{--            }--}}
{{--        });--}}
{{--        let result = await response.json();--}}
{{--        if (result.answer === 'ok') {--}}
{{--            document.getElementById(id).remove();--}}
{{--            sortable();--}}
{{--        } else {--}}
{{--            alert(result);--}}
{{--        }--}}
{{--    }--}}

{{--    del();--}}


{{--    function sortable() {--}}
{{--        // Получаем все элементы списка в текущем порядке (включая перетащенные)--}}
{{--        const items = document.querySelectorAll('.post_list_ul li');--}}

{{--        items.forEach((item, index) => {--}}
{{--            // Находим элемент с номером--}}
{{--            const posNum = item.querySelector('.pos_num');--}}

{{--            if (posNum) {--}}
{{--                // Присваиваем номер по порядку (начиная с 1)--}}
{{--                posNum.textContent = index + 1;--}}
{{--            }--}}
{{--        });--}}
{{--    }--}}

{{--    function createUploadPlaceholder(fileId) {--}}
{{--        const li = document.createElement('li');--}}
{{--        li.className = 'ui-state-default myClass';--}}
{{--        li.id = `upload-${fileId}`;--}}
{{--        li.dataset.id = fileId;--}}

{{--        li.innerHTML = `--}}
{{--    <div class="upload-placeholder">Загружается...</div>--}}
{{--    <img class="zoom img-fluid box3 del loading" src="" alt="Фото">--}}
{{--    <span class="pos_num"></span> <!-- ДОБАВЛЕНО! -->--}}
{{--    <div class="round-popup">--}}
{{--      <button type="button" class="close"><span data-id="${fileId}">&times;</span></button>--}}
{{--    </div>--}}
{{--  `;--}}

{{--        document.querySelector('.post_list_ul').appendChild(li);--}}
{{--        return li;--}}
{{--    }--}}


{{--    // Обновляет макет реальным фото--}}
{{--    function updateWithRealImage(placeholderLi, photoData) {--}}
{{--        const img = placeholderLi.querySelector('.zoom');--}}
{{--        const placeholder = placeholderLi.querySelector('.upload-placeholder');--}}

{{--        // Сохраняем .pos_num (он нужен для нумерации)--}}
{{--        const posNum = placeholderLi.querySelector('.pos_num');--}}

{{--        // Обновляем данные изображения--}}
{{--        img.src = photoData.path + '&cs=240x0';--}}
{{--        img.alt = photoData.name || 'Фото';--}}
{{--        img.setAttribute('data-file', photoData.id);--}}


{{--        // Удаляем ТОЛЬКО макет-заглушку, а не весь контент--}}
{{--        if (placeholder) {--}}
{{--            placeholder.remove();--}}
{{--        }--}}

{{--        // Восстанавливаем .pos_num, если он был удалён--}}
{{--        if (!posNum) {--}}
{{--            const newPosNum = document.createElement('span');--}}
{{--            newPosNum.className = 'pos_num';--}}
{{--            placeholderLi.insertBefore(newPosNum, img); // Вставляем перед img--}}
{{--        }--}}

{{--        img.classList.remove('loading');--}}

{{--        // Остальные обновления--}}
{{--        placeholderLi.id = photoData.id;--}}
{{--        placeholderLi.dataset.id = photoData.id;--}}

{{--        const closeBtn = placeholderLi.querySelector('.close span');--}}
{{--        closeBtn.dataset.id = photoData.id;--}}

{{--        sortable();--}}
{{--        del();--}}
{{--    }--}}


{{--    async function sendStoreImg(url, files) {--}}
{{--        let response = await fetch(url, { /* ... */});--}}
{{--        let result = await response.json();--}}

{{--        if (result.path) {--}}
{{--            const text = `<li class="ui-state-default myClass" id="${result.id}" data-id="${result.id}">--}}
{{--<span class="pos_num">${result.id}</span>--}}
{{--<img class="zoom img-fluid box3 del" src="${result.path}/" data-file="${result.id}"> <!-- Оставляем пустым! -->--}}
{{--        <div class="round-popup">--}}
{{--          <button type="button" class="close"><span data-id="${result.id}">&times;</span></button>--}}
{{--        </div>--}}
{{--      </li>`;--}}

{{--            document.querySelector('.post_list_ul').insertAdjacentHTML('beforeend', text);--}}

{{--            // Вызываем updateWithRealImage для заполнения data-file--}}
{{--            const newLi = document.getElementById(result.id);--}}
{{--            updateWithRealImage(newLi, {--}}
{{--                id: result.id,--}}
{{--                path: result.path,--}}
{{--                name: 'Новое фото' // Можно взять из File.name--}}
{{--            });--}}

{{--            sortable();--}}
{{--            del();--}}
{{--        }--}}
{{--    }--}}


{{--    let input = document.getElementById('files');--}}

{{--    input.addEventListener('change', function (e) {--}}
{{--        const files = e.target.files;--}}
{{--        const url = '/img_subj_store';--}}

{{--        for (const file of files) {--}}
{{--            // Генерируем уникальный ID для макета--}}
{{--            const fileId = `temp_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;--}}

{{--            // Создаём макет--}}
{{--            const placeholderLi = createUploadPlaceholder(fileId);--}}

{{--            // Отправляем файл--}}
{{--            const formData = new FormData();--}}
{{--            formData.append('img', file);--}}
{{--            formData.append('id', {{ $subj }});--}}

{{--            fetch(url, {--}}
{{--                method: 'POST',--}}
{{--                body: formData,--}}
{{--                headers: {--}}
{{--                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content--}}
{{--                }--}}
{{--            })--}}
{{--                .then(response => response.json())--}}
{{--                .then(result => {--}}
{{--                    if (result.path) {--}}
{{--                        // Обновляем макет реальным фото--}}
{{--                        updateWithRealImage(placeholderLi, {--}}
{{--                            id: result.id,--}}
{{--                            path: result.path,--}}
{{--                            name: file.name--}}
{{--                        });--}}
{{--                    } else {--}}
{{--                        alert('Ошибка загрузки: ' + (result.message || 'Неизвестный ответ'));--}}
{{--                        placeholderLi.remove(); // Удаляем макет при ошибке--}}
{{--                    }--}}
{{--                })--}}
{{--                .catch(error => {--}}
{{--                    console.error('Ошибка отправки:', error);--}}
{{--                    alert('Не удалось отправить файл');--}}
{{--                    placeholderLi.remove();--}}
{{--                });--}}
{{--        }--}}

{{--        this.value = ''; // Очищаем input--}}
{{--    });--}}


{{--    $(document).ready(function () {--}}
{{--        // Инициализируем Sortable с поддержкой тач--}}
{{--        $("#post_sortable").sortable({--}}

{{--            placeholder: "ui-state-highlight",--}}
{{--            forcePlaceholderSize: true, // Улучшает визуал на мобильных--}}
{{--            tolerance: "pointer",     // Более точное срабатывание на касание--}}
{{--            update: function (event, ui) {--}}
{{--                var post_order_ids = [];--}}
{{--                $('#post_sortable li').each(function () {--}}
{{--                    post_order_ids.push($(this).data("id"));--}}
{{--                });--}}

{{--                $.ajax({--}}
{{--                    type: "POST",--}}
{{--                    url: "{{ route('img_subj.order_change') }}",--}}
{{--                    dataType: "json",--}}
{{--                    data: {--}}
{{--                        order: post_order_ids,--}}
{{--                        _token: "{{ csrf_token() }}"--}}
{{--                    },--}}
{{--                    success: function (response) {--}}
{{--                        toastr.success(response.message);--}}
{{--                        sortable(); // Обновляем номера позиций--}}
{{--                    },--}}
{{--                    error: function (xhr, status, error) {--}}
{{--                        console.log(xhr.responseText);--}}
{{--                        toastr.error("Ошибка сохранения порядка");--}}
{{--                    }--}}

{{--                });--}}


{{--            }--}}

{{--        }).disableSelection(); // Защищает от выделения текста при перетаскивании--}}
{{--    });--}}


{{--</script>--}}
</body>
</html>