<?php
/** @var $fields array */
use Doubleedesign\Comet\WordPress\Classic\{PreprocessedHTML, TemplateHandler};
use Doubleedesign\Comet\Core\{Heading, Accordion, AccordionPanel};

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);
$beforeComponents = [];
if (isset($attributes['component']['heading']) && is_string($attributes['component']['heading'])) {
    array_push($beforeComponents, new Heading([], $attributes['component']['heading']));
}
if ($attributes['component']['introCopy'] && is_string($attributes['component']['introCopy'])) {
    array_push($beforeComponents, new PreprocessedHTML([], $attributes['component']['introCopy']));
}

$panels = array_map(function($panel) {
    $content = TemplateHandler::get_nested_flexible_content($panel['panel_content__modules']);
    $content = new PreprocessedHTML([], $content);

    return new AccordionPanel(['title' => $panel['heading'] ?? ''], [$content]);
}, $attributes['component']['panels'] ?? []);

$attributes['component'] = array_filter($attributes['component'], function($key) {
    return !in_array($key, ['heading', 'introCopy']);
}, ARRAY_FILTER_USE_KEY);

// TODO: Support full-width background colours
$component = new Accordion(
    array_merge($attributes['container'], $attributes['component']),
    $panels,
    $beforeComponents
);
$component->render();
