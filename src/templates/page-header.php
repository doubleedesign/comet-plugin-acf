<?php
/** @var $fields array */
use Doubleedesign\Comet\WordPress\Classic\TemplateHandler;
use Doubleedesign\Comet\Core\{PageHeader};

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);
$heading = !empty($attributes['component']['heading'])
    ? $attributes['component']['heading']
    : (!empty(get_the_title()) ? get_the_title() : 'New Page');
if (class_exists('Doubleedesign\Breadcrumbs\Breadcrumbs') && $attributes['component']['showBreadcrumbs']) {
    $breadcrumbs = Doubleedesign\Breadcrumbs\Breadcrumbs::$instance->get_raw_breadcrumbs();
}

unset($attributes['component']['heading']);
unset($attributes['component']['showBreadcrumbs']);

$component = new PageHeader(array_merge($attributes['container'], $attributes['component']), $heading, $breadcrumbs ?? []);
$component->render();
