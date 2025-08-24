<?php
/**
 * Plugin name: Comet Components for ACF Flexible Content
 * Description: Double-E Design's foundational components for building page layouts using ACF Flexible Content modules.
 *
 * Author:              Double-E Design
 * Author URI:          https://www.doubleedesign.com.au
 * Version:             0.0.3
 * Requires PHP:        8.3
 * Requires plugins:    advanced-custom-fields-pro
 * Text Domain:         comet
 *
 * @package Comet
 */
if (!defined('COMET_COMPOSER_VENDOR_URL')) {
    define('COMET_COMPOSER_VENDOR_URL', get_site_url() . '/wp-content/plugins/comet-plugin-acf/vendor');
}

const COMET_VERSION = '0.0.3';
require_once __DIR__ . '/vendor/autoload.php';

use Doubleedesign\Comet\WordPress\Classic\PluginEntrypoint;

new PluginEntrypoint();

function activate_comet_plugin_acf(): void {
	PluginEntrypoint::activate();
}
function deactivate_comet_plugin_acf(): void {
	PluginEntrypoint::deactivate();
}
function uninstall_comet_plugin_acf(): void {
	PluginEntrypoint::uninstall();
}
register_activation_hook(__FILE__, 'activate_comet_plugin_acf');
register_deactivation_hook(__FILE__, 'deactivate_comet_plugin_acf');
register_uninstall_hook(__FILE__, 'uninstall_comet_plugin_acf');

