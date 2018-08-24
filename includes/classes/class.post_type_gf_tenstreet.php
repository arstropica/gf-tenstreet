<?php

/**
 *
 * Gravity Forms TenStreet Custom Post Type
 * 
 * Handles reporting & logging
 * 
 * @author arstropica
 *
 */
class Post_Type_GF_TenStreet {
    
    /**
     * The key used by the Gravity Forms TenStreet Custom Post Type.
     *
     * @var string
     */
    const POST_TYPE = 'gftenstreet';
    
    /**
     * Constructor
     * 
     * @param boolean $flush_rewite
     * 
     */
    public function __construct($flush_rewite = false) {
        
        $this->register();
        
        if ($flush_rewite) {

            flush_rewrite_rules();
            
        }
    }
    
    /**
     * Register Gravity Forms TenStreet Custom Post Type
     *
     * @return void
     */
    protected function register() {
        $labels = array (
            'name' => _x( 'Lead', 'Post Type General Name', 'gf-tenstreet' ),
            'singular_name' => _x( 'Lead', 'Post Type Singular Name', 'gf-tenstreet' ),
            'menu_name' => __( 'TenStreet', 'gf-tenstreet' ),
            'name_admin_bar' => __( 'Leads', 'gf-tenstreet' ),
            'parent_item_colon' => __( 'Parent Lead:', 'gf-tenstreet' ),
            'all_items' => __( 'All Leads', 'gf-tenstreet' ),
            'add_new_item' => __( 'Add New Lead', 'gf-tenstreet' ),
            'add_new' => __( 'Add New', 'gf-tenstreet' ),
            'new_item' => __( 'New Lead', 'gf-tenstreet' ),
            'edit_item' => __( 'Edit Lead', 'gf-tenstreet' ),
            'update_item' => __( 'Update Lead', 'gf-tenstreet' ),
            'view_item' => __( 'View Lead', 'gf-tenstreet' ),
            'search_items' => __( 'Search Leads', 'gf-tenstreet' ),
            'not_found' => __( 'Not found', 'gf-tenstreet' ),
            'not_found_in_trash' => __( 'Not found in Trash', 'gf-tenstreet' )
        );
        $args = array (
            'label' => __( 'TenStreet Leads', 'gf-tenstreet' ),
            'description' => __( 'Gravity Forms TenStreet Leads', 'gf-tenstreet' ),
            'labels' => $labels,
            'supports' => array (
                'title',
                'content',
                'custom-fields'
            ),
            'taxonomies' => array (),
            'hierarchical' => false,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 90,
            'show_in_admin_bar' => false,
            'show_in_nav_menus' => false,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'capability_type' => 'post',
            'capabilities' => array (
                'create_posts' => false,
                'delete_posts' => false
            ),
            'map_meta_cap' => false
        ); // Set to false, if users are not allowed to edit/delete existing posts
        
        register_post_type( Post_Type_GF_TenStreet::POST_TYPE, $args );
        
        $this->remove_custom_post_comment();
    }
    
    /**
     * Disable Comment Functionality
     *
     * @return void
     */
    function remove_custom_post_comment() {
        
        remove_post_type_support( Post_Type_GF_TenStreet::POST_TYPE, 'comments' );
        
    }
    
}