<?php
/** @var $fields array */
use Doubleedesign\Comet\Core\{Container, ContentImageBasic, Gallery};

$images = array_map(function($image) {
    \Symfony\Component\VarDumper\VarDumper::dump($image);

    return new ContentImageBasic($image);
}, $fields['images'] ?? []);
$gallery = new Gallery(['columns' => 4], $images);
$component = new Container(['width'=> $fields['width'] ?? 'contained'], [$gallery]);
$component->render();
