<?php

namespace Doubleedesign\Comet\WordPress\Classic;

class ComponentAssets {

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_comet_combined_component_css'], 10);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_comet_combined_component_js'], 10);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_font_awesome'], 10);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_font_awesome'], 10);
        add_filter('script_loader_tag', [$this, 'script_type_module'], 10, 3);
        add_filter('script_loader_tag', [$this, 'script_base_path'], 10, 3);
    }

    /**
     * Combined stylesheet for all components
     *
     * @return void
     */
    public function enqueue_comet_combined_component_css(): void {
        $libraryDir = COMET_COMPOSER_VENDOR_URL . '/doubleedesign/comet-components-core';
        wp_enqueue_style('comet-components', "$libraryDir/dist/dist.css", array(), COMET_VERSION, 'all');
    }

    /**
     * Bundled JS for all components for the front-end
     *
     * @return void
     */
    public function enqueue_comet_combined_component_js(): void {
        $libraryDir = COMET_COMPOSER_VENDOR_URL . '/doubleedesign/comet-components-core';
        wp_enqueue_script('comet-components-js', "$libraryDir/dist/dist.js", array(), COMET_VERSION, true);
    }

    /**
     * Font Awesome JS
     *
     * @return void
     */
    public function enqueue_font_awesome(): void {
        $kit_id = get_option('options_font_awesome_kit');
        if ($kit_id) {
            wp_enqueue_script('font-awesome', "https://kit.fontawesome.com/$kit_id.js", [], '7', true);
        }
    }

    /**
     * Add type=module to script tags
     *
     * @param  $tag
     * @param  $handle
     * @param  $src
     *
     * @return mixed|string
     */
    public function script_type_module($tag, $handle, $src): mixed {
        if (str_starts_with($handle, 'comet-')) {
            $tag = '<script type="module" src="' . esc_url($src) . '" id="' . $handle . '" ></script>';
        }

        return $tag;
    }

    /**
     * Add data-base-path attribute to Comet Components script tag
     * so Vue SFC loader can find its templates
     *
     * @param  $tag
     * @param  $handle
     * @param  $src
     *
     * @return mixed|string
     */
    public function script_base_path($tag, $handle, $src): mixed {
        if ($handle === 'comet-components-js') {
            $libraryDir = COMET_COMPOSER_VENDOR_URL . '/doubleedesign/comet-components-core';
            $libraryDirShort = str_replace(get_site_url(), '', $libraryDir);
            $tag = '<script type="module" src="' . esc_url($src) . '" id="' . $handle . '" data-base-path="' . $libraryDirShort . '" ></script>';
        }

        return $tag;
    }

}
