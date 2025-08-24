<?php
namespace Doubleedesign\Comet\WordPress\Classic;

class TemplateHandler {

    public function __construct() {
        add_filter('acf_dynamic_preview_template_paths', [$this, 'register_template_paths'], 10, 1);
    }

    public function register_template_paths($paths): array {

        return [
            plugin_dir_path(__DIR__) . 'src' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR,
            ...$paths
        ];
    }
}
