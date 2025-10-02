<?php
namespace Doubleedesign\Comet\WordPress\Classic;
use Doubleedesign\Comet\Core\{AspectRatio, Utils};

class Fields {

    public function __construct() {
        add_action('acf/include_fields', [$this, 'register_flexible_content_fields'], 5, 0);
        add_filter('acf/load_value/name=content_modules', [$this, 'set_default_modules'], 10, 3);
        add_filter('acf/fields/flexible_content/no_value_message', [$this, 'customise_no_value_message'], 10, 2);
    }

    public function customise_no_value_message($message, $field): string {
        if ($field['key'] === 'field_content-modules') {
            return sprintf(
                __('Click the "%s" button to add a section to the page', 'comet'),
                $field['button_label']
            );
        }

        if ($field['parent'] === 'field__accordion__panels') {
            return sprintf(
                __('Click the "%s" button to add content to this panel', 'comet'),
                $field['button_label']
            );
        }

        return $message;
    }

    public function register_flexible_content_fields(): void {
        $post_types = apply_filters('comet_acf_flexible_modules_post_types', ['page']);

        $locations = array_map(fn($post_type) => array(
            array(
                'param'    => 'post_type',
                'operator' => '==',
                'value'    => $post_type,
            )
        ), $post_types);

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
                    'button_label'      => 'Add section',
                ),
            ),
            'location'              => $locations,
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

        // Sort the final list by name
        uasort($final['fields'][0]['layouts'], function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        acf_add_local_field_group($final);
    }

    private function create_select_field(string $module, string $label, ?string $default_value = null, ?int $wrapper_width = null, ?array $choices = null, ?array $extra = null): array {
        if (empty($choices)) {
            $choices = match ($label) {
                'Width', 'Section width' => array(
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
                    'theme' => 'Set by colour theme',
                    'light' => 'Light',
                    'dark'  => 'Dark',
                    'white' => 'White'
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
            'ui'                => str_contains(strtolower($label), 'colour'), // enables select2
            ...$extra ?? []
        );
    }

    private function create_conditional_width_field(string $module, string $label, string $conditional_field, ?int $wrapper_width = 25): array {
        $field_slug = Utils::kebab_case($label);

        $field_common = array(
            'label'   => 'Container width',
            'name'    => 'container_width',
            'type'    => 'select',
            'wrapper' => [
                'width' => $wrapper_width
            ]
        );

        $common_conditions = array(
            'field'    => $conditional_field,
            'operator' => '==',
        );

        return array(
            // Full-width module - all container widths available
            array(
                ...$field_common,
                'key'               => "field__{$module}__{$field_slug}__full",
                'conditional_logic' => array(
                    array(
                        array(
                            ...$common_conditions,
                            'value'    => 'fullwidth',
                        ),
                    ),
                ),
                'choices' => array(
                    'fullwidth'      => 'Full-width',
                    'wide'           => 'Wide',
                    'contained'      => 'Contained',
                    'narrow'         => 'Narrow',
                ),
            ),
            // Wide module
            array(
                ...$field_common,
                'key'               => "field__{$module}__{$field_slug}__wide",
                'conditional_logic' => array(
                    array(
                        array(
                            ...$common_conditions,
                            'value'    => 'wide',
                        ),
                    ),
                ),
                'choices' => array(
                    'wide'      => 'Wide',
                    'contained' => 'Contained',
                    'narrow'    => 'Narrow',
                ),
            ),
            // Contained module
            array(
                ...$field_common,
                'key'               => "field__{$module}__{$field_slug}__contained",
                'conditional_logic' => array(
                    array(
                        array(
                            ...$common_conditions,
                            'value'    => 'contained',
                        ),
                    ),
                ),
                'choices' => array(
                    'contained' => 'Contained',
                    'narrow'    => 'Narrow',
                ),
            ),
            // Narrow module - only narrow container available
            array(
                ...$field_common,
                'key'               => "field__{$module}__{$field_slug}__narrow",
                'conditional_logic' => array(
                    array(
                        array(
                            ...$common_conditions,
                            'value'    => 'narrow',
                        ),
                    ),
                ),
                'choices' => array(
                    'narrow'    => 'Narrow',
                ),
            ),
        );
    }

    private function create_horizontal_alignment_field(string $parent_key, ?string $label = 'Horizontal alignment', ?string $default_value = 'default', ?int $wrapper_width = 30): array {
        return array(
            'key'           => "field__{$parent_key}__horizontal-alignment",
            'label'         => $label,
            'name'          => 'horizontal_alignment',
            'type'          => 'button_group',
            'choices'       => array(
                'default'   => '<span class="acf-js-tooltip" title="Automatic"><i class="fa-solid fa-wand-sparkles"></i></span>',
                'start'     => '<span class="acf-js-tooltip" title="Start"><i class="fa-solid fa-objects-align-left"></i></span>',
                'center'    => '<span class="acf-js-tooltip" title="Middle"><i class="fa-solid fa-objects-align-center-horizontal"></i></span>',
                'end'       => '<span class="acf-js-tooltip" title="End"><i class="fa-solid fa-objects-align-right"></i></span>',
            ),
            'default_value' => $default_value,
            'return_format' => 'value',
            'multiple'      => false,
            'allow_null'    => 0,
            'ui'            => 1,
            'wrapper'       => [
                'width' => $wrapper_width
            ]
        );
    }

    private function create_vertical_alignment_field(string $parent_key, ?string $label = 'Vertical alignment', ?string $default_value = 'center', ?int $wrapper_width = 30): array {
        return array(
            'key'           => "field__{$parent_key}__vertical-alignment",
            'label'         => $label,
            'name'          => 'vertical_alignment',
            'type'          => 'button_group',
            'choices'       => array(
                'start'   => '<span class="acf-js-tooltip" title="Top"><i class="fa-solid fa-objects-align-top"></i></span></span>',
                'center'  => '<span class="acf-js-tooltip" title="Middle"><i class="fa-solid fa-objects-align-center-vertical"></i></span>',
                'end'     => '<span class="acf-js-tooltip" title="Bottom"><i class="fa-solid fa-objects-align-bottom"></i></span>',
            ),
            'default_value' => $default_value,
            'return_format' => 'value',
            'multiple'      => false,
            'allow_null'    => 0,
            'ui'            => 1,
            'wrapper'       => [
                'width' => $wrapper_width
            ]
        );
    }

    private function create_orientation_field(string $parent_key, ?string $label = 'Orientation', ?string $default_value = 'horizontal', ?int $wrapper_width = 25): array {
        return array(
            'key'           => "field__{$parent_key}__orientation",
            'label'         => $label,
            'name'          => 'orientation',
            'type'          => 'button_group',
            'choices'       => array(
                'horizontal'   => '<span class="acf-js-tooltip" title="Horizontal"><i class="fa-solid fa-arrow-right-to-line"></i></span>',
                'vertical'     => '<span class="acf-js-tooltip" title="Vertical"><i class="fa-solid fa-arrow-down-to-line"></i></span>',
            ),
            'default_value' => $default_value,
            'return_format' => 'value',
            'multiple'      => false,
            'allow_null'    => 0,
            'ui'            => 1,
            'wrapper'       => [
                'width' => $wrapper_width
            ]
        );
    }

    private function create_button_group_repeater($parent_key, $wrapper_width = 100, $required = true): array {
        return array(
            'key'               => "field__{$parent_key}__button-group",
            'label'             => 'Buttons',
            'name'              => 'buttons',
            'type'              => 'repeater',
            'min'               => 1,
            'max'               => 5,
            'layout'            => 'table',
            'button_label'      => 'Add button',
            'repeatable'        => true,
            'wrapper'           => array(
                'width' => $wrapper_width,
            ),
            'sub_fields'        => array(
                array(
                    'key'               => "field__{$parent_key}__button-group__button",
                    'label'             => 'Button',
                    'name'              => 'button',
                    'type'              => 'link',
                    'return_format'     => 'array',
                    'repeatable'        => true,
                    'required'          => $required,
                    'wrapper'           => array(
                        'width' => 70,
                    ),
                ),
                array(
                    'key'           => "field__{$parent_key}__button-group__button__style",
                    'label'         => 'Style',
                    'name'          => 'style',
                    'type'          => 'button_group',
                    'choices'       => array(
                        'default'   => 'Solid',
                        'isOutline' => 'Outline',
                    ),
                    'wrapper' => array(
                        'width' => 30,
                    ),
                )
            ),
        );
    }

    private function create_link_group_field($parent_key, $wrapper_width = 100): array {
        return array(
            'key'               => "field__{$parent_key}__link-group",
            'label'             => 'Links',
            'name'              => 'links',
            'type'              => 'repeater',
            'min'               => 1,
            'max'               => 5,
            'layout'            => 'table',
            'button_label'      => 'Add link',
            'repeatable'        => true,
            'wrapper'           => array(
                'width' => $wrapper_width,
            ),
            'sub_fields'        => array(
                array(
                    'key'               => "field__{$parent_key}__link-group__link",
                    'label'             => 'Link',
                    'name'              => 'link',
                    'type'              => 'link',
                    'return_format'     => 'array',
                    'repeatable'        => true,
                    'required'          => true,
                ),
            ),
        );
    }

    private function create_aspect_ratio_field(string $parent, ?string $label = 'Aspect ratio', ?AspectRatio $default_value = AspectRatio::SQUARE, ?int $wrapper_width = 33): array {
        $enum_array = array_combine(
            array_column(AspectRatio::cases(), 'name'),
            array_column(AspectRatio::cases(), 'value')
        );

        $options = array_reduce(array_keys($enum_array), function($carry, $key) use ($enum_array) {
            $value = $enum_array[$key];
            $label = str_replace('_', ' ', strtolower($key));
            $carry[$value] = ucwords($label) . " ($value)";

            return $carry;
        }, []);

        return array(
            'key'           => $parent . '__aspect_ratio',
            'label'         => $label,
            'name'          => 'aspect_ratio',
            'type'          => 'select',
            'choices'       => $options,
            'default_value' => $default_value->value,
            'return_format' => 'value',
            'multiple'      => false,
            'allow_null'    => 0,
            'ui'            => 0,
            'wrapper'       => [
                'width' => $wrapper_width
            ]
        );
    }

    /**
     * Default definitions of basic flexible content modules.
     * Note that these may be overridden by filters in other plugins and themes;
     * notably width field is stripped when these are used as nested modules
     * and in single post context in the Comet Canvas Classic theme.
     *
     * @return array
     */
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
                        'type'          => 'image_advanced',
                        'instructions'  => 'Note: Aspect ratio may be ignored on small viewports',
                        'required'      => 1,
                        'return_format' => 'array',
                        'preview_size'  => 'full',
                        'library'       => 'all',
                    ),
                    array(
                        'key'           => 'field__banner__content',
                        'label'         => 'Content',
                        'name'          => 'content',
                        'type'          => 'group',
                        'repeatable'    => true,
                        'wrapper'       => ['width' => 60],
                        'sub_fields'    => array(
                            array(
                                'key'           => 'field__banner__content__heading',
                                'label'         => 'Heading',
                                'name'          => 'heading',
                                'type'          => 'text',
                                'required'      => 0,
                                'maxlength'     => 120,
                                'repeatable'    => true,
                            ),
                            array(
                                'key'           => 'field__banner__content__text',
                                'label'         => 'Text',
                                'name'          => 'text',
                                'type'          => 'wysiwyg',
                                'tabs'          => 'all',
                                'toolbar'       => 'minimal',
                                'media_upload'  => 0,
                                'default_value' => '',
                                'repeatable'    => true,
                            ),
                            $this->create_button_group_repeater('banner-content', 100, false)
                        ),
                    ),
                    array(
                        'key'           => 'field__banner__options',
                        'type'          => 'group',
                        'name'          => 'options',
                        'wrapper'       => ['width' => 40],
                        'sub_fields'    => array(
                            $this->create_select_field('banner', 'Colour theme', 'Primary', 100),
                            $this->create_select_field('banner', 'Width', 'full', 50),
                            ...$this->create_conditional_width_field('banner', 'Container width', 'field_width_banner', 50),
                            array(
                                'key'           => 'field__banner__content-width',
                                'label'         => 'Content max width',
                                'name'          => 'content_max_width',
                                'instructions'  => 'Width relative to the container at its max-width; may appear differently on smaller screens',
                                'type'          => 'button_group',
                                'choices'       => array(
                                    '25' => '25% (1/4)',
                                    '75' => '75% (3/4)',
                                    '33' => '33% (1/3)',
                                    '66' => '66% (2/3)',
                                    '50' => '50% (1/2)',
                                    '100'=> '100%',
                                ),
                                'default_value' => '50',
                                'return_format' => 'value',
                                'append'        => '%',
                                'wrapper'       => [
                                    'width' => 100
                                ]
                            ),
                            $this->create_horizontal_alignment_field('banner', 'Horizontal alignment', 'start', 50),
                            $this->create_vertical_alignment_field('banner', 'Vertical alignment', 'middle', 50)
                        )
                    )
                )
            ),
            'layout_button-group' => array(
                'key'        => 'layout_button-group',
                'name'       => 'button_group',
                'label'      => 'Button group',
                'display'    => 'block',
                'sub_fields' => array(
                    $this->create_button_group_repeater('button-group'),
                    $this->create_select_field('button-group', 'Colour theme', 'Primary', 25),
                    $this->create_horizontal_alignment_field('button-group', 'Horizontal alignment', 'inherit', 25),
                    $this->create_orientation_field('button"group'),
                    $this->create_select_field('button-group', 'Width', 'contained', 25),
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
                    $this->create_button_group_repeater('call-to-action'),
                    $this->create_select_field('call-to-action', 'Colour theme', 'primary', 20),
                    $this->create_select_field('call-to-action', 'Background colour', 'white', 20),
                    $this->create_select_field('call-to-action', 'Section width', 'contained', 20),
                    ...$this->create_conditional_width_field('call-to-action', 'Content width', 'field_section-width_call-to-action', 20),
                    $this->create_horizontal_alignment_field('call-to-action', 'Horizontal alignment', 'start', 20),
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
                        'key'               => 'field__copy__heading',
                        'label'             => 'Heading (optional)',
                        'name'              => 'heading',
                        'type'              => 'text',
                        'repeatable'        => true,
                        'wrapper'           => array(
                            'width' => 75,
                        ),
                    ),
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
                    $this->create_select_field('field__copy', 'Colour theme', 'primary', 25),
                    $this->create_select_field('copy', 'Width', 'contained', 25),
                    array(
                        'key'           => 'field__copy__more-options',
                        'label'         => 'Show additional options',
                        'name'          => 'more_options',
                        'type'          => 'true_false',
                        'ui'            => 1,
                        'ui_on_text'    => 'Editing',
                        'ui_off_text'   => 'Closed',
                        'default_value' => false,
                        'repeatable'    => false,
                        'wrapper' 	     => array(
                            'width' => 25,
                        ),
                    ),
                    array(
                        'key'               => 'field__copy__buttons',
                        'type'              => 'group',
                        'name'              => 'buttons',
                        'instructions'      => 'Optionally add some buttons below your content, in the same section within the document structure',
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field'    => 'field__copy__more-options',
                                    'operator' => '==',
                                    'value'    => 1
                                ),
                            ),
                        ),
                        'sub_fields'    => array(
                            $this->create_button_group_repeater('field__copy__buttons', 100, false),
                            $this->create_horizontal_alignment_field('field__copy__buttons', 'Button group alignment', 'default', 20),
                            $this->create_orientation_field('field__copy__buttons', 'Button group orientation', 'horizontal', 20)
                        )
                    ),
                ),
            ),
            'layout_divider' => array(
                'key' 	      => 'layout_divider',
                'name'       => 'divider',
                'label'      => 'Divider',
                'display'    => 'block',
                'sub_fields' => array(
                    $this->create_select_field('divider', 'Width', 'contained', 25),
                    $this->create_select_field('divider', 'Colour theme', 'light', 25),
                )
            ),
            'layout_gallery' => array(
                'key'        => 'layout_gallery',
                'name'       => 'gallery',
                'label'      => 'Gallery',
                'display'    => 'block',
                'sub_fields' => [
                    [
                        'key'               => 'field__gallery__images',
                        'label'             => 'Images',
                        'name'              => 'images',
                        'type'              => 'gallery',
                        'insert'            => 'append',
                        'library'           => 'all',
                        'min'               => 2,
                        'max'               => 24,
                        'preview_size'      => 'medium',
                    ],
                    $this->create_select_field('gallery', 'Width', 'contained', 25),
                    $this->create_aspect_ratio_field('gallery', 'Thumbnail aspect ratio', AspectRatio::SQUARE, 25),
                    [
                        'key'           => 'field__gallery__lightbox',
                        'label'         => 'Enable lightbox',
                        'name'          => 'lightbox',
                        'instructions'  => 'Overlay with full-size images opens when an image is clicked',
                        'type'          => 'true_false',
                        'ui'            => 1,
                        'ui_on_text'    => 'Yes',
                        'ui_off_text'   => 'No',
                        'default_value' => 1,
                        'wrapper'       => [
                            'width' => 50,
                        ],
                    ]
                ]
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
                        'type'          => class_exists('Doubleedesign\ACF\AdvancedImageField\AdvancedImageField') ? 'image_advanced' : 'image',
                        'required'      => 1,
                        'return_format' => 'array',
                        'preview_size'  => 'full',
                        'library'       => 'all',
                    ),
                    $this->create_select_field('image', 'Width', 'contained', 33)
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
            'layout_featured-posts' => array(
                'key'        => 'layout_featured-posts',
                'name'       => 'featured_posts',
                'label'      => 'Featured posts',
                'display'    => 'block',
                'sub_fields' => array(
                    array(
                        'key'               => 'field__featured-posts__heading',
                        'label'             => 'Heading',
                        'name'              => 'heading',
                        'type'              => 'text',
                        'default_value'     => 'Featured Posts',
                        'placeholder'       => 'Featured Posts',
                        'repeatable'        => true,
                    ),
                    array(
                        'key'                  => 'field__featured-posts__posts',
                        'label'                => 'Select posts to feature',
                        'name'                 => 'posts',
                        'type'                 => 'relationship',
                        'post_type'            => array('post'),
                        'return_format'        => 'id',
                        'multiple'             => true,
                        'repeatable'           => true,
                        'min'                  => 1,
                        'max'                  => 3
                    ),
                    $this->create_select_field('featured-posts', 'Colour theme'),
                    $this->create_select_field('featured-posts', 'Width'),
                ),
            ),
            'layout_link-group' => array(
                'key'        => 'layout_link-group',
                'name'       => 'link_group',
                'label'      => 'Link group',
                'display'    => 'block',
                'sub_fields' => array(
                    array(
                        'key'           => 'field__link-group__heading',
                        'label'         => 'Heading (optional)',
                        'name'          => 'heading',
                        'type'          => 'text',
                        'repeatable'    => true,
                        'wrapper'       => ['width' => 75]
                    ),
                    $this->create_select_field('link-group', 'Colour theme', 'Primary', 25),
                    $this->create_link_group_field('link-group'),
                    $this->create_select_field('link-group', 'Width', 'contained', 25),
                ),
            ),
        );

        return apply_filters('comet_acf_get_basic_modules', $default);
    }

    private function get_nestable_modules(): array {
        $default = array_filter($this->get_basic_modules(), function($module) {
            return !in_array($module['name'], array('page_header', 'latest_posts', 'child_pages', 'banner', 'gallery', 'call-to-action'));
        });

        // Remove sub-fields that are not suitable for nested instances
        $default = array_map(function($module) {
            $module['sub_fields'] = array_filter($module['sub_fields'], function($field) {
                return isset($field['name']) && !in_array($field['name'], array('width'));
            });

            return $module;
        }, $default);

        return apply_filters('comet_acf_get_nestable_modules', $default);
    }

    private function get_complex_modules(): array {
        $default = array(
            'layout_accordion' => array(
                'key'        => 'layout_accordion',
                'name'       => 'accordion',
                'label'      => 'Accordion',
                'display'    => 'block',
                'sub_fields' => array(
                    array(
                        'key'               => 'field__accordion__heading',
                        'label'             => 'Heading (optional)',
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
                        'collapsed'         => 'field__accordion-panel__heading',
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
                                'button_label'      => 'Add content',
                                'wrapper'           => array(
                                    'data-nested' => true
                                )
                            ),
                        ),
                    ),
                ),
            ),
            'layout_copy-image' => array(
                'key' 	      => 'layout_copy-image',
                'name' 	     => 'copy_image',
                'label'      => 'Copy + image',
                'display'    => 'block',
                'sub_fields' => array(
                    $this->create_select_field('copy-image', 'Width', 'contained', 25),
                    $this->create_select_field('copy-image', 'Colour theme', 'primary', 25),
                    $this->create_select_field('copy-image', 'Background colour', 'white', 25),
                    $this->create_select_field('copy-image', 'Order', 'copy-image', 25, array(
                        'copy-image' => 'Copy + Image',
                        'image-copy' => 'Image + Copy',
                    )),
                    array(
                        'key'           => 'field__copy-image__content',
                        'label'         => 'Content',
                        'name'          => 'content',
                        'type'          => 'group',
                        'sub_fields'    => array(
                            array(
                                'key'           => 'field__copy-image__content__heading',
                                'label'         => 'Heading (optional)',
                                'name'          => 'heading',
                                'type'          => 'text',
                                'repeatable'    => true,
                            ),
                            array(
                                'key'           => 'field__copy-image__content__body',
                                'label'         => 'Body text',
                                'name'          => 'Body text',
                                'type'          => 'wysiwyg',
                                'toolbar'       => 'minimal',
                                'tabs'          => 'visual',
                                'media_upload'  => false,
                                'repeatable'    => true,
                            ),
                            $this->create_button_group_repeater('field__copy-image__content')
                        ),
                        'wrapper'       => array('width' => 50)
                    ),
                    array(
                        'key'           => 'field__copy-image__image',
                        'label'         => 'Image',
                        'name'          => 'image',
                        'type'          => class_exists('Doubleedesign\ACF\AdvancedImageField\AdvancedImageField') ? 'image_advanced' : 'image',
                        'required'      => 1,
                        'return_format' => 'array',
                        'preview_size'  => 'full',
                        'library'       => 'all',
                        'wrapper'       => array('width' => 50)
                    ),
                ),
            ),
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

            if (get_the_id() != get_option('page_for_posts')) {
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
        }

        return $value;
    }
}
