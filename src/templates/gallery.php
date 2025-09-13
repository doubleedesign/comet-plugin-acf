<?php
/** @var $fields array */
use Doubleedesign\Comet\Core\{Container, ContentImageBasic, Gallery, Utils, AspectRatio};

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

$component = new Container(['size'=> $fields['width'] ?? 'contained', 'context' => 'gallery'], [$gallery]);
$component->render();
