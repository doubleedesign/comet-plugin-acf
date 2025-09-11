<?php
/** @var array $fields */
$width = $fields['width'] ?? 'contained';
$colorTheme = $fields['colorTheme'] ?? $fields['colour-theme'] ?? 'dark';

echo <<<HTML
	<hr class="divider" data-color-theme="$colorTheme" data-size="$width"/>
HTML;
