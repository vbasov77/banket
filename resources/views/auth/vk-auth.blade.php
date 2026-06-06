@extends('layouts.app')
@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-4 col-md-6 col-sm-12 m-5">
            <div>
                <div>
                    <script nonce="csp_nonce" src="https://unpkg.com/@vkid/sdk@<3.0.0/dist-sdk/umd/index.js"></script>
                    <script nonce="csp_nonce" type="text/javascript">
                        if ('VKIDSDK' in window) {
                            const VKID = window.VKIDSDK;

                            VKID.Config.init({
                                app: {{config('services.vk.client_id')}},
                                redirectUrl: '{{config('services.vk.redirect')}}',
                                responseMode: VKID.ConfigResponseMode.Callback,
                                source: VKID.ConfigSource.LOWCODE,
                                scope: 'email,vkid.personal_info,friends,photos,offline' // запрашиваем email и личные данные
                            });


                            const oneTap = new VKID.OneTap();

                            oneTap.render({
                                container: document.currentScript.parentElement,
                                showAlternativeLogin: true
                            })
                                .on(VKID.WidgetEvents.ERROR, vkidOnError)
                                .on(VKID.OneTapInternalEvents.LOGIN_SUCCESS, function (payload) {
                                    const code = payload.code;
                                    const deviceId = payload.device_id;

                                    VKID.Auth.exchangeCode(code, deviceId)
                                        .then(vkidOnSuccess)
                                        .catch(vkidOnError);
                                });

                            function vkidOnSuccess(data) {
                                console.log('Получены данные от VK:', data);

                                fetch('/auth/vk/save', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        access_token: data.access_token,
                                        refresh_token: data.refresh_token,
                                        expires_in: data.expires_in,
                                        user_id: data.user_id,
                                        scope: data.scope
                                    })
                                })
                                    .then(response => {
                                        if (!response.ok) {
                                            throw new Error(`HTTP error! status: ${response.status}`);
                                        }
                                        return response.json();
                                    })
                                    .then(result => {
                                        console.log('Данные успешно сохранены:', result);
                                        // Перенаправляем пользователя после успешного сохранения
                                        window.location.href = '/profile';
                                    })
                                    .catch(error => {
                                        console.error('Ошибка при сохранении данных:', error);
                                        alert('Произошла ошибка при сохранении данных. Попробуйте снова.');
                                    });
                            }

                            function vkidOnError(error) {
                                // Обработка ошибки
                            }
                        }
                    </script>
                </div>
            </div>
        </div>
    </div>
@endsection
