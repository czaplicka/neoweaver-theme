<?php
/**
 * NeoWeaver Engine - Core Functions
 */

// ─── 1. THEME SUPPORT ────────────────────────────────────────────────────────
add_action( 'after_setup_theme', function () {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
} );

// ─── 2. UNCONDITIONAL ASSETS ─────────────────────────────────────────────────
add_action( 'wp_enqueue_scripts', function () {
    $version  = time();
    $base_url = get_stylesheet_directory_uri();

    wp_enqueue_style( 'neo-style', get_stylesheet_uri(), [], $version, 'all' );
    wp_enqueue_style( 'neo-swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], null );
    wp_enqueue_script( 'neo-header', $base_url . '/assets/js/neo-header.js', [], $version, false );
    wp_enqueue_script( 'neo-swiper-core', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], null, true );
    wp_enqueue_script( 'neo-swiper-init', $base_url . '/assets/js/neo-swiper.js', [ 'neo-swiper-core' ], $version, true );
}, 20 );

function neoweaver_admin_styles() {
    wp_enqueue_style(
        'neoweaver-admin',
        get_template_directory_uri() . '/admin-style.css',
        [],
        '1.0.0'
    );
}
add_action( 'admin_enqueue_scripts', 'neoweaver_admin_styles' );
// ─── 3. CONDITIONAL ASSET: neo-os.js ─────────────────────────────────────────
add_action( 'wp', function () {
    if (
        is_page_template( 'page-neoweave-os.php' ) ||
        is_page_template( 'template-neoweave-os-terminal.php' )
    ) {
        $version  = time();
        $base_url = get_stylesheet_directory_uri();
        add_action( 'wp_enqueue_scripts', function () use ( $base_url, $version ) {
            wp_enqueue_script( 'neo-os', $base_url . '/assets/js/neo-os.js', [], $version, true );
        }, 20 );
    }
} );

// ─── 4. SHORTCODE: INTERACTIVE LOG LIST ──────────────────────────────────────
function neo_interactive_blog_shortcode() {
    $args = [
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    ];

    $query  = new WP_Query( $args );
    $output = '<div class="neo-terminal-wrapper neo-interactive-archives">';

    $output .= '
    <div class="neo-terminal-sidebar">
        <div class="neo-scanline"></div>
        <div class="neo-system-info">
            <p>[NODE: ARCHIVE_01]</p>
            <p>[STATUS: <span class="neo-blink neo-accent">ENCRYPTED</span>]</p>
            <p>[ENTROPY: <span id="entropy-val">14.2%</span>]</p>
        </div>
        <nav class="neo-terminal-menu">
            <button type="button" class="neo-btn is-active" data-filter="all" onclick="neoFilterLogs(\'all\')">[ SHOW_ALL ]</button>
            <button type="button" class="neo-btn" data-filter="lore" onclick="neoFilterLogs(\'lore\')">[ LORE_DATA ]</button>
            <button type="button" class="neo-btn" data-filter="dev" onclick="neoFilterLogs(\'dev\')">[ DEV_LOGS ]</button>
        </nav>
    </div>';

    $output .= '
    <div class="neo-terminal-screen">
        <header class="neo-screen-header">SELECT DATA_STREAM TO INITIALIZE...</header>
        <div id="neo-log-display" class="neo-log-display">';

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $title    = strtoupper( get_the_title() );
            $date     = get_the_date( 'Ymd' );
            $link     = get_permalink();
            $cats     = get_the_category();
            $cat_slug = ! empty( $cats ) ? $cats[0]->slug : 'lore';

            $output .= '
            <div class="neo-interactive-log-item"
                 onclick="window.location=\'' . esc_url( $link ) . '\'"
                 data-category="' . esc_attr( $cat_slug ) . '">
                <span class="neo-log-date">[' . esc_html( $date ) . ']</span>
                <span class="neo-log-title">&gt; ' . esc_html( $title ) . '</span>
                <span class="neo-cursor">_</span>
            </div>';
        }
        wp_reset_postdata();
    }

    $output .= '
        </div>
        <footer class="neo-screen-footer">
            <span class="neo-prompt">guest@neoweave:~$</span>
            <span class="neo-typing-text">list_archives --active</span>
        </footer>
    </div>
</div>';

    $output .= '
    <script>
    function neoFilterLogs(category) {
        const items   = document.querySelectorAll(".neo-interactive-log-item");
        const buttons = document.querySelectorAll(".neo-terminal-menu button");
        items.forEach(item => {
            const cat = item.getAttribute("data-category") || "all";
            item.style.display = (category === "all" || cat === category) ? "flex" : "none";
        });
        buttons.forEach(btn => {
            btn.classList.toggle("is-active", btn.getAttribute("data-filter") === category);
        });
    }
    </script>';

    return $output;
}
add_shortcode( 'neo_interactive_blog', 'neo_interactive_blog_shortcode' );

// ─── 5. SIDEBAR REGISTRATION ─────────────────────────────────────────────────
add_action( 'widgets_init', function () {
    register_sidebar( [
        'name'          => 'Terminal Sidebar',
        'id'            => 'terminal-sidebar',
        'description'   => 'Panel dla statystyk Agenta.',
        'before_widget' => '<section id="%1$s" class="neo-widget %2$s neo-terminal-card">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="neo-widget-title neo-glitch-text">',
        'after_title'   => '</h2>',
    ] );
} );

// ─── 6. NEOWEAVER CHECKOUT (Block Checkout) ───────────────────────────────────

// Zapis character_id z session do meta zamówienia
add_action( 'woocommerce_store_api_checkout_order_processed', function( $order ) {
    $character_id = WC()->session->get( 'neoweaver_character_id' );
    if ( $character_id ) {
        $order->update_meta_data( '_neoweaver_character_id', $character_id );
        $order->save();
        WC()->session->__unset( 'neoweaver_character_id' );
    }
} );

// Walidacja — blokuj zamówienie gdy brak wyboru agenta
add_action( 'woocommerce_store_api_checkout_update_order_from_request', function( $order, $request ) {
    $character_id  = WC()->session->get( 'neoweaver_character_id' );
    $has_neoweaver = false;

    foreach ( $order->get_items() as $item ) {
        if ( $item->get_product()->get_attribute( 'neoweaver_item_id' ) ) {
            $has_neoweaver = true;
            break;
        }
    }

    if ( $has_neoweaver && empty( $character_id ) ) {
        throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException(
            'neoweaver_no_agent',
            'Please select a Field Agent to receive your item.',
            400
        );
    }
}, 10, 2 );

// Hook po opłaceniu → zapis do Supabase
add_action( 'woocommerce_order_status_completed', function( $order_id ) {
    $order        = wc_get_order( $order_id );
    $character_id = $order->get_meta( '_neoweaver_character_id' );

    if ( ! $character_id ) return;

    $supa_url = defined('SUPABASE_URL') ? SUPABASE_URL : DB_SUPABASE_URL;
    $supa_key = defined('SUPABASE_KEY') ? SUPABASE_KEY : DB_SUPABASE_KEY;

    foreach ( $order->get_items() as $item ) {
        $product   = $item->get_product();
        $item_uuid = $product->get_attribute( 'neoweaver_item_id' );

        if ( ! $item_uuid ) continue;

        $body = wp_json_encode( [
            'character_id' => $character_id,
            'item_id'      => $item_uuid,
            'is_equipped'  => false,
            'quantity'     => $item->get_quantity(),
            'container_id' => null,
        ] );

        $response = wp_remote_post(
            $supa_url . '/rest/v1/cyber_character_inventory',
            [
                'headers' => [
                    'apikey'        => $supa_key,
                    'Authorization' => 'Bearer ' . $supa_key,
                    'Content-Type'  => 'application/json',
                    'Prefer'        => 'return=representation',
                ],
                'body' => $body,
            ]
        );

        $status_code   = wp_remote_retrieve_response_code( $response );
        $body_response = wp_remote_retrieve_body( $response );

        if ( is_wp_error( $response ) ) {
            error_log( '[NeoWeaver] WP HTTP error: ' . $response->get_error_message() );
        } elseif ( $status_code >= 400 ) {
            error_log( '[NeoWeaver] Supabase error ' . $status_code . ': ' . $body_response );
        } else {
            error_log( '[NeoWeaver] Item added to inventory: ' . $body_response );
        }
    }
} );
add_action( 'after_setup_theme', 'neoweaver_register_footer_menus' );

function neoweaver_register_footer_menus() {
    register_nav_menus(
        array(
            'neo_header_menu'   => __( 'Neo Header Menu', 'neoweaver' ),
            'neo_footer_useful' => __( 'Neo Footer - Useful Links', 'neoweaver' ),
            'neo_footer_game'   => __( 'Neo Footer - Game Menu', 'neoweaver' ),
            'neo_footer_nde'    => __( 'Neo Footer - NDE System', 'neoweaver' ),
            'neo_footer_legal'  => __( 'Neo Footer - Legal / Extra', 'neoweaver' ),
        )
    );
function neoweaver_footer_menu( $location ) {
    wp_nav_menu(
        array(
            'theme_location' => $location,
            'container'      => false,
            'menu_class'     => 'neo-footer-list',
            'fallback_cb'    => false,
            'depth'          => 1,
        )
    );
}
}
function neoweaver_header_menu() {
    wp_nav_menu(
        array(
            'theme_location' => 'neo_header_menu',
            'container'      => false,
            'menu_id'        => '',
            'menu_class'     => 'neo-menu-list',
            'fallback_cb'    => false,
            'depth'          => 1,
            'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
        )
    );
}
/**
 * Allow SVG uploads for administrator users.
 *
 * @param array $upload_mimes Allowed mime types.
 *
 * @return mixed
 */
add_filter(
	'upload_mimes',
	function ( $upload_mimes ) {
		// By default, only administrator users are allowed to add SVGs.
		// To enable more user types edit or comment the lines below but beware of
		// the security risks if you allow any user to upload SVG files.
		if ( ! current_user_can( 'administrator' ) ) {
			return $upload_mimes;
		}

		$upload_mimes['svg']  = 'image/svg+xml';
		$upload_mimes['svgz'] = 'image/svg+xml';

		return $upload_mimes;
	}
);
add_filter(
	'wp_check_filetype_and_ext',
	function ( $wp_check_filetype_and_ext, $file, $filename, $mimes, $real_mime ) {

		if ( ! $wp_check_filetype_and_ext['type'] ) {

			$check_filetype  = wp_check_filetype( $filename, $mimes );
			$ext             = $check_filetype['ext'];
			$type            = $check_filetype['type'];
			$proper_filename = $filename;

			if ( $type && 0 === strpos( $type, 'image/' ) && 'svg' !== $ext ) {
				$ext  = false;
				$type = false;
			}

			$wp_check_filetype_and_ext = compact( 'ext', 'type', 'proper_filename' );
		}

		return $wp_check_filetype_and_ext;

	},
	10,
	5
);
// Add duplicate button to post/page list of actions.
add_filter( 'post_row_actions', 'wpcode_snippet_duplicate_post_link', 10, 2 );
add_filter( 'page_row_actions', 'wpcode_snippet_duplicate_post_link', 10, 2 );

// Let's make sure the function doesn't already exist.
if ( ! function_exists( 'wpcode_snippet_duplicate_post_link' ) ) {
	/**
	 * @param array   $actions The actions added as links to the admin.
	 * @param WP_Post $post The post object.
	 *
	 * @return array
	 */
	function wpcode_snippet_duplicate_post_link( $actions, $post ) {

		// Don't add action if the current user can't create posts of this post type.
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

/**
 * Handle the custom action when clicking the button we added above.
 */
add_action( 'admin_action_wpcode_snippet_duplicate_post', function () {

	if ( empty( $_GET['post_id'] ) ) {
		wp_die( 'No post id set for the duplicate action.' );
	}

	$post_id = absint( $_GET['post_id'] );

	// Check the nonce specific to the post we are duplicating.
	if ( ! isset( $_GET['wpcode_duplicate_nonce'] ) || ! wp_verify_nonce( $_GET['wpcode_duplicate_nonce'], 'wpcode_duplicate_post_' . $post_id ) ) {
		// Display a message if the nonce is invalid, may it expired.
		wp_die( 'The link you followed has expired, please try again.' );
	}

	// Load the post we want to duplicate.
	$post = get_post( $post_id );

	// Create a new post data array from the post loaded.
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
// no wp version
add_filter('the_generator', '__return_empty_string');
//block nonadmin from admin
add_action( 'admin_init', function() {
	if ( ! current_user_can( 'administrator' ) ) {
       wp_redirect( home_url() );
       exit;
	}
} );
// Add a column to the users table to display the last login time
add_filter( 'manage_users_columns', function ( $columns ) {
	$columns['last_login'] = __( 'Last Login' );

	return $columns;
} );

// Populate the last login column with data
add_filter( 'manage_users_custom_column', function ( $value, $column_name, $user_id ) {
	if ( 'last_login' === $column_name ) {
		$last_login = get_user_meta( $user_id, 'last_login', true );
		if ( $last_login ) {
			$value = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_login );
		} else {
			$value = __( 'Never' );
		}
	}
// Record the last login time when a user logs in
add_action( 'wp_login', function ( $login, $user ) {
	update_user_meta( $user->ID, 'last_login', time() );
}, 10, 2 );
    //revisions
    add_filter( 'wp_revisions_to_keep', function( $limit ) {
	// Limit to the last 20 revisions. Change 20 to whatever limit you want.
	return 20;
} );
	return $value;
}, 10, 3 );
//OG
<meta property="og:title" content="<?php echo esc_attr( get_the_title() ); ?>"/>
<meta property="og:description" content="<?php echo esc_attr( get_the_excerpt() ); ?>"/>
<meta property="og:url" content="<?php echo esc_attr( get_permalink() ); ?>"/>
<meta property="og:type" content="article"/>
if ( has_post_thumbnail() ) : ?>
<meta property="og:image" content="<?php echo esc_attr( get_the_post_thumbnail_url() ); ?>"/>
<?php endif;?>
//smoth scroll
<script>
document.addEventListener('click', function(e) {
    // Check if the clicked element is an anchor with href starting with '#'
    if (e.target.tagName === 'A' && e.target.getAttribute('href') && e.target.getAttribute('href').startsWith('#')) {
        e.preventDefault();
        const targetId = e.target.getAttribute('href').slice(1); // Remove the '#' from the href
        const targetElement = document.getElementById(targetId);
        
        if (targetElement) {
            targetElement.scrollIntoView({
                behavior: 'smooth'
            });
        }
    }
});
</script> <?php
//hide errors
add_filter(
	'login_errors',
	function ( $error ) {
		// Edit the line below to customize the message.
		return 'Something is wrong!';
	}
);
//login logo
add_filter( 'login_head', function () {
	// Update the line below with the URL to your own logo.
	// Adjust the Width & Height accordingly.
	$custom_logo = 'https://neoweaver.nieodparady.pl/wp-content/uploads/logonw.svg';
	$logo_width  = 84;
	$logo_height = 84;

	printf(
		'<style>.login h1 a {background-image:url(%1$s) !important; margin:0 auto; width: %2$spx; height: %3$spx; background-size: 100%%;}</style>',
		$custom_logo,
		$logo_width,
		$logo_height
	);
}, 990 );
// no www w comment
add_filter( 'comment_form_default_fields', function ($fields) {
	if ( isset( $fields['url'] ) ) {
		unset( $fields['url'] );
	}
	if ( isset( $fields['cookies'] ) ) {
		// Remove the website mention from the cookies checkbox label.
		$fields['cookies'] = str_replace('name, email, and website', 'name and email', $fields['cookies']);
	}

	return $fields;
}, 150 );
//Add default ALT to avatar/Gravatar Images
add_filter(
	'pre_get_avatar_data',
	function ( $atts ) {
		if ( empty( $atts['alt'] ) ) {
			if ( have_comments() ) {
				$author = get_comment_author();
			} else {
				$author = get_the_author_meta( 'display_name' );
			}
			$alt = sprintf( 'Avatar for %s', $author );

			$atts['alt'] = $alt;
		}
		return $atts;
	}
);
// Lowercase Filenames for Uploads
add_filter( 'sanitize_file_name', 'mb_strtolower' );
//Remove Query Strings From Static Files
function wpcode_snippet_remove_query_strings_split( $src ) {
	$output = preg_split( "/(&ver|\?ver)/", $src );

	return $output ? $output[0] : '';
}

add_action( 'init', function () {
	if ( ! is_admin() ) {
		add_filter( 'script_loader_src', 'wpcode_snippet_remove_query_strings_split', 15 );
		add_filter( 'style_loader_src', 'wpcode_snippet_remove_query_strings_split', 15 );
	}
} );
