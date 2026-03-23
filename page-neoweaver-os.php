<?php
/**
 * Template Name: NeoWeave OS Terminal
 * Description: Fullscreen NeoWeave terminal interface.
 */

get_header();
?>

<div class="neo-monitor-frame neo-crt">
    <div class="neo-scanlines"></div>
    <div class="neo-noise"></div>

    <div class="neo-terminal-wrapper">
        <header class="neo-os-header">
            <div class="neo-os-header-left">
                <span class="neo-status-dot neo-blink"></span> 
                <span class="neo-os-brand">NEO_WEAVE_OS_1.0.0</span>
            </div>
            <div class="neo-os-header-right">
                <span class="neo-node-id">SYSTEM_STREAM: <?php echo esc_html( strtoupper( get_the_title() ) ); ?></span>
            </div>
        </header>

        <main class="neo-os-content">
            <div class="neo-status-bar">
                <span class="neo-sys-path">
                    UPLINK_PATH: <span class="neo-accent">CORE://<?php echo esc_html( strtoupper( get_the_title() ) ); ?></span>
                </span>
            </div>

            <div class="neo-content-area">
                <?php
                while ( have_posts() ) :
                    the_post();
                    the_content();
                endwhile;
                ?>
            </div>
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

<?php 
get_footer(); 
?>