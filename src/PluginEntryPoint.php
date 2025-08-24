<?php
namespace Doubleedesign\Comet\WordPress\Classic;

class PluginEntrypoint {
    private static string $version = '0.0.3';

    public function __construct() {
        new Fields();
        new TemplateHandler();
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
