<?php
if(!defined('ABSPATH')) die('Dream more!');
global $wpdb;
$page = wpdm_query_var('cp', 'int');
$items_per_page = 10;
$where = \WPDM\__\__::query_var('status', 'txt') === 'closed' ? "where ( expiry  <= ".time()." and expiry > 0 ) or closed = 1" : "where (expiry  > ".time()." or expiry = 0) and closed = 0";
$total = $wpdb->get_var("select count(*) from {$this->table_fr} $where");
$requests = WPDM()->dropZone->fileRequests(wpdm_query_var('status', 'txt'), $page, $items_per_page);
$status = array_keys(DZF_STATUS);

?>
<style>
    .table h3{
        font-size: 16px;
        margin-bottom: 5px;
        font-weight: 700;
    }
</style>
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="pull-right">
            <div class="btn-group btn-group-sm">
                <label type="button" class="btn btn-success" data-toggle="modal" data-target="#w3eden__bootModal_fs9kvn7w1"><i class="fa fa-file-circle-plus"></i> <?= __('New request', WPDM_TEXT_DOMAIN); ?></label>
            </div>
        </div>
        <div class="btn-group btn-group-sm m-0" data-toggle="buttons">
        <label class="btn btn-<?= !isset($_REQUEST['status']) ? 'primary':'secondary' ?>" onclick="location.href='<?= admin_url('/edit.php?post_type=wpdmpro&page=wpdm-dropzone&tab=file-requests') ?>'">
	        <?= !isset($_REQUEST['status']) ? '<i class="fa fa-check-double"></i> ':'' ?> <?php echo __('Open', WPDM_TEXT_DOMAIN);; ?>
        </label>
        <label class="btn btn-<?= wpdm_query_var('status') === 'closed' ? 'primary':'secondary' ?>" onclick="location.href='<?= admin_url('/edit.php?post_type=wpdmpro&page=wpdm-dropzone&tab=file-requests&status=closed') ?>'">
	        <?= wpdm_query_var('status') === 'closed' ? '<i class="fa fa-check-double"></i> ':'' ?> <?php echo __('Closed', WPDM_TEXT_DOMAIN);; ?>
        </label>
        </div>
    </div>
    <table class="table table-striped">
        <thead>
        <tr>
            <th><?php _e('Request Details', WPDM_TEXT_DOMAIN); ?></th>
            <th class="text-center"><?php _e('Files', WPDM_TEXT_DOMAIN); ?></th>
            <th><?php _e('Created On', WPDM_TEXT_DOMAIN); ?></th>
            <th><?php _e('Expiry', WPDM_TEXT_DOMAIN); ?></th>
            <th class="text-right"><?php _e('Actions', WPDM_TEXT_DOMAIN); ?></th>
        </tr>
        </thead>
       <?php
       foreach ($requests as $id => $request) { ?>
           <tr id="reqrow_<?= $request->ID ?>">
               <td>
                   <h3><?= esc_html($request->title) ?></h3>
                   <?= esc_html($request->description); ?>
               </td>
               <td class="text-center c-pointer color-blue" onclick="WPDM.bootAlert('Files', {url: ajaxurl+'?action=wpdmdz_fc_files&id=<?= $request->ID ?>'}, 500)">
                   <h3 class="ttip"  title="<?= esc_attr__('Click to view files', WPDM_TEXT_DOMAIN) ?>"><?= $request->file_count ?></h3>
               </td>
               <td>
                   <?= wp_date(get_option('date_format')." ".get_option('time_format'), $request->date); ?>
               </td>
               <td>
                   <?= wp_date(get_option('date_format')." ".get_option('time_format'), $request->expiry); ?>
               </td>
               <td class="text-right">
                   <button type="button" class="btn btn-sm btn-secondary ttip" onclick="WPDM.copyTxt('<?= home_url("/wpdm-file-request/{$request->code}"); ?>')" title="Copy Link"><i class="fa fa-copy"></i></button>
                   <button type="button" class="btn btn-sm btn-secondary ttip"  onclick="WPDM.bootAlert('Share Link', {url: ajaxurl+'?action=wpdmdz_fc_share&id=<?= $request->ID ?>'}, 500)" title="Share Link"><i class="fa fa-share"></i></button>
                   <button type="button" class="btn btn-sm btn-warning ttip closereq" data-request="<?= $request->ID ?>" title="Close Request"><i class="fa fa-ban"></i></button>
                   <button type="button" class="btn btn-sm btn-danger ttip delreq" data-request="<?= $request->ID ?>" title="Delete Link and Files"><i class="fa fa-trash"></i></button>
               </td>
           </tr>
       <?php }
       ?>
    </table>
</div>

<div class="modal fade" id="w3eden__bootModal_fs9kvn7w1" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="width: 400px">
        <form method="post" id="newreqform">
            <input type="hidden" name="action" value="wpdmdz_file_request" />
            <?php wp_nonce_field(WPDM_PRI_NONCE, 'nfr_nonce'); ?>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close m-0" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times-circle"></i></span></button>
                    <h4 class="modal-title"><?php esc_attr_e('New file request', WPDM_TEXT_DOMAIN); ?></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label><?php _e('Title', WPDM_TEXT_DOMAIN); ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="form-group">
                        <label><?php _e('Description', WPDM_TEXT_DOMAIN); ?></label>
                        <textarea class="form-control" name="description" placeholder="(optional)"></textarea>
                    </div>
                    <div class="form-group">
                        <label><input onclick="(jQuery(this).is(':checked') ? jQuery('#expdt').slideDown() : jQuery('#expdt').slideUp())" type="checkbox" name="dealline" value="1"> <?php _e('Set a deadline', WPDM_TEXT_DOMAIN); ?></label>
                        <div id="expdt" style="display: none"><input type="text" name="expiry_date" class="form-control dateinput"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </div><!-- /.modal-content -->
        </form>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>


    jQuery(function ($) {
        let $body = $('body');

        $('.dateinput').datetimepicker();

        $body.on('submit', '#newreqform', function (e) {
            e.preventDefault();
            let $form = $(this);
            WPDM.blockUI('#newreqform');
            $form.ajaxSubmit({
                url: ajaxurl,
                success: function (res) {
                    WPDM.unblockUI('#newreqform');
                    $('#w3eden__bootModal_fs9kvn7w1').modal('hide');
                    location.href = '<?= admin_url('/edit.php?post_type=wpdmpro&page=wpdm-dropzone&tab=file-requests'); ?>';
                }
            });
        });

        $body.on('click', '.closereq', function (e) {
            let $btn = $(this);
            let request = $(this).data('request');

            WPDM.confirm('<?= __('Closing File Request!', WPDM_TEXT_DOMAIN); ?>', '<?= __('Closing file request! Are you sure?', WPDM_TEXT_DOMAIN); ?>', [
                {
                    label: 'Yes, Confirm!',
                    class: 'btn btn-danger',
                    callback: function () {
                        $(this).find('.modal-body').html('<i class="fa fa-sun fa-spin"></i> <?= __('Closing', WPDM_TEXT_DOMAIN); ?>...');
                        var confirm = $(this);
                        $.post(ajaxurl, {action: 'wpdmdz_close_request', rid: request, dzcr_nonce: '<?= wp_create_nonce(WPDM_PRI_NONCE); ?>'}, function (res) {
                            $(`#reqrow_${request}`).remove();
                            confirm.modal('hide');
                        });
                    }
                },
                {
                    label: 'No, Later',
                    class: 'btn btn-info',
                    callback: function () {
                        $(this).modal('hide');
                    }
                }
            ]);
        });

        $body.on('click', '.delreq', function (e) {
            let $btn = $(this);
            let request = $(this).data('request');

            WPDM.confirm('<?= __('Deleting File Request!', WPDM_TEXT_DOMAIN); ?>', '<?= __('Deleting file request! Are you sure?', WPDM_TEXT_DOMAIN); ?>', [
                {
                    label: 'Yes, Confirm!',
                    class: 'btn btn-danger',
                    callback: function () {
                        $(this).find('.modal-body').html('<i class="fa fa-sun fa-spin"></i> <?= __('Deleting', WPDM_TEXT_DOMAIN); ?>...');
                        var confirm = $(this);
                        $.post(ajaxurl, {action: 'wpdmdz_delete_request', rid: request, dzdr_nonce: '<?= wp_create_nonce(WPDM_PRI_NONCE); ?>'}, function (res) {
                            $(`#reqrow_${request}`).remove();
                            confirm.modal('hide');
                        });
                    }
                },
                {
                    label: 'No, Later',
                    class: 'btn btn-info',
                    callback: function () {
                        $(this).modal('hide');
                    }
                }
            ]);
        });

        $body.on('click', '.btn-decline', function (e) {
            let $btn = $(this);
            $.post(ajaxurl, {action: 'wpdmdz_file_decline', dzfdnonce: '<?= wp_create_nonce(WPDM_PUB_NONCE); ?>', file: $btn.data('file')}, function (res) {
                $('.btn-accept, .btn-decline').remove();
                __wpdmdz__comments.comments = res.comments;
            });
        });
        
        $body.on('click', '.closereq', function (e) {
            e.preventDefault();
            let rid = $(this).data('request');

        });
        
    });
</script>