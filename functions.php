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
require_once get_stylesheet_directory() . '/includes/checkout-block.php';
require_once get_stylesheet_directory() . '/includes/class-neoweaver-checkout-block.php';

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
