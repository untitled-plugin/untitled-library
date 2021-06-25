<?php

declare(strict_types=1);

\defined('ABSPATH') || exit;

\add_action('admin_notices', static function (): void {
    $adminNoticeClass = 'notice-error';
    $adminNotice = \sprintf(
        '<b>Untitled Library</b> requires providing an API key. Go to <a href="%s">Settings</a> and fill it!',
        \is_multisite()
            ? \network_admin_url(\add_query_arg('page', 'untitled-library', 'settings.php'))
            : \admin_url(\add_query_arg('page', 'untitled-library', 'options-general.php'))
    );

    echo "<div class='notice {$adminNoticeClass} is-dismissible'><p>{$adminNotice}</p></div>";
});
