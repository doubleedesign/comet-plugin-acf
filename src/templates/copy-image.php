<?php
/** @var array $fields */
use Doubleedesign\Comet\WordPress\Classic\{TemplateHandler, PreprocessedHTML};
use Doubleedesign\Comet\Core\{
    ContentImageAdvanced,
    Columns,
    Column,
    Copy,
    Heading,
    ButtonGroup,
    Button,
    Utils};

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);

extract($attributes['component']);
list('colorTheme' => $colorTheme, 'backgroundColor' => $backgroundColor) = $attributes['component'];
list('heading' => $heading, 'bodyText' => $bodyText, 'buttons' => $buttons) = $attributes['component']['content'];

$content = [];
if (!empty($heading)) {
    array_push($content, new Heading([], $heading));
}
if (isset($bodyText) && is_string($bodyText)) {
    array_push($content, new PreprocessedHTML([], Utils::sanitise_content($bodyText)));
}
if (is_array($buttons) && !empty($buttons)) {
    array_push($content, new ButtonGroup(
        [],
        array_filter(array_map(
            function($button) use ($attributes, $colorTheme) {
                if (!is_array($button['button'])) {
                    return null;
                }

                // Swap 'url' to 'href'
                if (isset($button['button']['url'])) {
                    $button['button']['href'] = $button['button']['url'];
                    unset($button['button']['url']);
                }

                return new Button(
                    array_merge(
                        ['colorTheme' => $colorTheme ?? 'primary'],
                        $button['button'],
                        ['isOutline' => $button['style'] === 'isOutline']
                    ),
                    $button['button']['title']
                );
            },
            $buttons
        )
        )));
}

$content_col = new Column(
    [],
    [
        new Copy([
            'colorTheme' => $attributes['component']['colorTheme'],
            'isNested'   => true
        ], $content)
    ]
);

$image = TemplateHandler::transform_fields_to_comet_attributes($attributes['component']['image']);
$image = new ContentImageAdvanced($image['component']);
$image_col = new Column([], [$image]);

$columnsAttrs = [
    'shortName'         => 'copy-image',
    'backgroundColor'   => $backgroundColor,
    'vAlign'            => 'center',
];

if ($attributes['component']['order'] === 'copy-image') {
    $component = new Columns($columnsAttrs, [$content_col, $image_col]);
}
else {
    $component = new Columns($columnsAttrs, [$image_col, $content_col]);
}

$component->render();
