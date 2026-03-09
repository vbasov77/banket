@extends('layouts.app')
@section('content')

    <style>
        .carousel-wrapper {
            position: relative;
            margin: 20px auto;
            max-width: 100%;
            width: 100%; /* Явно задаём ширину */
        }

        .carousel {
            display: block;
            overflow: auto;
            scroll-behavior: smooth;
            scrollbar-width: none;
            -ms-overflow-style: none;
            width: 100%; /* Заполняем контейнер */
            height: 240px; /* Фиксируем высоту */
        }

        .carousel::-webkit-scrollbar {
            height: 0;
        }

        .carousel-prev,
        .carousel-next {
            display: none;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            background: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            /* Добавляем явные размеры */
        }

        .carousel-prev {
            left: -20px;
        }

        .carousel-next {
            right: -20px;
        }

        .carousel-content {
            display: grid;
            grid-auto-flow: column;
            grid-gap: 16px;
            padding: 0 60px; /* Отступы для кнопок */
            margin: 0 auto;
            min-width: 0; /* Важно для grid */
        }

        .carousel-item {
            display: block;
            min-width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            flex-shrink: 0; /* Запрещаем сжатие */
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        .parallax {
            height: 60vh;
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            perspective: 1px;
        }
    </style>

    <!-- Ваш HTML -->
    <div style="background-image: url('{{$images[0]->path}}');" class="parallax"></div>

    <section class="py-5">
        <div class="container px-4 px-lg-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="text-center mb-5">
                        <h1>{{ $obj->name_obj }}</h1>
                    </div>

                    <!-- Карусель 1 -->
                    <div class="carousel-wrapper">
                        <div class="carousel">
                            <div class="carousel-content">
                                @foreach ($images as $image)
                                    <img
                                            class="carousel-item"
                                            src="{{ $image->path }}"
                                            alt="{{ $obj->name_obj }}"
                                    >
                                @endforeach
                            </div>
                        </div>
                        <button class="carousel-prev">
                            <!-- SVG -->
                        </button>
                        <button class="carousel-next">
                            <!-- SVG -->
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        class Carousel {
            constructor(wrapper) {
                this.wrapper = wrapper;
                this.carousel = wrapper.querySelector('.carousel');
                this.content = wrapper.querySelector('.carousel-content');
                this.prevBtn = wrapper.querySelector('.carousel-prev');
                this.nextBtn = wrapper.querySelector('.carousel-next');

                this.gap = 16;
                this.width = this.carousel.offsetWidth;

                // Инициализация после полной загрузки DOM
                if (document.readyState === 'complete') {
                    this.init();
                } else {
                    window.addEventListener('load', () => this.init());
                }
            }

            init() {
                // Проверяем наличие элементов
                if (!this.carousel || !this.content) return;

                // Обновляем ширину при ресайзе
                window.addEventListener('resize', () => {
                    this.width = this.carousel.offsetWidth;
                    this.updateButtons();
                });

                // Обработчики кнопок
                this.nextBtn?.addEventListener('click', () => {
                    this.carousel.scrollBy(this.width + this.gap, 0);
                    this.updateButtons();
                });

                this.prevBtn?.addEventListener('click', () => {
                    this.carousel.scrollBy(-(this.width + this.gap), 0);
                    this.updateButtons();
                });

                // Наблюдение за скроллом
                this.carousel.addEventListener('scroll', () => {
                    this.updateButtons();
                });

                // Первоначальное состояние
                setTimeout(() => this.updateButtons(), 100); // Даём время на рендеринг
            }

            updateButtons() {
                const scrollLeft = this.carousel.scrollLeft;
                const contentWidth = this.content.scrollWidth;
                const carouselWidth = this.width;

                // Кнопка «Назад»
                if (scrollLeft <= this.gap) {
                    this.prevBtn.style.display = 'none';
                } else {
                    this.prevBtn.style.display = 'flex';
                }

                // Кнопка «Вперёд»
                if (scrollLeft + carouselWidth + this.gap >= contentWidth) {
                    this.nextBtn.style.display = 'none';
                } else {
                    this.nextBtn.style.display = 'flex';
                }
            }
        }

        // Инициализация всех каруселей
        document.querySelectorAll('.carousel-wrapper').forEach(wrapper => {
            new Carousel(wrapper);
        });
    </script>


@endsection
