<?php
/** @var $fields array */
use Doubleedesign\Comet\WordPress\Classic\TemplateHandler;
use Doubleedesign\Comet\Core\{PageHeader};

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);

// Special handling for posts, archives, etc
$post_id = get_the_id();
if ($post_id == get_option('page_for_posts') || get_post_type() == 'post') {
    $heading = get_the_title($post_id);
}
else if (is_category()) {
    $heading = single_cat_title('', false);
}
else if (is_tag()) {
    $heading = single_tag_title('', false);
}
else if (is_author()) {
    $heading = get_the_author();
}
else if (is_archive()) {
    $heading = get_the_archive_title();
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

if (isset($attributes['container'])) {
    $component = new PageHeader(array_merge($attributes['container'], $attributes['component']), $heading, $breadcrumbs ?? []);
    $component->render();
}
else {
    $component = new PageHeader($attributes['component'], $heading, $breadcrumbs ?? []);
    $component->render();
}
