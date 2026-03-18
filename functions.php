<?php
// CSS
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'neoweave-style',
        get_stylesheet_uri(),
        [],
        wp_get_theme()->get('Version')
    );

    wp_enqueue_style(
        'neoweave-swiper',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
        [],
        null
    );
});

// JS
add_action( 'wp_enqueue_scripts', function() {

    // Header JS
    wp_enqueue_script(
        'neoweave-header',
        get_template_directory_uri() . '/assets/js/neo-header.js',
        [],
        wp_get_theme()->get('Version'),
        true
    );

    // Swiper core
    wp_enqueue_script(
        'neoweave-swiper-core',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
        [],
        null,
        true
    );

    // Swiper init (zależny od core)
    wp_enqueue_script(
        'neoweave-swiper-init',
        get_template_directory_uri() . '/assets/js/neo-swiper.js',
        ['neoweave-swiper-core'],
        wp_get_theme()->get('Version'),
        true
    );

    // JS tylko dla NeoWeave OS
    if ( is_page_template( 'page-neoweave-os.php' ) ) {
        wp_enqueue_script(
            'neoweave-os',
            get_template_directory_uri() . '/assets/js/neo-os.js',
            [],
            wp_get_theme()->get('Version'),
            true
        );
    }
});
add_action( 'after_setup_theme', function() {
    add_theme_support( 'title-tag' );
});
add_filter( 'post_row_actions', 'wpcode_snippet_duplicate_post_link', 10, 2 );
add_filter( 'page_row_actions', 'wpcode_snippet_duplicate_post_link', 10, 2 );
if ( ! function_exists( 'wpcode_snippet_duplicate_post_link' ) ) {
	function wpcode_snippet_duplicate_post_link( $actions, $post ) {
		$post_type_object = get_post_type_object( $post->post_type );
		if ( null === $post_type_object || ! current_user_can( $post_type_object->cap->create_posts ) ) {
			return $actions;
		}
		$url = wp_nonce_url(
			add_query_arg(
				array(
					'action'  => 'wpcode_snippet_duplicate_post',
					'post_id' => $post->ID,
				),
				'admin.php'
			),
			'wpcode_duplicate_post_' . $post->ID,
			'wpcode_duplicate_nonce'
		);
		$actions['wpcode_duplicate'] = '<a href="' . $url . '" title="Duplicate item" rel="permalink">Duplicate</a>';
		return $actions;
	}
}
add_action( 'admin_action_wpcode_snippet_duplicate_post', function () {
	if ( empty( $_GET['post_id'] ) ) {
		wp_die( 'No post id set for the duplicate action.' );
	}
	$post_id = absint( $_GET['post_id'] );
	if ( ! isset( $_GET['wpcode_duplicate_nonce'] ) || ! wp_verify_nonce( $_GET['wpcode_duplicate_nonce'], 'wpcode_duplicate_post_' . $post_id ) ) {
		// Display a message if the nonce is invalid, may it expired.
		wp_die( 'The link you followed has expired, please try again.' );
	}
	$post = get_post( $post_id );
	if ( $post ) {
		$current_user = wp_get_current_user();
		$new_post     = array(
			'comment_status' => $post->comment_status,
			'menu_order'     => $post->menu_order,
			'ping_status'    => $post->ping_status,
			'post_author'    => $current_user->ID,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_name'      => $post->post_name,
			'post_parent'    => $post->post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => 'draft',
			'post_title'     => $post->post_title . ' (copy)',// Add "(copy)" to the title.
			'post_type'      => $post->post_type,
			'to_ping'        => $post->to_ping,
		);
		// Create the new post
		$duplicate_id = wp_insert_post( $new_post );
		// Copy the taxonomy terms.
		$taxonomies = get_object_taxonomies( get_post_type( $post ) );
		if ( $taxonomies ) {
			foreach ( $taxonomies as $taxonomy ) {
				$post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
				wp_set_object_terms( $duplicate_id, $post_terms, $taxonomy );
			}
		}
		// Copy all the custom fields.
		$post_meta = get_post_meta( $post_id );
		if ( $post_meta ) {

			foreach ( $post_meta as $meta_key => $meta_values ) {
				if ( '_wp_old_slug' === $meta_key ) { // skip old slug.
					continue;
				}
				foreach ( $meta_values as $meta_value ) {
					add_post_meta( $duplicate_id, $meta_key, maybe_unserialize( $meta_value ) );
				}
			}
		}

		// Redirect to edit the new post.
		wp_safe_redirect(
			add_query_arg(
				array(
					'action' => 'edit',
					'post'   => $duplicate_id
				),
				admin_url( 'post.php' )
			)
		);
		exit;
	} else {
		wp_die( 'Error loading post for duplication, please try again.' );
	}
} );
function neoweave_interactive_blog_shortcode() {
    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    );
    $query = new WP_Query($args);
    $output = '<div class="neoweave-terminal-wrapper">';
    $output .= '
    <div class="terminal-sidebar">
        <div class="scanline"></div>
        <div class="system-info">
            <p>NODE: ARCHIVE_01</p>
            <p>STATUS: <span class="blink">ENCRYPTED</span></p>
            <p>ENTROPY: 14.2%</p>
        </div>
        <nav class="terminal-menu">
            <button onclick="filterLogs(\\'all\\')">[ SHOW_ALL ]</button>
            <button onclick="filterLogs(\\'lore\\')">[ LORE_ONLY ]</button>
        </nav>
    </div>';
    $output .= '<div class="terminal-screen">
        <header class="screen-header">SELECT DATA_STREAM TO INITIALIZE...</header>
        <div id="log-display" class="log-display">';
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $title = strtoupper(get_the_title());
            $date = get_the_date('Ymd');
            $id = get_the_ID();         
            $output .= '
            <div class="interactive-log-item" onclick="window.location=\\''.get_permalink().'\\'" data-category="lore">
                <span class="log-date">[' . $date . ']</span>
                <span class="log-title">> ' . $title . '</span>
                <span class="log-cursor">_</span>
            </div>';
        }
        wp_reset_postdata();
    }
    $output .= '</div>
        <footer class="screen-footer">
            <span class="prompt">guest@neoweave:~$</span> <span class="typing-text">list_archives --active</span>
        </footer>
    </div></div>';
    return $output;
}
add_shortcode('neoweave_interactive_blog', 'neoweave_interactive_blog_shortcode');
