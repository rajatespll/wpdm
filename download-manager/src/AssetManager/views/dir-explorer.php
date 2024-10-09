<?php
if(!defined('ABSPATH')) die('Dream more!');

?>
<style>
    .file-block{
        display: inline-block;
        width: 128px;
        font-size: 12px;
        padding: 8px;
        margin: 4px;
        border-radius: 4px;
        text-align: center;
        cursor: pointer;
        transition: all ease-in-out 300ms;
        position: relative;
    }
    .file-block .ctrl {
        position: absolute;
        right: 0;
        top: 0;
        opacity: 0;
        padding: 5px 10px;
    }
    .file-block:hover{
        background: linear-gradient(60deg, rgba(var(--color-primary-rgb), 0.1), rgba(0,0,0,0.02) );
        transition: all ease-in-out 300ms;
    }
    .file-block:hover .ctrl{
        opacity: 1;
    }
    .file-block .thumbnail .thumb{
        width: 48px;
        margin: 8px auto;

    }
    #dxbc {
        font-size: 13px;
    }
    #dxbc .fa{
        margin-left: 6px;
        margin-right: 3px;
        color: var(--color-secondary);
    }
    .file-block .dropdown-menu{
        font-size: 10px;
        width: 10px;
        min-width: 100px;
        padding: 0;
    }
    .file-block .dropdown-item{
        padding: 6px 12px;
    }
</style>
<div id="dirview" class="w3eden">
	<div class="card">
		<div class="card-header" id="dxbc"></div>
		<div class="card-body" id="explorer">

		</div>
	</div>
</div>
<script>
    jQuery(function ($) {
        function explore_dir(dir) {
            WPDM.blockUI('#explorer');
            $.get(wpdm_url.ajax, {action: 'explore_dir', base: '<?= $base_dir ?>', dir: dir, wpdm_direx: '<?= wp_create_nonce(WPDM_PUB_NONCE); ?>', pid: '<?= \WPDM\__\Crypt::encrypt($pid) ?>'}, function (res) {
                if(res.success === true) {
                    $('#explorer').html('');
                    $.each(res.content, function (index, item) {
                        let thumb = item.type === 'DIR'? '<img src="<?= WPDM_ASSET_URL ?>file-type-icons/folder-yellow.svg" />' : WPDM.fileTypeIcon(item.type);
                        let type = item.type === 'DIR' ? 'dir' : 'file';
                        let dropdown = `<div class="dropdown dropleft"><div class="ctrl" data-toggle="dropdown" ><i class="fa fa-ellipsis-v"></i></div>  <div class="dropdown-menu"><a class="dropdown-item" href="#">File Info</a><a class="dropdown-item" href="#">Download</a><a class="dropdown-item" href="#">Share</a></div></div>`;
                        let block = `<div class='file-block ${type} ${item.type}' data-path='${item.path}'><div class="file-block-area" data-path='${item.path}'><div class='thumbnail'><div class="thumb">${thumb}</div></div><div class='fname ellipsis'>${item.name}</div><div class="fsize text-small text-muted">${item.size}</div></div></div>`;
                        $('#explorer').append(block);
                    });
                    $('#dxbc').html(res.breadcrumb);
                } else {
                    $('#explorer').html(WPDM.html("div", res.message, 'alert alert-info m-0'));
                }
                WPDM.unblockUI('#explorer');
            });

        }
        explore_dir('<?= $dir ?>');
        $('body').on('click', '.file-block.DIR,.breadcrumb-block', function (e) {
            e.preventDefault();
            explore_dir($(this).data('path'));
        });

        $('body').on('click', '.file-block.file .file-block-area', function (e) {
            e.preventDefault();
            WPDM.bootAlert("<?= __('File Info', WPDM_TEXT_DOMAIN); ?>", {url: wpdm_url.ajax+"?action=wpdm_dir_fileinfo&file="+$(this).data('path')+"&base=<?= $base_dir ?>&pid=<<?= \WPDM\__\Crypt::encrypt("PID_".$pid) ?>&wpdm_direx=<?= wp_create_nonce(WPDM_PUB_NONCE); ?>"}, 400, true);
        });

    });
</script>
