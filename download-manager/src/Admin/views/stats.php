<?php
$type = wpdm_query_var('type', array('validate' => 'alpha', 'default' => 'overview'));
$base_page_uri = "edit.php?post_type=wpdmpro&page=wpdm-stats";
?>
<div class="wrap w3eden">

    <?php

    $actions = [
        ['link' => "edit.php?post_type=wpdmpro&page=wpdm-stats&type=prepare-export&__xnonce=".wp_create_nonce(NONCE_KEY), "class" => "success", "name" => '<i class="sinc far fa-arrow-alt-circle-down"></i> ' . __("Export Full History", "download-manager")]
    ];

    $menus = [
            ['link' => "edit.php?post_type=wpdmpro&page=wpdm-stats", "name" => __("Overview", "download-manager"), "active" => ($type === 'overview')],
            ['link' => "edit.php?post_type=wpdmpro&page=wpdm-stats&type=history", "name" => __("Download History", "download-manager"), "active" => ($type === 'history')],
            ['link' => "edit.php?post_type=wpdmpro&page=wpdm-stats&type=insight", "name" => __("Insights", "download-manager"), "active" => ($type === 'insight')],
    ];

    WPDM()->admin->pageHeader(esc_attr__( 'History and Stats', WPDM_TEXT_DOMAIN ), 'chart-pie color-purple', $menus, $actions);

    ?>


    <div class="container-fluid">

        <div class="wpdm-admin-page-content">
            <?php
            if(file_exists(wpdm_admin_tpl_path("stats/{$type}.php"))) include wpdm_admin_tpl_path("stats/{$type}.php");
            else do_action("wpdm_stats_page_content/{$type}");
            ?>
        </div>
    </div>

    <style>
        .notice{ display: none; }
    </style>
