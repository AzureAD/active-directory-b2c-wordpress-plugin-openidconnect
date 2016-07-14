<?php
class cpimauth_settings_page
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
            'Cpim Login Settings', 
            'manage_options', 
            'cpimauth-settings-page', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'cpimauth_config_elements' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>CPIM Service Configuration Settings</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'cpimauth_option_group' );   
                do_settings_sections( 'cpimauth-settings-page' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'cpimauth_option_group', // Option group
            'cpimauth_config_elements', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'service_config_section', // ID
            'Cpim Custom Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'cpimauth-settings-page' // Page
        );  

        add_settings_field(
            'cpim_aad_tenant', 
            "Your blog's AAD tenant", 
            array( $this, 'cpim_aad_tenant_callback' ), 
            'cpimauth-settings-page', 
            'service_config_section'
        );      

        add_settings_field(
            'cpim_client_id', // ID
            "Your blog's AAD client Id", // Title 
            array( $this, 'cpim_client_id_callback' ), // Callback
            'cpimauth-settings-page', // Page
            'service_config_section' // Section           
        );      

        add_settings_field(
            'cpim_client_secret', // ID
            "Your blog's AAD client secret", // Title 
            array( $this, 'cpim_client_secret_callback' ), // Callback
            'cpimauth-settings-page', // Page
            'service_config_section' // Section           
        );      

        add_settings_field(
            'cpim_subscriber_policy_id', // ID
            "Your blog's user login policy", // Title 
            array( $this, 'cpim_subscriber_policy_id_callback' ), // Callback
            'cpimauth-settings-page', // Page
            'service_config_section' // Section           
        );      

        add_settings_field(
            'cpim_admin_policy_id', // ID
            "Your blog's admin login policy", // Title 
            array( $this, 'cpim_admin_policy_id_callback' ), // Callback
            'cpimauth-settings-page', // Page
            'service_config_section' // Section           
        );      

        add_settings_field(
            'cpim_localaccountcreation_policy_id', // ID
            "Your blog's user local account creation policy", // Title 
            array( $this, 'cpim_localaccountcreation_policy_id_callback' ), // Callback
            'cpimauth-settings-page', // Page
            'service_config_section' // Section           
        );      

        add_settings_field(
            'cpim_disable_passwords', // ID
            "Disable all passwords", // Title 
            array( $this, 'cpim_disable_passwords_callback' ), // Callback
            'cpimauth-settings-page', // Page
            'service_config_section' // Section           
        );      

        add_settings_field(
            'cpim_use_ssl_for_redirect', // ID
            "Does your blog have an SSL certificate?", // Title 
            array( $this, 'cpim_use_ssl_for_redirect_callback' ), // Callback
            'cpimauth-settings-page', // Page
            'service_config_section' // Section           
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
        if( isset( $input['cpim_client_id'] ) )
            $new_input['cpim_client_id'] = sanitize_text_field( $input['cpim_client_id'] );

        if( isset( $input['cpim_client_secret'] ) )
            $new_input['cpim_client_secret'] = sanitize_text_field( $input['cpim_client_secret'] );

        if( isset( $input['cpim_aad_tenant'] ) )
            $new_input['cpim_aad_tenant'] = sanitize_text_field(strtolower( $input['cpim_aad_tenant'] ));

        if( isset( $input['cpim_subscriber_policy_id'] ) )
            $new_input['cpim_subscriber_policy_id'] = sanitize_text_field(strtolower( $input['cpim_subscriber_policy_id'] ));

        if( isset( $input['cpim_localaccountcreation_policy_id'] ) )
            $new_input['cpim_localaccountcreation_policy_id'] = sanitize_text_field(strtolower( $input['cpim_localaccountcreation_policy_id'] ));

        if( isset( $input['cpim_admin_policy_id'] ) )
            $new_input['cpim_admin_policy_id'] = sanitize_text_field(strtolower( $input['cpim_admin_policy_id'] ));

        if( isset( $input['cpim_disable_passwords'] ) )
            $new_input['cpim_disable_passwords'] = $input['cpim_disable_passwords'];

        if( isset( $input['cpim_use_ssl_for_redirect'] ) )
            $new_input['cpim_use_ssl_for_redirect'] = sanitize_text_field( $input['cpim_use_ssl_for_redirect'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter the settings your created for your blog at http://cpim.windows.net:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function cpim_client_id_callback()
    {
        printf(
            '<input type="text" id="cpim_client_id" name="cpimauth_config_elements[cpim_client_id]" value="%s" />',
            isset( $this->options['cpim_client_id'] ) ? esc_attr( $this->options['cpim_client_id']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function cpim_client_secret_callback()
    {
        printf(
            '<input type="text" id="cpim_client_secret" name="cpimauth_config_elements[cpim_client_secret]" value="%s" />',
            isset( $this->options['cpim_client_secret'] ) ? esc_attr( $this->options['cpim_client_secret']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function cpim_admin_policy_id_callback()
    {
        printf(
            '<input type="text" id="cpim_admin_policy_id" name="cpimauth_config_elements[cpim_admin_policy_id]" value="%s" />',
            isset( $this->options['cpim_admin_policy_id'] ) ? esc_attr( $this->options['cpim_admin_policy_id']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function cpim_disable_passwords_callback()
    {
        $current_value = 0;
        if (!empty($this->options['cpim_disable_passwords']))
            $current_value = $this->options['cpim_disable_passwords'];
        
        echo '<input type="checkbox" id="cpim_disable_passwords" name="cpimauth_config_elements[cpim_disable_passwords]" value="1" class="code" ' . checked( 1, $current_value, false ) . ' />';
    }
    
    /** 
     * Get the settings option array and print one of its values
     */
    public function cpim_subscriber_policy_id_callback()
    {
        printf(
            '<input type="text" id="cpim_subscriber_policy_id" name="cpimauth_config_elements[cpim_subscriber_policy_id]" value="%s" />',
            isset( $this->options['cpim_subscriber_policy_id'] ) ? esc_attr( $this->options['cpim_subscriber_policy_id']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function cpim_localaccountcreation_policy_id_callback()
    {
        printf(
            '<input type="text" id="cpim_localaccountcreation_policy_id" name="cpimauth_config_elements[cpim_localaccountcreation_policy_id]" value="%s" />',
            isset( $this->options['cpim_localaccountcreation_policy_id'] ) ? esc_attr( $this->options['cpim_localaccountcreation_policy_id']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function cpim_use_ssl_for_redirect_callback()
    {
        $current_value = 0;
        if (!empty($this->options['cpim_use_ssl_for_redirect']))
            $current_value = $this->options['cpim_use_ssl_for_redirect'];
        
        echo '<input type="checkbox" id="cpim_use_ssl_for_redirect" name="cpimauth_config_elements[cpim_use_ssl_for_redirect]" value="1" class="code" ' . checked( 1, $current_value, false ) . ' />';
    }
    
    /** 
     * Get the settings option array and print one of its values
     */
    public function cpim_aad_tenant_callback()
    {
        printf(
            '<input type="text" id="cpim_aad_tenant" name="cpimauth_config_elements[cpim_aad_tenant]" value="%s" />',
            isset( $this->options['cpim_aad_tenant'] ) ? esc_attr( $this->options['cpim_aad_tenant']) : ''
        );
    }
}

if( is_admin() )
    $cpimauth_settings_page = new cpimauth_settings_page();

?>