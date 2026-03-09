
// Класс лайтбокса (исправленный)
class Lightbox {
    constructor() {
        this.lightbox = document.getElementById('lightbox');
        this.img = document.getElementById('lightbox-img');
        this.closeBtn = document.querySelector('.lightbox-close');
        this.prevBtn = document.querySelector('.lightbox-prev');
        this.nextBtn = document.querySelector('.lightbox-next');

        this.images = [];
        this.currentIndex = 0;

        this.bindEvents();
    }

    bindEvents() {
        this.closeBtn.addEventListener('click', () => this.hide());

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') this.hide();
        });

        this.prevBtn.addEventListener('click', () => {
            this.showImage(this.currentIndex - 1);
        });

        this.nextBtn.addEventListener('click', () => {
            this.showImage(this.currentIndex + 1);
        });
    }

    show(images, index) {
        // Проверяем валидность данных
        if (!images || images.length === 0 || index < 0 || index >= images.length) {
            console.error('Invalid images or index in Lightbox.show()');
            return;
        }

        this.images = images;
        this.currentIndex = index;

        // Убедимся, что элемент существует
        const targetImage = this.images[this.currentIndex];
        if (!targetImage || !targetImage.src) {
            console.error('Image source is missing:', targetImage);
            return;
        }

        this.img.src = targetImage.src;
        this.lightbox.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    hide() {
        this.lightbox.classList.add('hidden');
        document.body.style.overflow = '';
    }

    showImage(newIndex) {
        if (newIndex < 0 || newIndex >= this.images.length) return;

        this.currentIndex = newIndex;
        const targetImage = this.images[this.currentIndex];

        if (targetImage && targetImage.src) {
            this.img.src = targetImage.src;
        } else {
            console.error('Cannot load image at index:', newIndex);
        }
    }
}

// Инициализация лайтбокса
const lightbox = new Lightbox();

// Обработчики кликов на изображениях
document.querySelectorAll('.item-carousel').forEach(img => {
    img.addEventListener('click', () => {
        const carouselWrapper = img.closest('.carousel-wrapper');
        if (!carouselWrapper) {
            console.error('Carousel wrapper not found for image:', img);
            return;
        }

        const images = carouselWrapper.querySelectorAll('.item-carousel');
        const index = parseInt(img.dataset.index, 10);

        if (isNaN(index) || index < 0 || index >= images.length) {
            console.error('Invalid index:', index);
            return;
        }

        lightbox.show(images, index);
    });
});