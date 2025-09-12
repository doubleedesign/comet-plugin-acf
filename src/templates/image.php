<?php
/** @var $fields array */
use Doubleedesign\Comet\WordPress\Classic\TemplateHandler;
use Doubleedesign\Comet\Core\{Container,ContentImageAdvanced};

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);
if ($fields['isNested'] === false && isset($attributes['container'])) {
    $component = new Container($attributes['container'], [new ContentImageAdvanced($attributes['component']['image'])]);
    $component->render();
}
else {
    $component = new ContentImageAdvanced($attributes['component']['image']);
    $component->render();
}
