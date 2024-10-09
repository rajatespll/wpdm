<?php
if(!defined('ABSPATH')) die('Dream more!');

$files = WPDM()->dropZone->getFiles();

?>
<div class="wrap w3eden">

	<?php

	$actions = [];
	$menus = [
		['link' => "edit.php?post_type=wpdmpro&page=wpdm-dropzone", "name" => __("All files", "download-manager"), "active" => (wpdm_query_var('tab', 'txt') === '')],
		['link' => "edit.php?post_type=wpdmpro&page=wpdm-dropzone&tab=file-requests", "name" => __("File requests", "download-manager"), "active" => (wpdm_query_var('tab','txt') === 'file-requests')],
	];

	if(isset($_GET['file'])) {
		$menus[] = ['link' => "edit.php?post_type=wpdmpro&page=wpdm-dropzone&tab=file-info&file=".wpdm_query_var('file', 'int'), "name" => __("File Info", "download-manager"), "active" => true];
    }

	WPDM()->admin->pageHeader(esc_attr__( 'DropZone', WPDM_TEXT_DOMAIN ), 'boxes color-purple', $menus, $actions);

	?>
    <link rel="stylesheet" href="<?= WPDM_CSS_URL ?>settings-ui.css" />
 

    <div class="wpdm-admin-page-content">
        <div id="wpdm-wrapper-panel" class="panel panel-default">

            <div class="panel-body">

                <?php
                if(wpdm_query_var('tab') === '')
                    include wpdm_tpl_path("dropzone-admin-files.php", __DIR__);
                if(wpdm_query_var('tab') === 'file-requests')
	                include wpdm_tpl_path("dropzone-admin-file-requests.php", __DIR__);
                if(wpdm_query_var('tab') === 'file-info')
                    include wpdm_tpl_path("dropzone-admin-file-details.php", __DIR__);
                ?>

            </div>

        </div>


    </div>
</div>
