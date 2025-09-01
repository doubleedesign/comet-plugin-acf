<?php

namespace Doubleedesign\Comet\WordPress\Classic;

class GlobalSettings {

    public function __construct() {
        add_action('acf/init', array($this, 'add_global_options_page'), 10);
        add_filter('doublee_global_settings_fields', [$this, 'add_global_settings_fields'], 10, 1);
        add_filter('doublee_global_settings_contributors', [$this, 'add_this_plugin_to_global_settings_about_tab'], 10, 1);
        add_action('acf/include_fields', [$this, 'register_global_settings_fields'], 20, 0);
    }

    public function add_global_options_page(): void {
        // Bail if the page already exists (registered by the Double-E Design Base Plugin)
        // or the ACF Options Page feature is not available
        if (acf_get_options_page('acf-options-global-options') || !function_exists('acf_add_options_page')) {
            return;
        }

        $firstWord = explode(' ', get_bloginfo('name'))[0];
        acf_add_options_page(array(
            'page_title'  => 'Global Settings and Information for ' . get_bloginfo('name'),
            'menu_title'  => $firstWord . ' settings',
            'parent_slug' => 'themes.php',
            'menu_slug'   => 'acf-options-global-options',
            'position'    => 0
        ));
    }

    public function register_global_settings_fields(): void {
        // If the Global Settings fields have already been registered by the Double-E Design Base Plugin, do nothing here
        // because we will add our fields using the provided filter instead.
        if ($this->global_settings_already_registered() || !function_exists('acf_add_local_field_group')) {
            return;
        }

        // Register Global Settings fields if Double-E Design Base Plugin is not active
        // Register the same logo field + the fields added in add_global_settings_fields()
        $default = array(
            'key'                   => 'group_5876ae3e825e9',
            'title'                 => 'Global options',
            'fields'                => array(
                array(
                    'key'               => 'field_65910e84e0efd',
                    'label'             => 'Brand',
                    'name'              => '',
                    'aria-label'        => '',
                    'type'              => 'tab',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                    'placement'         => 'left',
                    'endpoint'          => 0,
                    'selected'          => 0,
                    'repeatable'        => true,
                ),
                array(
                    'key'               => 'field_65910e95e0efe',
                    'label'             => 'Logo',
                    'name'              => 'logo',
                    'aria-label'        => '',
                    'type'              => 'image',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '65',
                        'class' => '',
                        'id'    => '',
                    ),
                    'return_format'     => 'id',
                    'library'           => 'all',
                    'min_width'         => '',
                    'min_height'        => '',
                    'min_size'          => '',
                    'max_width'         => '',
                    'max_height'        => '',
                    'max_size'          => '',
                    'mime_types'        => '',
                    'preview_size'      => 'medium',
                    'uploader'          => '',
                    'acfe_thumbnail'    => 0,
                    'repeatable'        => true,
                ),
                array(
                    'key'               => 'field_67144bbfa473a',
                    'label'             => 'Accounts & Assets',
                    'name'              => '',
                    'aria-label'        => '',
                    'type'              => 'tab',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                    'placement'         => 'top',
                    'endpoint'          => 0,
                    'selected'          => 0,
                ),
            ),
            'location'              => array(
                array(
                    array(
                        'param'    => 'options_page',
                        'operator' => '==',
                        'value'    => 'acf-options-global-options',
                    ),
                ),
            ),
            'menu_order'            => 0,
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen'        => '',
            'active'                => true,
            'description'           => '',
            'show_in_rest'          => 0,
            'modified'              => 1740105843,
        );

        // Allow other plugins and themes to modify the default fields before registering
        $final = apply_filters('doublee_global_settings_fields', $default);
        acf_add_local_field_group($final);
    }

    /**
     * Add fields to the Global Options provided by the Double-E Design Base Plugin
     * or registered by this one if that one is not active / has not registered the field group.
     *
     * @param  $fields
     *
     * @return array
     */
    public function add_global_settings_fields($fields): array {
        $font_awesome_kit_field = array(
            'key'               => 'field_font_awesome_kit',
            'label'             => 'Font Awesome kit code (for icons)',
            'name'              => 'font_awesome_kit',
            'aria-label'        => '',
            'type'              => 'text',
            'instructions'      => 'If your developer has not included this, you can sign up and create a kit at https://fontawesome.com/',
            'required'          => 0,
            'conditional_logic' => 0,
            'wrapper'           => array(
                'width' => '',
                'class' => '',
                'id'    => '',
            ),
            'default_value'     => '',
            'maxlength'         => '',
            'allow_in_bindings' => 0,
            'placeholder'       => '',
            'prepend'           => '',
            'append'            => '',
        );

        // Find the "Accounts & Assets" tab
        $index = array_search('Accounts & Assets', array_column($fields['fields'], 'label'));
        if ($index !== false) {
            // Insert the new field after the tab
            array_splice($fields['fields'], $index + 1, 0, [$font_awesome_kit_field]);
        }
        else {
            // If the tab is not found, just append the field to the end
            $fields['fields'][] = $font_awesome_kit_field;
        }

        return $fields;
    }

    public function add_this_plugin_to_global_settings_about_tab($contributors) {
        array_push($contributors, 'Comet Components for ACF Flexible Content plugin');

        return $contributors;
    }

    private function global_settings_already_registered(): bool {
        // Check if global settings fields already exist from Double-E Design Base Plugin
        $maybe_global_settings = acf_get_field_group('group_5876ae3e825e9');
        if ($maybe_global_settings && $maybe_global_settings['title'] == 'Global options') {
            return true;
        }

        return false;
    }
}
