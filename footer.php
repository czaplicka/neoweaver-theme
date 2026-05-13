<footer class="neo-footer-container neo-crt">
    <center><a href="javascript:void(0)" onclick="neoOpenAuthModal();"><button class="neo-login-submit">Operator, log in or register here</button></a></center>
    <div class="neoweave-terminal">
        <div class="neo-footer-columns">

            <div class="neo-footer-col">
                <h3 class="neo-footer-title"><span>USEFUL</span>_LINKS</h3>
                <?php neoweaver_footer_menu( 'neo_footer_useful' ); ?>
                <p class="neo-text-dim">
                    CONNECTION: <span class="neo-accent">ENCRYPTED</span>
                </p>
            </div>

            <div class="neo-footer-col">
                <h3 class="neo-footer-title"><span>GAME</span>_MENU</h3>
                <?php neoweaver_footer_menu( 'neo_footer_game' ); ?>
                <p class="neo-log-meta">© 2026 // ARCHITECT_PROTOCOL</p>
            </div>

            <div class="neo-footer-col">
                <h3 class="neo-footer-title"><span>NDE</span>_SYSTEM</h3>
                <?php neoweaver_footer_menu( 'neo_footer_nde' ); ?>
                <p class="neo-tag-status">FRAY_THRESHOLD: <span id="sync-value">98.4%</span></p>
            </div>

            <div class="neo-footer-col">
                <h3 class="neo-footer-title"><span>EXTRA</span>_CHANNELS</h3>
                <?php neoweaver_footer_menu( 'neo_footer_legal' ); ?>
                <p class="neo-text-dim">NODE: <span class="neo-accent">STABLE</span></p>
            </div>

        </div>

        <div class="neo-footer-bottom-line">
            <span class="neo-jitter-active">SYS_STATUS: NOMINAL</span>
            <span class="neo-scanline-tiny"></span>
        </div>
    </div>
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
