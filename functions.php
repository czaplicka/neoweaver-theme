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
// Enqueue styles and scripts that are needed on every page.
// is_page_template() must NOT be called here — the query is not yet run
// when wp_enqueue_scripts fires, so it would trigger the is_singular notice.
add_action( 'wp_enqueue_scripts', function () {
	$version  = time(); // bust cache on every load during development
	$base_url = get_stylesheet_directory_uri();

	// Main theme stylesheet
	wp_enqueue_style( 'neo-style', get_stylesheet_uri(), [], $version, 'all' );

	// Swiper CSS
	wp_enqueue_style( 'neo-swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], null );

	// Header / clock script
	wp_enqueue_script( 'neo-header', $base_url . '/assets/js/neo-header.js', [], $version, false );

	// Swiper core + init
	wp_enqueue_script( 'neo-swiper-core', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], null, true );
	wp_enqueue_script( 'neo-swiper-init', $base_url . '/assets/js/neo-swiper.js', [ 'neo-swiper-core' ], $version, true );
}, 20 );

// ─── 3. CONDITIONAL ASSET: neo-os.js ─────────────────────────────────────────
// is_page_template() is safe inside the `wp` hook because the main query has
// already run by the time `wp` fires — unlike wp_enqueue_scripts which can
// execute before the query is resolved (causing the is_singular() notice).
add_action( 'wp', function () {
	if (
		is_page_template( 'page-neoweave-os.php' ) ||
		is_page_template( 'template-neoweave-os-terminal.php' )
	) {
		$version  = time();
		$base_url = get_stylesheet_directory_uri();

		add_action(
			'wp_enqueue_scripts',
			function () use ( $base_url, $version ) {
				wp_enqueue_script( 'neo-os', $base_url . '/assets/js/neo-os.js', [], $version, true );
			},
			20
		);
	}
} );

// ─── 4. SHORTCODE: INTERACTIVE LOG LIST (Terminal Style) ─────────────────────
function neo_interactive_blog_shortcode() {
	$args = [
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	];

	$query  = new WP_Query( $args );
	$output = '<div class="neo-terminal-wrapper neo-interactive-archives">';

	// Terminal sidebar (filters)
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

	// Terminal screen (results)
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

	// Inline JS — filter logic
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
// 1. Pobierz postacie zalogowanego gracza z Supabase
function neoweaver_get_player_characters( $wp_user_id ) {
    $supa_url = defined('SUPABASE_URL') ? SUPABASE_URL : DB_SUPABASE_URL;
    $supa_key = defined('SUPABASE_KEY') ? SUPABASE_KEY : DB_SUPABASE_KEY;

    $response = wp_remote_get(
        $supa_url . '/rest/v1/cyber_characters?wp_user_id=eq.' . $wp_user_id . '&is_active=eq.true&select=id,name',
        [
            'headers' => [
                'apikey'        => $supa_key,
                'Authorization' => 'Bearer ' . $supa_key,
            ],
        ]
    );

    if ( is_wp_error( $response ) ) return [];
    return json_decode( wp_remote_retrieve_body( $response ), true );
}

// 2. Wyświetl pole wyboru postaci na checkoucie
add_action( 'woocommerce_after_order_notes', function( $checkout ) {
    // Sprawdź czy w koszyku jest produkt NeoWeaver
    $has_neoweaver = false;
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        if ( $cart_item['data']->get_attribute( 'neoweaver_item_id' ) ) {
            $has_neoweaver = true;
            break;
        }
    }
    if ( ! $has_neoweaver ) return;

    $characters = neoweaver_get_player_characters( get_current_user_id() );
    if ( empty( $characters ) ) return;

    $options = [ '' => '— Select your character —' ];
    foreach ( $characters as $char ) {
        $options[ $char['id'] ] = esc_html( $char['name'] );
    }

    woocommerce_form_field( 'neoweaver_character_id', [
        'type'     => 'select',
        'class'    => ['form-row-wide'],
        'label'    => '⚔️ Which Field Agent receives this item?',
        'required' => true,
        'options'  => $options,
    ], $checkout->get_value( 'neoweaver_character_id' ) );
} );

// 3. Walidacja — pole wymagane
add_action( 'woocommerce_checkout_process', function() {
    // Sprawdź czy w koszyku jest item NeoWeaver
    $has_neoweaver = false;
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        if ( $cart_item['data']->get_attribute( 'neoweaver_item_id' ) ) {
            $has_neoweaver = true;
            break;
        }
    }
    if ( ! $has_neoweaver ) return;

    if ( empty( $_POST['neoweaver_character_id'] ) ) {
        wc_add_notice( 'Please select a Field Agent to receive the item.', 'error' );
    }
} );

// 4. Zapisz wybrany character_id do meta zamówienia
add_action( 'woocommerce_order_status_completed', function( $order_id ) {
    $order        = wc_get_order( $order_id );
    $character_id = $order->get_meta( '_neoweaver_character_id' );

    if ( ! $character_id ) return;

    $supa_url     = defined('SUPABASE_URL') ? SUPABASE_URL : DB_SUPABASE_URL;
    $supa_key     = defined('SUPABASE_KEY') ? SUPABASE_KEY : DB_SUPABASE_KEY

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
                    'Prefer'        => 'return=representation', // zwróć wstawiony rekord
                ],
                'body' => $body,
            ]
        );

        $status_code  = wp_remote_retrieve_response_code( $response );
        $body_response = wp_remote_retrieve_body( $response );

        if ( is_wp_error( $response ) ) {
            error_log( '[NeoWeaver] WP HTTP error: ' . $response->get_error_message() );
        } elseif ( $status_code >= 400 ) {
            // Tu zobaczysz np. błąd triggera container fit
            error_log( '[NeoWeaver] Supabase error ' . $status_code . ': ' . $body_response );
        } else {
            error_log( '[NeoWeaver] Item added to inventory: ' . $body_response );
        }
    }
} );
add_action( 'woocommerce_after_order_notes', function( $checkout ) {
    $has_neoweaver = false;
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        if ( $cart_item['data']->get_attribute( 'neoweaver_item_id' ) ) {
            $has_neoweaver = true;
            break;
        }
    }
    if ( ! $has_neoweaver ) return;

    $characters = neoweaver_get_player_characters( get_current_user_id() );

    echo '<div id="neoweaver-character-field">';
    echo '<h3>⚔️ NeoWeaver — Field Agent Assignment</h3>';

    if ( empty( $characters ) ) {
        // Brak postaci — pokaż komunikat zamiast ukrywać sekcję
        echo '<p class="neoweaver-no-agent" style="color:#cc0000; font-weight:bold;">';
        echo '⚠️ You have no active Field Agents. ';
        echo 'Please <a href="/create-character">create a character</a> before purchasing this item.';
        echo '</p>';
    } else {
        $options = [ '' => '— Select your Field Agent —' ];
        foreach ( $characters as $char ) {
            $options[ $char['id'] ] = esc_html( $char['name'] );
        }

        woocommerce_form_field( 'neoweaver_character_id', [
            'type'     => 'select',
            'class'    => ['form-row-wide'],
            'label'    => 'Which Field Agent receives this item?',
            'required' => true,
            'options'  => $options,
        ], $checkout->get_value( 'neoweaver_character_id' ) );
    }

    echo '</div>';
} );
