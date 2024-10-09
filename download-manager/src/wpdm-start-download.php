<?php

use WPDM\__\FileSystem;
use WPDM\__\Crypt;
use WPDM\__\Messages;
use WPDM\__\TempStorage;

if(!defined("ABSPATH")) die('!');

error_reporting(0);

global $current_user, $dfiles;
$current_user = wp_get_current_user();

//Check for blocked IPs
if(wpdm_ip_blocked()) {
    $_ipblockedmsg =  __('Your IP address is blocked!', 'download-manager');
    $ipblockedmsg = get_option('__wpdm_blocked_ips_msg', '');
    $ipblockedmsg = $ipblockedmsg == ''?$_ipblockedmsg:$ipblockedmsg;
    Messages::error($ipblockedmsg, 1);
}

//Check for blocked users by email
if(is_user_logged_in() && $current_user->user_email && !wpdm_verify_email($current_user->user_email)) {
    $emsg =  get_option('__wpdm_blocked_domain_msg');
    if(trim($emsg) === '') $emsg = __('Your email address is blocked!', 'download-manager');
    Messages::fullPage('Error!', $emsg, 'error');
}

do_action("wpdm_onstart_download", $package);

$expired = get_post_meta($package['ID'], '__wpdm_expire_date', true);
$publish = get_post_meta($package['ID'], '__wpdm_publish_date', true);
if ($expired !== "" && strtotime($expired) < time()) {
	$msg = sprintf(esc_attr__("Download was expired on %s", "download-manager"), date_i18n(get_option('date_format') . " h:i A", strtotime($expired)+wpdm_tzoffset()));
	Messages::fullPage('Error!', $msg, 'error');
}

if ($publish !== "" && strtotime($publish) > time()) {
	$msg = sprintf(esc_attr__("Download will be available from %s", "download-manager"), date_i18n(get_option('date_format') . " h:i A", strtotime($publish)+wpdm_tzoffset()));
	Messages::fullPage('Error!', $msg, 'error');
}

$speed = (int)get_option('__wpdm_download_speed', 10240); //in KB - default 10 MB
$speed = $speed > 0 ? $speed : 10240;
$speed = apply_filters('wpdm_download_speed', $speed);
$user = get_user_by('id', $package['post_author']);
$user_upload_dir = UPLOAD_DIR . $user->user_login . '/';

$_content_dir = str_replace('\\','/',WP_CONTENT_DIR);
$_old_up_dir = $_content_dir.'/uploads/download-manager-files/';
//wpdmdd($package);
//Only published packages are downloadable
$downloadable_post_status = apply_filters("wpdm_downloadable_post_status", array('publish','private'), $package);
if(!in_array($package['post_status'], $downloadable_post_status)) Messages::fullPage("404", "<div class='card p-4 bg-danger text-white'>".__( "Package you are trying to download is not available!" , "download-manager" )."</div>");

$limit_msg = Messages::download_limit_exceeded($package['ID']);

if (wpdm_is_download_limit_exceed($package['ID'])) Messages::fullPage("Error!", $limit_msg, 'error');
//$files = WPDM()->package->getFiles($package['ID']);
$files = $package['files'];

$fileCount = count($files) + (isset($package['package_dir']) && $package['package_dir'] !== '' ? 1 : 0);

if($fileCount === 0){
    Messages::fullPage(__( "No Files", "download-manager" ),  __( "No file is attached with this package!", "download-manager" ));
}

//$idvdl = Individual file download status
$idvdl = ( WPDM()->package->isSingleFileDownloadAllowed( $package['ID'] ) || wpdm_query_var('oid', false) ) && isset($_GET['ind']);

$parallel_download = (int)get_option('__wpdm_parallel_download', 1);

if($parallel_download === 0 && (int)TempStorage::get("download.".wpdm_get_client_ip()) === 1)
    Messages::error(get_option('__wpdm_parallel_download_msg', "Another download is in progress from your IP, please wait until finished."), 1);

if ($fileCount > 1 && !$idvdl) {
    $zipped = get_post_meta($package['ID'], "__wpdm_zipped_file", true);
    $cache_zip = get_option('__wpdm_cache_zip', 0);
    $cache_zip_po = get_post_meta($package['ID'], "__wpdm_cache_zip", true);

    $cache_zip = $cache_zip_po == -1 || !$cache_zip_po ? $cache_zip : $cache_zip_po;
    if ($zipped == '' || !file_exists($zipped) || $cache_zip == 0) {
	    $zipped = '';
        $zipname = sanitize_file_name($package['title']) . '-' . $package['ID'] . '.zip';
        $_files = maybe_unserialize(get_post_meta($package['ID'], '__wpdm_files', true));
        $_files = is_array($_files) ? $_files : [];
        $_dir = get_post_meta($package['ID'], '__wpdm_package_dir', true);
        $_dir = $_dir !== '' ? Crypt::decrypt($_dir) : '';

        if($_dir !== '')
            $zipped = FileSystem::zipDir($_dir, trim(basename($_dir), '/'));
        if(count($_files) > 0) {
            if($zipped !== '')
                $_files['dirzip'] = $zipped;
            $zipped = FileSystem::zipFiles($_files, $zipname);
        }

        update_post_meta($package['ID'], "__wpdm_zipped_file", $zipped);
    }
    WPDM()->downloadHistory->add($package['ID'], '', wpdm_query_var('oid'));
    FileSystem::downloadFile($zipped, basename($zipped), $speed, 1, $package);
}
else {

    //Individual file or single file download section

    $indfile = '';

    if (isset($_GET['ind'])) {
        $indfile = isset($files[esc_attr($_GET['ind'])])?$files[esc_attr($_GET['ind'])]:Crypt::decrypt(esc_attr($_GET['ind']));
        if(!$indfile) {
            $indfile = reset($files);
        }
    } else if ($fileCount === 1) {
	    $indfile = reset($files);
    }

    $firstfile = reset($files);
	$firstfile = html_entity_decode($firstfile);
    $firstfile = file_exists($firstfile) ? $firstfile : UPLOAD_DIR.$firstfile;
    WPDM()->downloadHistory->add($package['ID'], $indfile ?: $firstfile, wpdm_query_var('oid'));

    //URL Download
    if ($indfile != '' && substr_count($indfile, '://')) {
	    $indfile = wpdm_escs(htmlspecialchars_decode($indfile));
	    header('location: ' . urldecode($indfile));
        die();
    }

	if(!$indfile && count($files) === 1)
		Messages::error(__('No file attached with the package!', WPDM_TEXT_DOMAIN), 1);

    $filepath = WPDM()->fileSystem->absPath($indfile, $package['ID']);
    if(!$filepath)
        Messages::fullPage('Error!', "<div class='card bg-danger text-white p-4'>" . __("Sorry! File not found!", "download-manager") . "</div>", 'error');

    $filename = wpdm_basename($filepath);
    $filename = preg_replace("/([0-9]+)[wpdm]+_/", "", $filename);

	FileSystem::downloadFile($filepath, $filename, $speed, 1, $package);


}

TempStorage::kill("download.".wpdm_get_client_ip());

do_action("after_download", $package);

die();

