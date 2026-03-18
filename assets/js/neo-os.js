<script>
document.addEventListener('scroll', function() {
    const winScroll = document.querySelector('.os-content').scrollTop;
    const height = document.querySelector('.os-content').scrollHeight - document.querySelector('.os-content').clientHeight;
    const scrolled = (winScroll / height) * 100;
    const progressBar = document.querySelector(".progress-bar");
    if(progressBar) {
        progressBar.style.width = scrolled + "%";
    }
}, true);
