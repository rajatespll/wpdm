<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Dream more!' );
}
?>
<div id="efin">
    <div class="card bg-light mb-3">
        <div class="card-header">
            Editing Info For:
        </div>
        <div class="card-body">
			<?= $file->name ?>
        </div>
    </div>
    <form method="post" id="updatefileinfo">
        <input type="hidden" name="action" value="wpdmdz_save_file_info">
        <input type="hidden" name="file" value="<?= $file->ID ?>">
        <input type="hidden" name="dzfinonce" value="<?= wp_create_nonce( WPDM_PUB_NONCE ) ?>">
        <div class="form-group">
            <label><?= __( 'Title', WPDM_TEXT_DOMAIN ); ?></label>
            <input type="text" class="form-control" name="file_title" value="<?= esc_attr( $file->title ); ?>">
        </div>
        <div class="form-group">
            <label><?= __( 'Description', WPDM_TEXT_DOMAIN ); ?></label>
            <textarea class="form-control" name="file_description"><?= esc_attr( $file->description ); ?></textarea>
        </div>
        <div>
            <button type="submit" data-act="save" class="btn btn-secondary btn-ufin" style="width: 165px">Save Changes</button>
            <button type="submit" data-act="notify" class="btn btn-success btn-ufin" style="width: calc(100% - 169px)">Save & Notify Admin</button>
        </div>
    </form>
</div>
