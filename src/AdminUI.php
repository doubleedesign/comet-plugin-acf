<?php

namespace Doubleedesign\Comet\WordPress\Classic;

class AdminUI {

    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets'], 100);
        add_filter('tiny_mce_before_init', [$this, 'add_common_css_to_tinymce'], 10, 1);
    }

    public function enqueue_assets(): void {
        wp_enqueue_style(
            'comet-acf-admin',
            plugins_url('src/assets/admin.css', __DIR__),
            [],
            PluginEntryPoint::get_version()
        );

        wp_enqueue_script(
            'comet-acf-admin',
            plugins_url('src/assets/admin.js', __DIR__),
            ['jquery', 'acf'],
            PluginEntryPoint::get_version(),
            true
        );
    }

    /**
     * Load Comet's global and common CSS into ACF WYSIWYG fields
     * Ref: https://pagegwood.com/web-development/custom-editor-stylesheets-advanced-custom-fields-wysiwyg/
     *
     * @param  $mce_init
     *
     * @wp-hook
     *
     * @return array
     */
    public function add_common_css_to_tinymce($mce_init): array {
        $css = plugins_url('src/assets/editor.css', __DIR__);

        if (isset($mce_init['content_css']) && $css) {
            $content_css_new = $mce_init['content_css'] . ',' . $css;
            $mce_init['content_css'] = $content_css_new;
        }

        return $mce_init;
    }

}
