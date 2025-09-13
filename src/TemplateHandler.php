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
            get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR,
            get_template_directory() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR,
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
     * Return the top-level flexible content as HTML for a given post ID.
     *
     * @param  $post_id
     *
     * @return string
     */
    public static function render_flexible_content($post_id): string {
        $post_types = apply_filters('comet_acf_flexible_modules_post_types', ['page']);

        if (in_array(get_post_type($post_id), $post_types)) {
            ob_start();

            // Get the raw field data instead of using have_rows() because it's more reliable with multiple instances of modules and whatnot
            $content_modules = get_field('content_modules', $post_id);

            if ($content_modules && is_array($content_modules)) {
                foreach ($content_modules as $index => $module) {
                    $layout = $module['acf_fc_layout'] ?? '';
                    $fields = $module;
                    // There are some cases where top-level modules are treated as nested before they get here
                    // because they're intended to be put into container in the template (or some other similar reason).
                    // We can specify that contextually using this filter.
                    $fields['isNested'] = apply_filters('comet_acf_flexible_content_is_nested', false, $layout, $post_id);

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

        return get_the_content();
    }

    /**
     * Return nested flexible content a HTML from a given set of module data.
     *
     * @param  array{acf_fc_layout:string, array}  $modules
     *
     * @return string
     */
    public static function get_nested_flexible_content(array $modules): string {
        ob_start();
        if (count($modules) > 0) {
            foreach ($modules as $module) {
                if (is_array($module) && array_key_exists('acf_fc_layout', $module)) {
                    $layout = $module['acf_fc_layout'];
                    $fields = array_slice($module, 1);

                    // If this is a copy module content string, just return that
                    if (isset($fields['copy']) && is_string($fields['copy'])) {
                        echo Utils::sanitise_content($fields['copy']);
                        continue;
                    }

                    // If this is a nested module with its own fields, we need to extract those
                    // It will be an associative array of ['module_name' => [fields]], we want the fields
                    if (count($fields) === 1) {
                        $fields = array_values($fields)[0];
                    }
                    // If there's more than one, that's something I didn't expect at the time of writing
                    // and thus have not handled

                    // Indicate to the template parts and components that this is nested content,
                    // where that has been accounted for
                    $fields['isNested'] = true;
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
        }

        return ob_get_clean();
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
        // Handle sub-fields that aren't flexible layouts
        if (!isset($fields['acf_fc_layout'])) {
            if (isset($fields['aspect_ratio'])) {
                $kebab_case_component = 'image';
            }
            else {
                $kebab_case_component = 'some-component';
            }
        }
        // Default - handle as layout
        else {
            $kebab_case_component = Utils::kebab_case($fields['acf_fc_layout']);
        }

        // Simplify the field names to get as many of them as possible to automatically match Comet Components attribute names
        $result = array_combine(
            // Transform the keys to camelCase, removing the "field_" prefix and the component name
            array_map(function($key) use ($kebab_case_component) {
                $first = str_replace('field_', '', $key);
                if ($kebab_case_component !== $key) {
                    $second = str_replace($kebab_case_component, '', $first);
                }
                $third = trim($second ?? $first, '_');
                // Transform Australian/British spelling because Comet uses American spelling for "color" because that's what CSS uses
                $fourth = str_replace('colour', 'color', $third);
                $fifth = str_replace('horizontal_alignment', 'hAlign', $fourth);
                $sixth = str_replace('vertical_alignment', 'vAlign', $fifth);

                return Utils::camel_case($sixth);
            }, array_keys($fields)),
            // Leave the values as they are
            $fields
        );

        // Recurse into nested field arrays and camelCase their keys
        $result = array_map(function($value) {
            if (is_array($value)) {
                return Utils::camel_case_array_keys($value);
            }

            return $value;
        }, $result);

        // The width field is generally expected to align with the Container component size field
        // Add exceptions here in future if necessary
        if (isset($result['width']) || isset($result['sectionWidth'])) {
            $container = array(
                'size'    => $result['width'] ?? $result['sectionWidth'] ?? null,
                'context' => $kebab_case_component,
            );
            // Filter out the container attributes for the inner ones
            $component = array_filter($result, function($key) use ($container) {
                return !in_array($key, array_keys($container));
            }, ARRAY_FILTER_USE_KEY);

            // Unset the now-not-needed attributes
            unset($component['width']);
            unset($component['acfFcLayout']);
            unset($component['editIntro']); // admin option not relevant to front-end rendering
            unset($component['editDescription']); // admin option not relevant to front-end rendering
            unset($component['moreOptions']); // admin option not relevant to front-end rendering

            return [
                'container' => $container,
                'component' => $component
            ];
        }

        return ['component' => $result];
    }
}
