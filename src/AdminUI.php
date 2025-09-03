<?php

namespace Doubleedesign\Comet\WordPress\Classic;

class AdminUI {

    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets'], 100);
        add_filter('tiny_mce_before_init', [$this, 'add_common_css_to_tinymce'], 10, 1);

	    add_filter('acf/prepare_field', [$this, 'prepare_fields_that_should_have_instructions_as_tooltips'], 10, 1);
	    add_filter('acf/get_field_label', [$this, 'render_some_acf_field_instructions_as_tooltips'], 10, 3);
    }

    public function enqueue_assets(): void {
        wp_enqueue_style(
            'comet-acf-admin',
            plugins_url('src/assets/admin.css', __DIR__),
            [],
            PluginEntryPoint::get_version()
        );

        wp_enqueue_script(
            'comet-acf-admin',
            plugins_url('src/assets/admin.js', __DIR__),
            ['jquery', 'acf'],
            PluginEntryPoint::get_version(),
            true
        );
    }

    /**
     * Load Comet's global and common CSS into ACF WYSIWYG fields
     * Ref: https://pagegwood.com/web-development/custom-editor-stylesheets-advanced-custom-fields-wysiwyg/
     *
     * @param  $mce_init
     *
     * @wp-hook
     *
     * @return array
     */
    public function add_common_css_to_tinymce($mce_init): array {
        $css = plugins_url('src/assets/editor.css', __DIR__);

        if (isset($mce_init['content_css']) && $css) {
            $content_css_new = $mce_init['content_css'] . ',' . $css;
            $mce_init['content_css'] = $content_css_new;
        }

        return $mce_init;
    }

	/**
	 * ACF does not have a filter to allow us to remove the instructions from the DOM,
	 * and I hate hacking such things with display:none or removing from the DOM on the client side with JS.
	 * This workaround moves the instructions into a custom field
	 * (which we then use in our custom label rendering function to render an icon + tooltip instead of the usual instruction markup).
	 *
	 * @param  $field
	 *
	 * @return array
	 */
	public function prepare_fields_that_should_have_instructions_as_tooltips($field): array {
		if ($this->should_render_instructions_as_tooltips($field) && $field['instructions']) {
			$field['tooltip'] = $field['instructions'];
			$field['instructions'] = '';
		}

		return $field;
	}

	public function render_some_acf_field_instructions_as_tooltips($label, $field, $context): string {
		if ($this->should_render_instructions_as_tooltips($field) && isset($field['tooltip'])) {
			// Note: Something is stripping tabindex from non-interactive elements like <span> in the admin, so we have to use a <button>
			// type="button" to make it focusable and accessible, without it submitting the form.
			return <<<HTML
				{$label}
				<button type="button" class="acf-js-tooltip" title="{$field['tooltip']}">
					<span class="dashicons dashicons-editor-help"></span>
					<span class="screen-reader-text" role="tooltip">{$field['tooltip']}</span>
				</button>
				HTML;
		}

		return $label;
	}

	protected function should_render_instructions_as_tooltips($field): bool {
		return in_array($field['label'], ['Focal point', 'Image offset']);
	}


}
