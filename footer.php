    <footer class="neo-footer-container neo-crt">
        <div class="footer-columns">
            <div class="footer-col">
                <h3><span>Useful</span> links</h3>
                <ul>
                    <li><a href="<?php echo esc_url( wp_login_url() ); ?>">Log in</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/logs/' ) ); ?>">[LOGS]</a></li>
                    <li><a href="#">Forum</a></li>
                    <li><a href="#">Pricing</a></li>
                </ul>
                <p class="neo-text-dim">
                    CONNECTION: <span class="neo-accent">ENCRYPTED</span>
                </p>
            </div>

            <div class="footer-col">
                <h3><span>Game</span> menu</h3>
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/terminal/' ) ); ?>">Terminal</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/nodes/' ) ); ?>">Nodes</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/agents/' ) ); ?>">Field Agents</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/deployments/' ) ); ?>">Deployments</a></li>
                </ul>
                <p class="neo-log">© 2026 // ARCHITECT_PROTOCOL</p>
            </div>

            <div class="footer-col">
                <h3>Meet<span> NDE</span></h3>
                <ul>
                    <li><a href="#">NDE</a></li>
                    <li><a href="#">Lore</a></li>
                    <li><a href="#">Handbook</a></li>
                    <li><a href="#">About</a></li>
                </ul>
                <p class="neo-tag">FRAY_THRESHOLD: 女0%</p>
            </div>
        </div>
    </footer>

    <?php wp_footer(); ?>
</body>
</html>