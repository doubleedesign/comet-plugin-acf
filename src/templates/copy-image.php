<?php
/** @var array $fields */
use Doubleedesign\Comet\WordPress\Classic\{TemplateHandler, PreprocessedHTML};
use Doubleedesign\Comet\Core\{Container,
    ContentImageAdvanced,
    Columns,
    Column,
    CopyBlock,
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
        array_map(
            function($button) use ($attributes, $colorTheme) {
                if (isset($button['button']['url'])) {
                    $button['button']['href'] = $button['button']['url'];
                    unset($button['button']['url']);
                }

                return new Button(
                    array_merge(
                        ['colorTheme' => $colorTheme],
                        $button['button'],
                        ['isOutline' => $button['style'] === 'isOutline']
                    ),
                    $button['button']['title']
                );
            },
            $buttons
        )
    ));
}

$content_col = new Column(
    [],
    [
        new CopyBlock([
            'colorTheme' => $attributes['component']['colorTheme'],
            'isNested'   => true
        ], $content)
    ]
);

$image = TemplateHandler::transform_fields_to_comet_attributes($attributes['component']['image']);
$image = new ContentImageAdvanced($image['component']);
$image_col = new Column([], [$image]);

$columnsAttrs = [
    'backgroundColor' => $backgroundColor,
    'vAlign'          => 'center',
];

if ($attributes['component']['order'] === 'copy-image') {
    $columns = new Columns($columnsAttrs, [$content_col, $image_col]);
}
else {
    $columns = new Columns($columnsAttrs, [$image_col, $content_col]);
}

$component = new Container(
    array_merge(['withWrapper' => true], $attributes['container']),
    [$columns]
);
$component->render();
