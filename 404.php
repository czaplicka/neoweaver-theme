<?php
get_header();
?>

<main class="neoweave-terminal neoweave-terminal--post">
    <div class="terminal-header">
        [ROUTING_ERROR] [NODE_NOT_FOUND]
    </div>

    <div class="status-bar">
        STATUS: SIGNAL_LOST // CODE: 404
    </div>

    <h1 class="terminal-title">
        NODE_NOT_FOUND<span class="terminal-cursor"></span>
    </h1>

    <p>
        The path you attempted to access does not resolve to a valid Node
        within the NeoWeave architecture. The thread may have been severed,
        archived, or never instantiated.
    </p>

    <span class="terminal-section-title">POSSIBLE_CAUSES</span>
    <ul>
        <li>Broken or outdated uplink.</li>
        <li>Node has been decommissioned by the Architect.</li>
        <li>Incorrect address injected into the Weave.</li>
    </ul>

    <span class="terminal-section-title">RECOMMENDED_ACTIONS</span>
    <ul>
        <li>Return to the <a href="<?php echo esc_url( home_url( '/' ) ); ?>">[ ARCHITECT_CORE ]</a>.</li>
        <li>Access the <a href="<?php echo esc_url( home_url( '/logs/' ) ); ?>">[ ACTIVE_LOGS ]</a> terminal.</li>
        <li>Recalibrate your deployment path and try again.</li>
    </ul>

    <div class="footer-log">
        [END_OF_TRANSMISSION] [RELINK_SUGGESTED] [EOT]
    </div>
</main>

<?php
get_footer();
