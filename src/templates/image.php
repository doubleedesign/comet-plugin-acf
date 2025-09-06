<?php
/** @var $fields array */
use Doubleedesign\Comet\WordPress\Classic\TemplateHandler;
use Doubleedesign\Comet\Core\ContentImageAdvanced;

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);
$component = new ContentImageAdvanced($attributes['component']);
$component->render();
