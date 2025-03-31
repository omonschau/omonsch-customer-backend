<?php
class MySettingsPage
{
    private $options;
    private $cap_options;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
    }

    public function add_plugin_page()
    {
        // Only for ADMINS
        add_options_page(
                'Settings Admin',
                'Customer Backend',
                'manage_options',
                'omonsch-customer-backend',
                array($this, 'create_admin_page')
        );
    }

    public function create_admin_page()
    {
        $this->options = get_option('omonsch_cb_options');
        $this->cap_options = get_option('omonsch_cb_options_caps');
        ?>
        <div class="wrap">
            <form method="post" action="options.php">
                <?php
                settings_fields('omonsch_cb_group');
                do_settings_sections('omonsch_cb');
                submit_button();
                ?>
            </form>
            <style>
                input:not([type="submit"], [type="checkbox"]) {
                    width: 100%;
                }
                .checkbox-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                    gap: 10px;
                }
                .checkbox-grid label {
                    display: flex;
                    align-items: center;
                    gap: 5px;
                }
            </style>
        </div>
        <?php
    }

    public function page_init()
    {
        register_setting('omonsch_cb_group', 'omonsch_cb_options', array($this, 'sanitize'));
        register_setting('omonsch_cb_group', 'omonsch_cb_options_caps', array($this, 'sanitize_caps'));

        add_settings_section(
                'omonsch_cb_general',
                'Allgemeine Einstellungen',
                array($this, 'print_section_info'),
                'omonsch_cb'
        );

        add_settings_field('github_api_token', 'Github API Token', array($this, 'cb_github_api_token_callback'), 'omonsch_cb', 'omonsch_cb_general');

        add_settings_section(
                'omonsch_cb_permissions',
                'Kundenberechtigungen',
                function() { ?>
                    <p>Wähle aus, welche Berechtigungen Kunden erhalten sollen.</p>
                    <u style="cursor: pointer;" onclick="toggleAllInputs()">Alles an/abwählen</u>
                    <script>
                        function toggleAllInputs() {
                            const inputs = document.querySelectorAll('input[type="checkbox"]');
                            const checked = inputs[0].checked;
                            inputs.forEach(input => input.checked = !checked);
                        }
                    </script>
                <?php },
                'omonsch_cb'
        );

        $hide = [
                'hide_customizer' => 'Customizer verstecken',
                'hide_options_writing' => 'Einstellungen => Schreiben verstecken',
                'hide_options_reading' => 'Einstellungen => Lesen verstecken',
                'hide_options_discussion' => 'Einstellungen => Diskussion verstecken',
                'hide_options_media' => 'Einstellungen => Medien verstecken',
                'hide_options_permalink' => 'Einstellungen => Permalinks verstecken',
        ];

        $caps = [
                'edit_theme_options' => 'Theme-Optionen bearbeiten',
                'edit_posts' => 'Beiträge bearbeiten',
                'delete_posts' => 'Beiträge löschen',
                'delete_others_posts' => 'Fremde Beiträge löschen',
                'edit_pages' => 'Seiten bearbeiten',
                'delete_pages' => 'Seiten löschen',
                'delete_others_pages' => 'Fremde Seiten löschen',
                'edit_published_pages' => 'Veröffentlichte Seiten bearbeiten',
                'delete_private_pages' => 'Private Seiten löschen',
                'delete_private_posts' => 'Private Beiträge löschen',
                'edit_private_pages' => 'Private Seiten bearbeiten',
                'edit_private_posts' => 'Private Beiträge bearbeiten',
                'publish_posts' => 'Beiträge veröffentlichen',
                'publish_pages' => 'Seiten veröffentlichen',
                'edit_others_pages' => 'Fremde Seiten bearbeiten',
                'edit_others_posts' => 'Fremde Beiträge bearbeiten',
                'edit_published_posts' => 'Veröffentlichte Beiträge bearbeiten',
                'upload_files' => 'Dateien hochladen'
        ];

        add_settings_field('customer_capabilities', 'Berechtigungen ausschalten', function() use ($caps) {
            echo '<div class="checkbox-grid">';
            foreach ($caps as $key => $label) {
                $checked = isset($this->cap_options[$key]) ? 'checked' : '';
                echo "<label><input type='checkbox' name='omonsch_cb_options_caps[$key]' value='1' $checked> $label</label>";
            }
            echo '</div>';
        }, 'omonsch_cb', 'omonsch_cb_permissions');

        add_settings_field('customer_hide', 'Ausblenden von Bereichen', function() use ($hide) {
            echo '<div class="checkbox-grid">';
            foreach ($hide as $key => $label) {
                $checked = isset($this->cap_options[$key]) ? 'checked' : '';
                echo "<label><input type='checkbox' name='omonsch_cb_options_caps[$key]' value='1' $checked> $label</label>";
            }
            echo '</div>';
        }, 'omonsch_cb', 'omonsch_cb_permissions');
    }

    public function sanitize($input)
    {
        $new_input = array();
        if (isset($input['github_api_token']))
            $new_input['github_api_token'] = sanitize_text_field($input['github_api_token']);

        return $new_input;
    }

    public function sanitize_caps($input)
    {
        return is_array($input) ? array_map('sanitize_text_field', $input) : array();
    }

    public function print_section_info()
    {
        print 'Einstellungen für das Kunden-Backend:';
    }

    public function cb_github_api_token_callback()
    {
        printf(
                '<input type="text" id="github_api_token" name="omonsch_cb_options[github_api_token]" value="%s" />',
                isset($this->options['github_api_token']) ? esc_attr($this->options['github_api_token']) : ''
        );
    }
}
// Check if admin
add_action('init','admin_check');
function admin_check()
{
    if (in_array('administrator', wp_get_current_user()->roles)) {
        $my_settings_page = new MySettingsPage();
    }
}
