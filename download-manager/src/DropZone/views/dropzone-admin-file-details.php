<?php
if(!defined('ABSPATH')) die('Dream more!');

$file = WPDM()->dropZone->getFile(wpdm_query_var('file', 'int'));
$comments = json_encode($file->comments) ?: '[]';
$owner = get_user_by('id', $file->owner);
if(is_object($owner))
	$owner = $owner->display_name;
?>
<style>
    #__comments .avatar{
        width: 48px;
        height: auto;
        border-radius: 3px;
    }
</style>
<div class="row">
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="pull-right">
                    <?php if((int)$file->status === DZF_STATUS['DELIVERED']) { ?>
                        <button class="btn btn-xs btn-success btn-accept" data-file="<?= $file->ID ?>"><i class="fa fa-check-double"></i> <?= __('Accept', WPDM_TEXT_DOMAIN); ?></button>
                        <button class="btn btn-xs btn-warning btn-decline" data-file="<?= $file->ID ?>"><i class="fa fa-times-circle"></i> <?= __('Decline', WPDM_TEXT_DOMAIN); ?></button>
                    <?php } ?>
					<a href="<?= home_url('/?wpdmdzdl='.$file->ID) ?>" class="btn btn-xs btn-info"><i class="fa fa-arrow-alt-circle-down"></i> <?= __('Download', WPDM_TEXT_DOMAIN); ?></a>
				</div>
				<?= __('File', WPDM_TEXT_DOMAIN); ?>
			</div>
			<div class="panel-body color-green">
				<strong><?= $file->name ?></strong>
			</div>
			<div class="panel-footer bg-white">
				<?= __('Sent by', WPDM_TEXT_DOMAIN).' <strong>'. $owner.'</strong>'; ?>
			</div>
			<div class="panel-footer bg-white">
				<?= __('Received on', WPDM_TEXT_DOMAIN).' <strong>'. wp_date(get_option('date_format').' '.get_option('time_format'), $file->date).'</strong>'; ?>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading"><?= __('Title', WPDM_TEXT_DOMAIN); ?></div>
			<div class="panel-body">
				<?= $file->title ?>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading"><?= __('Description', WPDM_TEXT_DOMAIN); ?></div>
			<div class="panel-body">
				<?= wpautop($file->description) ?>
			</div>
		</div>

	</div>
	<div class="col-md-6">
        <div id="cmtfrmw">
		<form method="post" id="cmtfrm">
            <input type="hidden" name="dzcnonce" value="<?= wp_create_nonce(WPDM_PUB_NONCE) ?>" />
            <input type="hidden" name="file" value="<?= wpdm_query_var('file', 'int') ?>" />
			<div class="panel panel-default">
				<textarea class="form-control" name="comment" required="required" style="box-shadow: none !important;border: 0 !important;min-height: 80px"></textarea>
				<div class="panel-footer text-right">
					<button type="submit" class="btn btn-primary btn-sm"><?= __('Add Comment', WPDM_TEXT_DOMAIN); ?></button>
				</div>
			</div>
		</form>
        </div>
        <div id="__comments">
            <div class="panel panel-default" v-for="(comment, index) in comments" :id="'comment_' + index">
                <div class="panel-body">
                    <div class="media">
                        <div class="pull-left"><div v-html="comment.avatar"></div></div>
                        <div class="media-body">
                            <strong>{{comment.name}}</strong><br/>
                            {{comment.comment}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
	</div>
</div>

<script>
    var __wpdmdz__comments = new Vue({
        el: '#__comments',
        data: {
            comments: <?= $comments; ?>
        }
    });
    jQuery(function ($) {
        let $body = $('body');
        $body.on('submit', '#cmtfrm', function (e) {
            e.preventDefault();
            WPDM.blockUI('#cmtfrmw');
            $(this).ajaxSubmit({
                url: ajaxurl+'?action=wpdmdz_add_comment',
                success: function (response) {
                    WPDM.unblockUI('#cmtfrmw');
                    __wpdmdz__comments.comments = response;
                }
            });
        });
        $body.on('click', '.btn-accept', function (e) {
            let $btn = $(this);
            $.post(ajaxurl, {action: 'wpdmdz_file_accept', dzfanonce: '<?= wp_create_nonce(WPDM_PUB_NONCE); ?>', file: $btn.data('file')}, function (res) {
                $('.btn-accept, .btn-decline').remove();
                __wpdmdz__comments.comments = res.comments;
            });
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