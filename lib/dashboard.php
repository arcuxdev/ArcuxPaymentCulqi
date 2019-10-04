<?php
class tokensDashboard {
  public function __construct() {
    add_action('admin_menu', array( $this,'add_settings_page'));
    add_action('admin_init', array( $this,'culqi_init'));
    add_action('admin_notices', array( $this,'tokens_culqi_admin_notice'));
  }
 
  public function add_settings_page() {
    add_submenu_page (
      'options-general.php', 
      'Ajustes Culqi',
      'Culqi Settings',
      'manage_options', 
      'culqi_settings', 
      array($this, 'create_admin_page')
    );
  }

  public function culqi_init() {
    add_settings_section(
      'eg_tokens_culqi_section',
      'Tokens Culqi',
      null,
      'culqi_settings'
    );
    add_settings_field(
      'token_public',
      'Token Public',
      array( $this, 'display_tk_public'),
      'culqi_settings',
      'eg_tokens_culqi_section'
    );
    add_settings_field(
      'token_private',
      'Token Private',
      array( $this, 'display_tk_private'),
      'culqi_settings',
      'eg_tokens_culqi_section'
    );
    add_settings_section(
      'eg_notification_culqi_section',
      'Notificaciones Culqi',
      null,
      'culqi_settings'
    );
    add_settings_field(
      'notification_slack',
      'Url Notificación por slack',
      array( $this, 'display_notification_slack'),
      'culqi_settings',
      'eg_notification_culqi_section'
    );
    add_settings_field(
      'notification_email',
      'Notificación al correo',
      array( $this, 'display_notification_email'),
      'culqi_settings',
      'eg_notification_culqi_section'
    );

    register_setting('culqi_settings','token_private');
    register_setting('culqi_settings','token_public');
    register_setting('culqi_settings','notification_slack');
    register_setting('culqi_settings','notification_email');
  }

  public function display_tk_private() {
    echo '<input class="regular-text" type="text" name="token_private" id="token_private" value="'. get_option('token_private') .'" required/>';
  }

  public function display_tk_public() {
    echo '<input class="regular-text" type="text" name="token_public" id="token_public" value="'. get_option('token_public') .'" required />';
  }

  public function display_notification_slack() {
    echo '<input class="regular-text" type="text" name="notification_slack" id="notification_slack" value="'. get_option('notification_slack') .'" />';
  }

  public function display_notification_email() {
    echo '<input name="notification_email" id="notification_email" type="checkbox" value="1" class="code" ' . checked( 1, get_option( 'notification_email' ), false ) . ' /> Enviar notificación al correo.';
  }
   
  public function create_admin_page () {
    if (!current_user_can('manage_options')) {
      return;
    } ?>

    <div class="wrap">
      <h1 style="padding: 15px 0 30px;"><strong>Culqi settings</strong></h1>
      <form method="POST" action="options.php">
        <?php         
          settings_fields('culqi_settings');   
          do_settings_sections('culqi_settings');
          submit_button(); 
        ?>
      </form>
    </div>
    <?php
  }

  public function tokens_culqi_admin_notice() {
    global $pagenow;
    $tk_private = strlen(get_option('token_private'));
    $tk_public  = strlen(get_option('token_public'));
    $screen     = get_current_screen();

      if ($tk_private == 0 || $tk_public == 0) { ?>
        <div class="notice notice-error is-dismissible notice-culqi">
          <p>Ingrese los tokens de Culqi. <a href="options-general.php?page=culqi_settings">Configuración</a></p>
        </div>
      <?php
      };
  }
}

?>