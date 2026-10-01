document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form#parser-store-form');
    if (!form) return;

    form.addEventListener('submit', () => {
        const bod1 = document.querySelector('main');
        if (bod1) bod1.style.display = 'none';

        const markup = `<div class="preloader">
            <img src="../../images/loader/preloader.svg">
        </div>`;

        bod1.insertAdjacentHTML('beforebegin', markup);
    });
});
