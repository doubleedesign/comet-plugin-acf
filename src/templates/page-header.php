<?php
/** @var $fields array */
use Doubleedesign\Comet\WordPress\Classic\TemplateHandler;
use Doubleedesign\Comet\Core\{PageHeader};

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);

// Special handling for posts, archives, etc
$post_id = get_the_id();
if (is_single()) {
    $heading = get_the_title($post_id);
}
else if ($post_id == get_option(PAGE_FOR_POSTS) || is_home()) {
    $heading = get_the_title(PAGE_FOR_POSTS);
}
else if (is_archive()) {
    $queried_object = get_queried_object();
    if (isset($queried_object->name)) {
        $heading = $queried_object->labels->archives;
    }
    else {
        $heading = get_the_archive_title();
    }
}
else {
    $heading = !empty($attributes['component']['heading'])
        ? $attributes['component']['heading']
        : (!empty(get_the_title()) ? get_the_title() : 'New Page');
}

if (class_exists('Doubleedesign\Breadcrumbs\Breadcrumbs') && isset($attributes['component']['showBreadcrumbs']) && $attributes['component']['showBreadcrumbs']) {
    $breadcrumbs = Doubleedesign\Breadcrumbs\Breadcrumbs::$instance->get_raw_breadcrumbs();
}

unset($attributes['component']['heading']);
unset($attributes['component']['showBreadcrumbs']);

if (is_single()) {
    // Add an ID so we can use aria-labelledby on the <article> in single.php
    $attributes['component']['id'] = 'page-header--post-' . $post_id;
}

if (isset($attributes['container'])) {
    $component = new PageHeader(array_merge($attributes['container'], $attributes['component']), $heading, $breadcrumbs ?? []);
    $component->render();
}
else {
    $component = new PageHeader($attributes['component'], $heading, $breadcrumbs ?? []);
    $component->render();
}
