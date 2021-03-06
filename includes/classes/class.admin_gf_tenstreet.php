<?php

/**
 * Gravity Forms TenStreet WordPress Admin Class
 * 
 * This class is responsible for defining WordPress admin functionality
 * 
 * @author arstropica
 *
 */
class Admin_GF_TenStreet extends GF_TenStreet {
    
    /**
     * Network Admin Class
     *
     * Generated by '__construct' method.
     *
     * @var Network_Admin_GF_TenStreet
     */
    private $network_admin_gf_tenstreet;
    
    /**
     * Constructor
     */
    function __construct() {
        if (is_network_admin()) {
            
            $this->network_admin_gf_tenstreet = new Network_Admin_GF_TenStreet();
        } else {
            
            // Single Edit Handler
            add_action( 'admin_action_settings_update_admin_gf_tenstreet', array (
                $this,
                'settings_update_Admin_GF_TenStreet'
            ) );
            
            // Load Assets
            add_action( 'admin_menu', array (
                &$this,
                'load_assets_Admin_GF_TenStreet'
            ) );
            
            // Add Notices
            add_action( 'admin_notices', array (
                $this,
                'notices_Admin_GF_TenStreet'
            ) );
            
            // Async
            add_filter( 'clean_url', array (
                $this,
                'add_async_forscript'
            ), 11, 1 );
        }
    }
    
    /**
     * Loads Assets.
     *
     * @return void
     */
    function load_assets_Admin_GF_TenStreet() {
        if (is_admin()) {
            
            // this will run when in the WordPress admin
            if (! is_network_admin()) {
                
                // $this->page_hook = add_menu_page('Lead Settings', 'TenStreet', 'manage_options', 'gf-tenstreet', array(&$this, 'display_settings_Admin_GF_TenStreet'));
                $this->page_hook = add_submenu_page( 'edit.php?post_type=gftenstreet', 'TenStreet', 'Settings', 'manage_options', 'gf-tenstreet', array (&$this, 'display_settings_Admin_GF_TenStreet') );
            }
            
            add_action( 'admin_print_scripts-' . $this->page_hook, array (
                &$this,
                'load_admin_scripts_GF_TenStreet'
            ) );
        }
    }

    /**
     * Handle Settings Update.
     *
     * @return void
     */
    function settings_update_Admin_GF_TenStreet() {
        
        check_admin_referer( 'gf-tenstreet-edit-settings' );
        
        $gf_tenstreet_admin_client_id = isset( $_POST ['gf_tenstreet_admin_client_id'] ) ? $_POST ['gf_tenstreet_admin_client_id'] : false;
        
        $gf_tenstreet_admin_client_password = isset( $_POST ['gf_tenstreet_admin_client_password'] ) ? $_POST ['gf_tenstreet_admin_client_password'] : false;
        
        $gf_tenstreet_admin_client_source = isset( $_POST ['gf_tenstreet_admin_client_source'] ) ? sanitize_text_field( $_POST ['gf_tenstreet_admin_client_source'] ) : false;

        $gf_tenstreet_admin_client_worklist = isset( $_POST ['gf_tenstreet_admin_client_worklist'] ) ? strip_tags( sanitize_textarea_field( $_POST ['gf_tenstreet_admin_client_worklist'] ) ) : false;
        
        $gf_tenstreet_admin_notify_error = isset( $_POST ['gf_tenstreet_admin_notify_error'] ) ? sanitize_text_field($_POST ['gf_tenstreet_admin_notify_error']) : null;
        
        if ($gf_tenstreet_admin_client_id && $gf_tenstreet_admin_client_password && $gf_tenstreet_admin_client_source) {
            
            update_option('gf_tenstreet_active', 1);
            
        } else {
            
            update_option('gf_tenstreet_active', 0);
            
        }
        
        update_option( 'gf_tenstreet_last_update', time() );
        
        update_option( 'gf_tenstreet_admin_client_id', $gf_tenstreet_admin_client_id );
        
        update_option( 'gf_tenstreet_admin_client_password', $gf_tenstreet_admin_client_password );
        
        update_option( 'gf_tenstreet_admin_client_source', $gf_tenstreet_admin_client_source );

        update_option( 'gf_tenstreet_admin_client_worklist', empty($gf_tenstreet_admin_client_worklist) ? null : array_filter( explode( "\n", str_replace("\r", "", $gf_tenstreet_admin_client_worklist ) ) ) );
        
        if (isset($gf_tenstreet_admin_notify_error)) {
            
            update_option( 'gf_tenstreet_admin_notify_error', $gf_tenstreet_admin_notify_error );
            
        }
        
        wp_redirect( add_query_arg( array (
            'updated' => 'updated'
        ), admin_url( 'admin.php?page=gf-tenstreet' ) ) );
        
        exit();
    }
    
    /**
     * Returns Update Messages
     */
    function print_notices_Admin_GF_TenStreet() {
        if (isset( $_GET ['updated'] )) {
            
            $message = isset( $_GET ['msg'] ) ? rawurldecode( $_GET ['msg'] ) : false;
            
            $error = isset( $_GET ['error'] ) ? rawurldecode( $_GET ['error'] ) : false;
            
            $messages = array ();
            
            if ('updated' == $_GET ['updated'])
                $messages [1] = __( 'Settings updated. ' . $message );
                else
                    $messages [0] = __( 'Settings could not be updated. ' . $error );
        }
        
        if (! empty( $messages )) {
            
            foreach ( $messages as $status => $msg )
                echo '<div id="message" class="' . ($status ? 'updated' : 'error') . '"><p>' . $msg . '</p></div>';
        }
    }
    
    /**
     * Registers the Blog Admin Page.
     *
     * @return void
     */
    function display_settings_Admin_GF_TenStreet() {
        $unauthorized = false;
        
        if (! current_user_can( 'manage_options' ))
            $unauthorized = __( 'You do not have sufficient permissions to edit these settings.' );
            
            if ($unauthorized) {
                
                echo "<p>{$unauthorized}</p>\n";
                
                return;
            }
            
            $this->print_notices_Admin_GF_TenStreet();
            
            $gf_tenstreet_admin_client_id = get_option( 'gf_tenstreet_admin_client_id', false );
                
            $gf_tenstreet_admin_client_password = get_option( 'gf_tenstreet_admin_client_password', false );
                
            $gf_tenstreet_admin_client_source = get_option( 'gf_tenstreet_admin_client_source', false );

            $gf_tenstreet_admin_client_worklist = get_option( 'gf_tenstreet_admin_client_worklist', false );
                
            $gf_tenstreet_admin_notify_error = get_option( 'gf_tenstreet_admin_notify_error', get_bloginfo('admin_email') );
                
            $gf_tenstreet_last_update = get_option( 'gf_tenstreet_last_update', false );
                
            $date = 'Y/m/d g:i:s a';
                
            ?>
<hr />

<h2><?php echo __('Gravity Forms TenStreet Settings', 'gf-tenstreet'); ?></h2>

<br />

<form method="post" action="<?php echo admin_url('admin.php'); ?>">

    <?php wp_nonce_field('gf-tenstreet-edit-settings'); ?>
    
    <input type="hidden" name="action" value="settings_update_admin_gf_tenstreet" />

	<table class="form-table">

		<tr>

			<th scope="row"><?php _e('TenStreet Client Id') ?></th>

			<td><input type="text" name="gf_tenstreet_admin_client_id"
				id="gf_tenstreet_admin_client_id" type="text" 
				value="<?php echo $gf_tenstreet_admin_client_id; ?>" autocomplete="off"
				size="4" style="width: 50px;" /></td>

		</tr>

		<tr>

			<th scope="row"><?php _e('TenStreet API Password') ?></th>

			<td><input type="password" name="gf_tenstreet_admin_client_password"
				id="gf_tenstreet_admin_client_password"
				value="<?php echo $gf_tenstreet_admin_client_password; ?>" autocomplete="off"
				class="regular-text" size="33" placeholder="Please enter password." /></td>

		</tr>

		<tr>

			<th scope="row"><?php _e('TenStreet API Source') ?></th>

			<td><input type="text" name="gf_tenstreet_admin_client_source"
				id="gf_tenstreet_admin_client_source" class="regular-text"
				value="<?php echo esc_attr($gf_tenstreet_admin_client_source); ?>" size="33" 
				placeholder="Please enter a Source name." /></td>

		</tr>

        <tr>

            <th scope="row"><?php _e('TenStreet Worklist') ?></th>

            <td><textarea name="gf_tenstreet_admin_client_worklist"
                id="gf_tenstreet_admin_client_worklist" cols="100"
                rows="10" class="mceEditor" autocomplete="off"
                placeholder="<?php esc_attr_e( 'Specify options for Worklist (one account per line)' ); ?>"
            ><?php echo esc_textarea( implode( "\n", $gf_tenstreet_admin_client_worklist ? : [] ) ); ?></textarea></td>

        </tr>

		<tr>

			<th scope="row"><?php _e('Email Error Notifications'); ?></th>

			<td><input type="text" name="gf_tenstreet_admin_notify_error"
				id="gf_tenstreet_admin_notify_error" class="regular-text"
				value="<?php echo esc_attr($gf_tenstreet_admin_notify_error); ?>" size="33" 
				placeholder="Please enter a single or comma separated list of email address(es)." /></td>

		</tr>

		<tr>

			<th scope="row"><?php _e('Last Updated'); ?></th>

			<td><label><?php echo(!$gf_tenstreet_last_update) ? __('Never') : mysql2date($date, date('Y-m-d h:i:s', $gf_tenstreet_last_update)); ?></label>

				<input name="gf_tenstreet_last_update" type="hidden"
				id="gf_tenstreet_last_update"
				value="<?php echo $gf_tenstreet_last_update ?>" /></td>

		</tr>

	</table>

            <?php submit_button(); ?>

</form>

	<?php
	}
	
	/**
	 * Load Admin Page Scripts
	 *
	 * @param mixed $hook
	 */
	function load_admin_scripts_GF_TenStreet($hook = false) {}
	
	/**
	 * Defer script loading
	 * 
	 * @param string $url
	 * @return string
	 */
	function add_async_forscript($url) {
	    if (strpos( $url, '#asyncLoad' ) === false) {
	        return $url;
	    } else
	        return str_replace( '#asyncLoad', '', $url ) . "' async=\"async\" defer='defer";
	}
	
	/**
	 * Add Admin Notices
	 *
	 * @return void
	 */
	function notices_Admin_GF_TenStreet() {
	    if ($this->is_plugin_activated( false )) {
	        if (! class_exists( "GFForms" )) {
	            $class = "error";
	            $message = "Gravity Forms TenStreet Addon requires Gravity Forms to function.  Please install and activate Gravity Forms.";
	            echo "<div class=\"$class\"> <p>$message</p></div>";
	        }
	    }
	    
	    if (! $this->is_plugin_activated( true )) {
	        $class = "error";
	        $message = "The Gravity Forms TenStreet Addon Plugin is not authorized.  Please <a href=\"" . admin_url( 'admin.php?page=gf-tenstreet' ) . "\">enter credentials</a> before using the plugin.";
	        echo "<div class=\"$class\"> <p>$message</p></div>";
	    }
	}
	
}