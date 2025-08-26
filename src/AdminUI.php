<?php

namespace Doubleedesign\Comet\WordPress\Classic;

class AdminUI {

    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets'], 100);
    }

    public function enqueue_assets(): void {
        wp_enqueue_style(
            'comet-acf-admin',
            plugins_url('src/assets/admin.css', __DIR__),
            [],
            PluginEntryPoint::get_version()
        );
    }

}
