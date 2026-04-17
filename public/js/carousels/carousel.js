class Carousel {
    constructor(wrapper) {
        this.wrapper = wrapper;
        this.carousel = wrapper.querySelector('.carousel');
        this.content = wrapper.querySelector('.carousel-content');
        this.prevBtn = wrapper.querySelector('.carousel-prev');
        this.nextBtn = wrapper.querySelector('.carousel-next');

        this.gap = 16;
        this.slideWidth = 0;
        this.slidesToScroll = 2;
        this.observer = null;

        // Свойства для drag-and-drop
        this.isDragging = false;
        this.startX = 0;
        this.scrollLeft = 0;

        this.init();
    }

    handleMouseDown(e) {
        e.preventDefault();
        this.isDragging = true;
        this.startX = e.pageX - this.carousel.offsetLeft;
        this.scrollLeft = this.carousel.scrollLeft;
        this.carousel.style.cursor = 'grabbing';
    }

    handleMouseMove(e) {
        if (!this.isDragging) return;
        e.preventDefault();

        const x = e.pageX - this.carousel.offsetLeft;
        const walk = (x - this.startX) * 2; // Коэффициент чувствительности
        this.carousel.scrollLeft = this.scrollLeft - walk;
    }

    handleMouseUp() {
        this.isDragging = false;
        this.carousel.style.cursor = 'grab';
    }

    handleMouseLeave() {
        this.isDragging = false;
        this.carousel.style.cursor = 'grab';
    }

    showControls() {
        if (this.nextBtn) {
            this.nextBtn.classList.add('visible');
        }
        // Если нужно показывать и «Назад» при наведении:
        // if (this.prevBtn) {
        //     this.prevBtn.classList.add('visible');
        // }
    }

    hideControlsOnWrapperLeave(e) {
        const related = e.relatedTarget;
        if (related && (related === this.prevBtn || related === this.nextBtn)) {
            return;
        }
        this.hideControls();
    }

    hideControls() {
        if (this.nextBtn) {
            this.nextBtn.classList.remove('visible');
        }
        if (this.prevBtn) {
            const scrollLeft = this.carousel.scrollLeft;
            if (scrollLeft > 0) {
                this.prevBtn.classList.add('visible');
            } else {
                this.prevBtn.classList.remove('visible');
            }
        }
    }



    init() {
        this.waitForImages().then(() => {
            setTimeout(() => {
                this.recalculateSlideWidth();
                // Гарантируем минимальную ширину слайда
                if (this.slideWidth < 200) {
                    this.slideWidth = 266;
                }
                this.updateButtons();

                if (this.prevBtn) {
                    this.prevBtn.addEventListener('click', () => this.prev());
                }
                if (this.nextBtn) {
                    this.nextBtn.addEventListener('click', () => this.next());
                }

                window.addEventListener('resize', () => {
                    this.recalculateSlideWidth();
                    if (this.slideWidth < 200) this.slideWidth = 266;
                    this.updateButtons();
                });

                this.carousel.addEventListener('scroll', () => this.updateButtons());
                this.setupMutationObserver();
                // Обработчики для drag-and-drop
                this.carousel.addEventListener('mousedown', this.handleMouseDown.bind(this));
                this.carousel.addEventListener('mousemove', this.handleMouseMove.bind(this));
                document.addEventListener('mouseup', this.handleMouseUp.bind(this));
                document.addEventListener('mouseleave', this.handleMouseLeave.bind(this));

                this.wrapper.addEventListener('mouseenter', () => this.showControls());
                this.wrapper.addEventListener('mouseleave', (e) => this.hideControlsOnWrapperLeave(e));

                if (this.prevBtn) {
                    this.prevBtn.addEventListener('mouseenter', (e) => {
                        e.stopPropagation();
                        this.showControls();
                    });
                }
                if (this.nextBtn) {
                    this.nextBtn.addEventListener('mouseenter', (e) => {
                        e.stopPropagation();
                        this.showControls();
                    });
                }

            }, 50); // Уменьшили задержку для более быстрого отклика
        });
    }

    setupMutationObserver() {
        if (this.observer) {
            this.observer.disconnect();
        }

        this.observer = new MutationObserver(() => {
            // Даём время на завершение изменений DOM
            setTimeout(() => {
                this.recalculateSlideWidth();
                if (this.slideWidth < 200) this.slideWidth = 266;
                this.updateButtons();
            }, 150);
        });

        if (this.content) {
            this.observer.observe(this.content, {
                childList: true,
                subtree: true,
                attributes: true
            });
        }
    }

    waitForImages() {
        const images = this.content?.querySelectorAll('img') || [];
        if (images.length === 0) return Promise.resolve();

        const promises = Array.from(images).map(img => {
            return new Promise(resolve => {
                if (img.complete) {
                    resolve();
                } else {
                    img.addEventListener('load', resolve);
                    img.addEventListener('error', resolve);
                }
            });
        });
        return Promise.all(promises);
    }

    recalculateSlideWidth() {
        const firstItem = this.content?.querySelector('.item-carousel');

        if (firstItem) {
            const style = window.getComputedStyle(firstItem);
            const marginLeft = parseFloat(style.marginLeft) || 0;
            const marginRight = parseFloat(style.marginRight) || 0;

            // Основной расчёт: ширина элемента + gap + margin
            this.slideWidth = firstItem.offsetWidth + this.gap + marginRight;


        } else {
            this.slideWidth = 266; // Резервное значение
        }
    }

    prev() {
        if (!this.carousel || !this.content) return;

        if (this.slideWidth <= 10) {
            this.recalculateSlideWidth();
            if (this.slideWidth <= 10) {
                this.slideWidth = 266;
            }
        }

        const currentScroll = this.carousel.scrollLeft;
        const newScroll = Math.max(0, currentScroll - this.slideWidth * this.slidesToScroll);

        this.carousel.scrollTo({
            left: newScroll,
            behavior: 'smooth'
        });

        setTimeout(() => this.updateButtons(), 300);
    }

    next() {
        if (!this.carousel || !this.content) return;

        if (this.slideWidth <= 10) {
            this.recalculateSlideWidth();
            if (this.slideWidth <= 10) {
                this.slideWidth = 266;
            }
        }

        const currentScroll = this.carousel.scrollLeft;
        const newScroll = currentScroll + this.slideWidth * this.slidesToScroll;

        this.carousel.scrollTo({
            left: newScroll,
            behavior: 'smooth'
        });

        setTimeout(() => this.updateButtons(), 300);
    }

    updateButtons() {
        if (!this.carousel || !this.content) return;

        const scrollLeft = this.carousel.scrollLeft;
        const contentWidth = this.content.scrollWidth;
        const carouselWidth = this.carousel.offsetWidth;

        if (this.prevBtn) {
            const shouldBeVisiblePrev = scrollLeft > 0;
            this.prevBtn.classList.toggle('visible', shouldBeVisiblePrev);
        }

        if (this.nextBtn) {
            const isAtEnd = scrollLeft + carouselWidth >= contentWidth - 5;
            this.nextBtn.classList.toggle('visible', !isAtEnd);
        }
    }

    reinitialize() {
        // Полный пересчёт с проверкой минимальной ширины
        this.recalculateSlideWidth();
        if (this.slideWidth < 200) {
            this.slideWidth = 266;
        }
        this.updateButtons();
    }

    destroy() {
        if (this.observer) {
            this.observer.disconnect();
        }
        window.removeEventListener('resize', this.updateButtons.bind(this));
        this.carousel?.removeEventListener('scroll', this.updateButtons.bind(this));
    }
}

// Инициализация каруселей после загрузки DOM
document.addEventListener('DOMContentLoaded', () => {
    const wrappers = document.querySelectorAll('.carousel-wrapper');
    wrappers.forEach(wrapper => {
        wrapper._carouselInstance = new Carousel(wrapper);
    });
});

// Функция для переинициализации всех каруселей (вызывать после авторизации)
function reinitializeCarousels() {
    document.querySelectorAll('.carousel-wrapper').forEach(wrapper => {
        if (wrapper._carouselInstance) {
            wrapper._carouselInstance.reinitialize();
        }
    });
}
