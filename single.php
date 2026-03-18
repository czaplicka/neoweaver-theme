<?php
get_header();
?>

<main class="neoweave-terminal neoweave-terminal--post">
    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : the_post(); ?>

            <div class="terminal-header">
                [LOG_ENTRY] [ID: <?php the_ID(); ?>] [NODE: SINGLE_POST]
            </div>

            <div class="status-bar">
                DATE: <?php echo esc_html( get_the_date( 'Y-m-d H:i' ) ); ?> //
                AUTHOR: <?php the_author(); ?>
            </div>

            <h1 class="terminal-title">
                <?php the_title(); ?>
            </h1>

            <div class="terminal-content">
                <?php the_content(); ?>
            </div>

            <div class="footer-log">
                [LOG_END] [EOT] <span class="terminal-cursor"></span>
            </div>

            <?php
            // Nawigacja między wpisami
            $prev = get_previous_post();
            $next = get_next_post();
            if ( $prev || $next ) :
            ?>
                <div class="nav-links">
                    <div class="nav-previous">
                        <?php if ( $prev ) : ?>
                            <a href="<?php echo get_permalink( $prev->ID ); ?>">&lt; PREV_LOG</a>
                        <?php endif; ?>
                    </div>
                    <div class="nav-next">
                        <?php if ( $next ) : ?>
                            <a href="<?php echo get_permalink( $next->ID ); ?>">NEXT_LOG &gt;</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php endwhile; ?>
    <?php else : ?>

        <div class="terminal-header">
            [LOG_ERROR] [NO_ENTRY_FOUND]
        </div>
        <p>No log entry could be retrieved for this request.</p>

    <?php endif; ?>
</main>

<?php
get_footer();
