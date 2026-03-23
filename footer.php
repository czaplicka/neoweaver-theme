<footer class="neo-footer-container neo-crt">
    <div class="neoweave-terminal">
        <div class="neo-footer-columns">
            <div class="neo-footer-col">
                <h3 class="neo-footer-title"><span>USEFUL</span>_LINKS</h3>
                <ul class="neo-footer-list">
                    <li><a href="javascript:void(0)" onclick="neoOpenAuthModal();">Log in</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/logs/' ) ); ?>">> [LOGS]</a></li>
                    <li><a href="https://nde.nieodparady.pl/neoweaver/">> FORUM</a></li>
                    <li><a href="/shop/">> PRICING</a></li>
                </ul>
                <p class="neo-text-dim">
                    CONNECTION: <span class="neo-accent">ENCRYPTED</span>
                </p>
            </div>

            <div class="neo-footer-col">
                <h3 class="neo-footer-title"><span>GAME</span>_MENU</h3>
                <ul class="neo-footer-list">
                    <li><a href="<?php echo esc_url( home_url( '/terminal/' ) ); ?>">> TERMINAL</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/nodes/' ) ); ?>">> NODES</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/agents/' ) ); ?>">> FIELD_AGENTS</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/deployments/' ) ); ?>">> DEPLOYMENTS</a></li>
                </ul>
                <p class="neo-log-meta">© 2026 // ARCHITECT_PROTOCOL</p>
            </div>

            <div class="neo-footer-col">
                <h3 class="terminal-header"><span>NDE</span>_SYSTEM</h3>
                <ul class="neo-footer-list">
                    <li><a href="https://nde.nieodparady.pl/nde/">> NDE_ENGINE</a></li>
                    <li><a href="https://nde.nieodparady.pl/lore/">> LORE_KERNEL</a></li>
                    <li><a href="https://nde.nieodparady.pl/handbook/">> HANDBOOK</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">> ABOUT</a></li>
                </ul>
                <p class="neo-tag-status">FRAY_THRESHOLD: <span id="sync-value">98.4%</span></p>
            </div>
        </div>
        
        <div class="neo-footer-bottom-line">
            <span class="neo-jitter-active">SYS_STATUS: NOMINAL</span>
            <span class="neo-scanline-tiny"></span>
        </div></div>
    </footer>

<div class="neo-auth-modal" id="neo-auth-modal">
  <div class="neo-auth-backdrop" onclick="neoCloseAuthModal()"></div>

  <div class="neo-auth-window neoweave-terminal">
    <div class="terminal-header">
      ACCESS_GATEWAY::IDENT_VERIFICATION
    </div>

    <h2 class="terminal-title">
      SYSTEM_LOGIN<span class="terminal-cursor"></span>
    </h2>

    <div class="status-bar">
      STATUS: CREDENTIAL_CHECKPOINT_ACTIVE
    </div>

    <div class="neo-auth-tabs">
      <button type="button" class="neo-auth-tab is-active"
              data-target="neo-auth-login"
              onclick="neoSwitchAuthTab('login')">
        LOGIN
      </button>
      <button type="button" class="neo-auth-tab"
              data-target="neo-auth-register"
              onclick="neoSwitchAuthTab('register')">
        REGISTER
      </button>
    </div>

    <div class="neo-auth-panel is-active" id="neo-auth-login">
      <?php
      wp_login_form([
        'label_username' => 'FIELD_AGENT_ID',
        'label_password' => 'ACCESS_KEY',
        'label_remember' => 'REMEMBER_SESSION',
        'label_log_in'   => 'INITIALIZE_LOGIN',
        'remember'       => true,
       // 'redirect'       => home_url('/terminal'),
        'id_username'    => 'neo-user-login',
        'id_password'    => 'neo-user-pass',
        'id_submit'      => 'neo-login-submit'
      ]);
      ?>
    </div>

    <div class="neo-auth-panel" id="neo-auth-register">
  <form method="post" action="<?php echo esc_url( site_url('wp-login.php?action=register', 'login_post') ); ?>">
    <p class="neo-auth-label">CONTACT_FREQUENCY (EMAIL)</p>
    <p><input type="email" name="user_email" required></p>

    <?php do_action('register_form'); ?>

    <p class="neo-auth-submit">
      <button type="submit" class="button button-primary">
        INITIALIZE_REGISTRATION
      </button>
    </p>
  </form>
</div>

    <div class="entropy-container">
      <span class="entropy-warning">SECURITY_LOG</span>
      <span class="entropy-bar">IDENTITY_NOISE_LEVEL</span>
    </div>

    <div class="footer-log">
      CONNECTION_STATUS: <span>ENCRYPTED</span>
    </div>
  </div>
</div>


    <?php wp_footer(); ?>
</body>
</html>