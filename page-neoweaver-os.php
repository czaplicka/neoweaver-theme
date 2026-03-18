<?php
/**
 * Template Name: NeoWeave OS Terminal
 * Description: Fullscreen NeoWeave terminal interface.
 */

get_header();
?>

<div class="monitor-frame neoweave-os">
    <div class="crt-overlay"></div>
    <div class="scanlines"></div>
    <div class="noise"></div>

    <div class="terminal-wrapper">
        <header class="os-header">
            <div class="os-header-left">
                <span class="blink-dot"></span> NEO_WEAVE_OS
            </div>
            <div class="os-header-right">
                <span>SYSTEM NODE: <?php echo esc_html( strtoupper( get_the_title() ) ); ?></span>
            </div>
        </header>

        <main class="os-content">
            <div class="status-bar">
                <span class="sys-path">
                    UPLINK_PATH: CORE://<?php echo esc_html( strtoupper( get_the_title() ) ); ?>
                </span>
            </div>

            <?php
            while ( have_posts() ) :
                the_post();
                the_content();
            endwhile;
            ?>
        </main>

        <footer class="os-footer">
            <div class="progress-container">
                <div class="progress-bar"></div>
            </div>
            <div class="os-footer-meta">
                <span>SESSION_ACTIVE</span>
            </div>
        </footer>
    </div>
</div>

<?php get_footer(); ?>