@if(!session('cookie_accepted'))
    <style>
        .cookie-banner {
            position: fixed !important;
            bottom: 20px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            background: #333 !important;
            color: #fff !important;
            padding: 14px 20px !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2) !important;
            z-index: 9999 !important;
            display: flex !important;
            align-items: center !important;
            gap: 16px !important;
            max-width: 90% !important;
            text-align: center !important;
        }

        .btn-cookie-accept {
            background: #fff !important;
            color: #000 !important; /* белая кнопка, чёрный текст */
            border: none !important;
            padding: 6px 16px !important;
            border-radius: 6px !important;
            cursor: pointer !important;
            font-weight: 600 !important;
        }

        /* Адаптив для мобильных */
        @media (max-width: 480px) {
            .cookie-banner {
                max-width: 95% !important;
                padding: 12px 16px !important;
                flex-direction: column; /* на узких экранах текст и кнопка друг под другом */
                align-items: stretch; /* кнопка на всю ширину блока */
                gap: 10px !important;
                width: 100%;
            }

            .btn-cookie-accept {
                width: 100%;
                text-align: center;
            }

            /* Опционально: чуть уменьшаем шрифт, если текст не влезает */
            .cookie-banner span {
                font-size: 0.85rem !important;
            }
        }
    </style>

    <div class="cookie-banner" id="cookie-banner">
        <span>Оставаясь на сайте, вы соглашаетесь на обработку ваших персональных данных (файлов cookie).</span>
        <button type="button" id="accept-cookies" class="btn-cookie-accept">Принять</button>
    </div>

    <script>
        (function () {
            const btn = document.getElementById('accept-cookies');
            if (!btn) return;

            btn.addEventListener('click', function () {
                fetch('{{ route('cookie.accept') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                    .then(res => {
                        if (res.ok) {
                            const banner = document.getElementById('cookie-banner');
                            if (banner) banner.remove();
                        }
                    });
            });
        })();
    </script>
@endif
