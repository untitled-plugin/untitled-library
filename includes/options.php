<?php

\defined('ABSPATH') || exit;

\add_action(\is_multisite() ? 'network_admin_menu' : 'admin_menu', static function () {
    \add_submenu_page(
        \is_multisite() ? 'settings.php' : 'options-general.php',
        'untitled Library',
        'Untitled Library',
        'administrator',
        'untitled-library',
        static function () {
            require_once __DIR__ . '/../partials/settings.php';
        }
    );
}, 10);

\add_action('admin_init', static function () {
    \register_setting('untitled_library_options', 'untitled_library_options', 'untitled_library_options_validate');
    \add_settings_section('api_settings', '', static function () {
        return '';
    }, 'untitled_library');

    \add_settings_field(
        'untitled_library_setting_api_key',
        'API Key',
        static function () {
            $options = \get_site_option('untitled_library_options');
            \printf(
                "<input id='%s' name='%s' type='text' class='regular-text' value='%s' />",
                'untitled_library_setting_api_key',
                'untitled_library_options[api_key]',
                \esc_attr(isset($options['api_key']) ? $options['api_key'] : '')
            );
        },
        'untitled_library',
        'api_settings'
    );

    if (false === isset($_POST['option_page']) || 'untitled_library_options' !== $_POST['option_page']) {
        return;
    }

    $nonce = \wp_verify_nonce(isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '', 'untitled_library_options-options');

    if (false === $nonce) {
        return;
    }

    \update_site_option('untitled_library_options', $_POST['untitled_library_options']);
});
