<?php
namespace Doubleedesign\Comet\WordPress\Classic;

class PluginEntryPoint {
    private static string $version = '0.0.3';

    public function __construct() {
        add_action('admin_init', [$this, 'handle_no_acf'], 1);

        new Fields();
        new TemplateHandler();
        new ComponentAssets();
	    new GlobalSettings();

        if (is_admin()) {
            new AdminUI();
            new TinyMCEConfig();
        }
    }

    public function handle_no_acf(): void {
        if (!class_exists('ACF')) {
            deactivate_plugins('comet-plugin-acf/comet.php');
            add_action('admin_notices', function() {
                echo '<div class="error"><p><strong>Comet Components for ACF Flexible Content</strong> requires <a href="https://www.advancedcustomfields.com/" target="_blank">Advanced Custom Fields</a> to be installed and activated.</p></div>';
            });
        }
    }

    public static function get_version(): string {
        return self::$version;
    }

    public static function activate() {
        // Activation logic here
    }

    public static function deactivate() {
        // Deactivation logic here
    }

    public static function uninstall() {
        // Uninstallation logic here
    }
}
