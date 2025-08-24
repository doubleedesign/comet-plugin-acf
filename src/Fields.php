<?php
namespace Doubleedesign\Comet\WordPress\Classic;

class Fields {

    public function __construct() {
        add_action('acf/include_fields', [$this, 'register_flexible_content_fields'], 5, 0);
        add_filter('acf/fields/flexible_content/no_value_message', [$this, 'customise_no_value_message'], 10, 2);
    }

    public function customise_no_value_message($message, $field): string {
        return sprintf(
            __('Click the "%s" button to add a section to the page', 'acf-dynamic-preview'),
            $field['button_label']
        );
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
                    'theme' => 'Colour theme',
                    'light' => 'Light',
                    'dark'  => 'Dark',
                    'white' => 'White'
                ),
                default => [],
            };
        }

        $default_value = $default_value ?: array_key_first($choices);
        $wrapper_width = $wrapper_width ?: match ($label) {
            'Width', 'Colour theme', 'Background colour' => 33,
            default             => 100,
        };

        return array(
            'key'               => 'field_' . $this->snake_case($label) . '_' . $module,
            'label'             => $label,
            'name'              => $this->snake_case($label),
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
            ...$extra ?? []
        );
    }

    private function snake_case(string $string): string {
        return strtolower(preg_replace('/[^A-Za-z0-9]+/', '_', $string));
    }

    public function register_flexible_content_fields(): void {
        acf_add_local_field_group(array(
            'key'    => 'group_content-modules',
            'title'  => 'Content modules',
            'fields' => array(
                array(
                    'key'               => 'field_content-modules',
                    'label'             => 'Content modules',
                    'name'              => 'content_modules',
                    'type'              => 'flexible_content',
                    'layouts'           => array(
                        'layout_page-header' => array(
                            'key'        => 'layout_page-header',
                            'name'       => 'page_header',
                            'label'      => 'Page header',
                            'display'    => 'block',
                            'sub_fields' => array(
                                array(
                                    'key'           => 'field_61c4385824ff1',
                                    'label'         => 'Heading',
                                    'name'          => 'heading',
                                    'type'          => 'text',
                                    'instructions'  => 'If nothing is entered, the page title will be used.',
                                    'default_value' => '',
                                    'placeholder'   => '',
                                ),
                                $this->create_select_field('page-header', 'Colour theme'),
                                $this->create_select_field('page-header', 'Background colour', 'white'),
                                // TODO: Only add this field if Breadcrumbs plugin is active
                                array(
                                    'key'           => 'field_61c438d824ff2',
                                    'label'         => 'Show breadcrumbs',
                                    'name'          => 'show_breadcrumbs',
                                    'type'          => 'true_false',
                                    'ui'            => 1,
                                    'ui_on_text'    => 'Yes',
                                    'ui_off_text'   => 'No',
                                    'default_value' => 1,
                                    'wrapper'       => array(
                                        'width' => 33,
                                    ),
                                    'repeatable' => true,
                                )
                            )
                        ),
                        'layout_accordion' => array(
                            'key'        => 'layout_accordion',
                            'name'       => 'accordion',
                            'label'      => 'Accordion',
                            'display'    => 'block',
                            'sub_fields' => array(
                                array(
                                    'key'               => 'field_5ef6dd0c7828d',
                                    'label'             => 'Heading',
                                    'name'              => 'heading',
                                    'type'              => 'text',
                                    'repeatable'        => true,
                                ),
                                array(
                                    'key'               => 'field_5ef6dd177828e',
                                    'label'             => 'Intro copy',
                                    'name'              => 'intro_copy',
                                    'type'              => 'wysiwyg',
                                    'tabs'              => 'all',
                                    'toolbar'           => 'full',
                                    'media_upload'      => 1,
                                    'delay'             => 0,
                                    'repeatable'        => true,
                                ),
                                $this->create_select_field('accordion', 'Colour theme'),
                                $this->create_select_field('accordion', 'Width'),
                                array(
                                    'key'               => 'field_5ef5c8184d1f3',
                                    'label'             => 'Panels',
                                    'name'              => 'panels',
                                    'type'              => 'repeater',
                                    'collapsed'         => 'field_5ef5c81f4d1f4',
                                    'min'               => 0,
                                    'max'               => 20,
                                    'layout'            => 'block',
                                    'button_label'      => 'Add panel',
                                    'rows_per_page'     => 20,
                                    'repeatable'        => true,
                                    'sub_fields'        => array(
                                        array(
                                            'key'               => 'field_5ef5c81f4d1f4',
                                            'label'             => 'Heading',
                                            'name'              => 'heading',
                                            'type'              => 'text',
                                            'parent_repeater'   => 'field_5ef5c8184d1f3',
                                            'repeatable'        => true,
                                        ),
                                        array(
                                            'key'               => 'field_5ef5c82a4d1f5',
                                            'label'             => 'Content',
                                            'name'              => 'content',
                                            'type'              => 'wysiwyg',
                                            'tabs'              => 'all',
                                            'toolbar'           => 'full',
                                            'media_upload'      => 1,
                                            'delay'             => 0,
                                            'parent_repeater'   => 'field_5ef5c8184d1f3',
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
                                    'key'               => 'field_62ca950f097c4',
                                    'label'             => 'Heading',
                                    'name'              => 'heading',
                                    'type'              => 'text',
                                    'required'          => true,
                                    'maxlength'         => 120,
                                    'repeatable'        => true,
                                ),
                                array(
                                    'key'               => 'field_62ca9523097c5',
                                    'label'             => 'Description',
                                    'name'              => 'description',
                                    'type'              => 'wysiwyg',
                                    'toolbar'           => 'basic',
                                    'required'          => false,
                                    'default_value'     => '',
                                    'repeatable'        => true,
                                ),
                                array(
                                    'key'               => 'field_62ca952a097c6',
                                    'label'             => 'Button',
                                    'name'              => 'button',
                                    'type'              => 'link',
                                    'return_format'     => 'array',
                                    'repeatable'        => true,
                                ),
                                $this->create_select_field('call-to-action', 'Colour theme'),
                                $this->create_select_field('call-to-action', 'Background colour', 'white'),
                                $this->create_select_field('call-to-action', 'Width'),
                            ),
                        ),
                        'layout_child-pages' => array(
                            'key'        => 'layout_child-pages',
                            'name'       => 'child_pages',
                            'label'      => 'Child pages',
                            'display'    => 'block',
                            'sub_fields' => array(
                                array(
                                    'key'               => 'field_62cfcf2612d8c',
                                    'type'              => 'message',
                                    'message'           => 'Automatic preview cards of this page\'s sub-pages, linked to the page.',
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
                                    'key'               => 'field_5d8f039098f5c',
                                    'label'             => 'Content',
                                    'name'              => 'copy',
                                    'type'              => 'wysiwyg',
                                    'tabs'              => 'all',
                                    'toolbar'           => 'full',
                                    'media_upload'      => 1,
                                    'delay'             => 0,
                                    'repeatable'        => true,
                                ),
                                $this->create_select_field('copy', 'Width'),
                            ),
                        ),
                        'layout_copy-image' => array(
                            'key'        => 'layout_copy-image',
                            'name'       => 'copy_image',
                            'label'      => 'Copy + image',
                            'display'    => 'block',
                            'sub_fields' => array(
                                array(
                                    'key'               => 'field_5d8f026a98f53',
                                    'label'             => 'Image',
                                    'name'              => 'image',
                                    'type'              => 'image',
                                    'wrapper'           => array(
                                        'width' => 50,
                                    ),
                                    'return_format' => 'id',
                                    'library'       => 'all',
                                    'preview_size'  => 'large',
                                    'repeatable'    => true,
                                ),
                                array(
                                    'key'               => 'field_5d8f028198f55',
                                    'label'             => 'Copy',
                                    'name'              => 'copy',
                                    'type'              => 'wysiwyg',
                                    'wrapper'           => array(
                                        'width' => 50,
                                    ),
                                    'tabs'          => 'all',
                                    'toolbar'       => 'full',
                                    'media_upload'  => 1,
                                    'delay'         => 0,
                                    'repeatable'    => true,
                                ),
                                array(
                                    'key'               => 'field_5ef6c04a8856b',
                                    'label'             => 'Order',
                                    'name'              => 'order',
                                    'type'              => 'radio',
                                    'wrapper'           => array(
                                        'width' => 25,
                                    ),
                                    'choices' => array(
                                        'copy_image' => 'Copy + image',
                                        'image_copy' => 'Image + copy',
                                    ),
                                    'default_value'     => 'image_copy',
                                    'return_format'     => 'value',
                                    'layout'            => 'horizontal',
                                    'repeatable'        => true,
                                ),
                                $this->create_select_field('copy-image', 'Background colour', 'white'),
                                $this->create_select_field('copy-image', 'Width'),
                                array(
                                    'key'               => 'field_640926f6a3d99',
                                    'label'             => 'Image cropping',
                                    'name'              => 'image_cropping',
                                    'type'              => 'select',
                                    'wrapper'           => array(
                                        'width' => 25,
                                    ),
                                    'choices' => array(
                                        'none'         => 'No cropping',
                                        'square'       => 'Crop to square',
                                        'sixteen-nine' => 'Crop to 16:9 aspect ratio',
                                        'four-three'   => 'Crop to 4:3 aspect ratio',
                                    ),
                                    'default_value'      => 'none',
                                    'return_format'      => 'value',
                                    'repeatable'         => true,
                                    'create_options'     => 0,
                                    'save_options'       => 0,
                                ),
                            ),
                        ),
                        'layout_hero' => array(
                            'key'        => 'layout_5ef5c8bb4d1f6',
                            'name'       => 'hero',
                            'label'      => 'Hero',
                            'display'    => 'block',
                            'sub_fields' => array(
                                array(
                                    'key'               => 'field_5ef5c8c14d1f7',
                                    'label'             => 'Image',
                                    'name'              => 'image',
                                    'type'              => 'image',
                                    'wrapper'           => array(
                                        'width' => 60,
                                    ),
                                    'return_format' => 'id',
                                    'library'       => 'all',
                                    'preview_size'  => '1536x1536',
                                    'repeatable'    => true,
                                ),
                                array(
                                    'key'               => 'field_64085568a545f',
                                    'label'             => 'Copy',
                                    'name'              => 'copy',
                                    'type'              => 'group',
                                    'wrapper'           => array(
                                        'width' => 40,
                                    ),
                                    'layout'                  => 'block',
                                    'repeatable'              => true,
                                    'sub_fields'              => array(
                                        array(
                                            'key'               => 'field_5ef5c8d94d1f8',
                                            'label'             => 'Heading',
                                            'name'              => 'heading',
                                            'type'              => 'text',
                                            'repeatable'        => true,
                                        ),
                                        array(
                                            'key'               => 'field_62ca923c3f401',
                                            'label'             => 'Copy',
                                            'name'              => 'copy',
                                            'type'              => 'wysiwyg',
                                            'tabs'              => 'all',
                                            'toolbar'           => 'basic',
                                            'media_upload'      => 0,
                                            'delay'             => 0,
                                            'repeatable'        => true,
                                        ),
                                        array(
                                            'key'               => 'field_5ef5c8f14d1f9',
                                            'label'             => 'Button',
                                            'name'              => 'button',
                                            'type'              => 'link',
                                            'return_format'     => 'array',
                                            'repeatable'        => true,
                                        ),
                                        $this->create_select_field('hero', 'Colour theme'),
                                        $this->create_select_field('hero', 'Background colour', 'white'),
                                        $this->create_select_field('hero', 'Width', 'fullwidth'),
                                    ),
                                ),
                            ),
                        ),
                        'layout_latest-posts' => array(
                            'key'        => 'layout_62cfd164dc9f3',
                            'name'       => 'latest_posts',
                            'label'      => 'Latest posts',
                            'display'    => 'block',
                            'sub_fields' => array(
                                array(
                                    'key'               => 'field_62cfd16cdc9f4',
                                    'label'             => 'Heading',
                                    'name'              => 'heading',
                                    'type'              => 'text',
                                    'repeatable'        => true,
                                ),
                                array(
                                    'key'                  => 'field_62cfd175dc9f5',
                                    'label'                => 'Show posts from',
                                    'name'                 => 'show_posts_from',
                                    'type'                 => 'taxonomy',
                                    'taxonomy'             => 'category',
                                    'field_type'           => 'checkbox',
                                    'return_format'        => 'id',
                                    'repeatable'           => true,
                                ),
                            ),
                        ),
                    ),
                    'button_label' => 'Add module',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'page',
                    ),
                ),
            ),
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
        ));
    }
}
