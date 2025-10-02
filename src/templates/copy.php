<?php
/** @var $fields array */
use Doubleedesign\Comet\WordPress\Classic\{PreprocessedHTML, TemplateHandler};
use Doubleedesign\Comet\Core\{Button, ButtonGroup, Copy, Heading, Utils};

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);
$heading = $attributes['component']['heading'] ? new Heading([], $attributes['component']['heading']) : null;
$content = $attributes['component']['copy'] ?? '';
$content = new PreprocessedHTML([], Utils::sanitise_content($content));

$buttons = $attributes['component']['buttons'] ?? null;
if (is_array($buttons['buttons']) && count($buttons['buttons']) > 0) {
    $buttonGroup = new ButtonGroup(
        [
            'hAlign'      => $attributes['component']['buttons']['horizontalAlignment'] ?? null,
            'orientation' => $attributes['component']['buttons']['orientation'] ?? null,
        ],
        array_map(
            function($button) use ($attributes) {

                return new Button(
                    array_merge(
                        [
                            'colorTheme' => $attributes['component']['colorTheme'] ?? 'primary',
                            'isOutline'  => $button['style'] === 'isOutline',
                            'href'       => $button['button']['url'] ?? '#',
                            'target'     => $button['button']['target'] ?? null,
                        ],
                    ),
                    $button['button']['title'] ?? 'Read more'
                );
            },
            $buttons['buttons']
        )
    );
}

$component = new Copy(
    array_merge(
        $attributes['container'],
        Utils::array_pick($attributes['component'], ['colorTheme', 'isNested'])
    ),
    [$heading, $content, ...(isset($buttonGroup) ? [$buttonGroup] : [])]
);
$component->render();
