<?php
/** @var $fields array */
use Doubleedesign\Comet\Core\{BannerV2, Heading, Copy, ButtonGroup, Button};
use Doubleedesign\Comet\WordPress\Classic\{TemplateHandler, PreprocessedHTML};

$content = [];
$transformedOptions = TemplateHandler::transform_fields_to_comet_attributes($fields['options'] ?? [])['component'];
$colorTheme = $transformedOptions['colorTheme'] ?? 'primary';

if (isset($fields['content']['heading'])) {
    array_push($content, new Heading(['level' => 1], $fields['content']['heading']));
}
if (isset($fields['content']['text'])) {
    array_push($content, new Copy(
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

// Make sure we get the full size image here
$image_id = $fields['image']['image_id'] ?? null;
if ($image_id) {
    $image_data = wp_get_attachment_image_src($image_id, 'full');
    if ($image_data) {
        $fields['image']['src'] = $image_data[0];
    }
}

$component = new BannerV2(
    [
        'width'            => $fields['options']['width'],
        ...$transformedOptions,
        'image'           => [
            'src' => $fields['image']['src'] ?? $fields['image']['url'] ?? '',
            'alt' => $fields['image']['alt'] ?? '',
        ],
        'aspectRatio'      => $fields['image']['aspect_ratio'] ?? null,
        'focalPoint'       => $fields['image']['focal_point'] ?? null,
        'offset'           => $fields['image']['image_offset'] ?? null,
        'colorTheme'       => $colorTheme,
    ],
    $content
);
$component->render();
