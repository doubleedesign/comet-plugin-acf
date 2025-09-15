<?php
/** @var $fields array */
use Doubleedesign\Comet\WordPress\Classic\{PreprocessedHTML, TemplateHandler};
use Doubleedesign\Comet\Core\{Config, CallToAction, Heading, ButtonGroup, Button, Utils};

$attributes = TemplateHandler::transform_fields_to_comet_attributes($fields);
$current_url = get_the_permalink(get_the_id());

$buttons = $attributes['component']['buttons'] ?? null;
if (is_array($buttons) && count($buttons) > 0) {
    $buttonGroup = new ButtonGroup(
        [],
        array_filter(array_map(
            function($button) use ($attributes, $current_url) {
                if (!is_array($button['button'])) {
                    return null;
                }

                // Swap 'url' to 'href' for Button component
                if (isset($button['button']['url'])) {
                    $button['button']['href'] = $button['button']['url'];
                    unset($button['button']['url']);
                }

                // If this is shown on the same page it links to, make it a hash link to the top of the content
                if ($button['button']['href'] == $current_url) {
                    $button['button']['href'] = '#content';
                }

                return new Button(
                    array_merge(
                        ['colorTheme' => $attributes['component']['colorTheme'] ?? 'primary'],
                        $button['button'],
                        ['isOutline' => $button['style'] === 'isOutline']
                    ),
                    $button['button']['title']
                );
            },
            $buttons
        )));
}

$defaults = Config::getInstance()->get_component_defaults('call-to-action');

$innerComponents = [
    new Heading($defaults['headingAttrs'] ?? [], $fields['heading'] ?? ''),
    ...(!empty($attributes['component']['description']) ? [new PreprocessedHTML([], $attributes['component']['description'])] : []),
    ...(isset($buttonGroup) ? [$buttonGroup] : []),
];

unset($attributes['component']['heading']);
unset($attributes['component']['button']);
unset($attributes['component']['description']);

// This component is declared not nestable in Fields.php, if that changes in the future this will need to account for that
$attributes['component']['isNested'] = false;

// We need to further transform some attributes for this particular module
$finalAttrs = array_merge(
    $attributes['container'],
    Utils::array_pick($attributes['component'], ['backgroundColor', 'colorTheme', 'hAlign']),
    array(
        'innerSize'   => $fields['section-width'] !== $fields['container_width'] ? $fields['container_width'] : null,
        'orientation' => 'vertical'
    )
);

$component = new CallToAction($finalAttrs, $innerComponents);
$component->render();
