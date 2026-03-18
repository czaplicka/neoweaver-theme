<?php
/**
 * Template Name: NeoWeave Front Terminal
 */

get_header();
?>

<div class="nw-terminal-root">
    <div class="nw-terminal-container">

        <span class="nw-label">Initializing_Architect_Protocol...</span>
        <h1 class="nw-main-title">NEOWEAVER: ARCHITECT_CORE</h1>

        <?php
        if ( have_posts() ) :
            while ( have_posts() ) : the_post();
                the_content();
            endwhile;
        endif;
        ?>

        <div class="nw-status-line">
            <span style="font-size: 0.8rem; opacity: 0.7;">REALITY_STABILITY_INDEX:</span><br>
            <div class="nw-bar-entropy">STABILITY: </div>
        </div>

        <div style="margin-top: 80px; font-size: 1rem; opacity: 0.5; font-family: monospace;">
            &gt; CONNECTION_SECURE // NDE_ENCRYPTION_ACTIVE<br>
            &gt; STANDBY_FOR_INPUT <span class="nw-cursor"></span>
        </div>

    </div>
</div>

<?php
get_footer();