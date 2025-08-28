<?php
namespace Doubleedesign\Comet\WordPress\Classic;

/**
 * A class to handle the rendering of preprocessed HTML content, such as from ACF WYSIWYG fields
 * in a similar way to how Comet Components are rendered, for compatibility of handling
 */
class PreprocessedHTML {
    private string $content;

    public function __construct(array $attributes, string $content) {
        $this->content = $content;
    }

    public function render(): void {
        echo $this->content;
    }
}
