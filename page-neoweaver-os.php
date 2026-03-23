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

        <footer class="neo-os-footer">
            <div class="neo-progress-container">
                <div class="neo-progress-bar"></div>
            </div>
            <div class="neo-os-footer-meta">
                <div class="neo-meta-item">
                    <span class="status-dot"></span><span class="neo-label"> SESSION:</span> 
                    <span class="neo-value neo-accent">ACTIVE</span>
                </div>
                <div class="neo-meta-item">
                    <span class="neo-label">SYNC:</span> 
                    <span id="sync-value" class="neo-value">98.4%</span>
                </div>
            </div>
        </footer>
    </div>
</div>

<?php get_footer(); ?>
