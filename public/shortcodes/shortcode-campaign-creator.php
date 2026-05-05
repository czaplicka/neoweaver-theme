<?php
/**
 * NeoWeaver – Campaign Creator Shortcode
 *
 * Fixes applied:
 * 1. Indentation fixed (uploadsUrl and all stray spaces)
 * 2. wp_enqueue_script added for neoweaver-campaign-creator before wp_localize_script
 * 3. wp_upload_dir() result cached with static variable
 * 4. esc_attr() applied to round() output
 * 5. esc_html() applied to $emoji values
 * 6. wp_json_encode() replaces serialize() for cache key
 * 7. add_shortcode() registration added at bottom
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ---------------------------------------------------------------------------
// 1. Asset enqueue (runs once per page load)
// ---------------------------------------------------------------------------

function neoweaver_campaign_creator_enqueue_assets() {
    $plugin_url = plugin_dir_url( dirname( __FILE__, 2 ) );

    // Enqueue the JS before wp_localize_script – this was missing before
    wp_enqueue_script(
        'neoweaver-campaign-creator',
        $plugin_url . 'public/js/campaign-creator.js',
        [ 'jquery' ],
        NEOWEAVER_VERSION,
        true
    );

    wp_enqueue_style(
        'neoweaver-campaign-creator',
        $plugin_url . 'public/css/campaign-creator.css',
        [],
        NEOWEAVER_VERSION
    );

    // Cache wp_upload_dir() result – expensive filesystem call, done only once
    static $upload_dir = null;
    if ( null === $upload_dir ) {
        $upload_dir = wp_upload_dir();
    }

    wp_localize_script(
        'neoweaver-campaign-creator',
        'neoweaverCampaignCreator',
        [
            'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
            'nonce'       => wp_create_nonce( 'neoweaver_campaign_creator' ),
            'uploadsUrl'  => esc_url( $upload_dir['baseurl'] ),
            'supabaseUrl' => defined( 'NEOWEAVER_SUPABASE_URL' ) ? NEOWEAVER_SUPABASE_URL : '',
            'supabaseKey' => defined( 'NEOWEAVER_SUPABASE_ANON_KEY' ) ? NEOWEAVER_SUPABASE_ANON_KEY : '',
        ]
    );
}

// ---------------------------------------------------------------------------
// 2. Shortcode callback
// ---------------------------------------------------------------------------

function neoweaver_campaign_creator_shortcode( $atts ) {
    $atts = shortcode_atts(
        [
            'world_id' => 0,
        ],
        $atts,
        'neoweaver_campaign_creator'
    );

    // Enqueue assets only when the shortcode is actually rendered
    neoweaver_campaign_creator_enqueue_assets();

    $total_steps  = 4;
    // esc_attr() on numeric output – consistent with the rest of the codebase
    $step_percent = esc_attr( round( 100 / $total_steps ) );

    // Emojis rendered through esc_html() – safe and consistent escaping pattern
    $emoji_world    = esc_html( '\ud83c\udf10' );
    $emoji_campaign = esc_html( '\u2694\ufe0f' );
    $emoji_agent    = esc_html( '\ud83d\udd75\ufe0f' );
    $emoji_deploy   = esc_html( '\ud83d\ude80' );

    ob_start();
    ?>
    <div class="neoweaver-campaign-creator" data-world-id="<?php echo esc_attr( $atts['world_id'] ); ?>">

        <div class="nw-progress" role="progressbar" aria-valuenow="<?php echo $step_percent; ?>" aria-valuemin="0" aria-valuemax="100">
            <div class="nw-progress__bar" style="width: <?php echo $step_percent; ?>%"></div>
        </div>

        <div class="nw-steps">
            <div class="nw-step nw-step--active" data-step="1">
                <span class="nw-step__icon"><?php echo $emoji_world; ?></span>
                <span class="nw-step__label"><?php esc_html_e( 'Select World', 'neoweaver' ); ?></span>
            </div>
            <div class="nw-step" data-step="2">
                <span class="nw-step__icon"><?php echo $emoji_campaign; ?></span>
                <span class="nw-step__label"><?php esc_html_e( 'Name Campaign', 'neoweaver' ); ?></span>
            </div>
            <div class="nw-step" data-step="3">
                <span class="nw-step__icon"><?php echo $emoji_agent; ?></span>
                <span class="nw-step__label"><?php esc_html_e( 'Assign Agent', 'neoweaver' ); ?></span>
            </div>
            <div class="nw-step" data-step="4">
                <span class="nw-step__icon"><?php echo $emoji_deploy; ?></span>
                <span class="nw-step__label"><?php esc_html_e( 'Deploy', 'neoweaver' ); ?></span>
            </div>
        </div>

        <div class="nw-form-container">
            <!-- Step 1: World selection -->
            <div class="nw-form-step nw-form-step--active" data-step="1">
                <h2><?php esc_html_e( 'Choose your World (Node)', 'neoweaver' ); ?></h2>
                <div class="nw-world-list" id="nw-world-list">
                    <div class="nw-skeleton"></div>
                </div>
            </div>

            <!-- Step 2: Campaign name -->
            <div class="nw-form-step" data-step="2">
                <h2><?php esc_html_e( 'Name your Deployment', 'neoweaver' ); ?></h2>
                <label for="nw-campaign-name"><?php esc_html_e( 'Campaign name', 'neoweaver' ); ?></label>
                <input type="text" id="nw-campaign-name" name="campaign_name" maxlength="80"
                       placeholder="<?php esc_attr_e( 'Operation Shadow Grid…', 'neoweaver' ); ?>" />
                <p class="nw-hint"><?php esc_html_e( 'You can rename it later.', 'neoweaver' ); ?></p>
            </div>

            <!-- Step 3: Agent (character) assignment -->
            <div class="nw-form-step" data-step="3">
                <h2><?php esc_html_e( 'Assign a Field Agent', 'neoweaver' ); ?></h2>
                <div class="nw-agent-list" id="nw-agent-list">
                    <div class="nw-skeleton"></div>
                </div>
            </div>

            <!-- Step 4: Deploy confirmation -->
            <div class="nw-form-step" data-step="4">
                <h2><?php esc_html_e( 'Ready to Deploy?', 'neoweaver' ); ?></h2>
                <div class="nw-summary" id="nw-summary"></div>
                <button type="button" class="nw-btn nw-btn--primary" id="nw-deploy-btn">
                    <?php echo $emoji_deploy; ?> <?php esc_html_e( 'Launch Campaign', 'neoweaver' ); ?>
                </button>
            </div>
        </div><!-- .nw-form-container -->

        <div class="nw-nav">
            <button type="button" class="nw-btn nw-btn--ghost" id="nw-prev-btn" disabled>
                <?php esc_html_e( '\u2190 Back', 'neoweaver' ); ?>
            </button>
            <button type="button" class="nw-btn nw-btn--secondary" id="nw-next-btn">
                <?php esc_html_e( 'Next \u2192', 'neoweaver' ); ?>
            </button>
        </div>

    </div><!-- .neoweaver-campaign-creator -->
    <?php
    return ob_get_clean();
}

// ---------------------------------------------------------------------------
// 3. Supabase helper with fixed cache key (wp_json_encode instead of serialize)
// ---------------------------------------------------------------------------

function neoweaver_campaign_creator_supabase_get( string $endpoint, array $query_args = [] ): array {
    // wp_json_encode produces a stable, predictable string for cache key
    // serialize() can be ambiguous with object types; json is safer here
    $cache_key = 'neoweaver_supa_' . md5( $endpoint . wp_json_encode( $query_args ) );
    $cached    = get_transient( $cache_key );

    if ( false !== $cached ) {
        return $cached;
    }

    $supabase_url = defined( 'NEOWEAVER_SUPABASE_URL' ) ? NEOWEAVER_SUPABASE_URL : '';
    $supabase_key = defined( 'NEOWEAVER_SUPABASE_ANON_KEY' ) ? NEOWEAVER_SUPABASE_ANON_KEY : '';

    if ( empty( $supabase_url ) || empty( $supabase_key ) ) {
        return [];
    }

    $url      = trailingslashit( $supabase_url ) . 'rest/v1/' . ltrim( $endpoint, '/' );
    $url      = add_query_arg( $query_args, $url );

    $response = wp_remote_get( $url, [
        'headers' => [
            'apikey'        => $supabase_key,
            'Authorization' => 'Bearer ' . $supabase_key,
            'Content-Type'  => 'application/json',
        ],
        'timeout' => 10,
    ] );

    if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
        return [];
    }

    $data = json_decode( wp_remote_retrieve_body( $response ), true );
    $data = is_array( $data ) ? $data : [];

    set_transient( $cache_key, $data, MINUTE_IN_SECONDS * 5 );

    return $data;
}

// ---------------------------------------------------------------------------
// 4. Register shortcode — was missing entirely before this fix
// ---------------------------------------------------------------------------
add_shortcode( 'neoweaver_campaign_creator', 'neoweaver_campaign_creator_shortcode' );
