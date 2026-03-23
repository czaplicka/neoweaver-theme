<?php
get_header();
?>

<main class="neo-terminal-container neo-category-node">
    <div class="neo-terminal-header-meta">
        [CHANNEL_ROUTING] [CATEGORY_NODE] [ENCRYPTION: ACTIVE]
    </div>

    <div class="neo-status-bar">
        CATEGORY: <span class="neo-accent"><?php echo esc_html( single_cat_title( '', false ) ); ?></span> // 
        TOTAL_LOGS: <?php echo (int) $wp_query->found_posts; ?>
    </div>

    <?php if ( have_posts() ) : ?>

        <div class="neo-log-list">
            <?php while ( have_posts() ) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( 'neo-log-entry' ); ?>>
                    
                    <div class="neo-entry-meta">
                        <span class="neo-date-tag">[TIMESTAMP: <?php echo esc_html( get_the_date( 'Y-m-d' ) ); ?>]</span>
                        <span class="neo-id-tag">[REF: <?php the_ID(); ?>]</span>
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
                    </div>

                    <div class="neo-entry-actions">
                        <a class="neo-access-link" href="<?php the_permalink(); ?>">[ OPEN_LOG ]</a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

        <div class="neo-footer-log">
            [CHANNEL_END] [NO_FURTHER_ENTRIES_DETECTED] 
            <span class="neo-cursor"></span>
        </div>

        <nav class="neo-nav-links">
            <div class="neo-nav-previous">
                <?php next_posts_link( '&lt; SCAN_PREVIOUS_RECORDS' ); ?>
            </div>
            <div class="neo-nav-next">
                <?php previous_posts_link( 'SCAN_RECENT_RECORDS &gt;' ); ?>
            </div>
        </nav>

    <?php else : ?>

        <div class="neo-content-area">
            <h2 class="neo-title">EMPTY_CHANNEL</h2>
            <p class="neo-error">> WARNING: No logs have been assigned to this category node yet.</p>
            <p><a href="<?php echo esc_url( home_url() ); ?>" class="neo-accent">> RETURN TO CORE</a></p>
        </div>

    <?php endif; ?>
</main>

<?php
get_footer();