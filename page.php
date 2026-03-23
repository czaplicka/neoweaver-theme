<?php
/**
 * Standard Index Template for NeoWeave
 */
get_header();
?>

<main class="neo-terminal-container">
    <?php
    if ( have_posts() ) :
        while ( have_posts() ) : the_post();
    ?>
        <div class="neoweave-terminal"><div class="neo-terminal-header-meta">
            [DATA_STREAM: <?php echo strtoupper(get_post_type()); ?>] [ID: <?php the_ID(); ?>]
        </div>

        <h1 class="terminal-title"><?php the_title(); ?></h1></div>

        <div class="neo-content-area">
            <?php the_content(); ?>
        </div>

        <div class="neo-footer-log">
            [EOF_REACHED] <span class="neo-cursor"></span>
        </div>

        <hr class="neo-separator">

    <?php
        endwhile;
    else :
    ?>
        <div class="neo-content-area">
            <p class="neo-error">ERROR: NO_DATA_FOUND_AT_THIS_NODE</p>
        </div>
    <?php
    endif;
    ?>
</main>

<?php
get_footer();