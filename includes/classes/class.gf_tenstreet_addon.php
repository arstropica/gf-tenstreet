<?php

if (class_exists( "GFForms" ) && class_exists( "GFAddOn") && class_exists( "GFAPI" )) {
    
    GFForms::include_addon_framework();
    
    /**
     * Gravity Forms TenStreet Addon Class
     * 
     * @author arstropica
     *
     */
    class GF_TenStreet_Addon extends GFAddOn {
        
        /**
         *
         * @var String
         */
        protected $_version = "1.0";
        
        /**
         *
         * @var String
         */
        protected $_min_gravityforms_version = "1.7.9999";
        
        /**
         * TenStreet API Mode
         *
         * @var string
         */
        protected static $mode = 'PROD';
        
        /**
         *
         * @var String
         */
        protected $_slug = "gf-tenstreet-addon";
        
        /**
         *
         * @var String
         */
        protected $_path = "gf-tenstreet/includes/classes/class.gf_tenstreet_addon.php";
        
        /**
         *
         * @var String
         */
        protected $_full_path = __FILE__;
        
        /**
         *
         * @var String
         */
        protected $_title = "Gravity Forms TenStreet Add-on";
        
        /**
         *
         * @var String
         */
        protected $_short_title = "TenStreet";
        
        /**
         *
         * @var String
         */
        protected $plugin_path;

        /**
         *
         * @var array
         */
        protected $api_credential_fields = [
            'gf_tenstreet_company_id',
            'gf_tenstreet_company_name',
            'gf_tenstreet_referral_code',
            'gf_tenstreet_worklist'
        ];
        
        /**
         *
         * @var GF_TenStreet
         */
        protected $gf_tenstreet;
        
        /**
         * Constructor
         */
        public function __construct() {
            
            $this->_path = self::addon_local_path();
            
            $this->plugin_path = plugin_dir_path( dirname( dirname( __FILE__ ) ) );
            
            parent::__construct();
            
            $this->init();
            
        }
        
        /**
         * Initialize Add-on
         *
         * @return void
         */
        public function init() {
            
            parent::init();
            
            $gf_tenstreet_admin_forms = GFAPI::get_forms();
            
            if ($gf_tenstreet_admin_forms && is_array( $gf_tenstreet_admin_forms )) {
                
                foreach ( $gf_tenstreet_admin_forms as $form ) {
                    
                    add_action( 'gform_after_submission_' . $form['id'], array (
                        $this,
                        'maybe_api_submit'
                    ), 10, 2 );
                    
                }
                
            } else {
                
                add_action( 'gform_after_submission', array (
                    $this,
                    'maybe_api_submit'
                ), 10, 2 );
                
            }
            
        }
        
        /**
         * Get File Local Path
         *
         * @return string
         */
        public static function addon_local_path() {
            return plugin_basename( __FILE__ );
        }
        
        /**
         * Setup Form Fields for Mapping
         *
         * @param array $form
         * @return array
         */
        public function form_settings_fields($form) {
            $fields = $this->form_mapping_fields( $form );
            $custom_fields = $this->form_custom_fields($form);

            return array (
                array (
                    "title" => "Gravity Forms TenStreet Form Settings",
                    "fields" => array (
                        array (
                            "label" => "Enable TenStreet on this Form",
                            "type" => "checkbox",
                            "name" => "activate",
                            "tooltip" => "Select if this form will submit leads to TenStreet.",
                            "choices" => array (
                                array (
                                    "label" => "Activate",
                                    "name" => "activate"
                                )
                            )
                        ),
                        array (
                            "label" => "Enable Custom Field Mapping",
                            "type" => "checkbox",
                            "name" => "enabled",
                            "tooltip" => "Enable if using custom fields / field names.",
                            "choices" => array (
                                array (
                                    "label" => "Enabled",
                                    "name" => "enabled"
                                )
                            )
                        ),
                        array(
                            "label" => "TenStreet Company ID",
                            "type" => "text",
                            "name" => "gf_tenstreet_company_id",
                            "tooltip" => "Enter a Company Id for this form."
                        ),
                        array(
                            "label" => "TenStreet Company Name",
                            "type" => "text",
                            "name" => "gf_tenstreet_company_name",
                            "tooltip" => "Enter a Company Name for this form."
                        ),
                        array(
                            "label" => "TenStreet Referral Code",
                            "type" => "text",
                            "name" => "gf_tenstreet_referral_code",
                            "tooltip" => "Enter a Referral Code for this form."
                        ),
                        
                    )
                ),
                array (
                    "title" => "Field Mapping",
                    "fields" => $fields
                ),
                array(
                    "title" => "Custom Questions",
                    "fields" => $custom_fields
                )
            );
        }
        
        /**
         * Returns array of field ids and labels
         *
         * @param object $form
         * @return array
         */
        protected function get_form_field_data($form) {
            return array_reduce( $form ['fields'], function ($fields, $field) {
                if (in_array( $field->type, [
                    'name',
                    'address',
                    'date',
                    'time',
                    'text',
                    'select',
                    'email',
                    'phone',
                    'radio',
                    'textarea',
                    'checkbox'
                ] )) {
                    switch ($field->type) {
                        case 'name' :
                        case 'address' :
                            if (isset($field->inputs) && is_array($field->inputs)) {
                                foreach ($field->inputs as $input) {
                                    $fields [$input['id']] = $field->label . " ({$input['label']})";
                                }
                            } else {
                                $fields [$field->id] = $field->label;
                            }
                            break;
                        default :
                            $fields [$field->id] = $field->label;
                            break;
                    }
                }
                return $fields;
            } );
        }
        
        /**
         * Get Form Field By ID
         * 
         * @param array $form
         * @param integer $id
         * @return object|boolean
         */
        protected function get_form_field_by_id($form, $id) {
            $matches = array_filter($form['fields'], function($field) use ($id) {
                return $field->id == $id;
            });
            
            if (is_array($matches) && ! empty($matches)) {
                return current($matches);
            }
            
            return false;
        }
        
        /**
         * 
         * Return custom field settings
         * 
         * @param array $form
         * @return array
         */
        protected function form_custom_fields($form) {
            
            $settings = array ();
            
            $custom_fields = $this->get_api_fields(true, true);
            
            if ($custom_fields && is_array($custom_fields)) {
                
                foreach ($custom_fields as $name => $custom_field) {
                    
                    switch ($custom_field['type']) {
                        
                        case 'select' :
                            
                            if (isset($custom_field['choices']) && is_array($custom_field['choices'])) {
                                
                                $choices = array_merge (
                                    array(
                                        array (
                                            "label" => "Choose Answer",
                                            "value" => ""
                                        )
                                    ), array_map(function($choice) {
                                        return [
                                            "label" => $choice, 
                                            "value" => $choice
                                            ];
                                        }, $custom_field['choices'])
                                );
                                
                                $settings[$name] = array (
                                    "type" => "select",
                                    "label" => trim( $custom_field ['label'] ),
                                    "name" => trim( $custom_field ['name'] ),
                                    "tooltip" => "Select " . trim( $custom_field ['label'] ),
                                    "default_value" => "",
                                    "choices" => $choices
                                );
                                
                            }
                            break;
                            
                        default :
                        case 'text' :
                            $settings[$name] = array (
                                "type" => $custom_field['type'],
                                "label" => trim( $custom_field ['label'] ),
                                "name" => trim( $custom_field ['name'] ),
                                "tooltip" => "Enter " . trim( $custom_field ['label'] )
                            );
                            break;
                            
                    }
                }
                
            }
            // print_r($settings); exit;
            return $settings;
            
        }
        
        /**
         * Return Mapping Form Fields
         *
         * @param array $form
         * 
         * @return array
         */
        protected function form_mapping_fields($form) {
            
            $settings = array ();
            
            $api_fields = $this->get_api_fields();
            
            $api_field_labels = array ();
            
            $form_fields = array ();
            
            $question_labels = array ();
            
            $choices = array (
                0 => array (
                    "label" => "Choose Field",
                    "value" => ""
                )
            );
            
            if ($form && is_array( $form ) && isset( $form ['fields'] )) {
                $idx = 1;
                $field_data = $this->get_form_field_data($form);
                
                foreach ( $field_data as $field ) {
                    $form_fields [$idx] = $field;
                    $choices [$idx] = array (
                        "label" => $field,
                        "value" => $field
                    );
                    $idx ++;
                }
                
                $default = array (
                    "type" => "select",
                    "choices" => $choices
                );
                
                if ($api_fields && is_array( $api_fields ) && isset( $api_fields )) {
                    $api_field_labels = array_reduce( $api_fields, function ($labels, $field) {
                        $labels [] = trim( $field ['label'] );
                        return $labels;
                    } );
                        
                        $question_labels = array_diff( $form_fields, $api_field_labels );
                        $idx = 0;
                        foreach ( $api_fields as $idx => $field ) {
                            switch ($field ['name']) {
                                default :
                                    $select = array_merge( $default, array (
                                    "label" => trim( $field ['label'] ),
                                    "name" => trim( $field ['name'] ),
                                    "tooltip" => "Select Mapping for " . trim( $field ['label'] ),
                                    "default_value" => trim( $field ['label'] )
                                    ) );
                                    $settings [$idx] = $select;
                                    break;
                            }
                        }
                }
            }
            
            return $settings;
        }
        
        /**
         * Get Field Mapping from JSON
         * 
         * @param boolean $assoc Return associative array
         * @param boolean $custom Get custom fields
         * 
         * @return array|boolean
         */
        protected function get_api_fields($assoc = false, $custom = false) {
            
            $result = [];
            
            $data = file_get_contents( $this->plugin_path . '/data/' . ($custom ? 'custom-' : '') . 'fields.json' );
            
            if ($data) {
            
                try {
                
                    $api_fields = json_decode( $data, true );
                    
                    if ($api_fields) {
                        
                        if ($assoc) {
                            
                            foreach ($api_fields['fields'] as $field) {
                                
                                $result[$field['name']] = $field;
                                
                            }
                            
                        } else {
                            
                            $result = $api_fields['fields'];
                            
                        }
                        
                    }
                    
                } catch (\Exception $e) {
                    
                    return $result;
                    
                }
                
            }
            
            return $result;
            
        }
        
        /**
         * Return Default Mapping Form Fields
         *
         * @param array $form
         * 
         * @return array
         */
        protected function default_form_mapping_fields($form) {
            
            $settings = array ();
            
            $api_fields = $this->get_api_fields();
            
            if ($form && is_array( $form ) && isset( $form ['fields'] )) {

                if ($api_fields && is_array( $api_fields ) && isset( $api_fields )) {

                    foreach ( $api_fields as $field ) {
                            $settings [$field ['name']] = trim( $field ['label'] );
                        }
                }
            }
            
            return $settings;
        }
        
        /**
         * Map Form Entries to Form Fields
         *
         * @param array $form
         * 
         * @return array
         */
        protected function map_entry_fields($form) {
            
            $settings = $this->get_form_settings( $form );
            
            if (! $settings || empty( $settings ['enabled'] )) {
                $settings = $this->default_form_mapping_fields( $form );
            }
            
            $excluded_fields = array_merge(['enabled', 'activate'], $this->api_credential_fields);
            
            foreach ($excluded_fields as $field) {
                unset( $settings [$field] );
            }
            
            return $settings;
        }
        
        /**
         * Remove admin fields from entry array
         *
         * @param array $entry
         *
         * @return array
         */
        protected function clean_entry($entry) {
            $additional_fields = [
                'ip',
                'source_url',
                'user_agent'
            ];
            reset( $entry );
            return array_filter( $entry, function ($v) use($additional_fields, &$entry) {
                $k = key( $entry );
                next( $entry );
                return is_numeric( $k ) || in_array( $k, $additional_fields );
            } );
        }
        
        /**
         * Acquire Plugin Class (GF_TenStreet) instance
         *
         * @return GF_TenStreet
         */
        protected function get_gf_tenstreet() {
            if (! $this->gf_tenstreet) {
                $this->gf_tenstreet = apply_filters( 'gf_tenstreet_class_instance', null );
            }
            return $this->gf_tenstreet;
        }
        
        /**
         * Build Authentication Node
         *  
         * @param array $form Form Array
         * 
         * @return boolean|array
         */
        protected function build_tenstreet_authentication($form) {
            
            $authentication = false;
            
            $gf_tenstreet_admin_client_id = get_option( 'gf_tenstreet_admin_client_id', false );
            
            $gf_tenstreet_admin_client_password = get_option( 'gf_tenstreet_admin_client_password', false );
            
            $gf_tenstreet_admin_client_service = GF_TenStreet::get_service_name();
            
            if ($gf_tenstreet_admin_client_id && $gf_tenstreet_admin_client_password && $gf_tenstreet_admin_client_service) {
                
                $authentication = array(
                    "ClientId" => $gf_tenstreet_admin_client_id,
                    "Password" => $gf_tenstreet_admin_client_password,
                    "Service" => $gf_tenstreet_admin_client_service
                );
            }
            return $authentication;
        }
        
        /**
         * Build PersonalData Node
         * 
         * @param array $entry Submission Entry
         * @param array $form Form Array
         * 
         * @return array|boolean
         */
        protected function build_tenstreet_personaldata($entry, $form) {
            
            $api_fields = $this->get_api_fields(true);
            
            $personalData = false;
            
            $data = [];
            
            $map = $this->map_entry_fields( $form );
            
            $labels = $this->get_form_field_data($form);
            
            foreach ( $labels as $id => $label ) {
                $field = $this->get_form_field_by_id($form, $id);
                $type = $field ? $field->type : false;
                
                switch ($type) {
                    default :
                        if (isset( $entry [$id] )) {
                            if (is_array( $entry [$id] )) {
                                $data [$label] = implode( "; ", $entry [$id] );
                            } else {
                                $data [$label] = $entry [$id];
                            }
                        } elseif (count( preg_grep( '/^' . preg_quote( $id ) . '\./', array_keys( $entry ) ) ) > 0) {
                            $subkeys = preg_grep( '/^' . preg_quote( $id ) . '\./', array_keys( $entry ) );
                            $subvalues = [ ];
                            foreach ( $subkeys as $subkey ) {
                                $subvalues [] = $entry [$subkey];
                            }
                            $data [$label] = implode( "; ", array_filter( $subvalues ) );
                        } else {
                            $data [$label] = null;
                        }
                        break;
                }
            }
            
            if ($data && $api_fields) {
                $personalData = [];
            }
            
            foreach ($map as $name => $label) {
                if (array_key_exists($label, $data) && array_key_exists($name, $api_fields)) {
                    $personalData[$api_fields[$name]['node']][$name] = $data [$label];
                }
            }
            
            return $personalData;
        }
        
        /**
         * Build DisplayField Nodes
         * 
         * @param array $entry Submission Entry
         * @param array $form Form Array
         * 
         * @return boolean|array
         */
        protected function build_tenstreet_display_fields($entry, $form) {
            
            $displayFields = false;
            
            $entry_ids = [];
            
            $map = $this->map_entry_fields( $form );
            
            $labels = $this->get_form_field_data($form);
            
            foreach ( $labels as $id => $label ) {
                if (array_search($label, $map) === false) {
                    $entry_ids [$label] = $id;
                }
            }
            
            if ($entry_ids) {
                $displayFields = [];
                foreach ( $entry_ids as $label => $entry_id ) {
                    $displayField = [];
                    if (isset( $entry [$entry_id] )) {
                        if (is_array( $entry [$entry_id] )) {
                            $displayField["DisplayValue"] = implode( "; ", $entry [$entry_id] );
                        } else {
                            $displayField["DisplayValue"] = $entry [$entry_id];
                        }
                    } elseif (count( preg_grep( '/^' . preg_quote( $entry_id ) . '\./', array_keys( $entry ) ) ) > 0) {
                        $subkeys = preg_grep( '/^' . preg_quote( $entry_id ) . '\./', array_keys( $entry ) );
                        $subvalues = [ ];
                        foreach ( $subkeys as $subkey ) {
                            $subvalues [] = $entry [$subkey];
                        }
                        $displayField["DisplayValue"] = implode( "; ", array_filter( $subvalues ) );
                    }
                    if (isset($displayField["DisplayValue"]) && $displayField["DisplayValue"] !== "") {
                        $displayField["DisplayPrompt"] = $label;
                        $displayFields[] = ["DisplayField" => $displayField];
                    }
                }
            }
            return $displayFields;
        }
        
        /**
         * Build Custom Questions, if any
         * 
         * @param array $entry
         * @param array $form
         * @return array
         */
        protected function build_tenstreet_custom_questions($entry, $form) {
            
            $customQuestions = [];
            
            $settings = $this->get_form_settings( $form );
            
            $custom_fields = $this->get_api_fields(true, true);
            
            if ($custom_fields && is_array($custom_fields)) {
                
                foreach ($custom_fields as $id => $custom_field) {
                    
                    if (isset($settings[$id]) && $settings[$id] !== "") {
                        
                        $customQuestion = [
                            "QuestionId" => $custom_field["question_id"],
                            "Question" => $custom_field["question"],
                            "Answer" => $settings[$id]
                        ];
                        
                        $customQuestions[] = ["CustomQuestion" => $customQuestion];
                        
                    }
                    
                }
            }
            
            return $customQuestions;
        }
        
        /**
         * Generate Application Data Node
         *
         * @param array $entry Submission Entry
         * @param array $form Form Array
         * 
         * @return array
         */
        protected function build_tenstreet_application_data($entry, $form) {
            $applicationData = [];
            $settings = $this->get_form_settings( $form );
            
            if (isset($settings['gf_tenstreet_referral_code'])) {
                $applicationData['AppReferrer'] = $settings['gf_tenstreet_referral_code'];
            }
            $applicationData['DisplayFields'] = $this->build_tenstreet_display_fields($entry, $form);
            $applicationData['CustomQuestions'] = $this->build_tenstreet_custom_questions($entry, $form);
            
            return $applicationData;
        }
        
        /**
         * Generate API Data from Submission
         *
         * @param array $entry Submission Entry
         * @param array $form Form Array
         * 
         * @return array
         */
        protected function build_tenstreet_submission_data($entry, $form) {
            
            $settings = $this->get_form_settings( $form );
            $gf_tenstreet_admin_client_source = get_option( 'gf_tenstreet_admin_client_source', false );
            $tenstreetData = false;
            
            if ($gf_tenstreet_admin_client_source && isset($settings['gf_tenstreet_company_id'], $settings['gf_tenstreet_company_name'])) {
                $input = [];
                $input['Authentication'] = $this->build_tenstreet_authentication($form);
                $input['Mode'] = self::$mode;
                $input['Source'] = $gf_tenstreet_admin_client_source;
                $input['CompanyId'] = $settings['gf_tenstreet_company_id'];
                $input['CompanyName'] = $settings['gf_tenstreet_company_name'];
                $input['PersonalData'] = $this->build_tenstreet_personaldata($entry, $form);
                $input['ApplicationData'] = $this->build_tenstreet_application_data($entry, $form);
                
                $tenstreetData = ArrToXml::parse(['TenStreetData' => $input]);
            }
            
            return $tenstreetData;
        }
        
        /**
         * Conditionally handle API Submission and create Lead Post Type
         *
         * @param object $entry Submission Entry
         * @param array $form Form Array
         * 
         * @return int Post ID
         */
        public function maybe_api_submit($entry, $form) {
            
            $post_id = false;
            
            $settings = $this->get_form_settings( $form );
            
            if (isset($settings['activate']) && !empty($settings['activate'])) {
            
                $gf_tenstreet = $this->get_gf_tenstreet();
                $clean = $this->clean_entry( $entry );
    
                $lead = $this->build_tenstreet_submission_data($clean, $form);
                
                if ($gf_tenstreet->is_plugin_activated( true )) {
                    $post_id = $this->api_submit( $lead, $entry, $form );
                }
                
            }
            
            return $post_id;
        }
        
        /**
         * Submit form entry to API
         *
         * @param array $lead Form Submission Entry
         * @param object $entry Submission Entry
         * @param array $form Form Array
         * 
         * @return int Post ID
         */
        protected function api_submit($lead, $entry, $form) {
            
            $response = null;
            
            $gf_tenstreet_api_submit_url = GF_TenStreet::get_wsdl_endpoint(self::$mode);
            
            $args = array (
                "method" => "POST",
                "timeout" => 60,
                "redirection" => 60,
                "body" => $lead
            );
            
            if ($gf_tenstreet_api_submit_url) {
                
                $_response = wp_remote_post( $gf_tenstreet_api_submit_url, $args );

                if (! is_wp_error( $_response )) {
                    if (isset( $_response ['response'] ) && preg_match('/^20[0-9]$/', $_response ['response'] ['code'])) {
                        if (isset( $_response ['body'] )) {
                            // $data = @json_decode( $_response ['body'], true );
                            $xml = simplexml_load_string($_response['body']);
                            if ($xml) {
                                $data = @json_decode(@json_encode((array)$xml), true);
                                if ($data) {
                                    $response = $data;
                                }
                            }
                        }
                    }
                }
            }
            
            if (isset($response) && isset($response['Status']) && $response['Status'] == 'Accepted') {
                $post = $this->get_post_data( $response, $entry, $form );
                $meta = $this->get_post_meta( $response );
                
                return $this->wp_insert_custom_post( $post, $meta, false );
            } else {
                $gf_tenstreet_admin_notify_error = get_option('gf_tenstreet_admin_notify_error', false);
                if ($gf_tenstreet_admin_notify_error && is_email($gf_tenstreet_admin_notify_error)) {
                    $this->send_admin_error_email($gf_tenstreet_admin_notify_error, $entry);
                }
            }
            return false;
        }
        
        /**
         * Insert or update a custom post type.
         *
         * @param array $post
         * @param bool $wp_error
         *
         * @return int|WP_Error
         */
        public function wp_insert_custom_post($post, $meta, $wp_error = false) {
            $post_id = wp_insert_post( $post, $wp_error );
            
            if (0 === $post_id || $post_id instanceof WP_Error) {
                return $post_id;
            }
            
            foreach ( $meta as $key => $value ) {
                update_post_meta( $post_id, $key, $value );
            }
            
            return $post_id;
        }
        
        /**
         * Extract data for post creation from API Submission data
         *
         * @param array $response TenStreet API Response
         * @param object $entry Submission Entry
         * @param array $form Form Array
         * 
         * @return array Post data
         */
        public function get_post_data($response, $entry, $form) {
            $post = false;
            if ($response && is_array( $response )) {
                $title = isset( $response ['DriverName'] ) ? $response ['DriverName'] : "Unknown Applicant (" . date( 'F j, Y H:i:s' ) . ")";
                $content = $this->build_post_content( $response, $entry, $form );
                $post_type = class_exists( 'Post_Type_GF_TenStreet' ) ? \Post_Type_GF_TenStreet::POST_TYPE : 'gftenstreet';
                $author = get_user_by( 'email', get_option( 'admin_email' ) );
                $post = array (
                    'post_content' => $content,
                    'post_title' => $title,
                    'post_status' => 'publish',
                    'post_type' => $post_type,
                    'author' => $author->ID
                );
            }
            return $post;
        }
        
        /**
         * Generate content for Lead Post Type
         *
         * @param array $response API Response Data
         * @param object $entry Submission Entry
         * @param array $form Form Array
         * 
         * @return string Lead Post Content
         */
        protected function build_post_content($response, $entry, $form) {
            $content = '';
            if (is_array( $response )) {
                $content .= '<h3>Applicant Details</h3>';
                $content .= '<table>';
                $content .= '<tr><th>Driver Name</th>' . '<td>' . ($response ['DriverName'] ? : 'None') . '</th></tr>';
                $content .= '<tr><th>Driver ID</th>' . '<td>' . ($response ['DriverId'] ? : 'None') . '</th></tr>';
                $content .= '<tr><th>Gravity Forms Entry</th>' . '<td><a href="' . admin_url("admin.php?page=gf_entries&view=entry&id=" . $entry ['form_id'] . "&lid=" . $entry ['id']) . '">Link</a></th></tr>';
                $content .= '</table>';
                $content .= '<hr />';
                $content .= '<p></p>';
                $content .= '<h3>Application Details</h3>';
                $content .= '<table>';
                $content .= '<tr><th>Application ID</th>' . '<td>' . ($response ['ApplicationId'] ? : 'None') . '</th></tr>';
                $content .= '<tr><th>Status</th>' . '<td>' . ($response ['Status'] ? : 'Rejected') . '</th></tr>';
                $content .= '<tr><th>Company ID</th>' . '<td>' . ($response ['CompanyPostedToId'] ? : 'None') . '</th></tr>';
                $content .= '<tr><th>TenStreet Log ID</th>' . '<td>' . ($response ['TenstreetLogId'] ? : 'None') . '</th></tr>';
                $content .= '<tr><th>Description</th>' . '<td>' . ($response ['Description'] ? : 'None') . '</th></tr>';
                $content .= '<tr><th>Created</th>' . '<td>' . date( 'F j, Y H:i:s', strtotime( ($response ['DateTime'] ? : 'now') ) ) . '</th></tr>';
                $content .= '</table>';
            }
            
            return $content;
        }
        
        /**
         * Extract data for post meta creation from API Submission data
         *
         * @param array $response API Response
         * 
         * @return array Post Meta data
         */
        function get_post_meta($response = null) {
            $meta = array ();
            if (is_array( $response )) {
                foreach ( $response as $field => $value ) {
                    $meta ["{$field}"] = $value;
                }
            }
            
            return $meta;
        }
        
        /**
         * Send mail notification on error
         * 
         * @param string $address
         * @param array $entry
         * 
         * @return void
         */
        function send_admin_error_email($address, $entry) {
            $to = $address;
            $subject = 'Gravity Forms TenStreet Error Notification';
            $message = "There was a problem sending your Gravity Forms submission to TenStreet.  The submission can be viewed at: " . admin_url("admin.php?page=gf_entries&view=entry&id=" . $entry ['form_id'] . "&lid=" . $entry ['id']);
            wp_mail($to, $subject, $message );
        }
        
    }
    
    new GF_TenStreet_Addon();
}