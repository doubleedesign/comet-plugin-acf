<?php
/** @var $fields array */
use Doubleedesign\Comet\WordPress\Classic\{PreprocessedHTML, TemplateHandler};
use Doubleedesign\Comet\Core\{Container};

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);
$content = $attributes['component']['content'] ?? '';
$content = new PreprocessedHTML([], wpautop($content));

if ($fields['isNested']) {
    $content->render();
}
else {
    $component = new Container($attributes['container'], [$content]);
    $component->render();
}
