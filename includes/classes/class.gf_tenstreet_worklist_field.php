<?php

if (class_exists( "GFForms" ) && class_exists( "GF_Field_Select")) {
    
    /**
     * Gravity Forms TenStreet Worklist Field Class
     *
     * @author arstropica
     *
     */
    class GF_TenStreet_Worklist_Field extends GF_Field_Select {
        
        /**
         * 
         * @var string
         */
        public $type = 'gf_tenstreet_worklist';
        
        /**
         * 
         * @var string
         */
        protected $group_name = 'gf_tenstreet_fields';
        
        /**
         * 
         * @var string
         */
        protected $placeholder = 'Choose a Worklist';
        
        /**
         * 
         * @param array $properties
         */
        public function __construct($properties = []) {
            
            parent::__construct($properties);
            
            if (isset($this->formId)) {

                $this->set_choices($this->formId);

            }

        }
        
        /**
         * Indicates if this field type can be used when configuring conditional logic rules.
         *
         * @return bool
         */
        public function is_conditional_logic_supported() {

            return false;

        }

        /**
         *
         * {@inheritDoc}
         * @see GF_Field::get_form_editor_field_settings()
         */
        public function get_form_editor_field_settings() {
            
            return array(
                'conditional_logic_field_setting',
                'prepopulate_field_setting',
                'error_message_setting',
                'enable_enhanced_ui_setting',
                'label_setting',
                'label_placement_setting',
                'admin_label_setting',
                'size_setting',
                'rules_setting',
                'placeholder_setting',
                'default_value_setting',
                'visibility_setting',
                'duplicate_setting',
                'description_setting',
                'css_class_setting',
            );
            
        }
        
        /**
         * 
         * {@inheritDoc}
         * @see GF_Field::get_form_editor_field_title()
         */
        public function get_form_editor_field_title() {

            return esc_attr__( 'Worklist', 'gf-tenstreet' );

        }
        
        /**
         * 
         * {@inheritDoc}
         * @see GF_Field::get_form_editor_button()
         */
        public function get_form_editor_button() {

            return array(
                'group' => $this->group_name,
                'text'  => $this->get_form_editor_field_title(),
            );

        }
        
        /**
         * Adds the field button to the specified group.
         *
         * @param array $field_groups The field groups containing the individual field buttons.
         *
         * @return array
         */
        public function add_button( $field_groups ) {

            $field_groups = $this->maybe_add_field_group( $field_groups );
            
            return parent::add_button( $field_groups );
        }

        /**
         * Adds the custom field group if it doesn't already exist.
         *
         * @param array $field_groups The field groups containing the individual field buttons.
         *
         * @return array
         */
        public function maybe_add_field_group( $field_groups ) {

            foreach ( $field_groups as $field_group ) {
                if ( $field_group['name'] == $this->group_name ) {
                    
                    return $field_groups;
                }
            }
            
            $field_groups[] = array(
                'name'   => $this->group_name,
                'label'  => __( 'TenStreet Fields', 'gf-tenstreet' ),
                'fields' => array()
            );
            
            return $field_groups;
        }

        /**
         * Return the HTML markup for the field choices.
         *
         * @param string $value The field value.
         *
         * @return string
         */
        public function get_choices( $value ) {
            
            $choices = $this->get_worklist_as_choices( $value );
            
            return $choices;
        }
        
        /**
         * Set worklist options from database or JSON file
         * 
         * @param integer   $form_id
         * @param boolean   $use_default    Use the default JSON values.
         * @return void
         */
        private function set_choices($form_id, $use_default = false) {
            
            $options = null;
            
            $gf_tenstreet_addon = GF_TenStreet_Addon::get_instance();
            
            if ($use_default) {
                
                $api_field = $gf_tenstreet_addon->get_custom_field_setting($this->type);
                
                if ($api_field && isset($api_field['choices'])) {
                    
                    $options = $api_field['choices'];
                    
                }
                
            } else {
                
                $options = get_option( "gf_tenstreet_admin_client_worklist", null );
                
            }
            
            if ($options) {
                
                $worklist_choices = array_map(function($opt) {
                    
                    return ['value' => $opt, 'text' => $opt];
                    
                }, $options);
                    
                $worklist_choices = apply_filters('gf_tenstreet_user_field', $worklist_choices, $form_id, $this);
                
                $this->choices = $worklist_choices;
                    
            }
        }
        
        /**
         * Return the HTML markup for the field choices.
         *
         * @param string    $value          The field value.
         * @param boolean   $use_default    Use the default JSON values.
         *
         * @return string|null
         */
        public function get_worklist_as_choices( $value, $use_default = false ) {

            if (! $this->choices && isset($this->formId)) {

                $this->set_choices($this->formId, $use_default);

            }
            
            return $this->choices ? GFCommon::get_select_choices($this, $value) : null;
        }
        
        /**
         *
         * {@inheritDoc}
         * @see GF_Field::get_field_input()
         */
        public function get_field_input( $form, $value = '', $entry = null ) {
            
            $form_id         = absint( $form['id'] );
            
            $is_entry_detail = $this->is_entry_detail();
            
            $is_form_editor  = $this->is_form_editor();
            
            $id       = $this->id;
            
            $field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";
            
            $size               = $this->size;
            
            $class_suffix       = $is_entry_detail ? '_admin' : '';
            
            $class              = $size . $class_suffix;
            
            $css_class          = trim( esc_attr( $class ) . ' gfield_select' );
            
            $tabindex           = $this->get_tabindex();
            
            $disabled_text      = $is_form_editor ? 'disabled="disabled"' : '';
            
            $required_attribute = $this->isRequired ? 'aria-required="true"' : '';
            
            $invalid_attribute  = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';
            
            return sprintf( "<div class='ginput_container ginput_container_select'><select name='input_%d' id='%s' class='%s' $tabindex %s %s %s>%s</select></div>", $id, $field_id, $css_class, $disabled_text, $required_attribute, $invalid_attribute, $this->get_choices( $value ) );
        }
        
    }
    
    GF_Fields::register( new GF_TenStreet_Worklist_Field() );

}