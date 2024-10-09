<?php
/**
 * Class DownloadStats
 */

namespace WPDM\__;

global $userDownloadCount;
$userDownloadCount = [];

class DownloadStats
{

    private $dbTable;


    function __construct()
    {
        global $wpdb;
        $this->dbTable = "{$wpdb->prefix}ahm_download_stats";
    }

    /**
     * @param $pid
     * @param $filename
     * @param $oid
     */
    function add($pid, $filename, $oid = null, $type = 'package'){
        global $wpdb;

	    $current_user = wp_get_current_user();


        if(defined("WPDM_DISABLE_STATS") && WPDM_DISABLE_STATS === true) return;
		if(__::query_var('open', 'int') || __::query_var('play', 'int')) return;

        //Handle downloads from email lock
        if(wpdm_query_var('subscriber' )){
            $subscriber = Crypt::decrypt(wpdm_query_var('subscriber' ));
            $wpdb->update("{$wpdb->prefix}ahm_emails", ['request_status' => 1], ['id' => $subscriber]);
        }

        $uid = $current_user->ID;
	    if(!$uid && __::query_var('_wpdmkey') !== '') {
		    $key  = "__wpdmkey_".__::query_var('_wpdmkey');
		    $keyData = TempStorage::get( "{$key}_{$pid}" );
			if(!$uid) $uid = (int)__::valueof($keyData, 'user');
	    }

	    $ip = (get_option('__wpdm_noip') == 0) ? wpdm_get_client_ip() : "";
        $agent = $_SERVER['HTTP_USER_AGENT'];
        //$hash = $this->getInstanceID($pid, $filename, $uid); // "uniq_".md5($pid.$filename.$uid.date("Y-m-d-h-i").wpdm_get_client_ip());
        if($this->sessionExists($pid, $filename, $uid) || wpdm_query_var('nostat', ['validate' => 'int']) === 1) return;
        //Session::set($hash, 1);
        $version = get_post_meta($pid, '__wpdm_version', true);
		$data = apply_filters("wpdm_download_history_row_data", array('pid' => (int)$pid, 'uid' => (int)$uid, 'oid' => $oid, 'year' => date("Y"), 'month' => date("m"), 'day' => date("d"), 'timestamp' => time(), 'ip' => "$ip", 'filename' => $filename, 'agent' => $agent, 'version' => $version, 'type' => $type));
        $wpdb->insert("{$this->dbTable}", $data);
        update_post_meta($pid, '__wpdm_download_count', (int)get_post_meta($pid, '__wpdm_download_count', true) + 1);

        $this->updateUserDownloadCount($pid);

        if (is_user_logged_in()) {
            $index = $current_user->ID;
        } else {
            $index = str_replace(".", "_", $ip);
            if ($index == '') $index = uniqid();
        }

        if ($ip == '') $ip = $index;
        Session::set('downloaded_' . $pid, $ip);
		do_action("wpdm_after_insert_download_history", $pid, $uid);
    }

	function sessionExists($pid, $filename, $uid)
	{
		$hash = "uniq_".md5($pid.$filename.$uid.date("Y-m-d-h-i").wpdm_get_client_ip());
		$exists =  (int)Session::get($hash) === 1;
		if(!$exists) Session::set($hash, 1);
		return $exists;
	}

    /**
     * Get user download count for the given package
     * @param $packageID
     * @param null $userID
     * @return int|null
     */
    function userDownloadCount($packageID, $userID = null)
    {
        global $wpdb, $userDownloadCount;
        $packageID = (int)$packageID;

        if(!$userID) {
            if(is_user_logged_in())
                $userID = get_current_user_id();
            else {
                $userID = wpdm_get_client_ip();
            }
        }
        $userID = esc_sql($userID);

        $piduid = $packageID."_".$userID;

        if(isset($userDownloadCount[$piduid])) return $userDownloadCount[$piduid];
        $sql = "select download_count from {$wpdb->prefix}ahm_user_download_counts  WHERE user = '{$userID}' and package_id = '{$packageID}'";
        $download_count = (int)$wpdb->get_var($sql);
        $userDownloadCount[$piduid] = $download_count;
        return $download_count;
    }

    /**
     * Reset user download count for the given package
     * @param $packageID
     * @param null $userID
     * @return bool|int
     */
    function resetUserDownloadCount($packageID, $userID = null)
    {
        global $wpdb;
        $packageID = (int)$packageID;
        $where['package_id'] = $packageID;
        if($userID !== 'all') {
            if (!$userID) {
                if (is_user_logged_in())
                    $userID = get_current_user_id();
                else {
                    $userID = wpdm_get_client_ip();
                }
            }
            $userID = esc_sql($userID);
            $where['user'] = $userID;
        }
        return $wpdb->update("{$wpdb->prefix}ahm_user_download_counts", ['download_count' => 0], $where);
    }

    /**
     * Update user download count for the given package
     * @param $packageID
     * @param null $userID
     */
    function updateUserDownloadCount($packageID, $userID = null)
    {
        global $wpdb;
        $packageID = (int)$packageID;

        if(!$userID) {
            if(is_user_logged_in())
                $userID = get_current_user_id();
            else {
                $userID = wpdm_get_client_ip();
            }
        }
        $userID = esc_sql($userID);
        $download_count = (int)$this->userDownloadCount($packageID, $userID);
        $download_count++;
        $found = $wpdb->get_var("select count(ID) from {$wpdb->prefix}ahm_user_download_counts where user = '$userID' and package_id = '$packageID'");
        if(!$found)
            $wpdb->insert("{$wpdb->prefix}ahm_user_download_counts", ['download_count' => $download_count, 'user' => $userID, 'package_id' => $packageID]);
        else
            $wpdb->update("{$wpdb->prefix}ahm_user_download_counts", ['download_count' => $download_count], ['user' => $userID, 'package_id' => $packageID]);
    }



    /**
     * @deprecated Use <strong>add</strong> method, WPDM()->downloadHistory->add($pid, $filename, $oid = null);
     * @param $pid
     * @param $uid
     * @param $oid
     * @param string $filename
     */
    function newStat($pid, $uid, $oid, $filename = "")
    {
        global $wpdb, $current_user;
        return true;

        //Deprecated
    }


}


