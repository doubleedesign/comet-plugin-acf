<?php
namespace Doubleedesign\Comet\WordPress\Classic;

class PluginEntryPoint {
    private static string $version = '0.0.3';

    public function __construct() {
        new Fields();
        new TemplateHandler();
        new ComponentAssets();

        if (is_admin()) {
            new AdminUI();
            new TinyMCEConfig();
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
