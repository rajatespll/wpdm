<?php
if(!defined('ABSPATH')) die('Dream more!');
global $wpdb;
$total = $wpdb->get_var("select count(*) from {$wpdb->prefix}ahm_download_stats");
?>
<div class="panel panel-default">
	<div class="panel-heading" id="wxstats"><?= __('Preparing Export File', WPDM_TEXT_DOMAIN) ?></div>
	<div class="panel-body" id="xportprogress">
		<div class="progress" id="wxprogressbar" style="height: 43px !important;border-radius: 3px !important;margin: 0;position: relative;background: #0d406799;box-shadow: none">
			<div id="wxprogress" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;line-height: 43px;background-color: #007bff"></div>
			<div class="fetfont" style="font-size:12px;position: absolute;line-height: 43px;height: 43px;width: 100%;z-index: 999;text-align: center;color: #ffffff;font-weight: 600;letter-spacing: 1px"><?= esc_attr__( 'Processing', WPDM_TEXT_DOMAIN ); ?>... <span id="wxloaded">0</span>%</div>
		</div>
	</div>
</div>
<script>
    function WPDM_Create_Export_File(key) {
        var $ = jQuery;
        $.get(ajaxurl, {action: 'wpdm_export_stats', _statexport_nonce: '<?php echo wp_create_nonce(NONCE_KEY); ?>', _key: key}, function (res) {
            $('#wxprogress').css('width', res.progress+"%");
            $('#wxloaded').html(res.progress);
            $('#exportcount').html(res.exported);
            if(res.continue)
                WPDM_Create_Export_File(key);
            else if(res.continue === false) {
                $('#wxprogressbar .fetfont').html('COMPLETED!');
                $('#xportprogress').html('<a class="btn btn-lg btn-success btn-block" href="'+res.exportfile+'"><?php _e('Download', 'download-manager'); ?></a>');
                $('#wxstats').html("<i class='fa fa-check-double color-green'></i> <?php _e("Export file is ready", "download-manager"); ?>");
            }
            else
                $('#wxprogressbar .fetfont').html('ERROR!');
        });
    }

	jQuery(function ($) {
        $.get(ajaxurl, {action: 'wpdm_export_stats', _statexport_nonce: '<?php echo wp_create_nonce(NONCE_KEY); ?>'}, function (data) {
            $('#wxstats').html("<span class='pull-right' id='importstat'><i class='color-green far fa-spin fa-sun'></i> <?php _e("Preparing: ", "download-manager"); ?> <span id='exportcount' class='color-green' style='display:inline-block;width: 30px;text-align:right'>"+data.exported+"</span></span><span><i class='fa fa-bars color-blue'></i> <?php _e("Entries Found", "download-manager"); ?>: "+data.entries+ "</span>");
            $('#wxloaded').html(data.progress);
            WPDM_Create_Export_File(data.key);
        })
    });
</script>