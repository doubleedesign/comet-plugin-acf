<?php
namespace Doubleedesign\Comet\WordPress\Classic;

class TinyMCEConfig {

    public function __construct() {
        add_filter('acf/fields/wysiwyg/toolbars', [$this, 'customise_wysiwyg_toolbars'], 10, 1);
        add_filter('mce_buttons_2', [$this, 'add_styleselect'], 10, 1);
        add_filter('tiny_mce_before_init', [$this, 'populate_styleselect'], 10, 1);
        add_action('admin_init', [$this, 'add_select2_custom_css']);
    }

    /**
     * Customise the buttons available in WYSIWYG field editors.
     * Notes: Themes and other plugins may have their own customisations
     *        The "full" toolbar is affected by TinyMCE filters such as tinymce_before_init and mce_buttons.
     *
     * @param  $toolbars
     *
     * @return array
     */
    public function customise_wysiwyg_toolbars($toolbars): array {
        $filtered_basic = array_filter($toolbars['Basic']['1'], function($button) {
            return !in_array($button, ['underline', 'fullscreen', 'blockquote']);
        });
        $toolbars['Basic']['1'] = array_merge(
            ['formatselect', 'styleselect', 'forecolor'],
            $filtered_basic,
            ['charmap', 'pastetext', 'removeformat', 'undo', 'redo']
        );

        $toolbars['Minimal']['1'] = array_merge(
            array_filter($filtered_basic, function($button) {
                return !in_array($button, ['alignleft', 'alignjustify', 'aligncenter', 'alignright', 'blockquote', 'bullist', 'numlist']);
            }),
            ['charmap', 'pastetext', 'removeformat', 'undo', 'redo'],
        );

        $always_remove = ['fullscreen', 'wp_more', 'alignjustify', 'indent', 'outdent', 'underline'];
        array_walk($toolbars, function(&$buttons) use ($always_remove) {
            $rows = array_keys($buttons);
            foreach ($rows as $row) {
                $buttons[$row] = array_filter($buttons[$row], function($button) use ($always_remove) {
                    return !in_array($button, $always_remove);
                });
                $buttons[$row] = array_unique($buttons[$row]); // ensure no accidental duplicates
            }
        });

        return $toolbars;
    }

    /**
     * Add custom formats menu
     *
     * @param  $buttons
     *
     * @return array
     */
    public function add_styleselect($buttons): array {
        array_unshift($buttons, 'styleselect');

        return $buttons;
    }

    /**
     * Populate custom formats menu
     * Notes: - 'selector' for block-level element that format is applied to; 'inline' to add wrapping tag e.g.'span'
     *        - Using 'attributes' to apply the classes instead of 'class' ensures previous classes are replaced rather than added to
     *        - 'styles' are inline styles that are applied to the items in the menu, not the output; options are pretty limited but enough to add things like colours
     *          (further styling customisation to the menu may be done in the admin stylesheet)
     *
     * @param  $settings
     *
     * @return array
     */
    public function populate_styleselect($settings): array {
        $style_formats = array(
            array(
                'title'   => 'Lead paragraph',
                'block'   => 'p',
                'classes' => 'lead'
            ),
            array(
                'title'    => 'Accent heading',
                'selector' => 'h2,h3,h4',
                'classes'  => 'is-style-accent'
            ),
            array(
                'title'    => 'Small heading',
                'selector' => 'h2,h3,h4,h5,h6',
                'classes'  => 'is-style-small'
            ),
            array(
                'title'    => 'Span within heading',
                'inline'   => 'span',
                'selector' => 'h2,h3,h4,h5,h6',
            )
        );

        $settings['style_formats'] = json_encode($style_formats);

        unset($settings['preview_styles']);

        return $settings;
    }

    public function add_select2_custom_css(): void {
        wp_enqueue_style(
            'comet-select2-admin-custom',
            plugins_url('src/assets/select2.css', __DIR__),
            [],
            PluginEntryPoint::get_version()
        );
    }

}
