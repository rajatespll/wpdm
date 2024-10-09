<?php

use WPDM\__\__;

if(!defined('ABSPATH')) die('Dream more!');
$id = uniqid();
?>
<form id="sharerl-<?= $id ?>" method="post">
    <input type="hidden" name="action" value="wpdmdz_share_rlink">
    <input type="hidden" name="rid" value="<?= (int)$request->ID ?>">
    <?= wp_nonce_field(WPDM_PRI_NONCE, 'dzsl_nonce') ?>
	<div class="form-group">
		<label for="emls"><?php _e('Emails', WPDM_TEXT_DOMAIN); ?> <span class="text-danger">*</span> </label>
		<input required class="form-control" type="text" id="emls" name="emails" placeholder="Multiple emails are separated by comma">
	</div>

	<div class="form-group">
		<label for="msgs"><?php _e('Message', WPDM_TEXT_DOMAIN); ?></label>
		<textarea class="form-control" id="msgs" name="message" placeholder="Add a message ( optional )"></textarea>
	</div>


	<div class="panel panel-default">
		<div class="panel-body">
            <label for="msgs"><?php _e('Share the link instead', WPDM_TEXT_DOMAIN); ?></label>
            <div class="input-group">
                <input type="text" readonly="readonly" id="fruc" class="form-control" value="<?= $request->url ?>">
                <div class="input-group-btn">
                    <button type="button" onclick="WPDM.copy('fruc')" class="btn btn-secondary"><i class="fa fa-copy"></i></button>
                </div>
            </div>
        </div>
	</div>


    <div class="text-right">
        <button type="submit" class="btn btn-primary"><?php _e('Share', WPDM_TEXT_DOMAIN); ?></button>
    </div>

</form>
<script>
    jQuery(function ($) {
        $('#sharerl-<?= $id ?>').on('submit', function (e) {
            e.preventDefault();
            let $form = $(this);
            WPDM.blockUI('#sharerl-<?= $id ?>');
            $form.ajaxSubmit({
                url: ajaxurl,
                success: function (res) {
                    WPDM.notify("Request link sent to the given email address", 'success', 'top-center', 4000);
                    $('#<?= __::query_var('__mdid', 'txt') ?>').modal('hide');
                }

            });
        });
    });
</script>