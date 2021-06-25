<div class="wrap">
    <div id="icon-themes" class="icon32"></div>
    <h2>Untitled Library settings</h2>

    <div class="up-right-side">

    </div>

    <form method="POST" action="" class="up-form">
        <?php \settings_fields('untitled_library_options'); ?>
        <?php \do_settings_sections('untitled_library'); ?>

        <?php \submit_button(); ?>
    </form>
</div>

<style>
    .up-right-side {
        float: right;
        margin: 10px 0 0 20px;
        width: 275px;
    }

    .up-form {
        float: left;
        max-width: calc(100% - 295px);
    }

    @media screen and (max-width: 782px) {
        .up-right-side {
            float: left;
            width: 100%;
            margin: 10px 0;
        }

        .up-form {
            float: left;
            max-width: inherit;
        }
    }
</style>