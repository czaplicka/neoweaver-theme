<?php
get_header();
?>

<main class="neo-terminal-container neo-archive">
    <div class="neo-terminal-header-meta">
        [ARCHIVE_SCAN] 
        [TYPE: <?php echo esc_html( strtoupper( get_post_type() ) ); ?>] 
        [QUERY: <?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?>]
    </div>

    <div class="neo-status-bar">
        RESULTS_FOUND: <span class="neo-accent"><?php echo (int) $wp_query->found_posts; ?></span> // 
        NODE: ARCHIVE_INDEX
    </div>

    <?php if ( have_posts() ) : ?>

        <div class="neo-log-list">
            <?php while ( have_posts() ) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( 'neo-log-entry' ); ?>>
                    
                    <div class="neo-entry-meta">
                        <span class="neo-id-tag">[ID: <?php the_ID(); ?>]</span>
                        <span class="neo-date-tag">[DATE: <?php echo esc_html( get_the_date( 'Y-m-d' ) ); ?>]</span>
                    </div>

                    <div class="neo-entry-main">
                        <h2 class="neo-entry-title">
                            <a href="<?php the_permalink(); ?>">
                                <span class="neo-arrow">></span> <?php the_title(); ?>
                            </a>
                        </h2>
                        
                        <div class="neo-entry-summary neo-content-area">
                            <?php the_excerpt(); ?>
                        </div>

                        <div class="neo-entry-tags">
                            <span class="neo-label">CATEGORIES:</span> <?php the_category( ' ' ); ?>
                            <?php if(has_tag()) : ?>
                                <span class="neo-label">// TAGS:</span> <?php the_tags( '', ' ', '' ); ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="neo-entry-actions">
                        <a class="neo-access-link" href="<?php the_permalink(); ?>">[ ACCESS_LOG ]</a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

        <div class="neo-footer-log">
            [ARCHIVE_SCAN_COMPLETE] [PAGE: <?php echo max( 1, get_query_var( 'paged' ) ); ?>] 
            <span class="neo-cursor"></span>
        </div>

        <nav class="neo-nav-links">
            <div class="neo-nav-previous">
                <?php next_posts_link( '&lt; LOAD_OLDER_DATA' ); ?>
            </div>
            <div class="neo-nav-next">
                <?php previous_posts_link( 'LOAD_NEWER_DATA &gt;' ); ?>
            </div>
        </nav>

    <?php else : ?>

        <div class="neo-content-area">
            <h2 class="neo-title">NO_LOGS_FOUND</h2>
            <p class="neo-error">> ERROR: No entries detected in this archive node.</p>
        </div>

    <?php endif; ?>
</main>

<?php
get_footer();