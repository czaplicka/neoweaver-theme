<script>
document.addEventListener('DOMContentLoaded', () => {
    let currentSync = 98.4;
    const syncEl = document.getElementById('sync-value');
    const clockEl = document.getElementById('terminal-clock');

    const updateClock = () => {
        const now = new Date();
        if (clockEl) clockEl.textContent = now.toTimeString().split(' ')[0];
    };
    setInterval(updateClock, 1000);
    updateClock();

    const updateSync = () => {
        if (!syncEl) return;
        currentSync += (Math.random() - 0.5) * 0.4;
        if (currentSync > 99.9) currentSync = 99.9;
        if (currentSync < 88.0) currentSync = 88.0;
        syncEl.textContent = currentSync.toFixed(1) + '%';

        if (currentSync < 92.0) {
            syncEl.classList.add('sync-critical', 'jitter-active');
        } else {
            syncEl.classList.remove('sync-critical', 'jitter-active');
        }
    };
    setInterval(updateSync, 1500);
});
