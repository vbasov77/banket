document.addEventListener('DOMContentLoaded', () => {
    const parallaxContainer = document.querySelector('.parallax-container');
    const parallaxBg = document.querySelector('.parallax-bg');
    const parallaxTitle = document.querySelector('.parallax-title'); // Получаем элемент названия

    if (!parallaxContainer || !parallaxBg) return;

    let isAnimating = false;

    function updateParallax() {
        if (isAnimating) return;
        isAnimating = true;

        requestAnimationFrame(() => {
            const rect = parallaxContainer.getBoundingClientRect();
            const scrollY = window.scrollY;
            const containerTop = rect.top + scrollY;

            // Вычисляем смещение (коэффициент 0.3 снижает эффект)
            const offset = (scrollY - containerTop) * 0.3;

            // Применяем смещение к фону
            parallaxBg.style.transform = `
                translateZ(-5px)
                scale(1.2)
                translateY(${offset}px)
            `;

            // Применяем менее интенсивное смещение к названию (0.15)
            if (parallaxTitle) {
                parallaxTitle.style.transform = `translateY(${offset * 0.15}px)`;
            }

            isAnimating = false;
        });
    }

    // Оптимизируем обработчики
    window.addEventListener('scroll', updateParallax, { passive: true });
    window.addEventListener('resize', updateParallax);

    // Первый вызов
    updateParallax();
});
