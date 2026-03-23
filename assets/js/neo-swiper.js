/**
 * NeoWeave - Infinite Data Stream Ticker
 * Tworzy płynny, poziomy pasek przesuwających się danych/tagów.
 */
document.addEventListener('DOMContentLoaded', function () {
    // Sprawdzenie czy Swiper jest załadowany (zależność w WP)
    if (typeof Swiper === 'undefined') {
        console.warn('NEO_WEAVE_ERROR: Swiper.js not detected. Data stream aborted.');
        return;
    }

    const streamContainer = document.querySelector('.neo-data-stream');
    if (!streamContainer) return;

    // Inicjalizacja strumienia danych
    const dataStream = new Swiper('.neo-data-stream', {
        loop: true,
        autoplay: {
            delay: 0,
            disableOnInteraction: false, // Strumień nie staje po dotknięciu
        },
        speed: 8000, // Zmniejszyłem lekko dla lepszej czytelności "danych"
        slidesPerView: 'auto',
        spaceBetween: 30, // Odstęp między "pakietami danych"
        freeMode: true,
        allowTouchMove: false, // Gracz nie może "przesunąć" strumienia ręcznie
        grabCursor: false,
    });

    // Opcjonalnie: Zatrzymanie przy błędzie Entropy (jeśli masz taką zmienną w JS)
    // window.addEventListener('entropy_critical', () => dataStream.autoplay.stop());
});