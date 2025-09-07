<?php
/** @var array $fields */
$width = $fields['width'] ?? 'full';
$colorTheme = $fields['colorTheme'] ?? 'dark';

echo <<<HTML
	<hr class="divider" data-color-theme="$colorTheme" data-size="$width"/>
HTML;
