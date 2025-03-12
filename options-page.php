<?php
class MySettingsPage
{
  /**
   * Holds the values to be used in the fields callbacks
   */
  private $options;

  /**
   * Start up
   */
  public function __construct()
  {
    add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
    add_action( 'admin_init', array( $this, 'page_init' ) );
  }

  /**
   * Add options page
   */
  public function add_plugin_page()
  {
    // This page will be under "Settings"
    add_options_page(
      'Settings Admin',
      'Omonsch Customer Backend',
      'manage_options',
      'omonsch-customer-backend',
      array( $this, 'create_admin_page' )
    );
  }

  /**
   * Options page callback
   */
  public function create_admin_page()
  {
    // Set class property
    $this->options = get_option( 'omonsch_cb_options' );
    ?>
    <div class="wrap">
      <form method="post" action="options.php">
        <?php
        // This prints out all hidden setting fields
        settings_fields( 'omonsch_cb_group' );
        do_settings_sections( 'omonsch_cb' );
        submit_button();
        ?>
      </form>
        <style>
            input:not([type="submit"]) {
                width: 100%;
            }
        </style>
    </div>
    <?php
  }

  /**
   * Register and add settings
   */
  public function page_init()
  {
    register_setting(
      'omonsch_cb_group', // Option group
      'omonsch_cb_options', // Option name
      array( $this, 'sanitize' ) // Sanitize
    );

    add_settings_section(
      'omonsch_cb_general', // ID
      'Omonsch Customer Backend', // Title
      array( $this, 'print_section_info' ), // Callback
      'omonsch_cb' // Page
    );

    add_settings_field(
      'github_api_token',
      'Github API Token',
      array( $this, 'cb_github_api_token_callback' ),
      'omonsch_cb',
      'omonsch_cb_general'
    );
  }

  /**
   * Sanitize each setting field as needed
   *
   * @param array $input Contains all settings fields as array keys
   */
  public function sanitize( $input )
  {
    $new_input = array();
    if( isset( $input['id_number'] ) )
      $new_input['id_number'] = absint( $input['id_number'] );

    if( isset( $input['github_api_token'] ) )
      $new_input['github_api_token'] = sanitize_text_field( $input['github_api_token'] );

    return $new_input;
  }

  /**
   * Print the Section text
   */
  public function print_section_info()
  {
    print 'Settings for the Customer Wordpress Backend:';
  }

  /**
   * Get the settings option array and print one of its values
   */
  public function cb_github_api_token_callback()
  {
    printf(
      '<input type="text" id="github_api_token" name="omonsch_cb_options[github_api_token]" value="%s" />',
      isset( $this->options['github_api_token'] ) ? esc_attr( $this->options['github_api_token']) : ''
    );
  }
}

if( is_admin() )
  $my_settings_page = new MySettingsPage();
