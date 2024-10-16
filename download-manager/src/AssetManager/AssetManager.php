<?php
/*
Asset Manager for WordPress Download Manager
Author: Shahjada
Version: 1.0.0
*/

namespace WPDM\AssetManager;

use WPDM\__\__;
use WPDM\__\Crypt;
use WPDM\__\Messages;
use WPDM\__\Session;
use WPDM\__\Template;
use WPDM\__\FileSystem;

define('WPDMAM_NONCE_KEY', 'r2pj@|k5.|;B1?n9MqB)%<w2Yz|XZx(alt@Aoc|~,|93lei|wR.R9~5X4D$ZH&*7U}Ot');


class AssetManager
{
    private static $instance;
    private $dir, $url, $root;
    private $mime_type;
    public $featuredAssets = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self;
            self::$instance->dir = dirname(__FILE__);
            self::$instance->url = WP_PLUGIN_URL . '/' . basename(self::$instance->dir);
            self::$instance->actions();
            //print_r($_SESSION);
        }
        return self::$instance;
    }

	public static function root($path = '')
	{
		if(!function_exists('get_home_path'))
			include_once ABSPATH.'wp-admin/includes/file.php';
		$current_user = wp_get_current_user();
		$fbRoot = get_option('_wpdm_file_browser_root');
		if(!$fbRoot) $fbRoot = get_home_path();
		$userRoot = current_user_can(WPDM_ADMIN_CAP) ? trailingslashit($fbRoot) : trailingslashit(UPLOAD_DIR . $current_user->user_login);

		//Create user root if dir doesn't already exist
		if(!current_user_can(WPDM_ADMIN_CAP) && !file_exists($userRoot))
		{
			@mkdir($userRoot, 0775, true);
			FileSystem::blockHTTPAccess($userRoot);
		}

		$userRoot = self::fsPath($userRoot);
		$userRootExt = $path !== '' ? $userRoot.$path : $userRoot;
		$userRootExt = preg_replace(array('/\.\.\//', '/\.\//', '/\/\.\.$/'), "", $userRootExt);
		$realUserRootExt = realpath($userRootExt);
		if($realUserRootExt) $realUserRootExt = self::fsPath($realUserRootExt);
		$userRootExt = self::fsPath($userRootExt);

		$userRootExt = apply_filters('wpdm_asset_manager_root', $userRootExt, $path);

		if(substr_count($userRootExt, $userRoot) == 0 || !$realUserRootExt || substr_count($realUserRootExt, $userRoot) === 0) return "[INVALID_PATH]";

		if (is_dir($userRootExt)) $userRootExt = trailingslashit($userRootExt);

		return $userRootExt;
	}

    private function actions()
    {

        add_action('wp', array($this, 'assetPage'), 1);
        add_action('init', array($this, 'assetPicker'), 1);
        add_action('init', array($this, 'download'), 1);

        //add_action('wp_ajax_wpdm_fm_file_upload', array($this,'uploadFile'));
        add_action('wp_ajax_wpdm_mkdir', array($this, 'mkDir'));
        add_action('wp_ajax_wpdm_newfile', array($this, 'newFile'));
        add_action('wp_ajax_wpdm_scandir', array($this, 'scanDir'));
        add_action('wp_ajax_wpdm_createzip', array($this, 'createZip'));
        add_action('wp_ajax_wpdm_unzipit', array($this, 'unZip'));
        add_action('wp_ajax_wpdm_openfile', array($this, 'openFile'));
        add_action('wp_ajax_wpdm_feature_asset', array($this, 'featureAsset'));
        add_action('wp_ajax_wpdm_filesettings', array($this, 'fileSettings'));
        add_action('wp_ajax_wpdm_changePerms', array($this, 'changePerms'));
        add_action('wp_ajax_wpdm_unlink', array($this, 'deleteItem'));
        add_action('wp_ajax_wpdm_rename', array($this, 'renameItem'));
        add_action('wp_ajax_wpdm_savefile', array($this, 'saveFile'));
        add_action('wp_ajax_wpdm_copypaste', array($this, 'copyItem'));
        add_action('wp_ajax_wpdm_cutpaste', array($this, 'moveItem'));
        add_action('wp_ajax_wpdm_addcomment', array($this, 'addComment'));
        add_action('wp_ajax_wpdm_newsharelink', array($this, 'addShareLink'));
        add_action('wp_ajax_wpdm_getlinkdet', array($this, 'getLinkDet'));
        add_action('wp_ajax_wpdm_updatelink', array($this, 'updateLink'));
        add_action('wp_ajax_wpdm_deletelink', array($this, 'deleteLink'));

        add_action('wpdm_after_upload_file', array($this, 'upload'), 1);
        //add_action('wp_enqueue_scripts', array($this,'siteScripts'));
        add_action('admin_enqueue_scripts', array($this, 'adminScripts'));

        //add_shortcode('wpdm_asset_manager', array($this,'_assetManager'));
        add_shortcode('wpdm_asset', array($this, 'wpdmAsset'));
        add_shortcode('wpdm_asset', array($this, 'wpdmAsset'));

	    add_shortcode("wpdm_dir_view", [$this, 'dirView']);
	    add_action("wp_ajax_explore_dir", [$this, 'exploreDir']);
	    add_action("wp_ajax_nopriv_explore_dir", [$this, 'exploreDir']);
	    add_action("wp_ajax_wpdm_dir_fileinfo", [$this, 'fileInfo']);
	    add_action("wp_ajax_nopriv_wpdm_dir_fileinfo", [$this, 'fileInfo']);


	    //add_filter('wpdm_frontend', array($this,'frontendFileManagerTab'));

        if (is_admin()) {
            add_action('admin_menu', array($this, 'adminMenu'), 10);
        }

    }

    function validateRequest($key, $admin_only = false)
    {
        if (isset($_REQUEST[$key]) && !wp_verify_nonce($_REQUEST[$key], WPDMAM_NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __("Error! Session Expired. Try refreshing page.", "download-manager")));
        check_ajax_referer(WPDMAM_NONCE_KEY, $key);
        if($admin_only) {
            if (!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __("<b>Unauthorized Action!</b><br/>Execution is cancelled by the system.", "download-manager")));
        } else {
            if (!current_user_can('upload_files') || !current_user_can('access_server_browser')) die('Error! Unauthorized Access.');
        }
    }

    function assetPicker()
    {
        global $wp_query;
        if (wpdm_query_var('assetpicker', 'int') === 1) {
            if(!current_user_can('access_server_browser')) Messages::fullPage("Error", esc_attr__( 'You are not authorized to access this page', 'download-manager' ), 'error');
            http_response_code(200);
            include Template::locate("asset-manager-picker.php", __DIR__.'/views');
            die();
        }
    }

    function assetPage()
    {
        global $wp_query;
        $url = parse_url(__::valueof($_SERVER, 'REQUEST_URI', ['validate' => 'escs']));
        if (preg_match('/wpdm\-asset\/([^\/]+)/', wpdm_valueof($url, 'path'), $matches)) {
            $asset = new Asset();
            $asset->resolveKey($matches[1]);
			$expire = wpdm_valueof(wpdm_valueof($asset,'links/'.$asset->activeLinkKey), 'access/expire');
			if($expire)
				$expire = strtotime(str_replace('T', ' ', $expire));
            http_response_code(200);
            include Template::locate("asset-viewer.php", __DIR__.'/views');
            die();
        }
    }

    function siteScripts()
    {
        global $post;

        if (is_single() && !has_shortcode($post->post_content, '[wpdm_asset_manager]')) return;

        $cm_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/plain'));
        wp_localize_script('jquery', 'wpdmcm_settings', $cm_settings);

        wp_enqueue_script('wp-theme-plugin-editor');
        wp_enqueue_style('wp-codemirror');

        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-autocomplete');
    }


    function adminScripts($hook)
    {
        if ($hook !== 'wpdmpro_page_wpdm-asset-manager') return;

        $cm_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/plain'));
        wp_localize_script('jquery', 'wpdmcm_settings', $cm_settings);

        wp_enqueue_script('wp-theme-plugin-editor');
        wp_enqueue_style('wp-codemirror');

        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-autocomplete');

    }

    public function download()
    {
        if (isset($_REQUEST['asset']) && isset($_REQUEST['key'])) {
            $asset = new Asset();
            $asset->get(wpdm_query_var('asset', 'int'));
            if (wp_verify_nonce($_REQUEST['key'], $asset->path))
                $asset->download();
            else
                \WPDM\__\Messages::error(apply_filters('wpdm_asset_download_link_expired_message', __("Download link is expired! Go back and Refresh the page to regenerate download link", "download-manager")), 1);
            die();
        }
        if (isset($_REQUEST['wpdmfmdl']) && is_user_logged_in()) {
            global $current_user;
            $file = AssetManager::root(Crypt::decrypt(wpdm_query_var('wpdmfmdl')));
            if (!$file) \WPDM\__\Messages::error("File Not Found!", 1);
            $params = wpdm_query_var('play') ? ['play' => wpdm_query_var('play')] : null;
            \WPDM\__\FileSystem::downloadFile($file, wp_basename($file), 10240, false, $params);
            die();
        }
    }

    public static function getDir()
    {
        return self::$instance->dir;
    }

    public static function getUrl()
    {
        return self::$instance->url;
    }

    public function adminMenu()
    {
        add_submenu_page('edit.php?post_type=wpdmpro', __("Asset Manager", 'download-manager'), __('Asset Manager', 'download-manager'), 'access_server_browser', 'wpdm-asset-manager', array($this, '_assetManager'));
    }

    static function mimeType($file)
    {
        $contenttype = wp_check_filetype($file);
        $contenttype = $contenttype['type'];
        if (!$contenttype) {
            $file = explode(".", $file);
            $contenttype = "unknown/" . end($file);
        }
        return $contenttype;
    }

    function mkDir()
    {
        global $current_user;
        if (isset($_REQUEST['__wpdm_mkdir']) && !wp_verify_nonce($_REQUEST['__wpdm_mkdir'], WPDMAM_NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __("Error! Session Expired. Try refreshing page.", "download-manager")));
        check_ajax_referer(WPDMAM_NONCE_KEY, '__wpdm_mkdir');
        if (!current_user_can('upload_files') || !current_user_can('access_server_browser')) die('Error! Unauthorized Access.');
        $root = AssetManager::root();
        $relpath = Crypt::decrypt(wpdm_query_var('path'));
        $path = AssetManager::root($relpath);
        if (!$path) wp_send_json(array('success' => false, 'message' => __("Error! Unauthorized Path.", "download-manager")));
        $name = wpdm_query_var('name', 'filename');
        mkdir($path . $name);
        wp_send_json(array('success' => true, 'path' => $path . $name));
    }

    function newFile()
    {
        global $current_user;
        if (isset($_REQUEST['__wpdm_newfile']) && !wp_verify_nonce($_REQUEST['__wpdm_newfile'], WPDMAM_NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __("Error! Session Expired. Try refreshing page.", "download-manager")));
        check_ajax_referer(WPDMAM_NONCE_KEY, '__wpdm_newfile');
        if (!current_user_can('upload_files') || !current_user_can('access_server_browser')) die('Error! Unauthorized Access.');
        $root = AssetManager::root();
        $relpath = Crypt::decrypt(wpdm_query_var('path'));
        $path = AssetManager::root($relpath);
        if (!$path) wp_send_json(array('success' => false, 'message' => __("Error! Unauthorized Path.", "download-manager")));

        $name = wpdm_query_var('name');
        //Check file is in allowed types
        if (WPDM()->fileSystem->isBlocked($name)) wp_send_json(array('success' => false, 'message' => __("Error! FileType is not allowed.", "download-manager")));

        $ret = file_put_contents($path . $name, '');
        if ($ret !== false)
            wp_send_json(array('success' => true, 'filepath' => $path . $name));
        else
            wp_send_json(array('success' => false, 'filepath' => $path . $name));

    }

    function scanDir()
    {
        if (!isset($_REQUEST['__wpdm_scandir']) || !wp_verify_nonce($_REQUEST['__wpdm_scandir'], NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __("Error! Session Expired. Try refreshing page.", "download-manager")));
        check_ajax_referer(NONCE_KEY, '__wpdm_scandir');
        //if (!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __("Error! You are not authorized to execute this action.", "download-manager")));
        if (!current_user_can('upload_files') || !current_user_can('access_server_browser')) die('Error! Unauthorized Access.');
        global $current_user;
	    $keyword = false;
        $root = AssetManager::root();
        if(wpdm_query_var('path') !== '__featured_assets__') {
            $relpath = Crypt::decrypt(wpdm_query_var('path'));
            $path = AssetManager::root($relpath);
            if (!$path) wp_send_json(array('success' => false, 'message' => __("Error! Unauthorized Path.", "download-manager")));
			if(!wpdm_query_var('keyword', 'txt'))
				$items = scandir($path, SCANDIR_SORT_ASCENDING);
			else {
				$keyword = wpdm_query_var('keyword', 'txt');
				$items = glob( "{$path}*{$keyword}*");
				foreach ($items as &$item) {
					$item = str_replace($path, "", $item);
				}
			}
        } else {
            $items = $this->featuredAssets();
            $path = AssetManager::root();
            foreach ($items as $id => $item) {
                $items[$id] = str_replace($path, "", $item);
            }
        }
        if(!is_array($items)) $items = [];
        $items = array_diff($items, ['.', '..']);

	    if ((int)wpdm_query_var('dirs') !== 1) {
		    $page           = wpdm_query_var( 'sdpage', 'int' );
		    $page           = $page < 1 ? 1 : $page;
		    $items_per_page = $keyword ? 90 : 50;
		    $total_pages    = $keyword ? 1 : (int) ceil( count( $items ) / $items_per_page );
		    $start          = $keyword ? 0 : ( $page - 1 ) * $items_per_page;
		    $items          = array_slice( $items, $start, $items_per_page );
	    }

        $_items = [];
        $_dirs = [];
        update_user_meta(get_current_user_id(), 'working_dir', $path);
        foreach ($items as $item) {

            $item_label = $item;
            $item_label = esc_attr($item_label);
            //$item_label = strlen($item_label) > 30 ? substr($item_label, 0, 15) . "..." . substr($item_label, strlen($item_label) - 15) : $item_label;
            $ext = explode('.', $item);
            $ext = end($ext);
            $icon = \WPDM\__\FileSystem::fileTypeIcon($ext);
            $type = is_dir($path . $item) ? 'dir' : 'file';
            $note = is_dir($path . $item) ? (count(scandir($path . $item)) - 2) . ' items' : number_format((filesize($path . $item) / 1024), 2) . ' KB';
            $rpath = str_replace($root, "", $path . $item);
            $wp_rel_path = str_replace(UPLOAD_DIR, '', $path . $item);
            $wp_rel_path = str_replace(ABSPATH, '', $wp_rel_path);
            $_rpath = Crypt::encrypt($rpath);
            if ($type === 'dir') {
                $_dirs[] = array('item_label' => $item_label, 'item' => $item, 'icon' => $icon, 'type' => $type, 'note' => $note, 'path' => $_rpath, 'id' => md5($rpath));
            } else {
                $contenttype = function_exists('mime_content_type') && is_readable($path . $item) ? mime_content_type($path . $item) : self::mimeType($item);
                $_items[] = array('item_label' => $item_label, 'item' => $item, 'icon' => $icon, 'type' => $type, 'contenttype' => $contenttype, 'note' => $note, 'path_on' => $path . $item, 'wp_rel_path' => $wp_rel_path, 'path' => $_rpath, 'id' => md5($rpath), 'featured' => $this->isFeatured($path . $item));
            }

        }

        $allitems = $_dirs;
        foreach ($_items as $_item) {
            $allitems[] = $_item;
        }
        $parts = explode("/", $relpath);
        $breadcrumb[] = "<i class='fa fa-hdd color-purple'></i><a href='#' class='media-folder' data-path=''>" . __("Home", "download-manager") . "</a>";
        $topath = array();
        foreach ($parts as $part) {
            $topath[] = $part;
            $rpath = Crypt::encrypt(implode("/", $topath));
            $breadcrumb[] = "<a href='#' class='media-folder' data-path='{$rpath}'>" . esc_attr($part) . "</a>";
        }
        $breadcrumb = implode("<i class='fa fa-folder-open'></i>", $breadcrumb);
        if ((int)wpdm_query_var('dirs') === 1)
            wp_send_json($_dirs);
        else
            wp_send_json(array('success' => true, 'total_pages' => $total_pages, 'current_page' => $page, 'items_per_page' => $items_per_page, 'items' => $allitems, 'breadcrumb' => $breadcrumb, 'root' => $root, WPDM_ADMIN_CAP => current_user_can(WPDM_ADMIN_CAP), 'roles' => $current_user->roles));
        die();
    }

    function createZip()
    {
        if (!isset($_REQUEST['__wpdm_createzip']) || !wp_verify_nonce($_REQUEST['__wpdm_createzip'], WPDMAM_NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __("Error! Session Expired. Try refreshing page.", "download-manager")));
        check_ajax_referer(WPDMAM_NONCE_KEY, '__wpdm_createzip');
        //if (!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __("Error! You are not authorized to execute this action.", "download-manager")));
        if (!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __("<b>Unauthorized Action!</b><br/>Execution is cancelled by the system.", "download-manager")));
        global $current_user;
        $root = AssetManager::root();
        $relpath = Crypt::decrypt(wpdm_query_var('dir_path'));
        $path = AssetManager::root($relpath);
        if (!$path) wp_send_json(array('success' => false, 'message' => __("Error! Unauthorized Path.", "download-manager")));
        $zipped = FileSystem::zipDir($path);
        rename($zipped, untrailingslashit($path) . ".zip");
        wp_send_json(array('success' => true, 'zipped' => untrailingslashit($path) . ".zip", 'refresh' => true));
        die();
    }

    function unZip(){
        if (!isset($_REQUEST['__wpdm_unzipit']) || !wp_verify_nonce($_REQUEST['__wpdm_unzipit'], WPDMAM_NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __("Error! Session Expired. Try refreshing page.", "download-manager")));
        check_ajax_referer(WPDMAM_NONCE_KEY, '__wpdm_unzipit');
        //if (!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __("Error! You are not authorized to execute this action.", "download-manager")));
        if (!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __("<b>Unauthorized Action!</b><br/>Execution is cancelled by the system.", "download-manager")));
        global $current_user;
        $root = AssetManager::root();
        $relpath = Crypt::decrypt(wpdm_query_var('dir_path'));
        $path = AssetManager::root($relpath);
        if (!$path || FileSystem::mime_type($path) !== 'application/zip') wp_send_json(array('success' => false, 'message' => __("Error! Unauthorized Path.", "download-manager")));
        FileSystem::unZip($path);
        wp_send_json(array('success' => true, 'refresh' => true));
        die();
    }

    function deleteItem()
    {

        if (!isset($_REQUEST['__wpdm_unlink']) || !wp_verify_nonce($_REQUEST['__wpdm_unlink'], WPDMAM_NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __("Error! Session Expired. Try refreshing page.", "download-manager")));
        check_ajax_referer(WPDMAM_NONCE_KEY, '__wpdm_unlink');
        if (!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __("Error! You are not authorized to execute this action.", "download-manager")));

        $relpath = Crypt::decrypt(wpdm_query_var('delete'));
        $path = AssetManager::root($relpath);
        if (!$path) wp_send_json(array('success' => false, 'message' => __("Error! Unauthorized Path.", "download-manager")));
        if (is_dir($path))
            $this->rmDir($path);
        else
            unlink($path);

        Asset::delete($path);

        die($path);
    }

    function openFile()
    {
        if (!isset($_REQUEST['__wpdm_openfile']) || !wp_verify_nonce($_REQUEST['__wpdm_openfile'], WPDMAM_NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __("Error! Session Expired. Try refreshing page.", "download-manager")));
        check_ajax_referer(WPDMAM_NONCE_KEY, '__wpdm_openfile');
        if (!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __("Error! You are not authorized to execute this action.", "download-manager")));
        $relpath = Crypt::decrypt(wpdm_query_var('file'));
        $path = AssetManager::root($relpath);
        if (!$path) wp_send_json(array('success' => false, 'message' => __("Error! Unauthorized Path.", "download-manager")));
        if (file_exists($path) && is_file($path)) {
            $cid = uniqid();
            Session::set($cid, $path);
            $type = function_exists('mime_content_type') ? mime_content_type($path) : self::mimeType($path);
            $ext = explode(".", $path);
            $ext = end($ext);
            $ext = strtolower($ext);

            if (strstr("__{$type}", "text/") || in_array($ext, array('txt', 'csv', 'css', 'html', 'log')))
                wp_send_json(array('content' => file_get_contents($path), 'id' => $cid));
            else if (strstr("__{$type}", "svg"))
                wp_send_json(array('content' => '', 'embed' => file_get_contents($path), 'id' => $cid));
            else {
                $file = Crypt::decrypt(wpdm_query_var('file'));
                $file = basename($file);
                $fetchurl = home_url("/?wpdmfmdl=" . wpdm_query_var('file'));
                if (strstr("__{$type}", "image/")) {
                    $embed_code = "<img src='$fetchurl' />";
                    wp_send_json(array('content' => '', 'embed' => $embed_code, 'id' => $cid));
                }
                if (strstr("__{$type}", "audio/")) {
                    $embed_code = do_shortcode("[audio src='{$fetchurl}&file={$file}']");
                    wp_send_json(array('content' => '', 'embed' => $embed_code, 'id' => $cid));
                }
                if (strstr("__{$type}", "video/")) {
                    $embed_code = do_shortcode("[video src='{$fetchurl}&file={$file}']");
                    wp_send_json(array('content' => '', 'embed' => $embed_code, 'id' => $cid));
                }
                if ($type === 'application/pdf') {
                    //$embed_code = do_shortcode("[video src='{$fetchurl}&file={$file}']");
                    $embed_code = "<iframe style='width: 100%;height: 100%;' src='{$fetchurl}&file={$file}&play=1'></iframe><style>#filecontent_alt{ padding: 0 !important; overflow: hidden; }</style>";
                    wp_send_json(array('content' => '', 'embed' => $embed_code, 'id' => $cid));
                }
            }


        } else {
            wp_send_json(array('content' => 'Failed to open file!', 'id' => uniqid()));
            die();
        }

    }

    function featuredAssets()
    {
        $features_assets = get_option("__wpdm_featured_assets");
        $features_assets = json_decode($features_assets, true);
        if(!is_array($features_assets)) $features_assets = [];
        return $features_assets;
    }

    function isFeatured($assetID_or_filePath)
    {
        if(!$assetID_or_filePath) return false;
        if(!is_array($this->featuredAssets))
            $this->featuredAssets = $this->featuredAssets();
        return (isset($this->featuredAssets[$assetID_or_filePath]) || in_array($assetID_or_filePath, $this->featuredAssets));
    }

    function featureAsset()
    {

        if (!isset($_REQUEST['__wpdm_feature_asset']) || !wp_verify_nonce($_REQUEST['__wpdm_feature_asset'], WPDMAM_NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __("Error! Session Expired. Try refreshing page.", "download-manager")));
        check_ajax_referer(WPDMAM_NONCE_KEY, '__wpdm_feature_asset');
        if (!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __("Error! Unauthorized Access.", "download-manager")));
        $relpath = Crypt::decrypt(wpdm_query_var('file'));
        $path = AssetManager::root($relpath);
        if (!$path) wp_send_json(array('success' => false, 'message' => __("Error! Unauthorized Path.", "download-manager")));
        if (file_exists($path)) {
            $asset = new Asset($path);
            $features_assets = $this->featuredAssets();
            $found_at = array_search($path, $features_assets);

            if($found_at > 0)
                unset($features_assets[$found_at]);
            else
                $features_assets[$asset->ID] = $path;

            $features_assets = json_encode($features_assets);
            update_option("__wpdm_featured_assets", $features_assets, false);
            wp_send_json(['success' => true, 'featured' => !$found_at ]);
        } else {
            wp_send_json(array('success' => false, 'message' => __("Error! Unauthorized Path.", "download-manager")));
            die();
        }

    }

    function fileSettings()
    {

        /*if (!isset($_REQUEST['__wpdm_filesettings']) || !wp_verify_nonce($_REQUEST['__wpdm_filesettings'], WPDMAM_NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __("Error! Session Expired. Try refreshing page.", "download-manager")));
        check_ajax_referer(WPDMAM_NONCE_KEY, '__wpdm_filesettings');
        if (!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __("Error! Unauthorized Access.", "download-manager")));*/
        $this->validateRequest('__wpdm_filesettings', true);
        $relpath = Crypt::decrypt(wpdm_query_var('file'));
        $path = AssetManager::root($relpath);
        if (!$path) wp_send_json(array('success' => false, 'message' => __("Error! Unauthorized Path.", "download-manager")));
        if (file_exists($path)) {
            $asset = new Asset($path);
            $asset->filemods = [
                'user'      => [ 'read' => ($asset->permissions['user'] >= 4), 'write' =>  in_array($asset->permissions['user'], [2, 3, 6, 7]), 'execute' =>  in_array($asset->permissions['user'], [1, 3, 5, 7])],
                'group'     => [ 'read' => ($asset->permissions['group'] >= 4), 'write' =>  in_array($asset->permissions['group'], [2, 3, 6, 7]), 'execute' =>  in_array($asset->permissions['group'], [1, 3, 5, 7])],
                'world'     => [ 'read' => ($asset->permissions['world'] >= 4), 'write' =>  in_array($asset->permissions['world'], [2, 3, 6, 7]), 'execute' =>  in_array($asset->permissions['world'], [1, 3, 5, 7])]
            ];
            $asset->fileEnc = wpdm_query_var('file');
            wp_send_json($asset);
        } else {
            wp_send_json(array('success' => false, 'message' => __("Error! Unauthorized Path.", "download-manager")));
            die();
        }

    }

    function changePerms()
    {
        $this->validateRequest('__wpdm_change_fileperms', true);
        $relpath = Crypt::decrypt(wpdm_query_var('__file'));
        $path = AssetManager::root($relpath);
        $perms = base_convert(implode("", wpdm_query_var('__perms')), 8, 10);
        chmod($path, $perms);
        wp_send_json(array('success' => true, 'message' => __("Permissions are changed!", "download-manager")));
    }


    function addComment()
    {
        if (!isset($_REQUEST['__wpdm_addcomment']) || !wp_verify_nonce($_REQUEST['__wpdm_addcomment'], NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __("Error! Session Expired. Try refreshing page.", "download-manager")));
        check_ajax_referer(NONCE_KEY, '__wpdm_addcomment');
        if (!is_user_logged_in()) wp_send_json(array('success' => false, 'message' => __("Error! You are not authorized to execute this action.", "download-manager")));

        $asset_id = wpdm_query_var('assetid', 'int');
        $asset = new Asset();
        $asset->get($asset_id)->newComment(wpdm_query_var('comment', 'txts'), get_current_user_id())->save();
        wp_send_json($asset->comments);
    }

    function addShareLink()
    {
        if (!isset($_REQUEST['__wpdm_newsharelink']) || !wp_verify_nonce($_REQUEST['__wpdm_newsharelink'], WPDMAM_NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __("Error! Session Expired. Try refreshing page.", "download-manager")));
        check_ajax_referer(WPDMAM_NONCE_KEY, '__wpdm_newsharelink');
        if (!current_user_can('access_server_browser')) wp_send_json(array('success' => false, 'message' => __("Error! You are not authorized to execute this action.", "download-manager")));

        $asset_ID = wpdm_query_var('asset', 'int');
        $asset = new Asset();
        $asset->get($asset_ID)->newLink(wpdm_query_var('access'))->save();
        wp_send_json($asset->links);
    }

    function getLinkDet()
    {
        if (!isset($_REQUEST['__wpdm_getlinkdet']) || !wp_verify_nonce($_REQUEST['__wpdm_getlinkdet'], WPDMAM_NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __("Error! Session Expired. Try refreshing page.", "download-manager")));
        check_ajax_referer(WPDMAM_NONCE_KEY, '__wpdm_getlinkdet');
        if (!current_user_can('access_server_browser')) wp_send_json(array('success' => false, 'message' => __("Error! You are not authorized to execute this action.", "download-manager")));

        $link_ID = wpdm_query_var('linkid', 'int');
        $link = Asset::getLink($link_ID);
        wp_send_json($link);
    }

    function updateLink()
    {
        if (!isset($_REQUEST['__wpdm_updatelink']) || !wp_verify_nonce($_REQUEST['__wpdm_updatelink'], WPDMAM_NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __("Error! Session Expired. Try refreshing page.", "download-manager")));
        check_ajax_referer(WPDMAM_NONCE_KEY, '__wpdm_updatelink');
        if (!current_user_can('access_server_browser')) wp_send_json(array('success' => false, 'message' => __("Error! You are not authorized to execute this action.", "download-manager")));

        $link_ID = wpdm_query_var('ID', 'int');
        $access = wpdm_query_var('access');
        if (!isset($access['roles'])) $access['roles'] = array();
        if (!isset($access['users'])) $access['users'] = array();
        $link = Asset::updateLink(array('access' => json_encode($access)), $link_ID);
        wp_send_json(array('success' => $link));
    }

    function deleteLink()
    {
        if (!isset($_REQUEST['__wpdm_deletelink']) || !wp_verify_nonce($_REQUEST['__wpdm_deletelink'], WPDMAM_NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __("Error! Session Expired. Try refreshing page.", "download-manager")));
        check_ajax_referer(WPDMAM_NONCE_KEY, '__wpdm_deletelink');
        if (!current_user_can('access_server_browser')) wp_send_json(array('success' => false, 'message' => __("Error! You are not authorized to execute this action.", "download-manager")));

        $link_ID = wpdm_query_var('linkid', 'int');
        $link = Asset::deleteLink($link_ID);
        wp_send_json(array('success' => $link));

    }

    function saveFile()
    {
        if (!isset($_REQUEST['__wpdm_savefile']) || !wp_verify_nonce($_REQUEST['__wpdm_savefile'], WPDMAM_NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __("Error! Session Expired. Try refreshing page.", "download-manager")));
        check_ajax_referer(WPDMAM_NONCE_KEY, '__wpdm_savefile');
        if (!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __("Error! You are not authorized to execute this action.", "download-manager")));

        $ofilepath = Session::get(wpdm_query_var('opened'));
        $relpath = Crypt::decrypt(wpdm_query_var('file'));
        $path = AssetManager::root($relpath);
        if (!$path) wp_send_json(array('success' => false, 'message' => __("Error! Unauthorized Path.", "download-manager")));

        if (WPDM()->fileSystem->isBlocked($path)) wp_send_json(array('success' => false, 'message' => __("Error! FileType is not allowed.", "download-manager")));

        if (file_exists($path) && is_file($path)) {
            $content = stripslashes_deep($_POST['content']);
            file_put_contents($path, $content);
            wp_send_json(array('success' => true, 'message' => 'Saved Successfully.', 'type' => 'success'));
        } else {
            wp_send_json(array('success' => false, 'message' => __("Error! Couldn't open file ( $path ).", "download-manager")));
        }

    }

    function renameItem()
    {
        if (!isset($_REQUEST['__wpdm_rename']) || !wp_verify_nonce($_REQUEST['__wpdm_rename'], WPDMAM_NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __("Error! Session Expired. Try refreshing page.", "download-manager")));
        check_ajax_referer(WPDMAM_NONCE_KEY, '__wpdm_rename');
        if (!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __("Error! You are not authorized to execute this action.", "download-manager")));
        global $current_user;
        $asset = new Asset();
        $asset->get(wpdm_query_var('assetid', 'int'));
        $root = AssetManager::root();
        $oldpath = $asset->path;
        $newpath = dirname($asset->path) . '/' . str_replace(array("/", "\\", "\"", "'"), "_", wpdm_query_var('newname'));

        if (WPDM()->fileSystem->isBlocked(wpdm_query_var('newname'))) wp_send_json(array('success' => false, 'message' => __("Error! FileType is not allowed.", "download-manager")));

        if (!strstr($newpath, $root)) die('Error!' . $newpath . " -- " . $root);
        rename($oldpath, $newpath);
        $asset->updatePath($newpath);
        wp_send_json($asset);
    }

    function moveItem()
    {
        if (!isset($_REQUEST['__wpdm_cutpaste']) || !wp_verify_nonce($_REQUEST['__wpdm_cutpaste'], WPDMAM_NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __("Error! Session Expired. Try refreshing page.", "download-manager")));
        check_ajax_referer(WPDMAM_NONCE_KEY, '__wpdm_cutpaste');
        if (!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __("Error! You are not authorized to execute this action.", "download-manager")));

        $opath = explode("|||", wpdm_query_var('source'));
        $olddir = Crypt::decrypt($opath[0]);
        $file = end($opath);

        //Check file is in allowed types
        if (WPDM()->fileSystem->isBlocked($file)) wp_send_json(array('success' => false, 'message' => __("Error! FileType is not allowed.", "download-manager")));


        $oldpath = AssetManager::root($olddir . '/' . $file);
        $newpath = AssetManager::root(Crypt::decrypt(wpdm_query_var('dest'))) . $file;
        if (!$oldpath) wp_send_json(array('success' => false, 'message' => __("Invalid source path", "download-manager")));
        if (!$newpath) wp_send_json(array('success' => false, 'message' => __("Invalid destination path", "download-manager")));
        rename($oldpath, $newpath);

        $asset = new Asset();
        $asset = $asset->get($oldpath);
        if ($asset)
            $asset->updatePath($newpath);

        wp_send_json(array('success' => true, 'message' => __("File moved successfully", "download-manager")));
    }

    function copyItem()
    {
        if (!isset($_REQUEST['__wpdm_copypaste']) || !wp_verify_nonce($_REQUEST['__wpdm_copypaste'], WPDMAM_NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __("Error! Session Expired. Try refreshing page.", "download-manager")));
        check_ajax_referer(WPDMAM_NONCE_KEY, '__wpdm_copypaste');
        if (!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __("Error! You are not authorized to execute this action.", "download-manager")));
        global $current_user;
        $root = AssetManager::root();
        $opath = explode("|||", wpdm_query_var('source'));
        $olddir = Crypt::decrypt($opath[0]);
        $file = end($opath);
        $oldpath = AssetManager::root($olddir . '/' . $file);
        $newpath = AssetManager::root(Crypt::decrypt(wpdm_query_var('dest'))) . $file;
        if (!strstr($oldpath, $root)) wp_send_json(array('success' => false, 'message' => __("Invalid source path", "download-manager")));
        if (!strstr($newpath, $root)) wp_send_json(array('success' => false, 'message' => __("Invalid destination path", "download-manager")));

        //Check file is in allowed types
        if (WPDM()->fileSystem->isBlocked($newpath)) wp_send_json(array('success' => false, 'message' => __("Error! FileType is not allowed.", "download-manager")));

        copy($oldpath, $newpath);

        wp_send_json(array('success' => true, 'message' => __("File copied successfully", "download-manager")));
    }

    function rmDir($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->rmDir("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    function copyDir($src, $dst)
    {
        $src = realpath($src);
        $dir = opendir($src);

        $dst = realpath($dst) . '/' . basename($src);
        @mkdir($dst);

        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->copyDir($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    function frontendFileManagerTab($tabs)
    {
        $tabs['asset-manager'] = array('label' => 'Asset Manager', 'callback' => array($this, '_assetManager'), 'icon' => 'fa fa-copy');
        return $tabs;
    }

    function _assetManager()
    {

        include Template::locate("asset-manager-ui.php", __DIR__.'/views');

    }

    /**
     * Shortcode processor for [wpdm_asset ...$params]
     * @param $params
     * @return bool|mixed|string
     */
    function wpdmAsset($params)
    {
        if (!isset($params['id'])) return \WPDM\__\Messages::error(__("Asset not found!", "download-manager"), -1);
        $path_or_id = (int)$params['id'];
        $asset = new Asset();
        $asset->get($path_or_id);
        ob_start();
        include Template::locate("embed-asset.php", __DIR__.'/views');
        $content = ob_get_clean();
        return $content;
    }

    function upload($file)
    {
        if (isset($_REQUEST['__wpdmfm_upload']) && wp_verify_nonce($_REQUEST['__wpdmfm_upload'], NONCE_KEY)) {
            $working_dir = get_user_meta(get_current_user_id(), 'working_dir', true);
            $root = AssetManager::root();
            if (!strstr($working_dir, $root)) wp_send_json(array('success' => false));
            if ($working_dir != '') {
                $dest = $working_dir . basename($file);
                rename($file, $dest);
                wp_send_json(array('success' => true, 'src' => $file, 'file' => $dest));
            } else
                wp_send_json(array('success' => false));
        }
    }

    /**
     * Extract zip
     */
    function extract()
    {
        $relpath = Crypt::decrypt(wpdm_query_var('zipfile'));
        $zipfile = AssetManager::root($relpath);
        $reldest = Crypt::decrypt(wpdm_query_var('zipdest'));
        if ($reldest == '') $reldest = dirname($zipfile);
        $zipdest = AssetManager::root($reldest);
        if (!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __("Error! Only Administrator can execute this operation.", "download-manager")));
        if (!$zipfile || !stristr($zipfile, '.zip')) wp_send_json(array('success' => false, 'message' => __("Error! Unauthorized Path.", "download-manager")));
        if (!$zipdest) wp_send_json(array('success' => false, 'message' => __("Error! Invalid Destination Path.", "download-manager")));
        if (!class_exists('\ZipArchive')) wp_send_json(array('success' => false, 'message' => __('Please activate "zlib" in your server to perform zip operations', 'download-manager')));
        $zip = new \ZipArchive();
        if ($zip->open($zipfile) === TRUE) {
            $zip->extractTo($zipdest);
            $zip->close();
            wp_send_json(array('success' => true, 'message' => __("Unzipped successfully.", "download-manager")));
        } else {
            wp_send_json(array('success' => false, 'message' => __("Error! Couldn't open the zip file.", "download-manager")));
        }
    }

	static function fsPath($path)
	{
		$path = str_replace("\\", "/", $path);
		if(is_dir($path)) $path = trailingslashit($path);
		return $path;
	}


	/**
	 * Directory view shortcode
	 * @param $params
	 *
	 * @return false|string
	 */
	function dirView( $params ) {
		ob_start();
		$pid = -1;
		if(isset($params['login']) && (int)$params['login'] === 1) {
			return WPDM()->user->login->form();
		}
		if(isset($params['dir'])) {
			$dir      = Crypt::encrypt( $params['dir'] );// $params['dir'];
			$base_dir = $dir;
		} else if(isset($params['pid'])) {
			$dir = get_post_meta((int)$params['pid'], '__wpdm_package_dir', true);
			$base_dir = $dir;
			$pid = (int)$params['pid'];
		}
		if($dir)
			include Template::locate("dir-explorer.php", __DIR__.'/views');
		return ob_get_clean();
	}

	function exploreDir() {
		__::isAuthentic('wpdm_direx', WPDM_PUB_NONCE, 'read', false);
		$dir = Crypt::decrypt(wpdm_query_var('dir'));
		$dir = realpath($dir);
		$pid = (int)Crypt::decrypt(wpdm_query_var('pid'));

		// $pid =  -1 for stand-alone dir
		if($pid === 0)  wp_send_json(['success' => false, 'message' => __('[1] Invalid dir path!', WPDM_TEXT_DOMAIN)]);

		$base_dir = rtrim(Crypt::decrypt(wpdm_query_var('base')), "/");
		if(substr_count($dir, $base_dir) === 0) wp_send_json(['success' => false, 'message' => __('[2] Invalid dir path!', WPDM_TEXT_DOMAIN)]);

		$contents = FileSystem::scanDir($dir, false);
		$dir_contents = [];
		foreach ($contents as $path){
			$type = is_dir($path) ? 'dir' : substr(FileSystem::fileExt(basename($path)), 0, 4);
			$dir_contents[] = ['name' => basename($path), 'path' => Crypt::encrypt($path), 'type' => strtoupper($type), 'size' => ($type === 'dir' ? '&mdash;' : __::formatBytes(filesize($path)))];
		}

		$rel_dir = str_replace(rtrim($base_dir, '/'), '', $dir);
		$parts = explode("/", $rel_dir);
		$breadcrumb[] = "<i class='fa fa-hdd color-purple'></i><a href='#' class='breadcrumb-block' data-path='".Crypt::encrypt($base_dir)."'>" . __("Home", "download-manager") . "</a>";
		$topath = array();
		foreach ($parts as $part) {
			$topath[] = $part;
			$rpath = Crypt::encrypt(realpath($base_dir.'/'.implode("/", $topath)));
			if($part)
				$breadcrumb[] = "<a href='#' class='breadcrumb-block' data-path='{$rpath}'>" . esc_attr($part) . "</a>";
		}
		$breadcrumb = implode("<i class='fa fa-folder-open'></i>", $breadcrumb);

		wp_send_json(['success' => true, 'content' => $dir_contents, 'breadcrumb' => $breadcrumb]);
	}

	function fileInfo() {
		__::isAuthentic('wpdm_direx', WPDM_PUB_NONCE, 'read', false);
		$file = Crypt::decrypt(wpdm_query_var('file'));
		$file = realpath($file);
		$base_dir = rtrim(Crypt::decrypt(wpdm_query_var('base')), "/");
		if(substr_count($file, $base_dir) === 0) {
			Messages::error(__('Invalid dir path!', WPDM_TEXT_DOMAIN));
			die();
		}
		include Template::locate("file-info.php", __DIR__.'/views');
		die();

	}

}

