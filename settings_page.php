<?php
class settings_page
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
            'B2C Authentication Settings', 
            'manage_options', 
            'b2c-settings-page', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'b2c_config_elements' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>B2C Service Configuration Settings</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'b2c_option_group' );   
                do_settings_sections( 'b2c-settings-page' );
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
            'b2c_option_group', // Option group
            'b2c_config_elements', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'service_config_section', // ID
            'B2C Custom Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'b2c-settings-page' // Page
        );  

        add_settings_field(
            'b2c_aad_tenant', 
            "Your blog's AAD tenant name", 
            array( $this, 'b2c_aad_tenant_callback' ), 
            'b2c-settings-page', 
            'service_config_section'
        );      

        add_settings_field(
            'b2c_client_id', // ID
            "Your blog's AAD client ID", // Title 
            array( $this, 'b2c_client_id_callback' ), // Callback
            'b2c-settings-page', // Page
            'service_config_section' // Section           
        );      

        add_settings_field(
            'b2c_client_secret', // ID
            "Your blog's AAD client secret", // Title 
            array( $this, 'b2c_client_secret_callback' ), // Callback
            'b2c-settings-page', // Page
            'service_config_section' // Section           
        );      

        add_settings_field(
            'b2c_subscriber_policy_id', // ID
            "Your blog's user login policy", // Title 
            array( $this, 'b2c_subscriber_policy_id_callback' ), // Callback
            'b2c-settings-page', // Page
            'service_config_section' // Section           
        );      

        add_settings_field(
            'b2c_admin_policy_id', // ID
            "Your blog's admin login policy", // Title 
            array( $this, 'b2c_admin_policy_id_callback' ), // Callback
            'b2c-settings-page', // Page
            'service_config_section' // Section           
        );      

        add_settings_field(
            'b2c_edit_profile_policy_id', // ID
            "Your blog's edit profile policy", // Title 
            array( $this, 'b2c_edit_profile_policy_id_callback' ), // Callback
            'b2c-settings-page', // Page
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
		if( isset( $input['b2c_aad_tenant'] ) )
            $new_input['b2c_aad_tenant'] = sanitize_text_field(strtolower( $input['b2c_aad_tenant'] ));
		
        if( isset( $input['b2c_client_id'] ) )
            $new_input['b2c_client_id'] = sanitize_text_field( $input['b2c_client_id'] );

        if( isset( $input['b2c_client_secret'] ) )
            $new_input['b2c_client_secret'] = sanitize_text_field( $input['b2c_client_secret'] );

        if( isset( $input['b2c_subscriber_policy_id'] ) )
            $new_input['b2c_subscriber_policy_id'] = sanitize_text_field(strtolower( $input['b2c_subscriber_policy_id'] ));
		
		if( isset( $input['b2c_admin_policy_id'] ) )
            $new_input['b2c_admin_policy_id'] = sanitize_text_field(strtolower( $input['b2c_admin_policy_id'] ));

        if( isset( $input['b2c_edit_profile_policy_id'] ) )
            $new_input['b2c_edit_profile_policy_id'] = sanitize_text_field(strtolower( $input['b2c_edit_profile_policy_id'] ));

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter the settings your created for your blog in the azure portal (https://portal.azure.com)';
    }
	
	/** 
     * Get the settings option array and print one of its values
     */
    public function b2c_aad_tenant_callback()
    {
        printf(
            '<input type="text" id="b2c_aad_tenant" name="b2c_config_elements[b2c_aad_tenant]" value="%s" />',
            isset( $this->options['b2c_aad_tenant'] ) ? esc_attr( $this->options['b2c_aad_tenant']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function b2c_client_id_callback()
    {
        printf(
            '<input type="text" id="b2c_client_id" name="b2c_config_elements[b2c_client_id]" value="%s" />',
            isset( $this->options['b2c_client_id'] ) ? esc_attr( $this->options['b2c_client_id']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function b2c_client_secret_callback()
    {
        printf(
            '<input type="text" id="b2c_client_secret" name="b2c_config_elements[b2c_client_secret]" value="%s" />',
            isset( $this->options['b2c_client_secret'] ) ? esc_attr( $this->options['b2c_client_secret']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function b2c_admin_policy_id_callback()
    {
        printf(
            '<input type="text" id="b2c_admin_policy_id" name="b2c_config_elements[b2c_admin_policy_id]" value="%s" />',
            isset( $this->options['b2c_admin_policy_id'] ) ? esc_attr( $this->options['b2c_admin_policy_id']) : ''
        );
    }
    
    /** 
     * Get the settings option array and print one of its values
     */
    public function b2c_subscriber_policy_id_callback()
    {
        printf(
            '<input type="text" id="b2c_subscriber_policy_id" name="b2c_config_elements[b2c_subscriber_policy_id]" value="%s" />',
            isset( $this->options['b2c_subscriber_policy_id'] ) ? esc_attr( $this->options['b2c_subscriber_policy_id']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function b2c_edit_profile_policy_id_callback()
    {
        printf(
            '<input type="text" id="b2c_edit_profile_policy_id" name="b2c_config_elements[b2c_edit_profile_policy_id]" value="%s" />',
            isset( $this->options['b2c_edit_profile_policy_id'] ) ? esc_attr( $this->options['b2c_edit_profile_policy_id']) : ''
        );
    }
}

if (is_admin()) $settings_page = new settings_page();

?>