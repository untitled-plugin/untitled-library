<?php
/**
 * Plugin Name: Untitled Library
 * Plugin URI: https://statik.space/
 * Description: The Untitled Library plugin offers you to manage all our plugins directly from the WordPress dashboard!
 * Network: True
 * Version: 1.0.0
 * Author: Untitled Plugin
 * Author URI: https://untitledplugin.com
 * WC tested up to: 5.7.1
 * WC requires at least: 5.0
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html.
 */

declare(strict_types=1);

\defined('ABSPATH') || exit;

include_once __DIR__ . '/includes/options.php';

$options = \get_site_option('untitled_library_options');

if (empty($options) || empty(\array_filter($options))) {
    include_once __DIR__ . '/includes/missing-key.php';

    return;
}

include_once __DIR__ . '/includes/plugins.php';
include_once __DIR__ . '/includes/themes.php';
