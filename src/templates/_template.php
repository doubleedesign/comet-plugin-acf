<?php
/**
 * Template for a new flexible content module.
 *
 * Delete everything above /** @var $fields array/ to use.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly. Delete this when your template part is ready to use.
}

/** @var $fields array */
use Doubleedesign\Comet\Core\Container;
use Doubleedesign\Comet\WordPress\Classic\TemplateHandler;

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);

if ($fields['isNested'] || !isset($attributes['container'])) {
    $component = '';
    $component->render();
}
else {
    $component = new Container($attributes['container'], []);
    $component->render();
}
