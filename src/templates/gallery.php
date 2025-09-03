<?php
/** @var $fields array */
use Doubleedesign\Comet\Core\{Container, Image, Gallery};

$images = array_map(fn($image) => new Image([]), $fields['images'] ?? []);
$gallery = new Gallery(['columns' => 4], $images);
$component = new Container(['width'=> $fields['width'] ?? 'contained'], [$gallery]);
$component->render();
