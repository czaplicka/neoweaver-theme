<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<header class="neo-terminal-header neo-crt">
    <div class="system-id">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="system-id-link">
            <img src="https://aquamarine-herring-148657.hostingersite.com/wp-content/uploads/2026/03/logonw.webp"
                 alt="NEOWEAVE"
                 class="terminal-logo">
        </a>
        <a href="<?php echo esc_url( home_url( '/terminal/' ) ); ?>">
            <div class="brand-text">
                <span class="status-dot"></span> ARCHITECT_PROTOCOL
            </div>
        </a>
    </div>
    <div class="node-info">
        <span class="label">NODE:</span>
        <span class="value" id="node-name-display">CONNECTING...</span>
    </div>
    <div class="terminal-meta">
        <span class="label">SYNC:</span>
        <span id="sync-value" class="value">98.4%</span>
        <span class="label">TIME:</span>
        <span id="terminal-clock" class="value">00:00:00</span>
    </div>
</header>
