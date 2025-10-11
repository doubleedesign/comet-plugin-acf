<?php
/** @var $fields array */
use Doubleedesign\Comet\WordPress\Classic\TemplateHandler;
use Doubleedesign\Comet\Core\{Container, ContentImageBasic, Gallery, Utils, AspectRatio};

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);

$images = array_map(function($image) use ($fields) {
    $attrs = Utils::array_pick($image, ['alt', 'caption']);
    $attrs['src'] = $image['sizes']['large']; // The image to display as the thumbnail in the gallery
    $attrs['aspectRatio'] = $fields['aspect_ratio'] ?? AspectRatio::SQUARE->value;

    if (isset($fields['lightbox']) && $fields['lightbox']) {
        $attrs['href'] = $image['url']; // The full-size image to open in the lightbox
    }

    return new ContentImageBasic($attrs);
}, $fields['images'] ?? []);

$gallery = new Gallery(['columns' => 4, 'imageCrop' => true], $images);

$component = new Container($attributes['container'], [$gallery]);
$component->render();
