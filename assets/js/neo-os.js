/**
 * NeoWeave OS - Terminal Progress Tracker
 * Zarządza paskiem postępu w dolnej części interfejsu systemowego.
 */
document.addEventListener('scroll', function() {
    const osContent = document.querySelector('.neo-os-content');
    const progressBar = document.querySelector(".neo-progress-bar");

    // Sprawdzamy, czy elementy istnieją w DOM, aby uniknąć błędów
    if (osContent && progressBar) {
        const winScroll = osContent.scrollTop;
        const height = osContent.scrollHeight - osContent.clientHeight;
        
        // Zabezpieczenie przed dzieleniem przez zero (pusta treść)
        if (height > 0) {
            const scrolled = (winScroll / height) * 100;
            progressBar.style.width = scrolled + "%";
            
            // Opcjonalne: Dodanie poświaty zależnej od postępu
            progressBar.style.boxShadow = `0 0 ${10 + (scrolled / 10)}px var(--neo-accent)`;
        }
    }
}, true); // 'true' jest kluczowe dla przechwytywania scrolla wewnątrz kontenera