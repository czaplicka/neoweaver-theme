<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class('neo-terminal-root'); ?>>

<header class="neo-terminal-header">
    <div class="neo-menu-container">
        <button class="neo-menu-trigger" onclick="toggleNeoMenu()">
            <span class="neo-menu-icon">▀</span> 
            <span class="neo-menu-label">SYSTEM</span>
        </button>

        <nav id="neo-main-nav" class="neo-dropdown">
            <div class="neo-scanline"></div>
            <?php neoweaver_header_menu(); ?>
        </nav>
    </div>

    <div class="neo-system-id">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="neo-brand-link">
            <img src="<?php echo esc_url( content_url( '/uploads/logonw.svg' ) ); ?>" 
                 alt="NEOWEAVE" 
                 class="neo-logo">
        </a>
    </div>

    <div class="neo-terminal-meta">
        <span class="neo-label">SYNC:</span>
        <span id="neo-sync" class="neo-value">98.4%</span>
    </div>

    <div class="neo-terminal-meta">
        <span class="neo-label">TIME:</span>
        <span id="neo-clock" class="neo-value-clock">00:00:00</span>
    </div>
</header>
