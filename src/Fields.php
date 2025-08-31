<?php
namespace Doubleedesign\Comet\WordPress\Classic;

use Doubleedesign\Comet\Core\Utils;

class Fields {

    public function __construct() {
        add_action('acf/include_fields', [$this, 'register_flexible_content_fields'], 5, 0);
        add_filter('acf/load_value/name=content_modules', [$this, 'set_default_modules'], 10, 3);
        add_filter('acf/fields/flexible_content/no_value_message', [$this, 'customise_no_value_message'], 10, 2);
    }

    public function customise_no_value_message($message, $field): string {
        return sprintf(
            __('Click the "%s" button to add a section to the page', 'acf-dynamic-preview'),
            $field['button_label']
        );
    }

    public function register_flexible_content_fields(): void {
        $post_types = apply_filters('comet_acf_flexible_modules_post_types', ['page']);
        $locations = array_map(fn($post_type) => array(
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => $post_type,
        ), $post_types);
        
        $exclusions = [get_option('page_for_posts')];
        if (!empty($exclusions)) {
            $locations[] = array(
                'param'    => 'post',
                'operator' => '!=',
                'value'    => implode(',', $exclusions),
            );
        }

        $final = array(
            'key'    => 'group_content-modules',
            'title'  => 'Content modules',
            'fields' => array(
                array(
                    'key'               => 'field_content-modules',
                    'label'             => 'Content modules',
                    'name'              => 'content_modules',
                    'type'              => 'flexible_content',
                    'layouts'           => array_merge(
                        $this->get_basic_modules(),
                        $this->get_complex_modules()
                    ),
                    'button_label'      => 'Add module',
                ),
            ),
            'location'              => array($locations),
            'menu_order'            => 0,
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen'        => array(
                0 => 'the_content',
            ),
            'active'                 => true,
            'description'            => '',
            'show_in_rest'           => 0,
            'acf_component_defaults' => array(
                'layout'        => 'block',
                'repeatable'    => '0',
                'pagination'    => '0',
                'rows_per_page' => '50',
                'min'           => '',
                'max'           => '',
                'button_label'  => '',
                'appearances'   => '',
            ),
            'modified' => 1755999529,
        );

        acf_add_local_field_group($final);
    }

    private function create_select_field(string $module, string $label, ?string $default_value = null, ?int $wrapper_width = null, ?array $choices = null, ?array $extra = null): array {
        if (empty($choices)) {
            $choices = match ($label) {
                'Width' => array(
                    'contained' => 'Contained',
                    'narrow'    => 'Narrow',
                    'wide'      => 'Wide',
                    'fullwidth' => 'Full-width',
                ),
                'Colour theme' => array(
                    'primary'   => 'Primary',
                    'secondary' => 'Secondary',
                    'accent'    => 'Accent',
                    'light'     => 'Light',
                    'dark'      => 'Dark',
                ),
                'Background colour' => array(
                    'theme' => 'Inherit from colour theme',
                    'light' => 'Light',
                    'dark'  => 'Dark',
                    'white' => 'White'
                ),
                'Alignment', 'Vertical alignment', 'Horizontal alignment' => array(
                    'default' => 'Inherit',
                    'start'   => 'Start',
                    'center'  => 'Middle',
                    'end'     => 'End',
                ),
                'Orientation' => array(
                    'horizontal' => 'Horizontal',
                    'vertical'   => 'Vertical',
                ),
                default => [],
            };
        }

        $default_value = $default_value ?: array_key_first($choices);
        $wrapper_width = $wrapper_width ?: 33;

        return array(
            'key'               => 'field_' . Utils::kebab_case($label) . '_' . $module,
            'label'             => $label,
            'name'              => Utils::kebab_case($label),
            'type'              => 'select',
            'wrapper'           => array(
                'width' => $wrapper_width,
            ),
            'choices'           => $choices,
            'default_value'     => $default_value,
            'return_format'     => 'value',
            'multiple'          => false,
            'repeatable'        => true,
            'allow_null'        => 0,
            'ui'                => 1, // enables select2
            ...$extra ?? []
        );
    }

    protected function get_basic_modules(): array {
        $breadcrumbs_for_page_header = class_exists('Doubleedesign\Breadcrumbs\Breadcrumbs') ? (
            array(
                'key'           => 'field__page-header__show-breadcrumbs',
                'label'         => 'Show breadcrumbs',
                'name'          => 'show_breadcrumbs',
                'type'          => 'true_false',
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
                'default_value' => 1,
                'wrapper'       => array(
                    'width' => 25,
                ),
                'repeatable' => false
            )) : array();

        $default = array(
            'layout_page-header' => array(
                'key'        => 'layout_page-header',
                'name'       => 'page_header',
                'label'      => 'Page header',
                'display'    => 'block',
                'sub_fields' => array(
                    array(
                        'key'           => 'field__page-header__heading',
                        'label'         => 'Heading',
                        'name'          => 'heading',
                        'type'          => 'text',
                        'instructions'  => 'If nothing is entered, the page title will be used.',
                        'default_value' => '',
                        'placeholder'   => '',
                    ),
                    $this->create_select_field('page-header', 'Colour theme', 'primary', !empty($breadcrumbs_for_page_header) ? 25 : 33),
                    $this->create_select_field('page-header', 'Background colour', 'white', !empty($breadcrumbs_for_page_header) ? 25 : 33),
                    $this->create_select_field('page-header', 'Width', 'contained', !empty($breadcrumbs_for_page_header) ? 25 : 33),
                    $breadcrumbs_for_page_header
                ),
                'max' => 1, // only allow one page header per page
            ),
            'layout_banner' => array(
                'key'        => 'layout_banner',
                'name'       => 'banner',
                'label'      => 'Banner',
                'display'    => 'block',
                'sub_fields' => array(
                    array(
                        'key'           => 'field__banner__image',
                        'label'         => 'Image',
                        'name'          => 'image',
                        'type'          => 'image',
                        'required'      => 1,
                        'return_format' => 'array',
                        'preview_size'  => 'full',
                        'library'       => 'all',
                    )
                )
            ),
            'layout_button-group' => array(
                'key'        => 'layout_button-group',
                'name'       => 'button_group',
                'label'      => 'Button group',
                'display'    => 'block',
                'sub_fields' => array(
                    $this->create_select_field('button-group', 'Colour theme'),
                    $this->create_select_field('button-group', 'Alignment'),
                    $this->create_select_field('button-group', 'Orientation'),
                    array(
                        'key'               => 'field__button-group__buttons',
                        'label'             => 'Buttons',
                        'name'              => 'buttons',
                        'type'              => 'repeater',
                        'collapsed'         => 'field__button-group__button',
                        'min'               => 1,
                        'max'               => 5,
                        'layout'            => 'block',
                        'button_label'      => 'Add button',
                        'repeatable'        => true,
                        'sub_fields'        => array(
                            array(
                                'key'               => 'field__button-group__button',
                                'label'             => 'Button',
                                'name'              => 'button',
                                'type'              => 'link',
                                'return_format'     => 'array',
                                'repeatable'        => true,
                            ),
                        ),
                    ),
                ),
            ),
            'layout_call-to-action' => array(
                'key'        => 'layout_call-to-action',
                'name'       => 'call_to_action',
                'label'      => 'Call-to-action',
                'display'    => 'block',
                'sub_fields' => array(
                    array(
                        'key'               => 'field__call-to-action__heading',
                        'label'             => 'Heading',
                        'name'              => 'heading',
                        'type'              => 'text',
                        'required'          => true,
                        'maxlength'         => 120,
                        'repeatable'        => true,
                        'wrapper'           => array(
                            'width' => 70,
                        )
                    ),
                    array(
                        'key'           => 'field__call-to-action__edit_description',
                        'label'         => 'Show description editor',
                        'name'          => 'edit_description',
                        'type'          => 'true_false',
                        'ui'            => 1,
                        'ui_on_text'    => 'Editing',
                        'ui_off_text'   => 'Closed',
                        'default_value' => 0,
                        'repeatable'    => false,
                        'wrapper' 	     => array(
                            'width' => 30,
                        ),
                    ),
                    array(
                        'key'               => 'field__call-to-action__description',
                        'label'             => 'Description',
                        'name'              => 'description',
                        'type'              => 'wysiwyg',
                        'toolbar'           => 'minimal',
                        'media_upload'      => 0,
                        'required'          => false,
                        'default_value'     => '',
                        'repeatable'        => true,
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field'    => 'field__call-to-action__edit_description',
                                    'operator' => '==',
                                    'value'    => '1',
                                ),
                            ),
                        ),
                    ),
                    array(
                        'key'               => 'field__call-to-action__button',
                        'label'             => 'Button',
                        'name'              => 'button',
                        'type'              => 'link',
                        'return_format'     => 'array',
                        'repeatable'        => true,
                        'wrapper'           => array(
                            'width' => 25
                        )
                    ),
                    $this->create_select_field('call-to-action', 'Colour theme', 'primary', 25),
                    $this->create_select_field('call-to-action', 'Background colour', 'white', 25),
                    $this->create_select_field('call-to-action', 'Width', 'contained', 25),
                ),
            ),
            'layout_child-pages' => array(
                'key'        => 'layout_child-pages',
                'name'       => 'child_pages',
                'label'      => 'Child pages',
                'display'    => 'block',
                'sub_fields' => array(
                    array(
                        'key'           => 'field__child-pages__heading',
                        'label'         => 'Heading',
                        'name'          => 'heading',
                        'type'          => 'text',
                        'default_value' => 'In this section',
                        'placeholder'   => 'In this section',
                        'repeatable'    => true,
                    ),
                    array(
                        'key'               => 'field__child-pages__message',
                        'type'              => 'message',
                        'message'           => 'Preview cards of this page\'s sub-pages, linked to the page, will automatically appear here. If there are none, the module will not be shown.',
                        'new_lines'         => 'wpautop',
                        'esc_html'          => 0,
                        'repeatable'        => true,
                    ),
                ),
            ),
            'layout_copy' => array(
                'key'        => 'layout_copy',
                'name'       => 'copy',
                'label'      => 'Copy',
                'display'    => 'block',
                'sub_fields' => array(
                    array(
                        'key'               => 'field__copy__content',
                        'label'             => 'Content',
                        'name'              => 'copy',
                        'type'              => 'wysiwyg',
                        'tabs'              => 'all',
                        'toolbar'           => 'basic',
                        'media_upload'      => 1,
                        'delay'             => 0,
                        'repeatable'        => true,
                    ),
                    $this->create_select_field('copy', 'Width'),
                ),
            ),
            'layout_gallery' => array(
                'key'        => 'layout_gallery',
                'name'       => 'gallery',
                'label'      => 'Gallery',
                'display'    => 'block',
                'sub_fields' => array(
                    array(
                        'key'               => 'field__gallery__images',
                        'label'             => 'Images',
                        'name'              => 'images',
                        'type'              => 'gallery',
                        'insert'            => 'append',
                        'library'           => 'all',
                        'min'               => 3,
                        'max'               => 24,
                        'preview_size'      => 'medium',
                    )
                )

            ),
            'layout_image' => array(
                'key'        => 'layout_image',
                'name'       => 'image',
                'label'      => 'Image',
                'display'    => 'block',
                'sub_fields' => array(
                    array(
                        'key'           => 'field__image__image',
                        'label'         => 'Image',
                        'name'          => 'image',
                        'type'          => 'image',
                        'instructions'  => 'Recommended size: 1200px wide or larger',
                        'required'      => 1,
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                        'library'       => 'all',
                    )
                )
            ),
            'layout_latest-posts' => array(
                'key'        => 'layout_latest-posts',
                'name'       => 'latest_posts',
                'label'      => 'Latest posts',
                'display'    => 'block',
                'sub_fields' => array(
                    array(
                        'key'               => 'field__latest-posts__heading',
                        'label'             => 'Heading',
                        'name'              => 'heading',
                        'type'              => 'text',
                        'default_value'     => 'Latest Posts',
                        'placeholder'       => 'Latest Posts',
                        'repeatable'        => true,
                        'wrapper'           => array(
                            'width' => 66,
                        )
                    ),
                    array(
                        'key'                  => 'field__latest-posts__category',
                        'label'                => 'Show posts from',
                        'name'                 => 'show_posts_from',
                        'type'                 => 'taxonomy',
                        'taxonomy'             => 'category',
                        'field_type'           => 'checkbox',
                        'return_format'        => 'id',
                        'repeatable'           => true,
                        'wrapper'              => array(
                            'width' => 33,
                        )
                    ),
                    $this->create_select_field('latest-posts', 'Colour theme'),
                    array(
                        'key'               => 'field__latest-posts__number',
                        'label'             => 'Number of posts to show',
                        'name'              => 'number',
                        'type'              => 'number',
                        'default_value'     => 3,
                        'min'               => 1,
                        'max'               => 30,
                        'step'              => 1,
                        'repeatable'        => true,
                        'wrapper'           => array(
                            'width' => 33,
                        )
                    ),
                ),
            ),
        );

        return apply_filters('comet_acf_get_basic_modules', $default);
    }

    protected function get_nestable_modules(): array {
        $default = array_filter($this->get_basic_modules(), function($module) {
            return !in_array($module['name'], array('page_header', 'latest_posts', 'child_pages', 'banner'));
        });

        // Remove sub-fields that are not suitable for nested instances
        $default = array_map(function($module) {
            $module['sub_fields'] = array_filter($module['sub_fields'], function($field) {
                return !in_array($field['name'], array('width'));
            });

            return $module;
        }, $default);

        return apply_filters('comet_acf_get_nestable_modules', $default);
    }

    protected function get_complex_modules(): array {
        $default = array(
            'layout_accordion' => array(
                'key'        => 'layout_accordion',
                'name'       => 'accordion',
                'label'      => 'Accordion',
                'display'    => 'block',
                'sub_fields' => array(
                    array(
                        'key'               => 'field__accordion__heading',
                        'label'             => 'Heading',
                        'name'              => 'heading',
                        'type'              => 'text',
                        'repeatable'        => true,
                    ),
                    $this->create_select_field('accordion', 'Colour theme'),
                    $this->create_select_field('accordion', 'Width'),
                    array(
                        'key'           => 'field__accordion__edit_intro',
                        'label'         => 'Show intro editor',
                        'name'          => 'edit_intro',
                        'type'          => 'true_false',
                        'ui'            => 1,
                        'ui_on_text'    => 'Editing',
                        'ui_off_text'   => 'Closed',
                        'default_value' => 0,
                        'repeatable'    => false,
                        'wrapper' 	     => array(
                            'width' => 33,
                        ),
                    ),
                    array(
                        'key'               => 'field_accordion__intro',
                        'label'             => 'Intro copy',
                        'name'              => 'intro_copy',
                        'type'              => 'wysiwyg',
                        'tabs'              => 'all',
                        'toolbar'           => 'minimal',
                        'media_upload'      => 1,
                        'delay'             => 0,
                        'repeatable'        => true,
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field'    => 'field__accordion__edit_intro',
                                    'operator' => '==',
                                    'value'    => '1',
                                ),
                            ),
                        ),
                    ),
                    array(
                        'key'               => 'field__accordion__panels',
                        'label'             => 'Panels',
                        'name'              => 'panels',
                        'type'              => 'repeater',
                        'collapsed'         => 'field_accordion-panel__heading',
                        'min'               => 0,
                        'max'               => 20,
                        'layout'            => 'block',
                        'button_label'      => 'Add panel',
                        'rows_per_page'     => 20,
                        'repeatable'        => true,
                        'sub_fields'        => array(
                            array(
                                'key'               => 'field__accordion-panel__heading',
                                'label'             => 'Heading',
                                'name'              => 'heading',
                                'type'              => 'text',
                                'parent_repeater'   => 'field__accordion__panels',
                                'repeatable'        => true,
                            ),
                            array(
                                'key'               => 'field__accordion-panel__content',
                                'label'             => 'Panel content',
                                'name'              => 'panel_content__modules',
                                'type'              => 'flexible_content',
                                'layouts'           => $this->get_nestable_modules(),
                                'button_label'      => 'Add module to panel',
                            ),
                        ),
                    ),
                ),
            ),
            // TODO: Add columns module - repeater of columns with nestable modules and various settings as per Comet capabilities.
        );

        return apply_filters('comet_acf_get_complex_modules', $default);
    }

    public function set_default_modules($value, $post_id, $field): array {
        if (!$value) {
            $all_modules = acf_get_field('field_content-modules')['layouts'];
            $page_header = $all_modules['layout_page-header'];
            $copy = $all_modules['layout_copy'];
            $value = array();

            if (isset($page_header)) {
                $fields = array_map(fn($sub_field) => $sub_field['key'], $page_header['sub_fields']);
                $default_values = array_map(fn($sub_field) => $sub_field['default_value'] ?? '', $page_header['sub_fields']);
                $defaults = array_combine($fields, $default_values);
                array_push($value, array(
                    'acf_fc_layout' => 'page_header',
                    ...$defaults
                ));
            }
            if (isset($copy)) {
                $fields = array_map(fn($sub_field) => $sub_field['key'], $copy['sub_fields']);
                $default_values = array_map(fn($sub_field) => $sub_field['default_value'] ?? '', $copy['sub_fields']);
                $defaults = array_combine($fields, $default_values);
                array_push($value, array(
                    'acf_fc_layout' => 'copy',
                    ...$defaults
                ));
            }
        }

        return $value;
    }
}
