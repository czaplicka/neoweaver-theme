<?php
get_header();
?>

<main class="neoweave-terminal">
    <?php
    if ( have_posts() ) :
        while ( have_posts() ) : the_post();
    ?>
        <h1 class="terminal-title"><?php the_title(); ?></h1>
        <div class="terminal-content">
            <?php the_content(); ?>
        </div>
    <?php
        endwhile;
    endif;
    ?>
</main>

<?php
get_footer();
