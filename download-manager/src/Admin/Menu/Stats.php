<?php

/**
 * User: shahnuralam
 * Date: 11/9/15
 * Time: 7:44 PM
 */

namespace WPDM\Admin\Menu;


use WPDM\__\__;
use WPDM\__\Crypt;
use WPDM\__\FileSystem;
use WPDM\__\Session;
use WPDM\__\TempStorage;

class Stats
{
    function __construct()
    {
        add_action('admin_menu', array($this, 'menu'));
        add_action('admin_init', array($this, 'quickExport'));
        add_action('wp_ajax_wpdm_stats_get_packages', array($this, 'ajax_callback_get_packages'));
        add_action('wp_ajax_wpdm_stats_get_users', array($this, 'ajax_callback_get_users'));
        add_action("wp_ajax_wpdm_export_stats", array($this, 'export'));
    }

    function menu()
    {
        add_submenu_page('edit.php?post_type=wpdmpro', __("Stats &lsaquo; Download Manager", "download-manager"), __("Stats", "download-manager"), WPDM_MENU_ACCESS_CAP, 'wpdm-stats', array($this, 'UI'));
    }

    function UI()
    {
        include wpdm_admin_tpl_path("stats.php");
    }

    public function ajax_callback_get_packages()
    {
        global $wpdb;
        $posts_table = "{$wpdb->prefix}posts";
        $packages = [];
        $term = wpdm_query_var('term');

        if ($term) {
            $result_rows = $wpdb->get_results("SELECT ID, post_title FROM $posts_table where `post_type` = 'wpdmpro' AND `post_title` LIKE  '%" . $term . "%' ");
            foreach ($result_rows as $row) {
                $packages[] = [
                    'id'   => $row->ID,
                    'text' => $row->post_title
                ];
            }
        }
        //results key is necessary for jquery select2
        wp_send_json(["results" => $packages]);
    }

    public function ajax_callback_get_users()
    {
        global $wpdb;
        $users_table = "{$wpdb->base_prefix}users";
        $term = wpdm_query_var('term');
        $users = [];

        if ($term) {
            $result_rows = $wpdb->get_results("SELECT ID, user_login, display_name, user_email FROM $users_table where `display_name` LIKE  '%" . $term . "%' OR `user_nicename` LIKE  '%" . $term . "%' OR `user_login` LIKE  '%" . $term . "%' OR `user_email` LIKE  '%" . $term . "%'  ");
            foreach ($result_rows as $row) {
                $text    = $row->display_name . " ( $row->user_login ) ";
                $users[] = [
	                'id'   => $row->ID,
	                'text' => $text
                ];
            }
        }
        //results key is necessary for jquery select2
        wp_send_json(["results" => $users]);
    }

	function prepareExport() {
		if(__::isAuthentic('xstats', WPDM_PUB_NONCE, WPDM_ADMIN_CAP, false)) {
		?>

			<?php
			die();
		}
	}

    function export(){
        if (!wp_verify_nonce(wpdm_query_var('_statexport_nonce'), NONCE_KEY) || !current_user_can(WPDM_ADMIN_CAP)) die('-1');


        do_action("wpdm_before_process_stats_export_data");

        global $wpdb;

        $items_per_page = 10;
        if(wpdm_query_var('_key') == ''){
            $key = uniqid();
            $export_file = WPDM_CACHE_DIR."wpdm-stat-export-{$key}.csv";
            $export['file'] = $export_file;
            $export['start'] = 0;
            $export['total'] = $wpdb->get_var("select count(*) from {$wpdb->prefix}ahm_download_stats");

        } else {
            $key = wpdm_query_var('_key');
            $export = TempStorage::get("export_{$key}");
            $export_file = $export['file'];
        }

        $file = fopen($export_file, 'a');

        //Add headers
        if ((int)$export['start'] === 0) {
            fputs($file, "Package ID,Package Name,Version,Filename,User ID,User Name,User Email,Order ID,Date,TimeStamp,IP,Agent\r\n");
        }

        $stats = $wpdb->get_results("select s.*,u.*,p.post_title from {$wpdb->prefix}ahm_download_stats s LEFT JOIN {$wpdb->prefix}users u ON s.uid=u.ID LEFT JOIN {$wpdb->prefix}posts p ON s.pid=p.ID limit {$export['start']}, $items_per_page");
        foreach ($stats as $d) {
            $csv_row = "{$d->pid},\"{$d->post_title}\",\"{$d->version}\",\"{$d->filename}\",{$d->uid},\"{$d->display_name}\",\"{$d->user_email}\",{$d->oid},{$d->year}-{$d->month}-{$d->day},{$d->timestamp},{$d->ip},\"{$d->agent}\"\r\n";;
            fputs($file, $csv_row . "\r\n");
        }
        fclose($file);


        $continue = true;
        $exported = $export['start'] + $items_per_page;
        $export['start'] = $exported;
        $progress = ($exported/$export['total'])*100;


        TempStorage::set("export_{$key}", $export);

        $response = array('key' => $key, 'continue' => $continue,'entries' => $export['total'], 'progress' => (int)$progress, 'exported' => $exported, 'file' => $export_file);

        if($exported >= $export['total']) {
            $progress = 100;
            $exported = $export['total'];
            $response['continue'] = false;
            $response['exportfile'] = FileSystem::instantDownloadURL($export_file);
            TempStorage::kill("export_{$key}");
        }

        wp_send_json($response);
    }

    function quickExport()
    {
        if (wpdm_query_var('page') == 'wpdm-stats' && wpdm_query_var('task') == 'export') {
            if(!current_user_can(WPDM_ADMIN_CAP) || !wp_verify_nonce(wpdm_query_var('__xnonce'), NONCE_KEY)) die('Invalid nonce!');

            do_action("wpdm_before_quick_export_stats_data");

            global $wpdb;
            $sql = wpdm_query_var("hash") !== '' ? Crypt::decrypt(wpdm_query_var('hash')) : "";
            if(!$sql) $sql = "SELECT [##fields##] FROM {$wpdb->prefix}ahm_download_stats";
            //$data = $wpdb->get_results("select s.*, p.post_title as file from {$wpdb->prefix}ahm_download_stats s, {$wpdb->prefix}posts p where p.ID = s.pid order by id DESC");
            $total = $wpdb->get_var(str_replace("[##fields##]", "count(*) as total", $sql));
            WPDM()->fileSystem->downloadHeaders("download-stats.csv");
            ob_start();
            echo "Package ID,Package Name,User ID,User Name,User Email,Order ID,Date,Timestamp\r\n";
            ob_flush();
            $pages = $total / 20;
            if ($pages > (int) $pages) $pages++;

            for ($i = 0; $i <= $pages; $i++) {
                $start = $i * 20;
                $data = $wpdb->get_results(str_replace("[##fields##]", "*", $sql)." limit $start, 20");
                ob_start();
                foreach ($data as $d) {
                    $package_name = get_the_title($d->pid);
                    $package_name = addslashes($package_name);
                    if ($d->uid > 0) {
                        $u = get_user_by('ID', $d->uid);
                        echo "{$d->pid},\"{$package_name}\",{$d->uid},\"{$u->display_name}\",\"{$u->user_email}\",{$d->oid},{$d->year}-{$d->month}-{$d->day},{$d->timestamp}\r\n";
                    } else
                        echo "{$d->pid},\"{$package_name}\",-,\"-\",\"-\",{$d->oid},{$d->year}-{$d->month}-{$d->day},{$d->timestamp}\r\n";
                }
                ob_flush();
            }
            die();
        }
    }
}
