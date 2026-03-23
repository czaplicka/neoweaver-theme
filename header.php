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
            <ul class="neo-menu-list">
                <li class="neo-menu-item"><a href="<?php echo esc_url(home_url('/agents/')); ?>"><span class="neo-shortcut">A</span>GENTS</a></li>
                <li class="neo-menu-item"><a href="<?php echo esc_url(home_url('/nodes/')); ?>"><span class="neo-shortcut">N</span>ODES</a></li>
                <li class="neo-menu-item"><a href="<?php echo esc_url(home_url('/deployments/')); ?>"><span class="neo-shortcut">D</span>EPLOS</a></li>
                <li class="neo-menu-item neo-separator"></li>
                <li class="neo-menu-item"><a href="<?php echo esc_url(home_url('/terminal/')); ?>"><span class="neo-shortcut">T</span>ERMINAL</a></li>
                <li class="neo-menu-item neo-highlight"><a href="<?php echo esc_url(home_url('/shop/')); ?>"><span class="neo-shortcut">C</span>REDIT_UP</a></li>
            </ul>
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