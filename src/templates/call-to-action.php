<?php
/** @var $fields array */
use Doubleedesign\Comet\WordPress\Classic\{PreprocessedHTML, TemplateHandler};
use Doubleedesign\Comet\Core\{Config, CallToAction, Heading, ButtonGroup, Button};

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);
$buttons = $attributes['component']['buttons'] ?? null;
if (is_array($buttons) && count($buttons) > 0) {
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
}

$defaults = Config::getInstance()->get_component_defaults('call-to-action');

$innerComponents = [
    new Heading($defaults['headingAttrs' ?? []], $fields['heading'] ?? ''),
    ...(!empty($attributes['component']['description']) ? [new PreprocessedHTML([], $attributes['component']['description'])] : []),
    ...(isset($buttonGroup) ? [$buttonGroup] : []),
];

unset($attributes['component']['heading']);
unset($attributes['component']['button']);
unset($attributes['component']['description']);

// This component is declared not nestable in Fields.php, if that changes in the future this will need to account for that
$attributes['component']['isNested'] = false;
$attributes['component']['hAlign'] = $defaults['hAlign'] ?? null;

$component = new CallToAction(array_merge($attributes['container'], $attributes['component']), $innerComponents);
$component->render();
