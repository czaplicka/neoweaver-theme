<?php
get_header();
?>

<main class="neoweave-terminal">
    <div class="terminal-header">
        [CHANNEL_ROUTING] [CATEGORY_NODE]
    </div>

    <div class="status-bar">
        CATEGORY: <?php echo esc_html( single_cat_title( '', false ) ); ?> //
        TOTAL_LOGS: <?php echo (int) $wp_query->found_posts; ?>
    </div>

    <?php if ( have_posts() ) : ?>

        <?php while ( have_posts() ) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class( 'log-entry' ); ?>>
                <div class="entry-meta">
                    <div>[DATE: <?php echo esc_html( get_the_date( 'Y-m-d' ) ); ?>]</div>
                </div>
                <div class="entry-main">
                    <h2 class="entry-title">
                        <a href="<?php the_permalink(); ?>">
                            &gt; <?php the_title(); ?>
                        </a>
                    </h2>
                    <div class="entry-summary">
                        <?php the_excerpt(); ?>
                    </div>
                </div>
                <div class="entry-actions">
                    <a class="access-link" href="<?php the_permalink(); ?>">[ OPEN_LOG ]</a>
                </div>
            </article>
        <?php endwhile; ?>

        <div class="footer-log">
            [CHANNEL_END] [NO_FURTHER_ENTRIES]
        </div>

        <div class="nav-links">
            <div class="nav-previous"><?php next_posts_link( '&lt; OLDER_LOGS' ); ?></div>
            <div class="nav-next"><?php previous_posts_link( 'NEWER_LOGS &gt;' ); ?></div>
        </div>

    <?php else : ?>

        <div class="terminal-section-title">EMPTY_CHANNEL</div>
        <p>No logs have been assigned to this category yet.</p>

    <?php endif; ?>
</main>

<?php
get_footer();
