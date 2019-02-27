<?php

/**
 * A class to create and manage the admin's B2C settings page.
 */
class B2C_Settings_Page
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
     * Adds a B2C options page under "Settings"
     */
    public function add_plugin_page()
    {
        add_options_page(
            'Settings Admin', 
            'B2C Authentication Settings', 
            'manage_options', 
            'b2c-settings-page', 
            array( $this, 'create_B2C_page' )
        );
    }

    /**
     * B2C Options page callback
     */
    public function create_B2C_page()
    {
        // Set class property
        $this->options = get_option( 'b2c_config_elements' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Azure AD B2C Settings</h2>           
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
     * Register the B2C options page and add the B2C settings boxes
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
            'Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'b2c-settings-page' // Page
        );  

        add_settings_field(
            'b2c_aad_tenant_name', // ID
            'Tenant Name', // Title 
            array( $this, 'b2c_aad_tenant_name_callback' ), // Callback
            'b2c-settings-page', // Page
            'service_config_section' // Section  
        );      

        add_settings_field(
            'b2c_aad_tenant_domain', // ID
            'Tenant Domain', // Title 
            array( $this, 'b2c_aad_tenant_domain_callback' ), // Callback
            'b2c-settings-page', // Page
            'service_config_section' // Section  
        );      

        add_settings_field(
            'b2c_client_id', // ID
            'Client ID (Application ID)', // Title 
            array( $this, 'b2c_client_id_callback' ), // Callback
            'b2c-settings-page', // Page
            'service_config_section' // Section           
        );      

        add_settings_field(
            'b2c_subscriber_policy_id', // ID
            'Sign-in Policy for Users', // Title 
            array( $this, 'b2c_subscriber_policy_id_callback' ), // Callback
            'b2c-settings-page', // Page
            'service_config_section' // Section           
        );      

        add_settings_field(
            'b2c_admin_policy_id', // ID
            'Sign-in Policy for Admins', // Title 
            array( $this, 'b2c_admin_policy_id_callback' ), // Callback
            'b2c-settings-page', // Page
            'service_config_section' // Section           
        );      

        add_settings_field(
            'b2c_edit_profile_policy_id', // ID
            'Edit Profile Policy', // Title 
            array( $this, 'b2c_edit_profile_policy_id_callback' ), // Callback
            'b2c-settings-page', // Page
            'service_config_section' // Section           
        );
		
        add_settings_field(
            'b2c_password_reset_policy_id', // ID
            'Password Reset Policy', // Title 
            array( $this, 'b2c_password_reset_policy_id_callback' ), // Callback
            'b2c-settings-page', // Page
            'service_config_section' // Section           
        );
		
		add_settings_field(
            'b2c_verify_tokens', // ID
            'Verify ID Tokens', // Title 
            array( $this, 'b2c_verify_tokens_callback' ), // Callback
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
		if( isset( $input['b2c_aad_tenant_name'] ) )
            $new_input['b2c_aad_tenant_name'] = sanitize_text_field(strtolower( $input['b2c_aad_tenant_name'] ));

        if( isset( $input['b2c_aad_tenant_domain'] ) )
            $new_input['b2c_aad_tenant_domain'] = sanitize_text_field(strtolower( $input['b2c_aad_tenant_domain'] ));
            
        if( isset( $input['b2c_client_id'] ) )
            $new_input['b2c_client_id'] = sanitize_text_field( $input['b2c_client_id'] );

        if( isset( $input['b2c_subscriber_policy_id'] ) )
            $new_input['b2c_subscriber_policy_id'] = sanitize_text_field(strtolower( $input['b2c_subscriber_policy_id'] ));
		
		if( isset( $input['b2c_admin_policy_id'] ) )
            $new_input['b2c_admin_policy_id'] = sanitize_text_field(strtolower( $input['b2c_admin_policy_id'] ));

        if( isset( $input['b2c_edit_profile_policy_id'] ) )
            $new_input['b2c_edit_profile_policy_id'] = sanitize_text_field(strtolower( $input['b2c_edit_profile_policy_id'] ));
		
        if( isset( $input['b2c_password_reset_policy_id'] ) )
            $new_input['b2c_password_reset_policy_id'] = sanitize_text_field(strtolower( $input['b2c_password_reset_policy_id'] ));
		
        $new_input['b2c_verify_tokens'] = $input['b2c_verify_tokens'];

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter the settings your created for your blog in the <a href="https://portal.azure.com" target="_blank">Azure Portal</a>';
    }
	
	/** 
     * Get the settings option array and print one of its values
     */
    public function b2c_aad_tenant_name_callback()
    {
        printf(
            '<input type="text" id="b2c_aad_tenant_name" name="b2c_config_elements[b2c_aad_tenant_name]" value="%s" />' 
            . '<br/><i>ex: contoso (used to create metadata endpoint uri like "contoso.b2clogin.com")</i>',
            isset( $this->options['b2c_aad_tenant_name'] ) ? esc_attr( $this->options['b2c_aad_tenant_name']) : ''
        );
    }

	/** 
     * Get the settings option array and print one of its values
     */
    public function b2c_aad_tenant_domain_callback()
    {
        printf(
            '<input type="text" id="b2c_aad_tenant_domain" name="b2c_config_elements[b2c_aad_tenant_domain]" value="%s" />' 
            . '<br/><i>ex: contoso.onmicrosoft.com</i>',
            isset( $this->options['b2c_aad_tenant_domain'] ) ? esc_attr( $this->options['b2c_aad_tenant_domain']) : ''
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
    public function b2c_admin_policy_id_callback()
    {
        printf(
            '<input type="text" id="b2c_admin_policy_id" name="b2c_config_elements[b2c_admin_policy_id]" value="%s" />'
            . '<br/><i>Can be the same as Sign-in Policy for Users but typically includes multi-factor authentication for extra protection of Wordpress administration mode.</i>',
            isset( $this->options['b2c_admin_policy_id'] ) ? esc_attr( $this->options['b2c_admin_policy_id']) : ''
        );
    }
    
    /** 
     * Get the settings option array and print one of its values
     */
    public function b2c_subscriber_policy_id_callback()
    {
        printf(
            '<input type="text" id="b2c_subscriber_policy_id" name="b2c_config_elements[b2c_subscriber_policy_id]" value="%s" />'
            . '<br/><i>Specify a Sign-in Policy if you manage creation of Wordpress subscriber accounts yourself.</i>'
            . '<br/><i>Specify a Sign-in/Sign-up policy to allow Wordpress users to create their own subscriber accounts.</i>',
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
	
    /** 
     * Get the settings option array and print one of its values
     */
    public function b2c_password_reset_policy_id_callback()
    {
        printf(
            '<input type="text" id="b2c_password_reset_policy_id" name="b2c_config_elements[b2c_password_reset_policy_id]" value="%s" />'
            . '<br/><i>Used if your Sign-in Policy for Users is using a sign-in/sign-up policy and the user clicks the forgotten password link.</i>',
            isset( $this->options['b2c_password_reset_policy_id'] ) ? esc_attr( $this->options['b2c_password_reset_policy_id']) : ''
        );
    }
	
	/** 
     * Get the settings option array and print one of its values
     */
    public function b2c_verify_tokens_callback()
    {
		
		if (empty($this->options['b2c_verify_tokens']))
            $this->options['b2c_verify_tokens'] = 0;
        
        $current_value = $this->options['b2c_verify_tokens'];
        
        echo '<input type="checkbox" id="b2c_verify_tokens" name="b2c_config_elements[b2c_verify_tokens]" value="1" class="code" ' . checked( 1, $current_value, false ) . ' />';
    }
}