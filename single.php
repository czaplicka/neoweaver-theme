<?php get_header(); ?>

<main class="neo-terminal-container neo-terminal--post"><div class="neoweave-terminal">
    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : the_post(); ?>

            <div class="neo-terminal-header-meta">
                [LOG_ENTRY] [ID: <?php the_ID(); ?>] [FILE: SINGLE_POST]
            </div>

            <div class="neo-status-bar">
                DATE: <?php echo esc_html( get_the_date( 'Y-m-d H:i' ) ); ?> //
                ECHO: <?php
                if ( has_tag() ) {
                    // Generuje listę tagów z prefiksem #
                    echo get_the_tag_list('<span class="neo-tags">#', '</span> <span class="neo-tags">#', '</span>');
                } else {
                    echo '<span class="neo-text-dim">NO_TAGS_DETECTED</span>';
                }
                ?>
            </div>

            <h1 class="neo-title">
                <?php the_title(); ?>
            </h1>

            <div class="neo-content-area">
                <?php the_content(); ?>
            </div>

            <div class="neo-footer-log">
                [LOG_END] [EOT] <span class="neo-cursor"></span>
            </div>

            <?php
            $prev = get_previous_post();
            $next = get_next_post();
            if ( $prev || $next ) :
            ?>
                <nav class="neo-nav-links">
                    <div class="neo-nav-previous">
                        <?php if ( $prev ) : ?>
                            <a href="<?php echo get_permalink( $prev->ID ); ?>">&lt; PREV_LOG</a>
                        <?php endif; ?>
                    </div>
                    <div class="neo-nav-next">
                        <?php if ( $next ) : ?>
                            <a href="<?php echo get_permalink( $next->ID ); ?>">NEXT_LOG &gt;</a>
                        <?php endif; ?>
                    </div>
                </nav>
            <?php endif; ?>

        <?php endwhile; ?>
    <?php endif; ?>
<div class="terminal-header"><a href="/logs/">[LOGS]</a></div>
    <div class="neo-terminal-footer-meta">
        <div class="neo-connection-status">
            &gt; CONNECTION_SECURE // NDE_ENCRYPTION_ACTIVE<br>
            &gt; STANDBY_FOR_INPUT <span class="neo-cursor"></span>
        </div>

        <?php if ( is_active_sidebar( 'terminal-sidebar' ) ) : ?>
            <aside id="secondary" class="neo-sidebar">
                <?php dynamic_sidebar( 'terminal-sidebar' ); ?>
            </aside>
        <?php endif; ?>
    </div>
</main></div>

<?php get_footer(); ?>