<?php
if(!defined('ABSPATH')) die('Dream more!');
global $wpdb;
$page = wpdm_query_var('cp', 'int');
$items_per_page = 15;
$where = wpdm_query_var('status') ? "where status='".wpdm_query_var('status', 'int')."'" : '';
$total = $wpdb->get_var("select count(*) from {$wpdb->prefix}ahm_dropzone $where");
$files = WPDM()->dropZone->allFiles(wpdm_query_var('status', 'txt'), $page, $items_per_page);
$status = array_keys(DZF_STATUS);

?>
<div class="panel panel-default">
<div class="panel-heading bg-white">
    <div class="btn-group btn-group-sm m-0" data-toggle="buttons">
        <label class="btn btn-link"><?php _e('Filter File Status', WPDM_TEXT_DOMAIN); ?>:</label>
        <label class="btn btn-<?= !isset($_REQUEST['status']) ? 'primary':'secondary' ?>" onclick="location.href='<?= admin_url('/edit.php?post_type=wpdmpro&page=wpdm-dropzone') ?>'">
	        <?= wpdm_query_var('status', 'txt') === '' ? '<i class="fa fa-check-double"></i> ':'' ?> <?php echo __('All', WPDM_TEXT_DOMAIN);; ?>
        </label>
	    <?php foreach (DZF_STATUS as $STATUS => $code) { ?>
        <label class="btn btn-<?= wpdm_query_var('status', 'txt') === $STATUS ? 'primary':'secondary' ?>" onclick="location.href='<?= admin_url('/edit.php?post_type=wpdmpro&page=wpdm-dropzone&status='.$STATUS) ?>'">
            <?= wpdm_query_var('status') === $STATUS ? '<i class="fa fa-check-double"></i> ':'' ?> <?php echo $STATUS; ?>
        </label>
	    <?php } ?>
    </div>

</div>
    <table class="table table-striped">
        <thead>
        <tr>
            <th><?= __('File', WPDM_TEXT_DOMAIN); ?></th>
            <th><?= __('Sender', WPDM_TEXT_DOMAIN); ?></th>
            <th><?= __('Title', WPDM_TEXT_DOMAIN); ?></th>
            <th><?= __('Date', WPDM_TEXT_DOMAIN); ?></th>
            <th><?= __('Status', WPDM_TEXT_DOMAIN); ?></th>
            <th class="text-right"><?= __('Action', WPDM_TEXT_DOMAIN); ?></th>
        </tr>
        </thead>
        <tbody>
		<?php foreach ($files as $file){
			$owner = get_user_by('id', $file->owner);
			if(is_object($owner))
				$owner = $owner->display_name;
			?>
            <tr id="filerow_<?= $file->ID ?>">
                <td><?= $file->name ?></td>
                <td><?=  $owner; ?></td>
                <td><?= $file->title ?></td>
                <td><?= wp_date(get_option('date_format')." ".get_option('time_format'), $file->date) ?></td>
                <td><?= DZF_STATUS_TITLE[$file->status] ?></td>
                <td class="text-right">
                    <a class="btn btn-info btn-sm" href="<?= admin_url("edit.php?post_type=wpdmpro&page=wpdm-dropzone&tab=file-info&user={$file->owner}&file={$file->ID}") ?>">View</a>
                    <button type="button" class="btn btn-danger btn-sm delete-dzf" data-file="<?= $file->ID ?>">Delete</button>
                </td>
            </tr>
		<?php } ?>
        </tbody>
    </table>
</div>
<?= wpdm_paginate_links($total, $items_per_page, $page); ?>

<script>

    jQuery(function ($) {
        let $body = $('body');

        $body.on('click', '.delete-dzf', function (e) {
            let $btn = $(this);
            let file = $(this).data('file');

            WPDM.confirm('<?= __('Deleting a file!', WPDM_TEXT_DOMAIN); ?>', '<?= __('Are you sure? This action can not be reverted!', WPDM_TEXT_DOMAIN); ?>', [
                {
                    label: 'Yes, Confirm!',
                    class: 'btn btn-danger',
                    callback: function () {
                        $(this).find('.modal-body').html('<i class="fa fa-sun fa-spin"></i> <?= __('Deleting', WPDM_TEXT_DOMAIN); ?>...');
                        var confirm = $(this);
                        $.post(ajaxurl, {action: 'wpdmdz_delete_file', file: file, dznonce: '<?= wp_create_nonce(WPDM_PUB_NONCE); ?>'}, function (res) {
                            $(`#filerow_${file}`).remove();
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
    });
</script>