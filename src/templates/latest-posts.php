<?php
/** @var $fields array */
use Doubleedesign\Comet\WordPress\Classic\TemplateHandler;
use Doubleedesign\Comet\Core\{Container, Heading, CardList, Card};

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);

$heading = new Heading(
    ['context'   => 'latest-posts'],
    $attributes['component']['heading'] ?? 'Latest Posts'
);

$query_args = [
    'posts_per_page' => $attributes['component']['number'] ?? 3,
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
];
if (!empty($attributes['component']['showPostsFrom'])) {
    $query_args['category__in'] = $attributes['component']['showPostsFrom'];
}
$post_ids = wp_list_pluck((new WP_Query($query_args))->posts, 'ID');

$cards = array_map(function($post_id) use ($attributes, $post_ids) {
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
        'orientation'       => count($post_ids) < 3 ? 'horizontal' : 'vertical',
        // no need to set colour theme unless it differs from that of the CardList
    ]);
}, $post_ids);

$cardList = new CardList(
    array(
        'context'    => 'latest-posts',
        'colorTheme' => $attributes['component']['colorTheme'] ?? null
    ),
    $cards
);

$component = new Container(
    array(
        'shortName'  => 'latest-posts',
        'colorTheme' => $attributes['component']['colorTheme'] ?? null
    ),
    [$heading, $cardList]
);
$component->render();
