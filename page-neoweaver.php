<?php
/**
 * Template Name: NeoWeave Page
 */

get_header();
?>

<div class="nw-terminal-root">
    <div class="nw-terminal-container">
        <?php
        if ( have_posts() ) :
            while ( have_posts() ) : the_post();
                the_content();
            endwhile;
        endif;
        ?>
    </div>
</div>

<?php
get_footer();