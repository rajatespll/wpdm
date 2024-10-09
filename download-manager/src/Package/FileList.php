<?php


namespace WPDM\Package;


use WPDM\__\__;
use WPDM\__\Crypt;
use WPDM\__\FileSystem;
use WPDM\__\Session;
use WPDM\__\UI;

class FileList
{

    /**
     * @var null Package
     */
    private $package = null;

    function __construct($package = null)
    {

    }

    private function fileEntrySimple()
    {

    }

	public static function fileDownloadLinks($ID, $play_only = false)
	{

		$current_user = wp_get_current_user();

		$package = WPDM()->package->init($ID);

		$fileinfo = maybe_unserialize(get_post_meta($ID, '__wpdm_fileinfo', true));

		if (function_exists('wpdmpp_effective_price') && wpdmpp_effective_price($ID) > 0) return self::premium($ID, $play_only);

		$files = maybe_unserialize($package->files);
		$permalink = get_permalink($ID);
		$sap = strpos($permalink, '?') ? '&' : '?';
		$fhtml = '';
		$idvdl = $package->isSingleFileDownloadAllowed();
		$pd = $package->avail_date ? strtotime($package->avail_date) : 0;
		$xd = $package->expire_date ? strtotime($package->expire_date) : 0;

		$package_download_url = $package->getDownloadURL($ID);

		$nodl = $play_only ? 'style="display: none"' : "";

		$permalink = get_permalink($ID);
		$sap = strpos($permalink, '?') ? '&' : '?';

		$cur = is_user_logged_in() ? $current_user->roles : array('guest');

		if (($xd > 0 && $xd < time()) || ($pd > 0 && $pd > time())) $idvdl = 0;

		if (is_array($files) && count($files) > 0) {

			$pwdlock = (int)get_post_meta($ID, '__wpdm_password_lock', true);

			//Check if any other lock option applied for this package
			$olock = 0;
			$noaccess = 0;

			$swl = 0;
			if ($package->quota <= 0) $package->quota = 9999999999999;
			if (is_user_logged_in()) $cur[] = 'guest';
			if (!$package->userCanDownload()) {
				$noaccess = 1;
			}
			if ($package->isLocked()) {
				$olock = 1;
			}

			$pwdcol = $dlcol = '';

			if ($noaccess === 0) {
				if ($idvdl && ($pwdlock || !$olock)) {
					$swl = 1;
				}
			}

			$allfiles = $files;

			$cattr = $data = "";

			if (is_array($allfiles)) {
				$pc = 0;
				$dir = get_post_meta($ID, '__wpdm_package_dir', true);
				$dir = $dir ? Crypt::decrypt($dir) : '';

				foreach ($allfiles as $fileID => $sfile) {

					$individual_file_actions = $individual_file_actions_locked = '';

					$ind = $fileID;
					$pc++;

					$filePass = wpdm_valueof($fileinfo, "{$fileID}/password");
					$fileTitle = wpdm_valueof($fileinfo, "{$fileID}/title");
					if(!$fileTitle && $dir && substr_count($sfile, $dir)) {
						$fileTitle = str_replace($dir, "", $sfile);
					}
					$fileTitle = $fileTitle ?: preg_replace("/([0-9]+)wpdm_/", "", wpdm_basename($sfile));
					$fileTitle = esc_attr($fileTitle);
					$fileVersion = wpdm_valueof($fileinfo, "{$fileID}/version");
					$fileVersion = $fileVersion ? " &mdash; {$fileVersion}" : '';
					$lastUpdate = wpdm_valueof($fileinfo, "{$fileID}/update_date");
					$lastUpdate = $lastUpdate ? " &mdash; Updated on {$lastUpdate}" : '';

					$ext = strtolower(pathinfo($sfile, PATHINFO_EXTENSION));

					$play_button = "";
					if($ext === 'mp3') {
						$play_url = add_query_arg(['forceplay' => 1, 'ind' => $ind], $package_download_url);
						$play_button_attrs = [
							'rel'               => 'nofollow',
							'style'             => 'width: 32px',
							'class'             => "inddl btn btn-success btn-xs wpdm-btn-play song-{$ID}-{$pc} ml-1",
							'data-song-index'   => "song-{$ID}-{$pc}",
							'id'                => "song-{$ID}-{$pc}",
							'data-state'        => 'stop',
							'data-player'       => "audio-player-{$ID}",
							'data-title'        => $fileTitle,
							'data-song'         => $play_url
						];
						$play_button = UI::a('#', UI::html('i', ['class' => 'fa fa-play']), $play_button_attrs);
						$play_button = apply_filters("wpdm_audio_play_button", $play_button, $play_url, $package, $fileID);
					}

					if ($swl) {

						if ($filePass == '' && $pwdlock) $filePass = $package->isPasswordProtected();

						$passField = '';
						if ($pwdlock && !$noaccess)
							$passField = "";
						if ($filePass != '' && $pwdlock && !$noaccess)
							$fhtml .= "";
						else {
							if ( substr_count( $sfile, 'magnet:' ) ) $download_url = $sfile;
							else
								$download_url = add_query_arg(['ind' => $ind, 'filename' => wp_basename($sfile)], $package_download_url);
							$link_attrs = ['class' => 'inddl btn btn-primary btn-xs mr-1', 'rel' => 'nofollow'];
							if((int)get_option('__wpdm_open_in_new_window', 0))
								$link_attrs['target'] = '_blank';
							$ind_download_link = UI::a($download_url, $fileTitle, $link_attrs);
							$ind_download_link = apply_filters("wpdm_single_file_download_link", $ind_download_link, $fileID, (array)$package);
							$fhtml .= $ind_download_link;
						}
					} else {
						$fhtml .= "";
					}
				}

			}

			$siteurl = home_url('/');


		}

		return $fhtml;

	}


	/**
     * @usage Callback function for [file_list] tag
     * @param $package
     * @param bool|false $play_only
     * @return string
     */
    public static function table($ID, $play_only = false)
    {

        $current_user = wp_get_current_user();

        $package = WPDM()->package->init($ID);

        $fileinfo = maybe_unserialize(get_post_meta($ID, '__wpdm_fileinfo', true));

        if (function_exists('wpdmpp_effective_price') && wpdmpp_effective_price($ID) > 0) return self::premium($ID, $play_only);

        $files = maybe_unserialize($package->files);
        $permalink = get_permalink($ID);
        $sap = strpos($permalink, '?') ? '&' : '?';
        $fhtml = '';
        $idvdl = $package->isSingleFileDownloadAllowed();
        $pd = $package->avail_date ? strtotime($package->avail_date) : 0;
        $xd = $package->expire_date ? strtotime($package->expire_date) : 0;

        $package_download_url = $package->getDownloadURL($ID, ['ind' => '{{fileid}}', 'filename' => '{{filename}}']);

	    $flat         = (int)get_option('__wpdm_flat_download_url', 0);

        $nodl = $play_only ? 'style="display: none"' : "";

        $permalink = get_permalink($ID);
        $sap = strpos($permalink, '?') ? '&' : '?';

        $cur = is_user_logged_in() ? $current_user->roles : array('guest');

        if (($xd > 0 && $xd < time()) || ($pd > 0 && $pd > time())) $idvdl = 0;


        $button_label = apply_filters("single_file_download_link_label", __("Download", "download-manager"), $package);

        if (is_array($files) && count($files) > 0) {

            $pwdlock = (int)get_post_meta($ID, '__wpdm_password_lock', true);

            //Check if any other lock option applied for this package
            $olock = 0;
            $noaccess = 0;

            $swl = 0;
            if ($package->quota <= 0) $package->quota = 9999999999999;
            if (is_user_logged_in()) $cur[] = 'guest';
            if (!$package->userCanDownload()) {
                $noaccess = 1;
            }
            if ($package->isLocked()) {
                $olock = 1;
            }

            $pwdcol = $dlcol = '';

            if ($noaccess === 0) {

                if ($pwdlock && $idvdl) $pwdcol = "<th>" . __("Password", "download-manager") . "</th>";
                //if ($idvdl && ($pwdlock || !$olock)) {
                if ($idvdl) {
                    $dlcol = "<th>" . __("Action", "download-manager") . "</th>";
                    $swl = 1;
                }
            }

            $allfiles = $files;

            $cattr = $data = "";
            if (count($allfiles) > 5)
                $data = '<input placeholder="' . sprintf(__("Search File... ( Total Files: %d )", "download-manager"), count($allfiles)) . '" style="margin:10px 0;border-radius: 0" type="search" class="form-control bg-white wpdm-pack-search-file" data-filelist="#wpdm-filelist-area-' . $ID . '" />';
            $fhtml = "<div {$cattr} data-packageid='{$ID}' id='wpdm-filelist-area-{$ID}' class='wpdm-filelist-area wpdm-filelist-area-{$ID}' style='position:relative'>{$data}<table id='wpdm-filelist-{$ID}' class='wpdm-filelist table table-hover'><thead><tr><th>" . __("File", "download-manager") . "</th>{$pwdcol}{$dlcol}</tr></thead><tbody>";

			if (is_array($allfiles)) {
                $pc = 0;
                $dir = get_post_meta($ID, '__wpdm_package_dir', true);
                $dir = $dir ? Crypt::decrypt($dir) : '';

                foreach ($allfiles as $fileID => $sfile) {

                    $individual_file_actions = $individual_file_actions_locked = '';
                    $individual_file_actions = apply_filters("individual_file_action", $individual_file_actions, $ID, $sfile, $fileID);
                    $individual_file_actions_locked = apply_filters("individual_file_action_locked", $individual_file_actions_locked, $ID, $sfile, $fileID);

                    $ind = $fileID; //\WPDM_Crypt::Encrypt($sfile);
                    $pc++;

                    //if (!isset($fileinfo[$fileID]) || !@is_array($fileinfo[$fileID])) $fileinfo[$fileID] = array();

                    $filePass = wpdm_valueof($fileinfo, "{$fileID}/password");
                    $fileTitle = wpdm_valueof($fileinfo, "{$fileID}/title");
                    if(!$fileTitle && $dir && substr_count($sfile, $dir)) {
                        $fileTitle = str_replace($dir, "", $sfile);
                    }
                    $fileTitle = $fileTitle ?: preg_replace("/([0-9]+)wpdm_/", "", wpdm_basename($sfile));
                    $fileTitle = esc_attr($fileTitle);
                    $fileVersion = wpdm_valueof($fileinfo, "{$fileID}/version");
                    $fileVersion = $fileVersion ? " &mdash; {$fileVersion}" : '';
                    $lastUpdate = wpdm_valueof($fileinfo, "{$fileID}/update_date");
                    $lastUpdate = $lastUpdate ? " &mdash; Updated on {$lastUpdate}" : '';

	                $ext = strtolower(pathinfo($sfile, PATHINFO_EXTENSION));

	                $play_button = "";
	                if($ext === 'mp3') {
		                $play_url = add_query_arg(['forceplay' => 1], str_replace( ['{{fileid}}', '{{filename}}'], [$ind, wp_basename($sfile)], $package_download_url));
		                $play_button_attrs = [
			                'rel'               => 'nofollow',
			                'style'             => 'width: 32px',
			                'class'             => "inddl btn btn-success btn-xs wpdm-btn-play song-{$ID}-{$pc} ml-1",
			                'data-song-index'   => "song-{$ID}-{$pc}",
			                'id'                => "song-{$ID}-{$pc}",
			                'data-state'        => 'stop',
			                'data-player'       => "audio-player-{$ID}",
			                'data-title'        => $fileTitle,
			                'data-song'         => $play_url
		                ];
		                $play_button = UI::a('#', UI::html('i', ['class' => 'fa fa-play']), $play_button_attrs);
		                $play_button = apply_filters("wpdm_audio_play_button", $play_button, $play_url, $package, $fileID);
	                }

                    if ($swl) {

                        if ($filePass == '' && $pwdlock) $filePass = $package->isPasswordProtected();

                        $fhtml .= "<tr><td>{$fileTitle}{$fileVersion}{$lastUpdate}</td>";
                        $passField = '';
                        if ($pwdlock && !$noaccess)
                            $passField = "<input style='width:150px'  onkeypress='jQuery(this).removeClass(\"input-error\");' size=10 type='password' value='' id='pass_{$ID}_{$ind}' placeholder='" . __("Password", "download-manager") . "' name='pass' class='form-control input-xs inddlps d-inline-block' />";
                        //$fhtml .= "<td width='120' class='text-right'><input  onkeypress='jQuery(this).removeClass(\"input-error\");' size=10 type='password' value='' id='pass_{$ID}_{$ind}' placeholder='".__( "Password" , "download-manager" )."' name='pass' class='form-control input-sm inddlps' /></td>";
                        if ($filePass != '' && $pwdlock && !$noaccess)
                            $fhtml .= "<td style='white-space: nowrap;text-align: right'>{$passField} <button class='inddl btn btn-primary btn-xs' data-pid='{$ID}' data-file='{$fileID}' rel='" . $permalink . $sap . "wpdmdl={$ID}" . "&ind=" . $ind . "' data-pass='#pass_{$ID}_{$ind}'><i class='fa fa-download'></i>&nbsp;" . $button_label . "</button>&nbsp;{$individual_file_actions}</td></tr>";
                        else {
	                        if ( substr_count( $sfile, 'magnet:' ) ) $download_url = $sfile;
							else
								$download_url = str_replace(['{{fileid}}', '{{filename}}'], [$ind, wp_basename($sfile)], $package_download_url); // add_query_arg(['ind' => $ind, 'filename' => wp_basename($sfile)], $package_download_url);
								//$download_url = add_query_arg(['ind' => $ind, 'filename' => wp_basename($sfile)], $package_download_url);
                            $link_attrs = ['class' => 'inddl btn btn-primary btn-xs', 'rel' => 'nofollow'];
                            if((int)get_option('__wpdm_open_in_new_window', 0))
                                $link_attrs['target'] = '_blank';
							if(!$olock)
								$ind_download_link = UI::a($download_url, $button_label, $link_attrs);
							else
								$ind_download_link = '<a href="#unlock" class="wpdm-download-link wpdm-download-locked btn btn-xs btn-info " data-package="'.$package->ID.'" data-file="'.$fileID.'">Download</a>';

							$packageA = (array)$package;
							$packageA['file_download_url'] = $download_url;
							$ind_download_link = apply_filters("wpdm_single_file_download_link", $ind_download_link, $fileID, $packageA);
                            $fhtml .= "<td style='white-space: nowrap;'  class='text-right'>{$ind_download_link} {$play_button} {$individual_file_actions}</td></tr>";
                        }
                    } else {
                        $fhtml .= "<tr><td>{$fileTitle}</td><td style='white-space: nowrap;'  class='text-right'>{$individual_file_actions_locked} {$play_button}</td></tr>";
                    }
                }

            }

            $fhtml .= "</tbody></table></div>";
            $siteurl = home_url('/');


        }

        return $fhtml;

    }


    /**
     * @usage Callback function for [file_list_extended] tag
     * @param $package
     * @return string
     * @usage Generate file list with preview
     */
    public static function extended($ID, $w = 88, $h = 88, $cols = 3)
    {

        $current_user = wp_get_current_user();

        $package = WPDM()->package->init($ID);

        $fileinfo = maybe_unserialize(get_post_meta($ID, '__wpdm_fileinfo', true));

        if (function_exists('wpdmpp_effective_price') && wpdmpp_effective_price($ID) > 0) return self::premium($ID);

        $files = maybe_unserialize($package->files);
        $permalink = get_permalink($ID);
        $sap = strpos($permalink, '?') ? '&' : '?';
        $fhtml = '';
        $idvdl = WPDM()->package->isSingleFileDownloadAllowed($ID);  //isset($package['individual_file_download']) ? $package['individual_file_download'] : 0;
        $pd = $package->avail_date ? strtotime($package->avail_date) : 0;
        $xd = $package->expire_date ? strtotime($package->expire_date) : 0;

        $cur = is_user_logged_in() ? $current_user->roles : array('guest');

        $permalink = get_permalink($ID);
        $sap = strpos($permalink, '?') ? '&' : '?';
        $download_url = $permalink . $sap . "wpdmdl={$ID}";

        Session::set('wpdmfilelistcd_' . $ID, 1);

        if (($xd > 0 && $xd < time()) || ($pd > 0 && $pd > time())) $idvdl = 0;

        $button_label = apply_filters("single_file_download_link_label", __("Download", "download-manager"), $package);


        if (count($files) > 0) {

            $pwdlock = (int)get_post_meta($ID, '__wpdm_password_lock', true);

            //Check if any other lock option apllied for this package
            $olock = $package->isLocked();

            $swl = 0;
            $package->quota = $package->quota > 0 ?: 9999999999999;
            if (is_user_logged_in()) $cur[] = 'guest';
            //if (!isset($package['access']) || count($package['access']) == 0 || !wpdm_user_has_access($ID) || wpdm_is_download_limit_exceed($ID) || $package['quota'] <= $package['download_count']) $olock = 1;

            if ($idvdl && ($pwdlock || !$olock)) {
                $swl = 1;
            }

            $allfiles = $files;

            $fhtml = "<div id='xfilelist'><div class='row'>";
            if (is_array($allfiles)) {

                $classes = array('1' => 'col-md-12', '2' => 'col-md-6', '3' => 'col-md-4', '4' => 'col-md-3', '6' => 'col-md-2');
                $class = isset($classes[$cols]) ? $classes[$cols] : 'col-md-4';

                foreach ($allfiles as $fileID => $sfile) {
                    $fhtml .= "<div class='{$class} col-sm-6 col-xs-6'><div class='panel panel-default card mb-4'>";
                    $ind = $fileID; //\WPDM_Crypt::Encrypt($sfile);

                    $filePass = wpdm_valueof($fileinfo, "{$fileID}/password");
                    $fileTitle = wpdm_valueof($fileinfo, "{$fileID}/title");
                    $fileTitle = $fileTitle ?: preg_replace("/([0-9]+)wpdm__/", "", wpdm_basename($sfile));
                    $fileTitle = esc_attr($fileTitle);

                    if ($filePass == '' && $pwdlock) $filePass = get_post_meta(get_the_ID(), '__wpdm_password', true);

                    $fhtml .= "<div class='panel-heading card-header ttip' title='{$fileTitle}'>{$fileTitle}</div>";

                    $imgext = array('png', 'jpg', 'jpeg', 'gif');
                    $ext = explode(".", $sfile);
                    $ext = end($ext);
                    $ext = strtolower($ext);
                    $filepath = file_exists($sfile) || __::is_url($sfile) ? $sfile : UPLOAD_DIR . $sfile;
                    $thumb = "";


                    $thumb = WPDM()->package->getThumbnail($ID, $fileID, [$w, $h]);
                    $cssclass = in_array($ext, $imgext) ? 'file-thumb wpdm-img-file' : 'file-thumb wpdm-file wpdm-file-' . $ext;
                    if ($thumb) {
                        //$file_thumb_attrs = apply_filters("", $file, $fileID, $thumb, $w, $h);
                        $fhtml .= "<div class='panel-body card-body text-center'><img class='{$cssclass}' src='{$thumb}' alt='{$fileTitle}' /></div><div class='panel-footer card-footer footer-info'>" . wpdm_file_size($sfile) . "</div><div class='panel-footer card-footer text-center'>";
                    } else
                        $fhtml .= "<div class='panel-body card-body text-center'><img class='file-ico' src='" . WPDM_BASE_URL . 'assets/file-type-icons/' . $ext . '.svg' . "' alt='{$fileTitle}' /></div><div class='panel-footer card-footer footer-info text-center'>" . wpdm_file_size($sfile) . "</div><div class='panel-footer card-footer text-center'>";


                    if ($swl) {

                        if ($filePass != '' && $pwdlock)
                            $fhtml .= "<div class='input-group input-group-sm'><input  onkeypress='jQuery(this).removeClass(\"input-error\");' size=10 type='password' value='' id='pass_{$ID}_{$ind}' placeholder='Password' name='pass' class='form-control inddlps' />";
                        if ($filePass != '' && $pwdlock)
                            $fhtml .= "<span class='input-group-btn input-group-append'><button class='inddl btn btn-secondary btn-light btn-block' data-pid='{$ID}' data-file='{$fileID}' data-pass='#pass_{$ID}_{$ind}'><i class='fas fa-arrow-alt-circle-down'></i></button></span></div>"; //rel='" . $download_url . "&ind=" . $ind . "'
                        else {
	                        if ( substr_count( $sfile, 'magnet:' ) ) $ind_download_link = $sfile;
	                        else
								$ind_download_link = "<a rel='nofollow' class='inddl btn btn-primary btn-xs' href='" . $download_url . "&ind=" . $ind . "'>" . $button_label . "</a>";
                            $ind_download_link = apply_filters("wpdm_single_file_download_link", $ind_download_link, $fileID, (array)$package);
                            $individual_file_actions = '';
                            $individual_file_actions = apply_filters("individual_file_action", $individual_file_actions, $ID, $sfile, $fileID);
                            $fhtml .= $ind_download_link . "&nbsp;{$individual_file_actions}";
                        }
                    }


                    $fhtml .= "</div></div></div>";
                }

            }

            $fhtml .= "</div></div>";
            $siteurl = home_url('/');
            //$fhtml .= "<script type='text/javascript' language='JavaScript'> jQuery('.inddl').click(function(){ var tis = this; jQuery.post('{$siteurl}',{wpdmfileid:'{$ID}',wpdmfile:jQuery(this).attr('file'),actioninddlpvr:1,filepass:jQuery(jQuery(this).attr('pass')).val()},function(res){ res = res.split('|'); var ret = res[1]; if(ret=='error') jQuery(jQuery(tis).attr('pass')).addClass('input-error'); if(ret=='ok') location.href=jQuery(tis).attr('rel')+'&_wpdmkey='+res[2];});}); </script> ";


        }


        return $fhtml;

    }

    /**
     * @usage Callback function for [file_list] tag
     * @param $package
     * @param bool|false $play_only
     * @return string
     */
    public static function premium($ID, $play_only = false)
    {

        if (!function_exists('wpdmpp_effective_price')) return self::table($ID, $play_only);


        $current_user = wp_get_current_user();

        $package = WPDM()->package->init($ID);
        $files = $package->files;

        $fileinfo = maybe_unserialize(get_post_meta($ID, '__wpdm_fileinfo', true));

        $fhtml = '<div class="list-group premium-files premium-files-' . $ID . '" id="premium-files-' . $ID . '">';

        $currency = wpdmpp_currency_sign();
        if (count($files) > 0) {
            $post_id = $ID;
            $license_req = get_post_meta($post_id, "__wpdm_enable_license", true);
            $license_pack = get_post_meta($post_id, "__wpdm_license_pack", true);
            //wpdmprecho($license_pack);
            $fileinfo = get_post_meta($post_id, '__wpdm_fileinfo', true);
            $allfiles = $files;

			$package_download_url = WPDM()->package->expirableDownloadLink($ID, count($allfiles)*2);

            if (is_array($allfiles)) {
                $pc = 0;
                foreach ($allfiles as $fileID => $sfile) {

                    $individual_file_actions = '';
                    $individual_file_actions = apply_filters("individual_file_action", $individual_file_actions, $ID, $sfile, $fileID);
                    $file_price = isset($fileinfo[$fileID], $fileinfo[$fileID]['price']) ? number_format((double)$fileinfo[$fileID]['price'], 2) : 0;
                    $ind = $fileID; //\WPDM_Crypt::Encrypt($sfile);
                    $pc++;

                    $fileTitle = wpdm_valueof($fileinfo, "{$fileID}/title");
                    $fileTitle = $fileTitle ?: preg_replace("/([0-9]+)wpdm__/", "", wpdm_basename($sfile));
                    $fileTitle = esc_attr($fileTitle);

	                $ext = strtolower(pathinfo($sfile, PATHINFO_EXTENSION));

	                $play_button = "";

	                if($ext === 'mp3' && get_wpdmpp_option('audio_preview', 0, 'int') === 1) {
		                $play_url = add_query_arg(['forceplay' => 1, 'ind' => $ind], $package_download_url);
		                $play_button_attrs = [
			                'rel'               => 'nofollow',
			                'style'               => 'font-size: 14px;width: 24px',
			                'class'             => "inddl btn btn-link wpdm-btn-play song-{$ID}-{$pc} p-0 mr-1",
			                'data-song-index'   => "song-{$ID}-{$pc}",
			                'id'                => "song-{$ID}-{$pc}",
			                'data-state'        => 'stop',
			                'data-player'       => "audio-player-{$ID}",
			                'data-title'        => $fileTitle,
			                'data-song'         => $play_url
		                ];
		                $play_button = UI::a('#', UI::html('i', ['class' => 'fa fa-play']), $play_button_attrs);
		                $play_button = apply_filters("wpdm_audio_play_button", $play_button, $play_url, $package, $fileID);
	                }


                    $data = $data_prices = "";

                    $pre_licenses = wpdmpp_get_licenses();


                    $active_lics = array();
                    $zl = 0;
                    $file_availabiliy_lic = [];
                    if ($license_req == 1 && is_array($fileinfo)) {
                        foreach ($pre_licenses as $licid => $lic) {
                            $lic['price'] = !isset($fileinfo[$fileID]['license_price']) || !isset($fileinfo[$fileID]['license_price'][$licid]) || $fileinfo[$fileID]['license_price'][$licid] == '' ? (isset($fileinfo[$fileID]['price']) && $zl == 0 ? $fileinfo[$fileID]['price'] : 0) : $fileinfo[$fileID]['license_price'][$licid];
                            $prc = number_format((double)$lic['price'], 2);
                            if ($zl == 0)
                                $file_price = $prc;
                            $active_lics[$licid] = $lic;
                            if ($lic['price'] > 0) {
                                $data .= " data-{$licid}='{$currency}{$prc}' ";
                                $data_prices .= " data-{$licid}='{$prc}' ";
                            }
                            if (isset($license_pack[$licid])) {
                                if (in_array($fileID, $license_pack[$licid]))
                                    $file_availabiliy_lic[] = "file_avail-{$licid}";
                            } else
                                $file_availabiliy_lic[] = "file_avail-{$licid}";
                            $zl++;
                        }

                        //if(count($active_lics) <= 1)
                        //    $data = $data_prices = "";
                    }

                    $file_availabiliy_lic = implode(" ", $file_availabiliy_lic);
                    if ($file_price > 0)
                        $fhtml .= "<div class='list-group-item eden-checkbox premium-file {$file_availabiliy_lic}'>{$play_button}<div {$data} class='badge badge-default pull-right'>{$currency}{$file_price}</div><label class='m-0 p-0'><input type='checkbox' {$data_prices} data-pid='{$ID}' data-file='{$fileID}' value='{$file_price}' class='wpdm-checkbox file-price file-price-{$ID}'> $fileTitle</label></div>";
                    else
                        $fhtml .= "<div class='list-group-item eden-checkbox free-file'>{$play_button}$fileTitle</div>";


                }

            }


        }

        return $fhtml . "</div>";

    }

    /**
     * @usage Callback function for [image_gallery_WxHxC] tag
     * @param $package
     * @return string
     * @usage Generate file list with preview
     */
    public static function imageGallery($ID, $w = 400, $h = 400, $cols = 3)
    {

        $current_user = wp_get_current_user();

        $package = WPDM()->package->init($ID);

        $fileinfo = maybe_unserialize(get_post_meta($ID, '__wpdm_fileinfo', true));
		if(!is_array($fileinfo)) $fileinfo = [];

        if (function_exists('wpdmpp_effective_price') && wpdmpp_effective_price($ID) > 0) return self::premium($ID);

        $files = maybe_unserialize($package->files);
        $permalink = get_permalink($ID);
        $sap = strpos($permalink, '?') ? '&' : '?';
        $fhtml = '';
        $idvdl = WPDM()->package->isSingleFileDownloadAllowed($ID);

        $pd = $package->avail_date ? strtotime($package->avail_date) : 0;
        $xd = $package->expire_date ? strtotime($package->expire_date) : 0;

        $cur = is_user_logged_in() ? $current_user->roles : array('guest');

        $permalink = get_permalink($ID);
        $sap = strpos($permalink, '?') ? '&' : '?';
        $download_url = $permalink . $sap . "wpdmdl={$ID}";

        Session::set('wpdmfilelistcd_' . $ID, 1);

        //Publish and expire date check
        if (($xd > 0 && $xd < time()) || ($pd > 0 && $pd > time())) $idvdl = 0;

        $button_label = apply_filters("single_file_download_link_label", __("Download", "download-manager"), $package);

        if (count($files) > 0) {


            $pwdlock = (int)get_post_meta($ID, '__wpdm_password_lock', true);

            //Check if any other lock option apllied for this package
            $olock = $package->isLocked();

            $swl = 0;
            $package->quota = $package->quota > 0 ?: 9999999999999;
            if (is_user_logged_in()) $cur[] = 'guest';

            if ($idvdl && ($pwdlock || !$olock)) {
                $swl = 1;
            }

            $fhtml = "<div id='wpdm-image-gallery'><div  class='row'>";


            $classes = array('1' => 'col-md-12', '2' => 'col-md-6', '3' => 'col-md-4', '4' => 'col-md-3', '6' => 'col-md-2');
            $class = isset($classes[$cols]) ? $classes[$cols] : 'col-md-4';

            foreach ($files as $fileID => $sfile) {
                $fhtml .= "<div class='{$class} col-sm-6 col-xs-6'><div class='card mb-4'>";
                $ind = $fileID; //\WPDM_Crypt::Encrypt($sfile);

                //if (!isset($fileinfo[$sfile]) || !@is_array($fileinfo[$sfile])) $fileinfo[$sfile] = array();
                if (!isset($fileinfo[$fileID]) || !is_array($fileinfo[$fileID])) $fileinfo[$fileID] = array();

                $filePass = isset($fileinfo[$sfile]['password']) ? $fileinfo[$sfile]['password'] : (isset($fileinfo[$fileID]['password']) ? $fileinfo[$fileID]['password'] : '');
                $fileTitle = isset($fileinfo[$sfile]['title']) && $fileinfo[$sfile]['title'] != '' ? $fileinfo[$sfile]['title'] : (isset($fileinfo[$fileID]['title']) && $fileinfo[$fileID]['title'] != '' ? $fileinfo[$fileID]['title'] : preg_replace("/([0-9]+)wpdm_/", "", wpdm_basename($sfile)));

                $fileTitle = esc_attr($fileTitle);

                if ($filePass == '' && $pwdlock) $filePass = get_post_meta($package->ID, '__wpdm_password', true);

                //$fhtml .= "<div class='panel-heading card-header ttip' title='{$fileTitle}'></div>";

                $imgext = array('png', 'jpg', 'jpeg', 'gif');
                $ext = explode(".", $sfile);
                $ext = end($ext);
                $ext = strtolower($ext);
                $filepath = file_exists($sfile) || wpdm_is_url($sfile) ? $sfile : UPLOAD_DIR . $sfile;
                $thumb = "";

                if ($ext == '') $ext = 'unknown';

                if (in_array($ext, $imgext))
                    $thumb = WPDM()->fileSystem->imageThumbnail($filepath, $w, $h, true);

                if ($thumb) {
                    //$file_thumb_attrs = apply_filters("", $file, $fileID, $thumb, $w, $h);
                    $fhtml .= "<img class='file-thumb card-img-top' src='{$thumb}' alt='{$fileTitle}' />" . "<div class='card-body'><strong class='d-block'>{$fileTitle}</strong><small>" . wpdm_file_size($sfile) . "</small></div><div class='card-footer'>";
                } else
                    $fhtml .= "<img class='file-ico card-img-top' src='" . \WPDM\__\FileSystem::fileTypeIcon($ext) . "' alt='{$fileTitle}' />" . "<div class='card-body'><strong  class='d-block'>{$fileTitle}</strong><small>" . wpdm_file_size($sfile) . "</small></div><div class='card-footer'>";


                if ($swl) {

                    if ($filePass != '' && $pwdlock)
                        $fhtml .= "<div class='input-group input-group-sm'><input  onkeydown='jQuery(this).removeClass(\"input-error\");' size=10 type='password' value='' id='pass_{$ID}_{$ind}' placeholder='Password' name='pass' class='form-control inddlps' />";
                    if ($filePass != '' && $pwdlock)
                        $fhtml .= "<span class='input-group-btn input-group-append'><button class='inddl btn btn-secondary btn-light btn-block' data-pid='{$ID}' data-file='{$fileID}' data-pass='#pass_{$ID}_{$ind}'><i class='fas fa-arrow-alt-circle-down'></i></button></span></div>"; //rel='" . $download_url . "&ind=" . $ind . "'
                    else {

                        $link_attrs = ['class' => 'inddl btn btn-primary btn-xs', 'rel' => 'nofollow'];
                        if((int)get_option('__wpdm_open_in_new_window', 0))
                            $link_attrs['target'] = '_blank';
                        $ind_download_link = UI::a("{$download_url}&ind={$ind}", $button_label, $link_attrs);
                        $ind_download_link = apply_filters("wpdm_single_file_download_link", $ind_download_link, $fileID, (array)$package);
                        $individual_file_actions = '';
                        $individual_file_actions = apply_filters("individual_file_action", $individual_file_actions, $ID, $sfile, $fileID);
                        $fhtml .= $ind_download_link . "&nbsp;{$individual_file_actions}";
                    }
                }


                $fhtml .= "</div></div></div>";
            }


            $fhtml .= "</div></div>";

        }


        return $fhtml;

    }


}
