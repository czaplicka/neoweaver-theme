<?php
/**
 * Template Name: NeoWeave Front Terminal
 */

get_header();
?>

<div class="neo-terminal-container neo-content-area">

  <div class="neo-terminal-status-top">
    <div class="terminal-header">INITIALIZING_ARCHITECT_PROTOCOL</div>
  </div>

  <h1 class="terminal-title">NEOWEAVER: ARCHITECT_CORE<span class="terminal-cursor"></span></h1>

        <div class="neo-content-area">
            <?php
            if ( have_posts() ) :
                while ( have_posts() ) : the_post();
                    the_content();
                endwhile;
            endif;
            ?>
        </div>

<div class="neo-stability-wrapper">
  STABILITY:
  <div class="neo-stability-bar">
    <div class="neo-stability-bar-fill"></div>
    <div class="neo-stability-bar-glitch"></div>
  </div>
</div>

        <div class="neo-terminal-footer-meta">
            <p>&gt; CONNECTION_SECURE // NDE_ENCRYPTION_ACTIVE</p>
            <p>&gt; STANDBY_FOR_INPUT <span class="neo-cursor"></span></p>
            <span class="neo-tag">NODE: CORE</span>
        </div>
		

        <?php if ( is_active_sidebar( 'terminal-sidebar' ) ) : ?>
            <aside id="secondary" class="neo-sidebar">
                <?php dynamic_sidebar( 'terminal-sidebar' ); ?>
            </aside>
        <?php endif; ?>
</div>

<?php
get_footer();