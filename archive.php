<?php
get_header();
?>

<main class="neoweave-terminal">
    <div class="terminal-header">
        [ARCHIVE_SCAN] 
        [TYPE: <?php echo esc_html( strtoupper( get_post_type() ) ); ?>] 
        [QUERY: <?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?>]
    </div>

    <div class="status-bar">
        RESULTS: <?php echo (int) $wp_query->found_posts; ?> // 
        NODE: ARCHIVE_INDEX
    </div>

    <?php if ( have_posts() ) : ?>

        <?php while ( have_posts() ) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class( 'log-entry' ); ?>>
                <div class="entry-meta">
                    <div>[ID: <?php the_ID(); ?>]</div>
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
                    <div class="entry-tags">
                        <?php the_category( ' ' ); ?>
                        <?php the_tags( ' // ', ' ', '' ); ?>
                    </div>
                </div>
                <div class="entry-actions">
                    <a class="access-link" href="<?php the_permalink(); ?>">[ ACCESS_LOG ]</a>
                </div>
            </article>
        <?php endwhile; ?>

        <div class="footer-log">
            [ARCHIVE_SCAN_COMPLETE] [PAGE: <?php echo max( 1, get_query_var( 'paged' ) ); ?>]
        </div>

        <div class="nav-links">
            <div class="nav-previous"><?php next_posts_link( '&lt; OLDER_LOGS' ); ?></div>
            <div class="nav-next"><?php previous_posts_link( 'NEWER_LOGS &gt;' ); ?></div>
        </div>

    <?php else : ?>

        <div class="terminal-section-title">NO_LOGS_FOUND</div>
        <p>No entries detected in this archive node.</p>

    <?php endif; ?>
</main>

<?php
get_footer();
