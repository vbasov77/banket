function getBlockSizes() {
    const width = window.innerWidth;

    if (width > 768) {
        return {
            viewportSize: 300,
            boundarySize: 400
        };
    } else {
        const viewportSize = width * 0.6; // 60 % от ширины экрана
        const boundarySize = width * 0.8; // 80 % от ширины экрана
        return {
            viewportSize: Math.round(viewportSize),
            boundarySize: Math.round(boundarySize)
        };
    }
}

$(document).ready(function () {

    // Настройка AJAX для передачи CSRF-токена
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    const sizes = getBlockSizes();
    const viewportSize = sizes.viewportSize;
    const boundarySize = sizes.boundarySize;

    var $uploadCrop = $('#upload-logo').croppie({
        enableExif: true,
        viewport: {
            width: viewportSize,
            height: viewportSize,
            type: 'square'
        },
        boundary: {
            width: boundarySize,
            height: boundarySize
        },
        showZoom: true,
        enableZoom: true
    });

    const imgElement = document.querySelector('img.cr-image');
    imgElement.style.display = 'none';

    $('#upload').on('change', function () {
        var reader = new FileReader();
        reader.onload = function (e) {
            $uploadCrop.croppie('bind', {
                url: e.target.result
            }).then(function () {
                imgElement.style.display = 'block';
                console.log('Изображение загружено в Croppie');
            });
        };
        reader.readAsDataURL(this.files[0]);
    });

    $('.upload-result').on('click', function (ev) {
        ev.preventDefault();
        const photoId = $('#photo-id').val();

        $uploadCrop.croppie('result', {
            type: 'blob',
            size: 'viewport',
            format: 'jpeg',
            quality: 1
        }).then(function (blob) {
            // Создаём File из Blob
            const file = new File([blob], 'cropped_image.jpg', {type: 'image/jpeg'});
            const preloader = $('.preloader-img');
            // Создаём FormData и добавляем файл и ID
            const formData = new FormData();
            formData.append('img', file);
            formData.append('id', photoId);

            // Отправляем AJAX-запрос
            $.ajax({
                url: "/img_obj_update",
                type: "POST",
                data: formData,
                processData: false,  // важно для FormData
                contentType: false,   // важно для FormData
                beforeSend: function () {
                    preloader.fadeIn(300);
                },
                success: function (data) {
                    if (data.success === true) {
                        window.location = '/my_obj';
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