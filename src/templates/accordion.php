<?php
/** @var $fields array */
use Doubleedesign\Comet\WordPress\Classic\{TemplateHandler};
use Doubleedesign\Comet\Core\{Container, Accordion, AccordionPanel};

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);

$component = new Container(
    $attributes['container'],
    [
        new Accordion(
            [$attributes['component']],
            array_map(function($panel) {
                \Symfony\Component\VarDumper\VarDumper::dump($panel);

                // return new AccordionPanel([]);
            }, $attributes['panels'] ?? [])
        )
    ]
);
