<?php

/**
 * Gravity Forms TenStreet WordPress Network Admin Class
 *
 * This class is responsible for defining WordPress network admin functionality
 *
 * @author arstropica
 *
 */
class Network_Admin_GF_TenStreet extends Admin_GF_TenStreet {

    /**
     * Constructor
     */
    function __construct() {
        
        // Load Assets
        add_action( 'network_admin_menu', array (
            &$this,
            'load_assets_Network_Admin_GF_TenStreet'
        ) );
        
        // Change Menu Label
        add_action( 'network_admin_menu', array (
            $this,
            'menu_label_Network_Admin_GF_TenStreet'
        ), 99 );
        
        // Single Edit Handler
        add_action( 'admin_action_site_update_network_admin_gf_tenstreet', array (
            $this,
            'site_update_Network_Admin_GF_TenStreet'
        ) );
        
        // Bulk Edit Handler
        add_action( 'admin_action_bulk_update_network_admin_gf_tenstreet', array (
            $this,
            'bulk_update_Network_Admin_GF_TenStreet'
        ) );
        
        // Edit Settings Handler
        add_action( 'admin_action_settings_update_network_admin_gf_tenstreet', array (
            $this,
            'settings_update_Network_Admin_GF_TenStreet'
        ) );
        
        // Add Network Notices
        add_action( 'network_admin_notices', array (
            $this,
            'notices_Network_Admin_GF_TenStreet'
        ) );
    }
    
    /**
     * Runs when the plugin is initialized
     *
     * @return void
     */
    function setup_page_Network_Admin_GF_TenStreet() {
        $screen = get_current_screen();
        
        if (in_array( $screen->id, array (
            'toplevel_page_gf-tenstreet-network'
        ) )) {
            
            // Filter Network Sites Table Actions
            add_filter( 'manage_sites_action_links', array (
                $this,
                'table_actions_Network_Admin_GF_TenStreet'
            ), 10, 3 );
            
            // Filter Network Sites Table Columns
            add_filter( 'wpmu_blogs_columns', array (
                $this,
                'table_columns_Network_Admin_GF_TenStreet'
            ), 10, 1 );
            
            // Hook Network Sites Tracking and Updated Row Data
            add_action( 'manage_sites_custom_column', array (
                $this,
                'table_rows_Network_Admin_GF_TenStreet'
            ), 10, 2 );
            
            // Filter Bulk Actions
            add_filter( "bulk_actions-sites-network", array (
                $this,
                'bulk_actions_Network_Admin_GF_TenStreet'
            ), 10, 1 );
        }
    }
    
    /**
     * Loads Assets.
     *
     * @return void
     */
    function load_assets_Network_Admin_GF_TenStreet() {
        if (is_network_admin()) {
            
            $this->page_hook = add_menu_page( 'Gravity Forms TenStreet Network Settings', 'TenStreet', 'manage_network_options', $this::slug, array (
                &$this,
                'display_settings_Network_Admin_GF_TenStreet'
            ), '', 59.095 );
            
            add_submenu_page( $this::slug, 'Gravity Forms TenStreet Network Settings', 'Settings', 'manage_network_options', 'admin.php?page=' . $this::slug . '&t=settings' );
            
            add_action( 'load-' . $this->page_hook, array (
                $this,
                'setup_page_Network_Admin_GF_TenStreet'
            ) );
            
            add_action( 'admin_print_scripts-' . $this->page_hook, array (
                &$this,
                'load_admin_scripts_Network_GF_TenStreet'
            ) );
        }
    }
    
    /**
     * Change Menu Label.
     *
     * @return void
     */
    function menu_label_Network_Admin_GF_TenStreet() {
        global $menu;
        global $submenu;
        if (isset( $submenu ['gf-tenstreet'] [0] [0] )) {
            $submenu ['gf-tenstreet'] [0] [0] = "Sites";
        }
    }
    
    /**
     * Handle Single Edit Site Update.
     *
     * @return void
     */
    function site_update_Network_Admin_GF_TenStreet() {
        $id = isset( $_POST ['id'] ) ? $_POST ['id'] : 0;
        
        $updated = false;
        
        if ($id) {
            
            check_admin_referer( 'gf-tenstreet-edit-site' );
            
            $details = get_blog_details( $id );
            
            $attributes = array (
                'archived' => $details->archived,
                'spam' => $details->spam,
                'deleted' => $details->deleted
            );
            
            if (! in_array( 1, $attributes )) {
                
                $gf_tenstreet_admin_client_id = isset( $_POST ['gf_tenstreet_admin_client_id'] ) ? $_POST ['gf_tenstreet_admin_client_id'] : null;
                
                $gf_tenstreet_admin_client_password = isset( $_POST ['gf_tenstreet_admin_client_password'] ) ? $_POST ['gf_tenstreet_admin_client_password'] : null;
                
                $gf_tenstreet_admin_client_source = isset( $_POST ['gf_tenstreet_admin_client_source'] ) ? $_POST ['gf_tenstreet_admin_client_source'] : null;
                
                $gf_tenstreet_admin_notify_error = isset( $_POST ['gf_tenstreet_admin_notify_error'] ) ? sanitize_email($_POST ['gf_tenstreet_admin_notify_error']) : null;
                
                $gf_tenstreet_active = isset( $_POST ['gf_tenstreet_active'] ) ? $_POST ['gf_tenstreet_active'] : null;
                
                if ($gf_tenstreet_admin_client_id || $gf_tenstreet_admin_client_password || ! $gf_tenstreet_admin_client_source || isset($gf_tenstreet_active)) {
                    
                    $updated = true;
                    
                    switch_to_blog( $id );
                    
                    if (isset($gf_tenstreet_admin_client_id)) {

                        update_option( 'gf_tenstreet_admin_client_id', $gf_tenstreet_admin_client_id );

                    }
                    
                    if (isset($gf_tenstreet_admin_client_password)) {

                        update_option( 'gf_tenstreet_admin_client_password', $gf_tenstreet_admin_client_password );

                    }
                    
                    if (isset($gf_tenstreet_admin_client_source)) {

                        update_option( 'gf_tenstreet_admin_client_source', $gf_tenstreet_admin_client_source );

                    }
                    
                    if (isset($gf_tenstreet_admin_notify_error)) {

                        update_option( 'gf_tenstreet_admin_notify_error', $gf_tenstreet_admin_notify_error );

                    }
                    
                    if (isset($gf_tenstreet_active)) {

                        update_option( 'gf_tenstreet_active', $gf_tenstreet_active );

                    }

                    update_option( 'gf_tenstreet_last_update', time() );
                    
                    restore_current_blog();
                }
            }
        }
        
        wp_redirect( add_query_arg( array (
            'update' => $updated ? 'updated' : 'failed',
            'id' => $id
        ), network_admin_url( 'admin.php?page=' . $this::slug . '&action=edit' ) ) );
        
        exit();
    }
    
    /**
     * Handle Bulk Edit Site Update.
     *
     * @return void
     */
    function bulk_update_Network_Admin_GF_TenStreet() {

        $blogs = isset( $_POST ['allblogs'] ) ? $_POST ['allblogs'] : array ();
        
        $action = isset( $_POST ['action2'] ) ? $_POST ['action2'] : false;
        
        $updated = false;
        
        if ($blogs && $action && $action != '-1') {
            
            check_admin_referer( 'bulk-sites' );
            
            $updated = true;
            
            $gf_tenstreet_active = $action == 'activate' ? 1 : 0;
            
            foreach ( $blogs as $id ) {
                
                $details = get_blog_details( $id );
                
                $attributes = array (
                    'archived' => $details->archived,
                    'spam' => $details->spam,
                    'deleted' => $details->deleted
                );
                
                if (! in_array( 1, $attributes )) {
                    
                    switch_to_blog( $id );
                    
                    update_option( 'gf_tenstreet_active', $gf_tenstreet_active );
                    
                    update_option( 'gf_tenstreet_last_update', time() );
                    
                    restore_current_blog();
                }
            }
        }
        
        wp_redirect( add_query_arg( array (
            'update' => $updated ? 'updated' : 'failed',
            'id' => $id
        ), network_admin_url( 'admin.php?page=' . $this::slug ) ) );
        
        exit();
    }
    
    /**
     * Handle Settings Tab Update.
     *
     * @return void
     */
    function settings_update_Network_Admin_GF_TenStreet() {
        
        check_admin_referer( 'gf-tenstreet-edit-settings' );
        
        $action = isset( $_POST ['action2'] ) ? $_POST ['action2'] : false;
        
        $message = isset( $_POST ['gf_tenstreet_message'] ) ? $_POST ['gf_tenstreet_message'] : false;
        
        $error = isset( $_POST ['gf_tenstreet_error'] ) ? $_POST ['gf_tenstreet_error'] : false;
        
        $updated = false;
        
        if ($action) {
            
            if ($action == 'authorize') {
                
                $updated = true;
            }
            
            if ($action == 'deauthorize') {
                
                $updated = true;
            }
        }
        
        wp_redirect( add_query_arg( array (
            'updated' => ($updated ? 'updated' : 'failed'),
            'operation' => $action,
            'msg' => rawurlencode( $message ),
            'error' => rawurlencode( $error )
        ), network_admin_url( 'admin.php?page=' . $this::slug . '&t=settings' ) ) );
        
        exit();
    }
    
    /**
     * Displays the Network Admin Page.
     *
     * @return void
     */
    function display_settings_Network_Admin_GF_TenStreet() {
        
        $action = isset( $_GET ['action'] ) ? $_GET ['action'] : 'view';
        
        $tab = isset( $_GET ['t'] ) ? $_GET ['t'] : 'network';
        
        switch ($action) :
        
        case 'edit' :
            
            $this->edit_site_Network_Admin_GF_TenStreet();
            
            break;
            
        case 'view' :
            
        default :
        ?>

		<div class="wrap">

        	<div id="icon-options-general" class="icon32">
        		<br>
        	</div>
        
        	<h2>

        <?php \_e('Gravity Forms TenStreet Settings', 'gf-tenstreet')?>

        <?php
			if (isset( $_REQUEST ['s'] ) && $_REQUEST ['s']) {
				printf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;' ) . '</span>', esc_html( $_REQUEST ['s'] ) );
			}
        ?>

    		</h2><br />

            <?php
        		if (isset( $_GET ['update'] )) {
        			
        			$messages = array ();
        			
        			if ('updated' == $_GET ['update'])
        				$messages [1] = __( 'Site(s) have been updated.' );
        			else
        				$messages [0] = __( 'One or more Sites could not be updated.' );
        		}
        		
        		if (! empty( $messages )) {
        			
        			foreach ( $messages as $status => $msg )
        				echo '<div id="message" class="' . ($status ? 'updated' : 'error') . '"><p>' . $msg . '</p></div>';
        			
        			echo '<br />';
        		}
        	?>
        
            <?php $this->tabs_Network_Admin_GF_TenStreet($tab); ?>
    
            <br />
        
    		<?php
        		switch ($tab) :
        			
        			case 'settings' :
        				
        				$this->display_auth_Network_Admin_GF_TenStreet();
        				
        				break;
        			
        			case 'network' :
        			
        			default :
        				
        				$this->network_settings_Network_Admin_GF_TenStreet();
        				
        				break;
        		endswitch;
        	?>
    
        </div>

		<?php
			break;
		endswitch;
	}
	
	/**
	 * Returns Update Messages
	 * 
	 * @return void()
	 * 
	 */
	function print_notices_Network_Admin_GF_TenStreet() {
	    
	    if (isset( $_GET ['updated'] )) {
	        
	        $message = isset( $_GET ['msg'] ) ? rawurldecode( $_GET ['msg'] ) : false;
	        
	        $error = isset( $_GET ['error'] ) ? rawurldecode( $_GET ['error'] ) : false;
	        
	        $messages = array ();
	        
	        if ('updated' == $_GET ['updated']) {
	            $messages [1] = __( 'Settings updated. ' . $message );
	        } else {
                $messages [0] = __( 'Settings could not be updated. ' . $error );
	        }
	    }
	    
	    if (! empty( $messages )) {
	        
	        foreach ( $messages as $status => $msg ) {
	            
	            echo '<div id="message" class="' . ($status ? 'updated' : 'error') . '"><p>' . $msg . '</p></div>';
	            
	        }
	    }
	}
	
	/**
	 * Add Admin Notices
	 *
	 * @return void
	 */
	function notices_Network_Admin_GF_TenStreet() {
	    
	    if ($this->is_plugin_activated( false )) {
	        if (! class_exists( "GFForms" )) {
	            $class = "error";
	            $message = "Gravity Forms TenStreet Addon requires Gravity Forms to function.  Please install and activate Gravity Forms.";
	            echo "<div class=\"$class\"> <p>$message</p></div>";
	        }
	    }
	    
	    if (! $this->is_plugin_activated( true )) {
	        $class = "error";
	        $message = "Gravity Forms TenStreet Addon Plugin is not authorized or is inactive.  Please <a href=\"" . network_admin_url( 'admin.php?page=' . $this::slug . '&t=settings' ) . "\">activate</a>.";
	        echo "<div class=\"$class\"> <p>$message</p></div>";
	    }
	}
	
	/**
	 * Display Network Section of Settings Page
	 * 
	 * @return void
	 * 
	 */
	function network_settings_Network_Admin_GF_TenStreet() {
	    
	    $wp_list_table = _get_list_table( 'WP_MS_Sites_List_Table', array (
	        'screen' => 'sites-network',
	        'plural' => 'sites',
	        'singular' => 'site'
	    ) );
	    
	    $pagenum = $wp_list_table->get_pagenum();
	    
	    $wp_list_table->_actions = $this->bulk_actions_Network_Admin_GF_TenStreet();
	    
	    $wp_list_table->prepare_items();
	    
	    ?>

<form
	action="<?php echo network_admin_url('admin.php?page=' . $this::slug); ?>"
	method="post" id="ms-search">

            <?php $wp_list_table->search_box(__('Search Sites'), 'site'); ?>

            <input type="hidden" name="action" value="blogs" />

</form>

<form
	action="<?php echo network_admin_url('admin.php?page=' . $this::slug); ?>" class="network_admin_gf_tenstreet-settings" method="post">

	<input type="hidden" name="action" value="bulk_update_network_admin_gf_tenstreet" />

	<?php $wp_list_table->display(); ?>

</form>

<script type="text/javascript">

    jQuery(document).ready(function ($) {

        $('SELECT[name=action2]').on('change', function (e) {

            $('SELECT[name=action2]').val($(this).val());

        });

    });

</script>

<?php }
	
/**
 * Display Edit Site Section of Settings Page
 * 
 * @return void
 * 
 */
function edit_site_Network_Admin_GF_TenStreet() {
    
    $unauthorized = false;
    
    if (! is_multisite()) {
        
        $unauthorized = __( 'Multisite support is not enabled.' );
    
    }
    
    if (! current_user_can( 'manage_sites' )) {
        
        $unauthorized = __( 'You do not have sufficient permissions to edit this site.' );
        
    }
    
    $id = isset( $_REQUEST ['id'] ) ? intval( $_REQUEST ['id'] ) : 0;
    
    if (! $id) {
                
        $unauthorized = __( 'Invalid site ID.' );
        
    }
                
    $details = get_blog_details( $id );
    
    if (! can_edit_network( $details->site_id )) {
        
        $unauthorized = __( 'You do not have permission to access this page.' );
                    
    }
                    
    if ($unauthorized) {
                        
        echo "<p>{$unauthorized}</p>\n";
                        
        return;
    }
                    
    $site_url_no_http = preg_replace( '#^http(s)?://#', '', get_blogaddress_by_id( $id ) );
    
    $title_site_url_linked = sprintf( __( 'Plugin Active: <a href="%1$s">%2$s</a>' ), get_blogaddress_by_id( $id ), $site_url_no_http );
    
    $gf_tenstreet_admin_client_id = \get_blog_option( $id, 'gf_tenstreet_admin_client_id', false );
    
    $gf_tenstreet_admin_client_password = \get_blog_option( $id, 'gf_tenstreet_admin_client_password', false );
    
    $gf_tenstreet_admin_client_source = \get_blog_option( $id, 'gf_tenstreet_admin_client_source', false );
    
    $gf_tenstreet_admin_notify_error = \get_blog_option( $id, 'gf_tenstreet_admin_notify_error', false );
    
    $gf_tenstreet_active = \get_blog_option( $id, 'gf_tenstreet_active', false );
    
    $gf_tenstreet_last_update = \get_blog_option( $id, 'gf_tenstreet_last_update', false );
    
    $date = 'Y/m/d g:i:s a';
    
    $is_main_site = \is_main_site( $id );
    
    if (isset( $_GET ['update'] )) {
        
        $messages = array ();
        
        if ('updated' == $_GET ['update']) {
            
            $messages [1] = __( 'Site updated.' );
            
        } else {
            
            $messages [0] = __( 'Site could not be updated.' );
            
        }
        
    }
    
    if (! empty( $messages )) {
        
        foreach ( $messages as $status => $msg ) {
            
            echo '<div id="message" class="' . ($status ? 'updated' : 'error') . '"><p>' . $msg . '</p></div>';
        }
    }
    ?>

<div class="wrap">

	<div id="icon-options-general" class="icon32">
		<br>
	</div>

	<h2 id="edit-site"><?php echo $title_site_url_linked ?></h2>

	<br />

	<form method="post" action="<?php echo network_admin_url('admin.php?page=' . $this::slug . '&action=edit'); ?>">

		<?php wp_nonce_field('gf-tenstreet-edit-site'); ?>

		<input type="hidden" name="id" value="<?php echo esc_attr($id) ?>" /> <input type="hidden" name="action" value="site_update_network_admin_gf_tenstreet" />

		<table class="form-table">

			<tr class="form-field form-required">

				<th scope="row"><?php _e('Domain') ?></th>

                <?php 
                
                $protocol = is_ssl() ? 'https://' : 'http://';
		
        		if ($is_main_site) { 
        		
                ?>
        
                	<td><code><?php
            			echo $protocol;
            			echo esc_attr( $details->domain )
            		?></code></td>
        
				<?php } else { ?>

					<td><?php echo $protocol; ?><input type="text" id="domain" value="<?php echo esc_attr($details->domain) ?>" size="33" readonly="readonly" /></td>

                        <?php } ?>
			</tr>

			<tr class="form-field">

				<th scope="row"><?php _e('TenStreet API Client Id') ?></th>

				<td><input type="text" name="gf_tenstreet_admin_client_id" id="gf_tenstreet_admin_client_id" value="<?php echo $gf_tenstreet_admin_client_id; ?>" /></td>

			</tr>

			<tr class="form-field">

				<th scope="row"><?php _e('TenStreet API Password') ?></th>

				<td><input type="password" name="gf_tenstreet_admin_client_password" id="gf_tenstreet_admin_client_password" value="<?php echo $gf_tenstreet_admin_client_password; ?>" /></td>

			</tr>

			<tr class="form-field">

				<th scope="row"><?php _e('TenStreet API Source') ?></th>

				<td><input type="text" name="gf_tenstreet_admin_client_source" id="gf_tenstreet_admin_client_source" value="<?php echo $gf_tenstreet_admin_client_source; ?>" /></td>

			</tr>

			<tr class="form-field">

				<th scope="row"><?php _e('Error Notification Email') ?></th>

				<td><input type="text" name="gf_tenstreet_admin_notify_error" id="gf_tenstreet_admin_notify_error" value="<?php echo $gf_tenstreet_admin_notify_error; ?>" /></td>

			</tr>

		<?php
		
		$attributes = array ();
		
		$attributes ['archived'] = $details->archived;
		
		$attributes ['spam'] = $details->spam;
		
		$attributes ['deleted'] = $details->deleted;
		
		?>

            <tr>
    			<th scope="row"><?php _e('Plugin Enabled'); ?></th>
    
    			<td><?php echo $gf_tenstreet_active ? 'True' : 'False'; ?><br /></td>
    
    		</tr>
    
    		<tr class="form-field">
    
    			<th scope="row"><?php _e('Last Updated'); ?></th>
    
    			<td>
    			
    				<label><?php echo (!$gf_tenstreet_last_update ) ? __('Never') : mysql2date($date, date('Y-m-d h:i:s', $gf_tenstreet_last_update)); ?></label>
    
    				<input name="gf_tenstreet_last_update" type="hidden" id="gf_tenstreet_last_update" value="<?php echo $gf_tenstreet_last_update ?>" />
    				
    			</td>
    
    		</tr>
    
    	</table>
    
    	<?php submit_button(); ?>
    
    </form>

</div>

<?php }
	
    /**
     * Displays the plugin authorization section of the settings page.
     *
     * @return void
     */
    function display_auth_Network_Admin_GF_TenStreet() {
        
        $unauthorized = false;
        
        if (! current_user_can( 'manage_options' )) {
            
            $unauthorized = __( 'You do not have sufficient permissions to edit these settings.' );
            
        }
        
        if ($unauthorized) {
            
            echo "<p>{$unauthorized}</p>\n";
            
            return;
        }
        
        echo "<p>You are authorized to manage this plugin.</p>";
        
    }
	
    /**
     * Add Tabbed Headings
     * 
     * @return void
     * 
     */
    function tabs_Network_Admin_GF_TenStreet($current = 'network') {
        
        $tabs = array (
            'network' => 'Sites',
            'settings' => 'Settings'
        );
        
        echo '<h2 class="nav-tab-wrapper">';
        
        foreach ( $tabs as $tab => $name ) {
            
            $class = ($tab == $current) ? ' nav-tab-active' : '';
            
            echo "<a class='nav-tab$class' href='" . network_admin_url( 'admin.php?page=' . $this::slug ) . "&t=$tab'>$name</a>";
            
        }
        
        echo '</h2>';
        
    }
    
    /**
     * Filter Network Table Actions
     * 
     * @return void
     * 
     */
    function table_actions_Network_Admin_GF_TenStreet($actions, $blog_id, $blogname) {
        
        $new_actions = array ();
        
        $new_actions ['backend'] = "<span class='backend'><a href='" . esc_url( get_admin_url( $blog_id ) ) . "' class='edit'>" . __( 'Dashboard' ) . '</a></span>';
        
        if (get_blog_status( $blog_id, 'public' ) == true && get_blog_status( $blog_id, 'archived' ) == false && get_blog_status( $blog_id, 'spam' ) == false && get_blog_status( $blog_id, 'deleted' ) == false) {
            
            $new_actions ['edit'] = '<span class="edit"><a href="' . esc_url( network_admin_url( 'admin.php?page=' . $this::slug . '&action=edit&id=' . $blog_id ) ) . '">' . __( 'Edit' ) . '</a></span>';
            
        }
        
        return $new_actions;
    }
    
    /**
     * Filter Network Table Columns
     * 
     * @return void
     * 
     */
    function table_columns_Network_Admin_GF_TenStreet($sites_columns) {
        
        $blogname_columns = (is_subdomain_install()) ? __( 'Domain' ) : __( 'Path' );
        
        $sites_columns = array (
            'cb' => '<input type="checkbox" />',
            'blogname' => $blogname_columns,
            'tenstreet_client_id' => __( 'TenStreet Client Id' ),
            'tenstreet_source' => __( 'TenStreet Source' ),
            'tenstreet_email' => __( 'Error Notification' ),
            'plugin_active' => __( 'Plugin Activated' ),
            'gf_tenstreet_updated' => __( 'Last Updated' )
        );
        
        if (has_filter( 'wpmublogsaction' )) {
            
            $sites_columns ['plugins'] = __( 'Actions' );
            
        }
            
        return $sites_columns;
        
    }
    
    /**
     * Filter Network Table Rows
     * 
     * @return void
     * 
     */
    function table_rows_Network_Admin_GF_TenStreet($column_name, $blog_id) {
        
        global $mode;
        
        $blog = get_blog_details( $blog_id );
        
        $blogname = (is_subdomain_install()) ? str_replace( '.' . get_current_site()->domain, '', $blog->domain ) : $blog->path;
        
        $output = "";
        
        switch ($column_name) {
            
            case 'cb' :
                {
                    
                    $output .= '<label class="screen-reader-text" for="blog_' . $blog_id . '">' . sprintf( __( 'Select %s' ), $blogname ) . '</label>';
                    
                    $output .= '<input type="checkbox" id="blog_' . $blog_id . '" name="allblogs[]" value="' . esc_attr( $blog_id ) . '" />';
                    
                    break;
                }
            case 'gf_tenstreet_updated' :
                {
                    
                    $gf_tenstreet_last_update = get_blog_option( $blog_id, "gf_tenstreet_last_update", false );
                    
                    if ('list' == $mode) {
                        
                        $date = 'Y/m/d';
                        
                    } else {
                        
                        $date = 'Y/m/d \<\b\r \/\> g:i:s a';
                        
                    }
                            
                    $output .= (! $gf_tenstreet_last_update) ? __( 'Never' ) : mysql2date( $date, date( 'Y-m-d', $gf_tenstreet_last_update ) );
                    
                    break;
                }
            case 'tenstreet_client_id' :
                {
                    
                    $tenstreet_client_id = get_blog_option( $blog_id, "gf_tenstreet_admin_client_id", false );
                    
                    $output .= (! $tenstreet_client_id) ? __( ' - ' ) : $tenstreet_client_id;
                    
                    break;
                }
            case 'tenstreet_source' :
                {
                    
                    $tenstreet_source = get_blog_option( $blog_id, "gf_tenstreet_admin_client_source", false );
                    
                    $output .= (! $tenstreet_source) ? __( ' - ' ) : $tenstreet_source;
                    
                    break;
                }
            case 'tenstreet_email' :
                {
                    
                    $tenstreet_email = get_blog_option( $blog_id, "gf_tenstreet_admin_notify_error", false );
                    
                    $output .= (! $tenstreet_email) ? __( ' - ' ) : 'Active';
                    
                    break;
                }
            case 'plugin_active' :
                {
                    
                    $plugin_active = get_blog_option( $blog_id, "gf_tenstreet_active", false );
                    
                    $output .= (! $plugin_active) ? __( 'Inactive' ) : __( 'Active' );
                    
                    break;
                }
        }
        
        if ($output) {
            
            echo $output;
            
        }
        
    }
    
    /**
     * Bulk Actions (Deprecated)
     * 
     * @return void
     * 
     */
    function bulk_actions_Network_Admin_GF_TenStreet() {
        
        $new_bulk_actions = array ();
        
        return $new_bulk_actions;
        
        if (current_user_can( 'delete_sites' )) {
            $new_bulk_actions ['activate'] = __( 'Activate', 'gf-tenstreet' );
            $new_bulk_actions ['deactivate'] = __( 'Deactivate', 'gf-tenstreet' );
        }
        
        return $new_bulk_actions;
    }
    
    /**
     * Load Admin Page Scripts
     *
     * @param mixed $hook
     */
    function load_admin_scripts_Network_GF_TenStreet($hook = false) {}
    
}