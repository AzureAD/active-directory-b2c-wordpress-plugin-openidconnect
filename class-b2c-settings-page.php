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
            'b2c_aad_tenant', // ID
            'Tenant Name / Tenant ID', // Title 
            array( $this, 'b2c_aad_tenant_callback' ), // Callback
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
            'b2c_verify_tokens', // ID
            'Verify ID Tokens', // Title 
            array( $this, 'b2c_verify_tokens_callback' ), // Callback
            'b2c-settings-page', // Page
            'service_config_section' // Section           
        );     		

        add_settings_field(
            'b2c_GA_Grant_Admins', // ID
            'Grant B2C Global Admins Wordpress Administrator Role', // Title
            array( $this, 'b2c_GA_Grant_Admins_callback'), // Callback
            'b2c-settings-page', // Page
            'service_config_section' // Section
        );

		add_settings_field(
			'b2c_client_secret', // ID
			'Client Secret', //Title
			 array( $this, 'b2c_client_secret_callback' ), // Callback
            'b2c-settings-page', // Page
            'service_config_section' // Section  
		);

		add_settings_field(
			'b2c_graph_id', // ID
			'Graph API Application ID (Not B2C Application ID)', //Title
			 array( $this, 'b2c_graph_id_callback' ), // Callback
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

        if( isset( $input['b2c_subscriber_policy_id'] ) )
            $new_input['b2c_subscriber_policy_id'] = sanitize_text_field(strtolower( $input['b2c_subscriber_policy_id'] ));
		
		if( isset( $input['b2c_admin_policy_id'] ) )
            $new_input['b2c_admin_policy_id'] = sanitize_text_field(strtolower( $input['b2c_admin_policy_id'] ));

        if( isset( $input['b2c_edit_profile_policy_id'] ) )
            $new_input['b2c_edit_profile_policy_id'] = sanitize_text_field(strtolower( $input['b2c_edit_profile_policy_id'] ));
		
        $new_input['b2c_verify_tokens'] = $input['b2c_verify_tokens'];

        $new_input['b2c_GA_Grant_Admins'] = $input['b2c_GA_Grant_Admins'];

		if( isset( $input['b2c_client_secret'] ) )
            $new_input['b2c_client_secret'] = sanitize_text_field( $input['b2c_client_secret'] );

		if( isset( $input['b2c_graph_id'] ) )
            $new_input['b2c_graph_id'] = sanitize_text_field( $input['b2c_graph_id'] );

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
    public function b2c_aad_tenant_callback()
    {
        printf(
            '<input type="text" id="b2c_aad_tenant" name="b2c_config_elements[b2c_aad_tenant]" value="%s" />' 
            . '<br/><i>i.e. contoso.onmicrosoft.com</i>',
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

    /**
     * Get the settings option array and print one of its values
     */
     public function b2c_GA_Grant_Admins_callback()
     {
         if(empty($this->options['b2c_GA_Grant_Admins']))
            $this->options['b2c_GA_Grant_Admins'] = 0;

            $current_value = $this->options['b2c_GA_Grant_Admins'];

            echo '<input type="checkbox" id="b2c_GA_Grant_Admins" name="b2c_config_elements[b2c_GA_Grant_Admins]" value="1" class="code" ' . checked( 1, $current_value, false ) . ' />';
     }

     /** 
     * Get the settings option array and print one of its values
     */
    public function b2c_graph_id_callback()
    {
        printf(
            '<input type="text" id="b2c_graph_id" name="b2c_config_elements[b2c_graph_id]" value="%s" />',
            isset( $this->options['b2c_graph_id'] ) ? esc_attr( $this->options['b2c_graph_id']) : ''
        );
    }

	/** 
     * Get the settings option array and print one of its values
     */
    public function b2c_client_secret_callback()
    {
        printf(
            '<input type="password" id="b2c_client_secret" name="b2c_config_elements[b2c_client_secret]" value="%s" />',
            isset( $this->options['b2c_client_secret'] ) ? esc_attr( $this->options['b2c_client_secret']) : ''
        );
    }
}