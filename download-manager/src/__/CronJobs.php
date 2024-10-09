<?php
/**
 * User: shahjada
 * Date: 2019-03-21
 * Time: 13:14
 */

namespace WPDM\__;


class CronJobs
{
    private static $instance;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    function __construct()
    {

        add_filter( 'cron_schedules', array($this, 'interval') );
        add_action( 'init', array($this, 'clearTempDataCPCron') );
        add_action( 'init', array($this, 'deleteExpired') );

        if ( ! wp_next_scheduled( '__wpdm_cron' ) ) {
            wp_schedule_event( time() + 3600, 'six_hourly', '__wpdm_cron' );
        }

        $this->schedule();


    }

    function interval( $schedules ) {
        $schedules['six_hourly'] = array(
            'interval' => 21600, //6 hours
            'display'  => esc_html__( 'Every 6 hours' ),
        );

        return $schedules;
    }

    function schedule(){
        add_action( '__wpdm_cron', array($this, 'clearTempData') );
    }

    function clearTempData(){
        if(!(int)get_option('__wpdm_auto_clean_cache', 0)) return;
        global $wpdb;
        $time = time();
        $wpdb->query("delete from {$wpdb->prefix}ahm_sessions where `expire` < $time");
        FileSystem::deleteFiles(WPDM_CACHE_DIR, false, '.zip');
        FileSystem::deleteFiles(WPDM_CACHE_DIR, false, array('filetime' => time() - 3600, 'ext' => '.txt'));
        //FileSystem::deleteFiles(WPDM_CACHE_DIR . 'pdfthumbs/', false);
    }

	function clearTempDataCPCron(){
		if(!isset($_REQUEST['cpc']) || !isset($_REQUEST['cronkey']) || $_REQUEST['cpc'] !== 'wpdmcc') return;

		if($_REQUEST['cronkey'] !== WPDM_CRON_KEY) return;

		global $wpdb;
        $time = time();
        $wpdb->query("delete from {$wpdb->prefix}ahm_sessions where `expire` < $time");
        FileSystem::deleteFiles(WPDM_CACHE_DIR, false, '.zip');
        FileSystem::deleteFiles(WPDM_CACHE_DIR, false, array('filetime' => time() - 3600, 'ext' => '.txt'));
        die('Cache cleared successfully!');
    }

	function deleteExpired(){

		if(!isset($_REQUEST['cde']) || !isset($_REQUEST['cronkey']) || $_REQUEST['cde'] !== 'wpdmde') return;
		if($_REQUEST['cronkey'] !== WPDM_CRON_KEY) return;

		if(!(int)get_option('__wpdm_delete_expired', 0)) return;

		global $wpdb;
		$today = date("YmdHi");
		$res = $wpdb->get_results("select post_id, meta_value as expire_date from {$wpdb->prefix}postmeta where meta_key = '__wpdm_expire_date' and meta_value <> ''");
		$deleted = 0;
		foreach ($res as $item) {
			$time = strtotime($item->expire_date);
			if($time < time() && get_post_status($item->post_id) == 'publish') {
				wp_trash_post($item->post_id);
				$deleted++;
			}
		}

		wp_send_json(['success' => true, 'delete_expired' => $deleted]);
	}
}

