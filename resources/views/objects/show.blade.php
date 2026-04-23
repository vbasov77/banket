@extends('layouts.app')
@section('content')

    <link href="{{ asset('css/obj/show_obj.css') }}" rel="stylesheet">

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
