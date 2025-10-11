<?php
/** @var $fields array */
use Doubleedesign\Comet\Core\{Container, Group, Heading, Card};
use Doubleedesign\Comet\WordPress\Classic\TemplateHandler;

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);
$post_ids = $attributes['component']['posts'] ?? [];
if (!$post_ids) {
    return; // Do not render at all if no posts are selected
}

$heading = new Heading(
    ['context'   => 'featured-posts'],
    $attributes['component']['heading'] ?? 'Featured Posts'
);

$orientation = 'vertical';
// If there is 1-2 posts and this instance is not nested, render as a horizontal card
if (count($post_ids) <= 2 && $fields['isNested'] == false) {
    $orientation = 'horizontal';
}
// If there is only one, always render as horizontal
if (count($post_ids) == 1) {
    $orientation = 'horizontal';
}

$cards = array_map(function($post_id) use ($attributes, $post_ids, $orientation) {
    $heading = get_the_title($post_id);
    $bodyText = get_the_excerpt($post_id);
    $imageUrl = get_the_post_thumbnail_url($post_id, 'large') ?: '';
    $imageAlt = get_post_meta(get_post_thumbnail_id($post_id), '_wp_attachment_image_alt', true);
    $link = ['href' => get_permalink($post_id), 'content' => 'Read more'];

    return new Card([
        'tagName'           => 'article',
        'heading'           => $heading,
        'bodyText'          => $bodyText,
        'image'             => [
            'src'   => $imageUrl,
            'alt'   => $imageAlt,
        ],
        'link'              => [
            'href'      => $link['href'],
            'content'   => $link['content'],
            'isOutline' => true
        ],
        'colorTheme'        => $attributes['component']['colorTheme'] ?? 'primary',
        'orientation'       => $orientation
    ]);
}, $post_ids);

if ($fields['isNested'] || !isset($attributes['container'])) {
    $component = new Group(
        array(
            'shortName'  => 'featured-posts',
            'colorTheme' => $attributes['component']['colorTheme'] ?? null
        ),
        [$heading, ...$cards]
    );
}
else {
    $component = new Container(
        array(
            ...$attributes['container'],
            'shortName'  => 'featured-posts',
            'colorTheme' => $attributes['component']['colorTheme'] ?? null
        ),
        [$heading, ...$cards]
    );
}
$component->render();
