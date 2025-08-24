<?php
$dynamic_preview_plugin_active = is_plugin_active('acf-dynamic-preview/index.php');
// These variables come from the function that processes the AJAX request for a dynamic preview, when using the plugin.
/** @var ?bool $is_backend_preview */
/** @var ?array $fields */
$is_backend_dynamic_preview = $dynamic_preview_plugin_active && isset($is_backend_preview) && $is_backend_preview;

if($is_backend_dynamic_preview && $fields) {
	echo 'hello world';
}
else {
	$heading = get_sub_field('heading');
	echo $heading;
}
