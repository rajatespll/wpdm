<?php
if(!defined('ABSPATH')) die('Dream more!');
?>
<div id="efin">
    <div class="card bg-light mb-3">
        <div class="card-header">
            <?php _e('File Name', 'download-manager'); ?>:
        </div>
        <div class="card-body">
			<?= $file->name ?>
        </div>
    </div>
    <div class="card bg-light mb-3">
        <div class="card-header">
            <?php _e('File Link', 'download-manager'); ?>:
        </div>
        <div class="card-body">
			<?php foreach ($links as $link) {
                ?>
                <div onclick="WPDM.copyTxt('<?= $link->url; ?>')" style="cursor: pointer" class="pull-right text-info ttip" title="<?php esc_attr_e('Copy Link', 'download-manager'); ?>"><i class="fa fa-copy"></i></div>
                <a target="_blank" href="<?= $link->url; ?>"><code><?= $link->url; ?></code></a>
                <?php
            } ?>
        </div>
    </div>
    <?php /*
    <div class="card bg-light mb-3">
        <div class="card-header">
			<?php _e('Email File Link', 'download-manager'); ?>:
        </div>
        <div class="card-body">
            <form method="post" id="sharedropzonefile">
                <input type="hidden" name="action" value="wpdmdz_send_file">
                <input type="hidden" name="file" value="<?= $file->ID ?>">
                <input type="hidden" name="dzsfn" value="<?= wp_create_nonce( WPDM_PUB_NONCE ) ?>">
                <div class="form-group">
                    <label><?= __( 'Emails', WPDM_TEXT_DOMAIN ); ?></label>
                    <input type="text" class="form-control" name="file_title" value="<?= esc_attr( $file->title ); ?>">
                </div>
                <div class="form-group">
                    <label><?= __( 'Message', WPDM_TEXT_DOMAIN ); ?></label>
                    <textarea class="form-control" name="file_description"><?= esc_attr( $file->description ); ?></textarea>
                </div>
                <div class="text-right">
                    <button type="submit" data-act="notify" class="btn btn-success btn-lg btn-ufin">Send File</button>
                </div>
            </form>
        </div>
    </div>
    */ ?>

</div>
