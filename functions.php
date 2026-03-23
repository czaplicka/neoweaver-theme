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
	wp_enqueue_script( 'neo-header', $base_url . '/assets/js/neo-header.js', [], $version, true );

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