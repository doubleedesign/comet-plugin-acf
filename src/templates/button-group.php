<?php
/** @var $fields array */
use Doubleedesign\Comet\WordPress\Classic\{TemplateHandler};
use Doubleedesign\Comet\Core\{ButtonGroup, Button, Container};

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);
$buttons = $attributes['component']['buttons'] ?? null;
unset($buttons['width']); // should be in $attributes['container']
if ($buttons === null) {
    return;
}

$buttonGroup = new ButtonGroup(
    [],
    array_map(
        function($button) use ($attributes) {
            return new Button(
                array_merge(
                    ['colorTheme' => $attributes['component']['colorTheme'] ?? 'primary'],
                    $button['button'],
                    ['isOutline' => $button['style'] === 'isOutline']
                ),
                $button['button']['title']
            );
        },
        $buttons
    )
);

// Nested button groups should not have the width field - only top-level ones should potentially have a container
if ($fields['width'] && $fields['isNested'] != false) {
    $container = new Container($attributes['container'], [$buttonGroup]);
    $container->render();
}
else {
    $buttonGroup->render();
}
