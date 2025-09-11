<?php
/**
 * Plugin name: Comet Components for ACF Flexible Content
 * Description: Double-E Design's foundational components for building page layouts using ACF Flexible Content modules.
 *
 * Author:              Double-E Design
 * Author URI:          https://www.doubleedesign.com.au
 * Version:             0.1.0
 * Requires PHP:        8.3
 * Requires plugins:    advanced-custom-fields-pro
 * Recommends plugins:	doublee-breadcrumbs, acf-advanced-image-field
 * Text Domain:         comet
 *
 * @package Comet
 */
if (!defined('COMET_COMPOSER_VENDOR_URL')) {
    define('COMET_COMPOSER_VENDOR_URL', get_site_url() . '/wp-content/plugins/comet-plugin-acf/vendor');
}

require_once __DIR__ . '/vendor/autoload.php';

add_action('plugins_loaded', function() {
    if (!class_exists('Doubleedesign\Comet\Core\Config')) {
        wp_die('<p>Comet Components Core Config class not found in Comet Components ACF plugin. Perhaps you need to install or update Composer dependencies.</p><p>If you are working locally with symlinked packages, you might want <code>$env:COMPOSER = "composer.local.json"; composer update</code>.</p>');
    }
    // Ensure global config is initialized
    Doubleedesign\Comet\Core\Config::getInstance();
});

use Doubleedesign\Comet\WordPress\Classic\{PluginEntrypoint, TemplateHandler};

new PluginEntryPoint();

function activate_comet_plugin_acf(): void {
    PluginEntryPoint::activate();
}
function deactivate_comet_plugin_acf(): void {
    PluginEntryPoint::deactivate();
}
function uninstall_comet_plugin_acf(): void {
    PluginEntryPoint::uninstall();
}
register_activation_hook(__FILE__, 'activate_comet_plugin_acf');
register_deactivation_hook(__FILE__, 'deactivate_comet_plugin_acf');
register_uninstall_hook(__FILE__, 'uninstall_comet_plugin_acf');

/**
 * Add a global alias for the content rendering method so themes can use it without namespacing/autoloading/etc issues.
 *
 * @param  $post_id
 *
 * @return string
 */
function comet_acf_render_flexible_content($post_id): string {
    return TemplateHandler::render_flexible_content($post_id);
}

// Workaround for a missing ACF function in ClassicPress
if (!function_exists('has_blocks')) {
    function has_blocks() {
        return false;
    }
}
