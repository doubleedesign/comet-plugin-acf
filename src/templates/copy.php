<?php
/** @var $fields array */
use Doubleedesign\Comet\WordPress\Classic\{PreprocessedHTML, TemplateHandler};
use Doubleedesign\Comet\Core\{Container, Heading, Utils};

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);
$heading = $attributes['component']['heading'] ? new Heading([], $attributes['component']['heading']) : null;
$content = $attributes['component']['copy'] ?? '';
$content = new PreprocessedHTML([], Utils::sanitise_content($content));

if ($fields['isNested']) {
    if ($heading) {
        $heading->render();
    }
    $content->render();
}
else {
    if ($heading) {
        $component = new Container($attributes['container'], [$heading, $content]);
        $component->render();
    }
    else {
        $component = new Container($attributes['container'], [$content]);
        $component->render();
    }
}
