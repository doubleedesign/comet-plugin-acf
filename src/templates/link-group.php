<?php
/** @var $fields array */
use Doubleedesign\Comet\Core\{Container, Heading, LinkGroup, Link};
use Doubleedesign\Comet\WordPress\Classic\TemplateHandler;

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);

$heading = $attributes['component']['heading'] ? new Heading([], $attributes['component']['heading']) : null;
$links = $attributes['component']['links'] ?? null;
unset($links['width']); // should be in $attributes['container'] if applicable
if ($links === null) {
    return;
}

$linkGroup = new LinkGroup(
    [],
    array_map(
        function($link) use ($attributes) {
            return new Link(
                array_merge(
                    [
                        'colorTheme' => $attributes['component']['colorTheme'] ?? 'primary',
                        'context'    => 'link-group',
                        'href'       => $link['link']['url'] ?? '#',

                    ],
                    $link['link'],
                ),
                $link['link']['title']
            );
        },
        $links
    )
);

if ($fields['isNested'] || !isset($attributes['container'])) {
    $heading->render();
    $linkGroup->render();
}
else {
    $component = new Container($attributes['container'], [$heading, $linkGroup]);
    $component->render();
}
