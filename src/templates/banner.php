<?php
/** @var $fields array */
use Doubleedesign\Comet\Core\{BannerV2, Heading, CopyBlock, ButtonGroup, Button};
use Doubleedesign\Comet\WordPress\Classic\{TemplateHandler, PreprocessedHTML};

$content = [];
$transformedOptions = TemplateHandler::transform_fields_to_comet_attributes($fields['options'] ?? [])['component'];
$colorTheme = $transformedOptions['colorTheme'] ?? 'primary';

if (isset($fields['content']['heading'])) {
    array_push($content, new Heading(['level' => 1], $fields['content']['heading']));
}
if (isset($fields['content']['text'])) {
    array_push($content, new CopyBlock(
        ['isNested' => true],
        [new PreprocessedHTML([], $fields['content']['text'])]
    ));
}
if (isset($fields['content']['buttons']) && is_array($fields['content']['buttons'])) {
    $buttons = $fields['content']['buttons'];
    $buttonGroup = new ButtonGroup(
        [
            // TODO: Implement button group layout options
            'hAlign'      => 'end',
            'orientation' => null,
        ],
        array_map(
            function($button) use ($fields, $colorTheme) {
                return new Button(
                    array_merge(
                        [
                            'colorTheme' => $colorTheme,
                            'isOutline'  => $button['style'] === 'isOutline',
                            'href'       => $button['button']['url'] ?? '#',
                            'target'     => $button['button']['target'] ?? null,
                        ],
                    ),
                    $button['button']['title'] ?? 'Read more'
                );
            },
            $buttons
        )
    );

    array_push($content, $buttonGroup);
}

$component = new BannerV2(
    [
        'width'            => $fields['options']['width'],
        ...$transformedOptions,
        'image'           => [
            'src' => $fields['image']['src'] ?? $fields['image']['url'] ?? '',
            'alt' => $fields['image']['alt'] ?? '',
        ],
        'aspectRatio'      => $fields['options']['aspect_ratio'],
        'focalPoint'       => $fields['options']['focal_point'],
        'colorTheme'       => $colorTheme,
    ],
    $content
);
$component->render();
