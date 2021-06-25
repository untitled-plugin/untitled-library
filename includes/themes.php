<?php declare(strict_types=1);

\defined('ABSPATH') || exit;

const API_THEMES_ENDPOINT = 'https://untitledplugin.com/api/untitled/v1/themes';

\add_action('install_themes_untitled_library', 'display_themes_table');

\add_filter('themes_api', function ($result, $action, $args) {
    $searchSlug = isset($args->slug);
    $empty = (object) ['themes' => [], 'info' => ['results' => 0]];

    if ('untitled_library' !== $args->browse && false === $searchSlug) {
        return $result;
    }

    $options = \get_site_option('untitled_library_options');

    if (false === isset($options['api_key'])) {
        return $searchSlug ? $result : $empty;
    }

    $response = \wp_remote_get(
        \add_query_arg(
            ['per_page' => $args->per_page, 'page' => $args->page, 'slug' => $args->slug ?? ''],
            API_THEMES_ENDPOINT
        ),
        ['headers' => ['Authorization' => "Bearer {$options['api_key']}"]]
    );

    if (200 !== \wp_remote_retrieve_response_code($response) || \is_wp_error($response)) {
        return $searchSlug ? $result : $empty;
    }

    $response = \json_decode(\wp_remote_retrieve_body($response));

    foreach ($response->themes as $key => $theme) {
        $response->themes[$key]->author = (array) $theme->author;
    }

    return $searchSlug && empty($response) ? $result : (object) $response;
}, 10, 3);

\add_filter('pre_set_site_transient_update_themes', static function ($value) {
    $options = \get_site_option('untitled_library_options');

    if (false === isset($options['api_key'])) {
        return $value;
    }

    $installedThemes = \wp_get_themes();
    $response = \wp_remote_post(
        API_THEMES_ENDPOINT,
        [
            'headers' => ['Authorization' => "Bearer {$options['api_key']}"],
            'body' => ['themes' => $installedThemes],
        ]
    );

    if (false === \is_object($value) || \is_wp_error($response)) {
        return $value;
    }

    $themesToUpdate = \json_decode(\wp_remote_retrieve_body($response), true);

    foreach (isset($themesToUpdate['themes']) ? $themesToUpdate['themes'] : [] as $themeName => $theme) {
        if (false === \array_key_exists($themeName, $installedThemes)) {
            continue;
        }

        if (\version_compare($installedThemes[$themeName]['Version'], $theme['new_version'], '<')) {
            $value->response[$themeName] = $theme;
        } else {
            $value->no_update[$themeName] = $theme;
        }
    }

    return $value;
});

\add_action('admin_enqueue_scripts', static function () {
    global $pagenow;

    if ('theme-install.php' !== $pagenow) {
        return;
    } ?>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        var nodeLi = document.createElement('li');
        var nodeA = document.createElement('a');
        nodeA.setAttribute('href', '#');
        nodeA.setAttribute('data-sort', 'untitled_library');
        nodeA.text = 'Untitled Library';

        nodeLi.appendChild(nodeA)
        document.querySelector('.filter-links').appendChild(nodeLi)
      });
    </script>
    <?php
});
