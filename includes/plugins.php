<?php

\defined('ABSPATH') || exit;

const API_PLUGINS_ENDPOINT = 'https://untitledplugin.com/api/untitled/v1/plugins';

\add_filter('install_plugins_tabs', static function ($tabs) {
    return \array_merge($tabs, [
        'untitled_library' => 'Untitled Library',
    ]);
});

\add_action('install_plugins_untitled_library', 'display_plugins_table');

\add_filter('install_plugins_table_api_args_untitled_library', static function ($args) {
    return \array_merge(\is_array($args) ? $args : [], [
        'untitled_library' => true,
        'per_page' => 36,
    ]);
});

\add_filter('plugins_api', function ($result, $action, $args) {
    $searchSlug = isset($args->slug);
    $empty = (object) ['plugins' => [], 'info' => ['results' => 0]];

    if (false === isset($args->untitled_library) && false === $searchSlug) {
        return $result;
    }

    global $paged;

    $options = \get_site_option('untitled_library_options');

    if (false === isset($options['api_key'])) {
        return $searchSlug ? $result : $empty;
    }

    $response = \wp_remote_get(
        \add_query_arg(
            ['per_page' => $args->per_page, 'page' => $paged, 'slug' => isset($args->slug) ? $args->slug : ''],
            API_PLUGINS_ENDPOINT
        ),
        ['headers' => ['Authorization' => "Bearer {$options['api_key']}"]]
    );

    if (200 !== \wp_remote_retrieve_response_code($response) || \is_wp_error($response)) {
        return $searchSlug ? $result : $empty;
    }

    $response = \json_decode(\wp_remote_retrieve_body($response), true);

    return $searchSlug && empty($response) ? $result : (object) $response;
}, 10, 3);

\add_filter('pre_set_site_transient_update_plugins', static function ($value) {
    $options = \get_site_option('untitled_library_options');

    if (false === isset($options['api_key'])) {
        return $value;
    }

    $installedPlugins = \get_plugins();
    $response = \wp_remote_post(
        API_PLUGINS_ENDPOINT,
        [
            'headers' => ['Authorization' => "Bearer {$options['api_key']}"],
            'body' => ['plugins' => $installedPlugins],
        ]
    );

    if (false === \is_object($value) || \is_wp_error($response)) {
        return $value;
    }

    $pluginsToUpdate = \json_decode(\wp_remote_retrieve_body($response), true);

    foreach (isset($pluginsToUpdate['plugins']) ? $pluginsToUpdate['plugins'] : [] as $pluginName => $plugin) {
        if (false === \array_key_exists($pluginName, $installedPlugins)) {
            continue;
        }

        if (\version_compare($installedPlugins[$pluginName]['Version'], $plugin['new_version'], '<')) {
            $value->response[$pluginName] = (object) $plugin;
        } else {
            $value->no_update[$pluginName] = (object) $plugin;
        }
    }

    return $value;
});

\add_action('admin_enqueue_scripts', static function (): void {
    global $pagenow;

    if ('plugin-install.php' !== $pagenow || (isset($_GET['tab']) ? $_GET['tab'] : '') !== 'untitled_library') {
        return;
    } ?>
    <style>
        .plugin-card-top h3 a {
            pointer-events: none;
            cursor: default;
            color: #32383b;
        }

        .plugin-action-buttons li:last-of-type,
        .plugin-card-bottom .column-rating,
        .plugin-card-bottom .column-downloaded {
            display: none;
        }

        .plugin-card-bottom .column-updated {
            max-width: 180px;
            float: left !important;
            text-align: left !important;
        }
    </style>
    <?php
});
