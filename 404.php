<?php
/**
 * 404 Error Template for NeoWeave
 */
get_header();
?>

<main class="neo-terminal-container neo-terminal--error">
    <div class="neo-terminal-header-meta">
        [ROUTING_ERROR] [NODE_NOT_FOUND] [PRIORITY: HIGH]
    </div>

    <div class="neo-status-bar">
        STATUS: <span class="neo-error">SIGNAL_LOST</span> // CODE: 404 // NODE: UNKNOWN
    </div>

    <h1 class="neo-title">
        NODE_NOT_FOUND<span class="neo-cursor"></span>
    </h1>

    <div class="neo-content-area">
        <p>
            The path you attempted to access does not resolve to a valid Node 
            within the <span class="neo-accent">NeoWeaver</span> architecture. 
            The thread may have been severed, archived, or never instantiated.
        </p>

        <h3 class="neo-label">POSSIBLE_CAUSES:</h3>
        <ul class="neo-footer-list">
            <li>> Broken or outdated uplink.</li>
            <li>> Node has been decommissioned by the Architect.</li>
            <li>> Incorrect address injected into the Weave.</li>
        </ul>

        <h3 class="neo-label">RECOMMENDED_ACTIONS:</h3>
        <ul class="neo-footer-list">
            <li>> Return to the <a href="<?php echo esc_url( home_url( '/' ) ); ?>">[ ARCHITECT_CORE ]</a>.</li>
            <li>> Access the <a href="<?php echo esc_url( home_url( '/logs/' ) ); ?>">[ ACTIVE_LOGS ]</a> terminal.</li>
            <li>> Recalibrate your deployment path and try again.</li>
        </ul>
    </div>

    <div class="neo-footer-log">
        [END_OF_TRANSMISSION] [RELINK_SUGGESTED] [EOT]
    </div>
</main>

<?php
get_footer();