<?php
/**
 * Template Name: NeoWeave Page
 */

get_header();
?>

<div class="neo-terminal-root">
    <div class="neo-terminal-container">
        
        <div class="neo-terminal-header-meta">
            [DATA_NODE: PAGE] [ID: <?php the_ID(); ?>] [STATUS: READ_ONLY]
        </div>

        <article class="neo-content-area">
            <?php
            if ( have_posts() ) :
                while ( have_posts() ) : the_post();
                    // Wyświetlamy tytuł strony, jeśli nie jest to strona główna
                    if ( !is_front_page() ) : ?>
                        <h1 class="neo-title"><?php the_title(); ?></h1>
                    <?php endif;

                    the_content();
                endwhile;
            endif;
            ?>
        </article>

        <div class="neo-footer-log">
            [END_OF_PAGE_DATA] <span class="neo-cursor"></span>
        </div>

    </div>
</div>

<?php
get_footer();