<?php
/** @var $fields array */
use Doubleedesign\Comet\WordPress\Classic\TemplateHandler;
use Doubleedesign\Comet\Core\{Container, Group, Heading, CardList, Card};

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);

$page_ids = wp_list_pluck((new WP_Query([
    'post_type'      => 'page',
    'post_parent'    => get_the_id(),
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
    'posts_per_page' => -1,
]))->posts, 'ID');

if (!$page_ids) {
    return; // Do not render at all if no child pages exist
}

$heading = new Heading(
    ['context'   => 'child-pages'],
    $attributes['component']['heading'] ?? 'In this section'
);

$orientation = 'vertical';
// If there is 1-2 pages and this instance is not nested, render as a horizontal card
if (count($page_ids) <= 2 && $fields['isNested'] == false) {
    $orientation = 'horizontal';
}
// If there is only one, always render as horizontal
if (count($page_ids) == 1) {
    $orientation = 'horizontal';
}

$cards = array_map(function($page_id) use ($attributes, $page_ids, $orientation) {
    $heading = get_the_title($page_id);
    $bodyText = get_the_excerpt($page_id) ?? '';
    $imageUrl = get_the_post_thumbnail_url($page_id, 'large') ?: '';
    $imageAlt = get_post_meta(get_post_thumbnail_id($page_id), '_wp_attachment_image_alt', true);
    $link = ['href' => get_permalink($page_id), 'content' => 'Read more'];

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
}, $page_ids);

$cardList = new CardList(
    array(
        'context'    => 'featured-posts',
        'colorTheme' => $attributes['component']['colorTheme'] ?? null
    ),
    $cards
);

if ($fields['isNested'] || !isset($attributes['container'])) {
    $component = new Group(
        array(
            'shortName'  => 'child-pages',
            'colorTheme' => $attributes['component']['colorTheme'] ?? null
        ),
        [$heading, $cardList]
    );
}
else {
    $component = new Container(
        array(
            'shortName'  => 'child-pages',
            'colorTheme' => $attributes['component']['colorTheme'] ?? null
        ),
        array(
            new Group(['context' => 'child-pages', 'shortName' => 'header'], [$heading]),
            $cardList
        )
    );
}
$component->render();
