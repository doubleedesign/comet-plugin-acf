<?php
/** @var $fields array */
use Doubleedesign\Comet\WordPress\Classic\{PreprocessedHTML, TemplateHandler};
use Doubleedesign\Comet\Core\{Container, CallToAction, Heading, ButtonGroup, Button};

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);

$button = $attributes['component']['button'] ?? null;
$innerComponents = [
    new Heading([], $attributes['heading'] ?? get_the_title() ?? 'New page'),
    ...(!empty($attributes['component']['description']) ? [new PreprocessedHTML([], $attributes['component']['description'])] : []),
    ...(isset($attributes['component']['button']) && is_array($attributes['component']['button']) ? [
        new ButtonGroup([],
            array(
                new Button(
                    array_merge(
                        ['colorTheme' => $attributes['component']['colorTheme'] ?? 'primary'],
                        $attributes['component']['button']
                    ),
                    $attributes['component']['button']['title']
                )
            )
        )] : [])
];

unset($attributes['component']['heading']);
unset($attributes['component']['button']);
unset($attributes['component']['description']);

$component = new Container($attributes['container'], [new CallToAction($attributes['component'], $innerComponents)]);
$component->render();
