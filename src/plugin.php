<?php
/*
    Plugin Name:    CANDDi tracking
    Plugin URI:     https://github.com/Canddi/CANDDi_Plugin_Wordpress
    Description:    CANDDi tracking installation for wordpress users
    Version:        1.0
    Author:         CANDDi
    Author URI:     www.canddi.com
    License:
    Copyright       (c) Campaign and Digital Intelligence 2014
*/


class CANDDiPlugin
{
    private $options;

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'canddi_add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'canddi_page_init' ) );
        add_action( 'admin_init', array( $this, 'canddi_plugin_settings_page_permission' ) );
        add_action( 'admin_head', array( $this, 'canddi_admin_js' ) );
        add_action( 'wp_head', array ( $this, 'canddi_custom_js') );
        // update our data structure to migrate old data to new
        add_action( 'plugins_loaded', array( $this, 'canddi_rename_variables' ) );
    }

    //check user has permission to access this plugin
    public function canddi_plugin_settings_page_permission() {
        if(!current_user_can('manage_options')) {
            return;
        }

        //Setting links while plugin is active
        add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'canddi_add_action_links' ) );
    }

    public function canddi_add_action_links($links) {
        $canddi_admin_links = array(
            '<a href="' . admin_url( 'options-general.php?page=canddi_settings' ) . '">Settings</a>',
        );

        return array_merge( $links, $canddi_admin_links );
    }

    public function canddi_add_plugin_page() {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin',
            'CANDDi',
            'manage_options',
            'canddi_settings',
            array( $this, 'canddi_create_admin_page' )
        );
    }

    public function canddi_create_admin_page() {
        $this->options = get_option( 'canddi_options' );
        ?>
        <div class="wrap">
            <h2>CANDDi Tracking</h2>
            <form method="post" action="options.php">
                <?php

                settings_fields( 'canddi_option_group' );
                do_settings_sections( 'canddi-setting-admin' );
                submit_button();
                $this->canddi_print_section_info_video();
                ?>
            </form>
        </div>
        <?php
    }

    public function canddi_page_init() {
        register_setting(
            'canddi_option_group',
            'canddi_options',
            array( $this, 'canddi_sanitize' )
        );

        add_settings_section(
            'canddi_setting_section',
            'CANDDi',
            array( $this, 'canddi_print_section_info' ),
            'canddi-setting-admin'
        );
        add_settings_field(
            'canddi_tracking_code',
            '',
            array( $this, 'canddi_script_textarea' ),
            'canddi-setting-admin',
            'canddi_setting_section'
        );
    }

    public function canddi_sanitize( $input ) {
        $new_input = array();

        if( isset( $input['canddi_tracking_code'] ) ) {
            $new_input['canddi_tracking_code'] =  trim($input['canddi_tracking_code']);
        }

        return $new_input;
    }

    public function canddi_print_section_info() {
        print 'Integrate <a href="http://www.canddi.com" target="_blank">CANDDi</a> with your WordPress website<br/><br/>
               <strong>Enter your CANDDi Tracker ID below</strong><br/>';
    }

    public function canddi_script_textarea() {
        $canddi_tracking_code = isset( $this->options['canddi_tracking_code'] ) ? esc_attr( $this->options['canddi_tracking_code']) : '';
        $safe_text = apply_filters( 'esc_textarea', $canddi_tracking_code);

        ?>
        <input cols="75" rows="15" name="<?php echo 'canddi_options[canddi_tracking_code]'; ?>" type="text" value="<?php echo trim($safe_text); ?>"/>
        <?php
    }

    //hook to display the script on front side
    public function canddi_custom_js() {
        $get_all_value_array = get_site_option( 'canddi_options', true );
        $canddi_tracking_id = $get_all_value_array['canddi_tracking_code'];

        $canddi_tracking_code = "
            <!-- CANDDi https://www.canddi.com/privacy -->
            <script async type='text/javascript' src='//cdns.canddi.com/p/$canddi_tracking_id.js'></script>
            <noscript style='position: absolute; left: -10px;'><img src='https://i.canddi.com/i.gif?A=$canddi_tracking_id'/></noscript>
            <!-- /END CANDDi -->
        ";

        if($canddi_tracking_code !='')
        {
            $safe_text = apply_filters( 'esc_textarea', $canddi_tracking_code );

            if ( !empty( $safe_text ) ) {
                echo trim((htmlspecialchars_decode($safe_text)));
            }
        }
    }

    public function canddi_print_section_info_video() {
        echo '<div class="textare_descrption">
                <h1>About CANDDi</h1>
                <div class="video_cover">
                    <iframe width="560" height="315" src="https://www.youtube.com/embed/3I-F6645GiE" frameborder="0" allowfullscreen></iframe>
                </div>
            </div>';
    }

    public function canddi_admin_js() {
        global $current_screen;

        $settings_page_canddi_settings=  $current_screen->base;

        if($settings_page_canddi_settings == 'settings_page_canddi_settings') {
            wp_register_script('canddi-scripts',    plugins_url('/js/custom.js', __FILE__ ) );
            wp_enqueue_script('canddi-scripts');
            wp_localize_script('canddi-scripts', 'wp_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
        }
    }

    public function canddi_rename_variables() {
        // make sure were not firing it for normal users
        if(!current_user_can('manage_options')) {
            return;
        }

        $new_values = get_site_option( 'canddi_options', true );
        $new_script = $new_values['canddi_tracking_code'];

        if($new_script == '') {
            $old_values = get_site_option( 'my_option_name', true );
            $old_script = $old_values['script_textarea'];

            if($old_script != '') {
                $safe_old_text = apply_filters( 'esc_textarea', $old_script );
                $new_value = array('canddi_tracking_code' => $safe_old_text);

                update_option('canddi_options', $new_value, true);
            }
        }
    }
}

// instantiate our class to get everything running
$canddi_tracking_code = new CANDDiPlugin();