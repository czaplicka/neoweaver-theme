<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Swiper === 'undefined') return;

    const stream = document.querySelector('.neo-data-stream');
    if (!stream) return;

    new Swiper('.neo-data-stream', {
        loop: true,
        autoplay: { delay: 0 },
        speed: 10000,
        slidesPerView: 'auto',
        freeMode: true
    });
});
