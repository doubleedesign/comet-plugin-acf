<?php
/** @var array $fields */
use Doubleedesign\Comet\WordPress\Classic\{TemplateHandler, PreprocessedHTML};
use Doubleedesign\Comet\Core\{Container, ContentImageAdvanced, Columns, Column, Heading, ButtonGroup, Button, Utils};

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);

extract($attributes['component']);
list('colorTheme' => $colorTheme, 'backgroundColor' => $backgroundColor) = $attributes['component'];
list('heading' => $heading, 'Body text' => $bodyText, 'buttons' => $buttons) = $attributes['component']['content'];

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

$content_col = new Column(['colorTheme' => $colorTheme], $content);

$image = TemplateHandler::transform_fields_to_comet_attributes($attributes['component']['image']);
$image = new ContentImageAdvanced($image);
$image_col = new Column([], [$image]);

if ($attributes['component']['order'] === 'copy-image') {
    $columns = new Columns(['backgroundColor' => $backgroundColor], [$content_col, $image_col]);
}
else {
    $columns = new Columns(['backgroundColor' => $backgroundColor], [$image_col, $content_col]);
}

$component = new Container(
    array_merge(['withWrapper' => true], $attributes['container']),
    [$columns]
);
$component->render();
