<?php
namespace Doubleedesign\Comet\WordPress\Classic;
use Doubleedesign\Comet\Core\Utils;
use Exception;

class TemplateHandler {
    protected static array $template_paths = [];

    public function __construct() {
        add_filter('acf_dynamic_preview_template_paths', [$this, 'register_template_paths_for_dynamic_preview'], 10, 1);

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

    /**
     * Return the flexible content as HTML for a given post ID.
     *
     * @param  $post_id
     *
     * @return string
     */
    public static function render_flexible_content($post_id): string {
        $post_types = apply_filters('comet_acf_flexible_modules_post_types', ['page']);
        if (in_array(get_post_type($post_id), $post_types)) {
            ob_start();
            if (have_rows('content_modules', $post_id)) {
                while (have_rows('content_modules')) {
                    the_row();
                    $layout = get_row_layout();
                    $fields = get_row(true);
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

        // Fallback to default content if called for a post type that doesn't support flexible content
        return get_the_content();
    }

    /**
     * We expect $fields to be passed from the function that includes a template part
     * that calls this function, either within a has_rows() loop
     * or as a standalone include such as by the ACF Dynamic Preview plugin.
     *
     * @param  array  $fields
     *
     * @return array
     */
    public static function transform_fields_to_comet_attributes(array $fields = []): array {
        $kebab_case_component = Utils::kebab_case($fields['acf_fc_layout']);

        // Simplify the field names to get as many of them as possible to automatically match Comet Components attribute names
        $result = array_combine(
            array_map(function($key) use ($kebab_case_component) {
                $first = str_replace('field_', '', $key);
                $second = str_replace($kebab_case_component, '', $first);
                $third = trim($second, '_');
                // Transform Australian/British spelling because Comet uses American spelling for "color" because that's what CSS uses
                $fourth = str_replace('colour', 'color', $third);

                return Utils::camel_case($fourth);
            }, array_keys($fields)),
            $fields
        );

        unset($result['acfFcLayout']); // This is not needed as an attribute

        // The width field is generally expected to align with the Container component size field
        // Add exceptions here in future if necessary
        $container = array(
            'size' => $result['width'] ?? 'contained'
        );
        // Filter out the container attributes for the inner ones
        $component = array_filter($result, function($key) use ($container) {
            return !in_array($key, array_keys($container));
        }, ARRAY_FILTER_USE_KEY);

        return [
            'container' => $container,
            'component' => $component
        ];
    }
}
