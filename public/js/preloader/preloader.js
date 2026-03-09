const bod1 = document.querySelector('main');
const markup = `<div class="preloader" >
            <img src="../../images/loader/preloader.svg" >
        </div>`;
bod1.style.display = 'none';
bod1.insertAdjacentHTML('beforebegin', markup);
document.addEventListener("DOMContentLoaded", () => {

    setTimeout(() => {
        document.querySelector('.preloader').remove();
        bod1.style.display = 'block'
    }, 1000);
});