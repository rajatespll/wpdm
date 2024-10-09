<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Dream more!' );
}

$pid = (int)str_replace("PID_", "", \WPDM\__\Crypt::decrypt( $_REQUEST['pid'] ));

$ind = md5(WPDM()->fileSystem->locateFile($file));
//wpdmdd(WPDM()->fileSystem->locateFile($file));
if ( $pid ) {
	if ( $pid === - 1 ) {
		$asset = WPDM()->asset->init( $file );
	}
	?>
    <div class="media">
        <div class="mr-3">
            <img src="<?= WPDM()->fileSystem->filePreview( $file ); ?>" style="width: 96px;min-width:96px" alt="File"/>
        </div>
        <div class="media-body">
            <h3 style="font-size: 16px;margin: 0 0 8px;line-break: anywhere"><?= basename( $file ); ?></h3>
            <div class="file-size"><?= \WPDM\__\__::formatBytes( filesize( $file ) ) ?></div>
            <div class="dl mt-3">
				<?php if ( $pid > 0 ) {
                    //echo WPDM()->package->downloadLink($pid, 0, ['ind' => $ind])
                    $url = WPDM()->package->getDownloadURL($pid);
                    $url = add_query_arg(['ind' => $ind], $url);
                    if(!WPDM()->package->isLocked())
                        echo "<a href='{$url}' class='btn btn-primary'>".__('Download', 'download-manager')."</a>";
                    //else
                    //    echo "<a href='#unlock'  class='wpdm-download-link wpdm-download-locked btn btn-xs btn-info' data-package='".$pid."' data-file='".$ind."'>".__('Download', 'download-manager')."</a>";
                    ?>

				<?php } else { ?>
                    <a href="<?= $asset->temp_download_url ?>"
                       class="btn btn-info btn-sm"><?php _e( 'Download', WPDM_TEXT_DOMAIN ); ?></a>
				<?php } ?>
            </div>
        </div>
    </div>
	<?php
} else {
	?>
    <div class="w3eden">
        <div class="alert alert-danger">
			<?php echo __( 'You are not authorized!', WPDM_TEXT_DOMAIN ) ?>
        </div>
    </div>
	<?php
}
