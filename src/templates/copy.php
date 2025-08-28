<?php
/** @var $fields array */
use Doubleedesign\Comet\WordPress\Classic\{PreprocessedHTML, TemplateHandler};
use Doubleedesign\Comet\Core\{Container};

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);
$content = $attributes['component']['content'] ?? '';

$component = new Container($attributes['container'], [new PreprocessedHTML([], wpautop($content))]);
$component->render();
