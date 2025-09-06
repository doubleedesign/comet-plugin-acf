<?php
/** @var $fields array */
use Doubleedesign\Comet\Core\{Container, ContentImageBasic, Gallery};

$images = array_map(fn($image) => new ContentImageBasic([]), $fields['images'] ?? []);
$gallery = new Gallery(['columns' => 4], $images);
$component = new Container(['width'=> $fields['width'] ?? 'contained'], [$gallery]);
$component->render();
