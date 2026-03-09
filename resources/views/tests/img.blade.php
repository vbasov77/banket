<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Обрезка квадратного фото</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{asset('css/croppie/croppie.css')}}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>


<script src="{{asset('js/jquery/3.6.0/jquery.min.js')}}"></script>
<script src="{{asset('js/croppie/croppie.js')}}"></script>
<script>
    $(document).ready(function () {
        // Настройка AJAX для передачи CSRF-токена
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var $uploadCrop = $('#upload-logo').croppie({
            enableExif: true,
            viewport: {
                width: 400,
                height: 400,
                type: 'square'
            },
            boundary: {
                width: 500,
                height: 500
            }
        });

        $('#upload').on('change', function () {
            var reader = new FileReader();
            reader.onload = function (e) {
                $uploadCrop.croppie('bind', {
                    url: e.target.result
                }).then(function () {
                    console.log('Изображение загружено в Croppie');
                });
            };
            reader.readAsDataURL(this.files[0]);
        });

        $('.upload-result').on('click', function (ev) {
            ev.preventDefault();

            const objectId = $('#object-id').val();
            if (!objectId || isNaN(objectId) || parseInt(objectId) <= 0) {
                alert('Пожалуйста, введите корректный ID объекта');
                return;
            }

            $uploadCrop.croppie('result', {
                type: 'blob',
                size: 'viewport',
                format: 'jpeg',
                quality: 0.9
            }).then(function (blob) {
                // Создаём File из Blob
                const file = new File([blob], 'cropped_image.jpg', { type: 'image/jpeg' });

                // Создаём FormData и добавляем файл и ID
                const formData = new FormData();
                formData.append('img', file);
                formData.append('id', objectId);

                // Отправляем AJAX-запрос
                $.ajax({
                    url: "{{ route('img_obj.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,  // важно для FormData
                    contentType: false,   // важно для FormData
                    success: function (data) {
                        if (data.path) {
                            var html = `
            <div>
                <p>Изображение успешно загружено для объекта #${data.id}!</p>
                <img src="${data.path + '&cs=360x0'}" class="img-fluid rounded" style="max-width: 100%;">
            </div>`;
                            $('#result-container').html(html);
                            console.log('Ответ сервера:', data);
                        } else {
                            alert('Ошибка: ' + data.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX ошибка:', xhr, status, error);
                        if (xhr.status === 419) {
                            alert('Сессия истекла. Перезагрузите страницу и попробуйте снова.');
                        } else {
                            alert('Ошибка при загрузке изображения. Проверьте консоль для деталей.');
                        }
                    }
                });
            }).catch(function (error) {
                console.error('Ошибка Croppie:', error);
                alert('Ошибка обрезки изображения');
            });
        });
    });
</script>
</body>
</html>
