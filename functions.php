<?php
/**
 * NeoWeaver Engine - Core Functions
 */

// ─── Helpers ────────────────────────────────────────────────────────────────
function neoweaver_asset_version( $relative_path ) {
	$file = get_stylesheet_directory() . $relative_path;

	return file_exists( $file ) ? filemtime( $file ) : wp_get_theme()->get( 'Version' );
}

// ─── 1. THEME SUPPORT ───────────────────────────────────────────────────────
add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
	}
);

// ─── 2. ASSETS ──────────────────────────────────────────────────────────────
add_action(
	'wp_enqueue_scripts',
	function () {
		$base_uri = get_stylesheet_directory_uri();

		wp_enqueue_style(
			'neo-style',
			get_stylesheet_uri(),
			array(),
			neoweaver_asset_version( '/style.css' ),
			'all'
		);

		wp_enqueue_style(
			'neo-swiper-css',
			'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
			array(),
			null
		);

		wp_enqueue_script(
			'neo-header',
			$base_uri . '/assets/js/neo-header.js',
			array(),
			neoweaver_asset_version( '/assets/js/neo-header.js' ),
			true
		);

		wp_enqueue_script(
			'neo-swiper-core',
			'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
			array(),
			null,
			true
		);

		wp_enqueue_script(
			'neo-swiper-init',
			$base_uri . '/assets/js/neo-swiper.js',
			array( 'neo-swiper-core' ),
			neoweaver_asset_version( '/assets/js/neo-swiper.js' ),
			true
		);

		if (
			is_page_template( 'page-neoweave-os.php' ) ||
			is_page_template( 'template-neoweave-os-terminal.php' )
		) {
			wp_enqueue_script(
				'neo-os',
				$base_uri . '/assets/js/neo-os.js',
				array(),
				neoweaver_asset_version( '/assets/js/neo-os.js' ),
				true
			);
		}
	},
	20
);

function neoweaver_admin_styles() {
	$file = get_stylesheet_directory() . '/admin-style.css';

	if ( file_exists( $file ) ) {
		wp_enqueue_style(
			'neoweaver-admin',
			get_stylesheet_directory_uri() . '/admin-style.css',
			array(),
			filemtime( $file )
		);
	}
}
add_action( 'admin_enqueue_scripts', 'neoweaver_admin_styles' );

// ─── 3. SHORTCODE: INTERACTIVE LOG LIST ─────────────────────────────────────
function neo_interactive_blog_shortcode() {
	$args = array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	);

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
		const items = document.querySelectorAll(".neo-interactive-log-item");
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

// ─── 4. SIDEBAR ─────────────────────────────────────────────────────────────
add_action(
	'widgets_init',
	function () {
		register_sidebar(
			array(
				'name'          => 'Terminal Sidebar',
				'id'            => 'terminal-sidebar',
				'description'   => 'Panel dla statystyk Agenta.',
				'before_widget' => '<section id="%1$s" class="neo-widget %2$s neo-terminal-card">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="neo-widget-title neo-glitch-text">',
				'after_title'   => '</h2>',
			)
		);
	}
);

// ─── 5. WOOCOMMERCE / CHECKOUT ──────────────────────────────────────────────

// Zapis character_id z session do meta zamówienia.
add_action(
	'woocommerce_store_api_checkout_order_processed',
	function ( $order ) {
		$character_id = WC()->session->get( 'neoweaver_character_id' );

		if ( $character_id ) {
			$order->update_meta_data( '_neoweaver_character_id', sanitize_text_field( $character_id ) );
			$order->save();
			WC()->session->__unset( 'neoweaver_character_id' );
		}
	}
);

// Walidacja — blokuj zamówienie gdy brak wyboru agenta.
add_action(
	'woocommerce_store_api_checkout_update_order_from_request',
	function ( $order, $request ) {
		$character_id  = WC()->session->get( 'neoweaver_character_id' );
		$has_neoweaver = false;

		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();

			if ( ! $product ) {
				continue;
			}

			if ( $product->get_attribute( 'neoweaver_item_id' ) ) {
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
	},
	10,
	2
);

// Hook po płatności → zapis do Supabase.
add_action(
	'woocommerce_payment_complete',
	function ( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$character_id = $order->get_meta( '_neoweaver_character_id' );

		if ( ! $character_id ) {
			return;
		}

		$supa_url = defined( 'SUPABASE_URL' ) ? SUPABASE_URL : ( defined( 'DB_SUPABASE_URL' ) ? DB_SUPABASE_URL : '' );
		$supa_key = defined( 'SUPABASE_KEY' ) ? SUPABASE_KEY : ( defined( 'DB_SUPABASE_KEY' ) ? DB_SUPABASE_KEY : '' );

		if ( empty( $supa_url ) || empty( $supa_key ) ) {
			error_log( '[NeoWeaver] Missing Supabase configuration.' );
			return;
		}

		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();

			if ( ! $product ) {
				continue;
			}

			$item_uuid = $product->get_attribute( 'neoweaver_item_id' );

			if ( ! $item_uuid ) {
				continue;
			}

			$body = wp_json_encode(
				array(
					'character_id' => sanitize_text_field( $character_id ),
					'item_id'      => sanitize_text_field( $item_uuid ),
					'is_equipped'  => false,
					'quantity'     => (int) $item->get_quantity(),
					'container_id' => null,
				)
			);

			$response = wp_remote_post(
				untrailingslashit( $supa_url ) . '/rest/v1/cyber_character_inventory',
				array(
					'headers' => array(
						'apikey'        => $supa_key,
						'Authorization' => 'Bearer ' . $supa_key,
						'Content-Type'  => 'application/json',
						'Prefer'        => 'return=representation',
					),
					'body'    => $body,
					'timeout' => 20,
				)
			);

			if ( is_wp_error( $response ) ) {
				error_log( '[NeoWeaver] WP HTTP error: ' . $response->get_error_message() );
				continue;
			}

			$status_code   = wp_remote_retrieve_response_code( $response );
			$body_response = wp_remote_retrieve_body( $response );

			if ( $status_code >= 400 ) {
				error_log( '[NeoWeaver] Supabase error ' . $status_code . ': ' . $body_response );
			} else {
				error_log( '[NeoWeaver] Item added to inventory: ' . $body_response );
			}
		}
	}
);

// ─── 6. MENUS ────────────────────────────────────────────────────────────────
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
}
add_action( 'after_setup_theme', 'neoweaver_register_footer_menus' );

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

// ─── 7. SVG ──────────────────────────────────────────────────────────────────
add_filter(
	'upload_mimes',
	function ( $upload_mimes ) {
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

// ─── 8. DUPLICATE POST/PAGE ─────────────────────────────────────────────────
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

		$actions['wpcode_duplicate'] = '<a href="' . esc_url( $url ) . '" title="Duplicate item" rel="permalink">Duplicate</a>';

		return $actions;
	}
}

add_action(
	'admin_action_wpcode_snippet_duplicate_post',
	function () {
		if ( empty( $_GET['post_id'] ) ) {
			wp_die( 'No post id set for the duplicate action.' );
		}

		$post_id = absint( $_GET['post_id'] );

		if ( ! isset( $_GET['wpcode_duplicate_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['wpcode_duplicate_nonce'] ) ), 'wpcode_duplicate_post_' . $post_id ) ) {
			wp_die( 'The link you followed has expired, please try again.' );
		}

		$post = get_post( $post_id );

		if ( ! $post ) {
			wp_die( 'Error loading post for duplication, please try again.' );
		}

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
			'post_title'     => $post->post_title . ' (copy)',
			'post_type'      => $post->post_type,
			'to_ping'        => $post->to_ping,
		);

		$duplicate_id = wp_insert_post( $new_post );
		$taxonomies   = get_object_taxonomies( get_post_type( $post ) );

		if ( $taxonomies ) {
			foreach ( $taxonomies as $taxonomy ) {
				$post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
				wp_set_object_terms( $duplicate_id, $post_terms, $taxonomy );
			}
		}

		$post_meta = get_post_meta( $post_id );

		if ( $post_meta ) {
			foreach ( $post_meta as $meta_key => $meta_values ) {
				if ( '_wp_old_slug' === $meta_key ) {
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
					'post'   => $duplicate_id,
				),
				admin_url( 'post.php' )
			)
		);
		exit;
	}
);

// ─── 9. HARDENING / CLEANUP ─────────────────────────────────────────────────
add_filter( 'the_generator', '__return_empty_string' );

add_action(
	'admin_init',
	function () {
		if ( current_user_can( 'administrator' ) || wp_doing_ajax() ) {
			return;
		}

		wp_safe_redirect( home_url() );
		exit;
	}
);

// ─── 10. USERS / LAST LOGIN ─────────────────────────────────────────────────
add_filter(
	'manage_users_columns',
	function ( $columns ) {
		$columns['last_login'] = __( 'Last Login', 'neoweaver' );
		return $columns;
	}
);

add_filter(
	'manage_users_custom_column',
	function ( $value, $column_name, $user_id ) {
		if ( 'last_login' === $column_name ) {
			$last_login = get_user_meta( $user_id, 'last_login', true );

			if ( $last_login ) {
				$value = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $last_login );
			} else {
				$value = __( 'Never', 'neoweaver' );
			}
		}

		return $value;
	},
	10,
	3
);

add_action(
	'wp_login',
	function ( $login, $user ) {
		update_user_meta( $user->ID, 'last_login', time() );
	},
	10,
	2
);

add_filter(
	'wp_revisions_to_keep',
	function () {
		return 20;
	}
);

// ─── 11. OG TAGS ────────────────────────────────────────────────────────────
function neoweaver_output_og_tags() {
	if ( ! is_singular() ) {
		return;
	}
	?>
	<meta property="og:title" content="<?php echo esc_attr( wp_get_document_title() ); ?>" />
	<meta property="og:description" content="<?php echo esc_attr( get_the_excerpt() ); ?>" />
	<meta property="og:url" content="<?php echo esc_url( get_permalink() ); ?>" />
	<meta property="og:type" content="article" />
	<?php if ( has_post_thumbnail() ) : ?>
		<meta property="og:image" content="<?php echo esc_url( get_the_post_thumbnail_url( null, 'full' ) ); ?>" />
	<?php endif; ?>
	<?php
}
add_action( 'wp_head', 'neoweaver_output_og_tags', 5 );

// ─── 12. SMOOTH SCROLL ──────────────────────────────────────────────────────
function neoweaver_smooth_scroll_script() {
	?>
	<script>
	document.addEventListener('click', function(e) {
		const link = e.target.closest('a[href^="#"]');
		if (!link) {
			return;
		}

		const href = link.getAttribute('href');
		if (!href || '#' === href) {
			return;
		}

		const targetId = href.slice(1);
		const targetElement = document.getElementById(targetId);

		if (targetElement) {
			e.preventDefault();
			targetElement.scrollIntoView({ behavior: 'smooth' });
		}
	});
	</script>
	<?php
}
add_action( 'wp_footer', 'neoweaver_smooth_scroll_script', 100 );

// ─── 13. LOGIN / COMMENTS / MEDIA ───────────────────────────────────────────
add_filter(
	'login_errors',
	function () {
		return 'Something is wrong!';
	}
);

add_action(
	'login_head',
	function () {
		$custom_logo = 'https://neoweaver.nieodparady.pl/wp-content/uploads/logonw.svg';
		$logo_width  = 84;
		$logo_height = 84;

		printf(
			'<style>.login h1 a {background-image:url(%1$s) !important; margin:0 auto; width:%2$spx; height:%3$spx; background-size:100%%;}</style>',
			esc_url( $custom_logo ),
			(int) $logo_width,
			(int) $logo_height
		);
	},
	990
);

add_filter(
	'comment_form_default_fields',
	function ( $fields ) {
		if ( isset( $fields['url'] ) ) {
			unset( $fields['url'] );
		}

		if ( isset( $fields['cookies'] ) ) {
			$fields['cookies'] = str_replace( 'name, email, and website', 'name and email', $fields['cookies'] );
		}

		return $fields;
	},
	150
);

add_filter(
	'pre_get_avatar_data',
	function ( $atts ) {
		if ( empty( $atts['alt'] ) ) {
			$author = get_the_author_meta( 'display_name' );
			$atts['alt'] = sprintf( 'Avatar for %s', $author );
		}

		return $atts;
	}
);

add_filter( 'sanitize_file_name', 'mb_strtolower' );

// ─── 14. EMAIL / ADMIN BAR ──────────────────────────────────────────────────
add_filter( 'auto_core_update_send_email', '__return_false' );
add_filter( 'auto_plugin_update_send_email', '__return_false' );
add_filter( 'auto_theme_update_send_email', '__return_false' );

function wpcode_send_new_user_notifications( $user_id, $notify = 'user' ) {
	if ( empty( $notify ) || 'admin' === $notify ) {
		return;
	} elseif ( 'both' === $notify ) {
		$notify = 'user';
	}

	wp_send_new_user_notifications( $user_id, $notify );
}

add_action(
	'init',
	function () {
		remove_action( 'register_new_user', 'wp_send_new_user_notifications' );
		remove_action( 'edit_user_created_user', 'wp_send_new_user_notifications' );

		add_action( 'register_new_user', 'wpcode_send_new_user_notifications' );
		add_action( 'edit_user_created_user', 'wpcode_send_new_user_notifications', 10, 2 );
	}
);

add_action(
	'wp_before_admin_bar_render',
	function () {
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu( 'wp-logo' );
	},
	0
);

add_filter(
	'admin_title',
	function ( $admin_title ) {
		return str_replace( ' &#8212; WordPress', '', $admin_title );
	}
);

// Hide dashboard update notifications for all users.
function kinsta_hide_update_nag() {
	remove_action( 'admin_notices', 'update_nag', 3 );
}
add_action( 'admin_menu', 'kinsta_hide_update_nag' );
