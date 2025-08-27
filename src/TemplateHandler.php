<?php
namespace Doubleedesign\Comet\WordPress\Classic;
use Doubleedesign\Comet\Core\Utils;
use Exception;

class TemplateHandler {
    protected static array $template_paths = [];

    public function __construct() {
        add_filter('acf_dynamic_preview_template_paths', [$this, 'register_template_paths_for_dynamic_preview'], 10, 1);
        add_filter('the_content', [$this, 'render_flexible_content'], 20);

        self::$template_paths = [
            // First look in the child theme for overrides, then the parent theme,
            // and if no overrides, use the plugin templates
            get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR,
            get_template_directory() . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR,
            plugin_dir_path(__DIR__) . 'src' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR,
        ];
    }

    public function register_template_paths_for_dynamic_preview($paths): array {

        return [
            plugin_dir_path(__DIR__) . 'src' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR,
            ...$paths
        ];
    }

    /**
     * Get the template file to use to render a module.
     *
     * @param  $module_name
     *
     * @return string|null
     * @throws Exception
     */
    public static function get_template_path($module_name): ?string {
        $name = Utils::kebab_case($module_name);

        // Loop through each directory in the template paths and return the first match
        $path = null;
        foreach (self::$template_paths as $base_path) {
            // In a subfolder per module, e.g. modules/hero/hero.php
            $file = $base_path . $name . DIRECTORY_SEPARATOR . $name . '.php';
            if (file_exists($file)) {
                $path = $file;
                break;
            }
            // In the main modules folder, e.g. modules/hero.php
            // Or directly in the custom directory configured by a theme or plugin using the filter
            else if (file_exists($base_path . $name . '.php')) {
                $path = $base_path . $name . '.php';
                break;
            }
        }

        if ($path && !is_dir($path)) {
            return $path;
        }
        else {
            throw new Exception(sprintf(
                "No module template file found for module '%s'. Searched in paths:\n %s",
                $name,
                implode("\n", self::$template_paths)
            ));
        }
    }

    public function render_flexible_content($content): string {
        $post_types = apply_filters('comet_canvas_acf_flexible_modules_post_types', ['page']);
        if (is_singular($post_types)) {
            ob_start();
            if (have_rows('content_modules')) {
                while (have_rows('content_modules')) {
                    the_row();
                    $layout = get_row_layout();
                    try {
                        $template_path = self::get_template_path($layout);
                        if ($template_path) {
                            include $template_path;
                        }
                    }
                    catch (Exception $e) {
                        echo '<!-- ' . esc_html($e->getMessage()) . ' -->';
                    }
                }
            }

            return ob_get_clean();
        }

        return $content;
    }
}
