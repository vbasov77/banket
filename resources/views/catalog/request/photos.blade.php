@extends('layouts.app')

@section('title', 'Фото: ' . $performer->name)

@section('content')

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css"
          integrity="sha512-3pIirOrwegjM6erE5gPSwkUzO+3cTjpnV9lexlNZqvupR64iZBnOOTiiLPb9M36zpMScbmUNIcHUqKD47M719g=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"
            integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>

    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"
            integrity="sha256-lSjKY0/srUM9BE3dPm+c4fBo1dky2v27Gdjm2uoZaL0=" crossorigin="anonymous"></script>
    <script src="{{ asset('js/jquery/jquery.ui.touch-punch.min.js') }}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"
            integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <style>
        .post_list_ul {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 24px;
            padding: 0;
            margin: 0;
            overflow-x: hidden;
            width: 100%;
        }

        .post_list_ul li {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            background: #f8f9fa;
            transition: box-shadow 0.3s ease;
            width: 100%;
            max-width: 100%;
        }

        .zoom {
            display: block;
            width: 100%;
            height: 150px;
            object-fit: cover;
            transition: filter 0.3s ease;
        }

        .post_list_ul li:hover {
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.12);
        }

        .zoom:hover {
            filter: brightness(0.95) saturate(1.05);
        }

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
            max-width: calc(100% - 24px);
            max-height: calc(100% - 24px);
        }

        .round-popup {
            position: absolute;
            top: 12px;
            right: 12px;
            opacity: 1;
            pointer-events: auto;
            width: 32px;
            height: 32px;
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
            0% { background-position: -40px 0; }
            100% { background-position: 40px 0; }
        }

        .zoom.loading {
            display: none;
        }

        @media (max-width: 480px) {
            .round-popup {
                opacity: 1;
                pointer-events: auto;
            }
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            z-index: 9999;
            background: currentColor;
            filter: blur(0.5px);
        }
    </style>

    <div class="container p-3">
        <div class="row">
            <div class="col-md-12">
                @if (session('message'))
                    <h3 style="color:green">{{ session('message') }}</h3>
                @endif
            </div>
        </div>
    </div>

    <section>
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5 justify-content-center">
                <div class="col-lg-10 mb-5">
                    <div class="col-md-12">
                        @if(count($performer->photos) > 0)
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
                                                @foreach ($performer->photos as $image)
                                                    <li class="ui-state-default myClass" id="{{ $image->id }}"
                                                        data-id="{{ $image->id }}">
                                                        <span class="pos_num">{{ $loop->index + 1 }}</span>
                                                        <img class="zoom img-fluid box3 del"
                                                             src="{{ $image->small_img ?? $image->big_img }}"
                                                             data-file="{{ $image->id }}">
                                                        <div class="round-popup">
                                                            <button type="button" class="close">
                                                                <span data-id="{{ $image->id }}">&times;</span>
                                                            </button>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <br>
                    <br>
                    <label for="files">Фото</label>
                    <input class="form-control" type="file" multiple="multiple" accept="image/*" id="files"
                           name="img[]"/>
                    <br>
                    <a href="{{ route('catalog.request.my_category') }}"
                       class="btn-outline-success">В панель управления</a>
                    <br>
                </div>
            </div>
        </div>
    </section>

    <script>
        window.photoStoreUrl = '{{ route('catalog.request.photo.store', $performer) }}';
        window.photoOrderUrl = '{{ route('catalog.request.photo.order', $performer) }}';
        window.performerId = {{ $performer->id }};
        const MAX_PHOTOS = 10;

        const counterEl = document.querySelector('.photo-counter') || (function () {
            const el = document.createElement('div');
            el.className = 'photo-counter';
            el.style.cssText = 'margin-bottom:10px; font-weight:bold; color:#1976d2;';
            document.querySelector('#post_sortable')?.before(el);
            return el;
        })();

        function updateCounter() {
            const count = document.querySelectorAll('#post_sortable li').length;
            counterEl.textContent = `Загружено фото: ${count} / ${MAX_PHOTOS}`;
            counterEl.style.color = count >= MAX_PHOTOS ? '#c62828' : '#66b266';
        }

        updateCounter();

        function del() {
            document.querySelectorAll('.close').forEach(button => {
                button.removeEventListener('click', handleDeleteClick);
                button.removeEventListener('touchstart', handleDeleteClick);

                button.addEventListener('click', handleDeleteClick);
                button.addEventListener('touchstart', handleDeleteClick, {passive: false});
            });
        }

        function handleDeleteClick(e) {
            e.preventDefault();
            e.stopPropagation();

            const span = e.target.closest('.close span[data-id]');
            if (!span) return;

            if (confirm('Подтвердите удаление')) {
                const id = span.getAttribute('data-id');
                sendDel('/catalog/request/' + window.performerId + '/photos/' + id, id);
            } else {
                alert('Удаление отменено');
            }
        }

        function createParticleEffect(element) {
            const rect = element.getBoundingClientRect();
            const container = document.body;
            const particlesCount = 80;
            const elementColor = window.getComputedStyle(element).color;

            for (let i = 0; i < particlesCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';

                const size = Math.random() * 7 + 1;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;

                const x = Math.random() * rect.width;
                const y = Math.random() * rect.height;

                particle.style.left = `${rect.left + x}px`;
                particle.style.top = `${rect.top + y}px`;
                particle.style.color = elementColor;

                const angle = Math.random() * Math.PI * 2;
                const distance = Math.random() * 150 + 120;

                const endX = x + Math.cos(angle) * distance;
                const endY = y + Math.sin(angle) * distance;

                container.appendChild(particle);

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
                    duration: Math.random() * 400 + 500,
                    easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
                    fill: 'forwards'
                });

                setTimeout(() => particle.remove(), 900);
            }

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
                    updateCounter();
                    sortable();
                }, 900);
            } else {
                alert(result.message || 'Ошибка удаления');
            }
        }

        del();

        function sortable() {
            const items = document.querySelectorAll('.post_list_ul li');

            items.forEach((item, index) => {
                const posNum = item.querySelector('.pos_num');

                if (posNum) {
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
            <span class="pos_num"></span>
            <div class="round-popup">
                <button type="button" class="close"><span data-id="${fileId}">&times;</span></button>
            </div>
        `;

            document.querySelector('.post_list_ul').appendChild(li);
            return li;
        }

        function updateWithRealImage(placeholderLi, photoData) {
            const img = placeholderLi.querySelector('.zoom');
            const placeholder = placeholderLi.querySelector('.upload-placeholder');
            const posNum = placeholderLi.querySelector('.pos_num');

            img.src = photoData.path;
            img.alt = 'Фото';
            img.setAttribute('data-file', photoData.id);

            if (placeholder) {
                placeholder.remove();
            }

            if (!posNum) {
                const newPosNum = document.createElement('span');
                newPosNum.className = 'pos_num';
                placeholderLi.insertBefore(newPosNum, img);
            }

            img.classList.remove('loading');

            placeholderLi.id = photoData.id;
            placeholderLi.dataset.id = photoData.id;

            const closeBtn = placeholderLi.querySelector('.close span');
            closeBtn.dataset.id = photoData.id;

            sortable();
            del();
        }

        let input = document.getElementById('files');

        input.addEventListener('change', function (e) {
            const files = e.target.files;
            const url = window.photoStoreUrl;

            const currentCount = document.querySelectorAll('#post_sortable li').length;
            const remaining = MAX_PHOTOS - currentCount;

            if (files.length > remaining) {
                alert(`Вы не можете загружать больше ${MAX_PHOTOS}-ти фото для одной карточки. Сейчас загружено: ${currentCount}. Удалите ненужные фото.`);
                this.value = '';
                return;
            }

            for (const file of files) {
                const fileId = `temp_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
                const placeholderLi = createUploadPlaceholder(fileId);

                const formData = new FormData();
                formData.append('img', file);

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
                            updateWithRealImage(placeholderLi, {
                                id: result.id,
                                path: result.path,
                                name: file.name
                            });

                            updateCounter();
                        } else {
                            alert('Ошибка загрузки: ' + (result.message || 'Неизвестный ответ'));
                            placeholderLi.remove();
                            updateCounter();
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка отправки:', error);
                        alert('Не удалось отправить файл');
                        placeholderLi.remove();
                        updateCounter();
                    });
            }

            this.value = '';
        });

        $(document).ready(function () {
            $('#post_sortable').sortable({
                placeholder: 'ui-state-highlight',
                forcePlaceholderSize: true,
                tolerance: 'pointer',
                update: function (event, ui) {
                    var post_order_ids = [];
                    $('#post_sortable li').each(function () {
                        post_order_ids.push($(this).data('id'));
                    });

                    $.ajax({
                        type: 'POST',
                        url: window.photoOrderUrl,
                        dataType: 'json',
                        data: {
                            order: post_order_ids,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            toastr.success(response.message);
                            sortable();
                        },
                        error: function (xhr, status, error) {
                            console.log(xhr.responseText);
                            toastr.error('Ошибка сохранения порядка');
                        }
                    });
                }
            }).disableSelection();
        });
    </script>
@endsection
