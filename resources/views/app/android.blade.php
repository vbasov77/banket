@extends('layouts.app', ['title' => 'Скачать FeastBoom для Android'])

@push('styles')
    <style>
        .app-page {
            min-height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .app-card {
            max-width: 460px;
            width: 100%;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 40px 32px;
            text-align: center;
        }

        .app-icon {
            width: 96px;
            height: 96px;
            border-radius: 24px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, #007bff, #00c6ff);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
        }

        .app-title {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 8px;
        }

        .app-subtitle {
            color: #666;
            font-size: 15px;
            margin: 0 0 28px;
        }

        .app-info {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-bottom: 28px;
            flex-wrap: wrap;
        }

        .app-info-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .app-info-item .label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .app-info-item .value {
            font-size: 16px;
            font-weight: 600;
        }

        .app-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 16px 24px;
            font-size: 17px;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .app-btn-primary {
            background: #007bff;
            color: #fff;
        }

        .app-btn-primary:hover {
            background: #0056b3;
        }

        .app-btn-primary:disabled {
            background: #a0c4ff;
            cursor: not-allowed;
        }

        .app-status {
            margin-top: 20px;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 14px;
            display: none;
        }

        .app-status.show { display: block; }
        .app-status.info { background: #e7f1ff; color: #0056b3; }
        .app-status.success { background: #e6f9ec; color: #1a7d3a; }
        .app-status.warning { background: #fff8e6; color: #996600; }

        .progress-bar {
            margin-top: 16px;
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
            display: none;
        }

        .progress-bar.show { display: block; }

        .progress-fill {
            height: 100%;
            background: #007bff;
            width: 0%;
            transition: width 0.3s;
            border-radius: 3px;
        }

        .app-hint {
            margin-top: 20px;
            font-size: 13px;
            color: #999;
            line-height: 1.5;
        }

        @media (max-width: 480px) {
            .app-card { padding: 32px 20px; }
            .app-title { font-size: 24px; }
        }
    </style>
@endpush

@section('content')
    <section class="app-page">
        <div class="app-card">
            <div class="app-icon"><img src="{{asset("icons/fb.svg")}}"></div>
            <h1 class="app-title">FeastBoom</h1>
            <p class="app-subtitle">Простой мессенджер FeastBoom</p>

            <div class="app-info">
                <div class="app-info-item">
                    <span class="label">Версия</span>
                    <span class="value">{{ $apkVersion }}</span>
                </div>
                <div class="app-info-item">
                    <span class="label">Размер</span>
                    <span class="value">{{ $apkSize }}</span>
                </div>
                <div class="app-info-item">
                    <span class="label">ОС</span>
                    <span class="value">Android 9+</span>
                </div>
            </div>

            <button type="button"
                    id="installBtn"
                    class="app-btn app-btn-primary"
                    onclick="checkAndInstall()">
                <span id="btnText">Обновить / Установить</span>
            </button>

            <div id="statusBox" class="app-status"></div>

            <div id="progressBar" class="progress-bar">
                <div id="progressFill" class="progress-fill"></div>
            </div>

            <p class="app-hint">
                Если приложение уже установлено, оно обновится автоматически.<br>
                Если нет — начнётся загрузка APK-файла.
            </p>
        </div>
    </section>

    @push('scripts')
        <script>
            const APK_URL = '{{ $apkUrl }}';
            const DEEP_LINK = 'feastboom://open';
            const PLAY_STORE = 'https://play.google.com/store/apps/details?id=com.feastboom.app';

            function setStatus(text, type) {
                const box = document.getElementById('statusBox');
                box.textContent = text;
                box.className = 'app-status show ' + (type || 'info');
            }

            function setBtnText(text, disabled) {
                document.getElementById('btnText').textContent = text;
                document.getElementById('installBtn').disabled = !!disabled;
            }

            async function checkAndInstall() {
                setBtnText('Проверяем...', true);
                setStatus('Проверяем, установлено ли приложение...', 'info');

                const isInstalled = await checkAppInstalled();

                if (isInstalled) {
                    // Приложение установлено — открываем его
                    setStatus('Приложение уже установлено. Открываем...', 'success');
                    window.location.href = DEEP_LINK;
                    setBtnText('Обновить', false);
                } else {
                    // Не установлено — скачиваем APK
                    setStatus('Приложение не найдено. Начинаем загрузку...', 'warning');
                    downloadApk();
                }
            }

            function checkAppInstalled() {
                return new Promise((resolve) => {
                    let resolved = false;

                    // Пытаемся открыть deep link
                    const iframe = document.createElement('iframe');
                    iframe.style.display = 'none';
                    iframe.src = DEEP_LINK;

                    const timeout = setTimeout(() => {
                        if (!resolved) {
                            resolved = true;
                            iframe.remove();
                            resolve(false);
                        }
                    }, 1500);

                    // Если приложение есть — страница теряет фокус
                    window.addEventListener('blur', function onBlur() {
                        if (!resolved) {
                            resolved = true;
                            clearTimeout(timeout);
                            iframe.remove();
                            window.removeEventListener('blur', onBlur);
                            resolve(true);
                        }
                    }, { once: true });

                    document.body.appendChild(iframe);
                });
            }

            function downloadApk() {
                setBtnText('Загрузка...', true);
                const bar = document.getElementById('progressBar');
                const fill = document.getElementById('progressFill');
                bar.classList.add('show');

                // Простой способ — редирект на скачивание
                // Прогресс-бар имитируется, т.к. XHR для APKblobs может быть тяжёлым
                let progress = 0;
                const timer = setInterval(() => {
                    progress += Math.random() * 15;
                    if (progress >= 90) {
                        progress = 90;
                        clearInterval(timer);
                    }
                    fill.style.width = progress + '%';
                }, 300);

                // Запускаем скачивание через скрытую ссылку
                const a = document.createElement('a');
                a.href = '{{ route('app.download') }}';
                a.download = 'feastboom.apk';
                document.body.appendChild(a);
                a.click();
                a.remove();

                // Через 2 секунды показываем финальный статус
                setTimeout(() => {
                    clearInterval(timer);
                    fill.style.width = '100%';
                    setStatus('APK загружен. Откройте файл и установите приложение.', 'success');
                    setBtnText('Установить снова', false);
                    setTimeout(() => {
                        bar.classList.remove('show');
                    }, 3000);
                }, 2000);
            }

            // Проверяем при загрузке страницы
            (async function initCheck() {
                const isInstalled = await checkAppInstalled();
                if (isInstalled) {
                    setBtnText('Обновить', false);
                    setStatus('FeastBoom установлен. Нажмите «Обновить» для проверки новой версии.', 'success');
                }
            })();
        </script>
    @endpush
@endsection
