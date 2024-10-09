<?php

namespace WPDM\DropZone;

use WPDM\__\__;
use WPDM\__\__MailUI;
use WPDM\__\Crypt;
use WPDM\__\Email;
use WPDM\__\FileSystem;
use WPDM\__\Messages;
use WPDM\__\Template;
use WPDM\__\UI;
use WPDM\AssetManager\AssetManager;

define("DZF_STATUS", ['PENDING' => 0, 'NEW' => 1, 'DELIVERED' => 2, 'ACCEPTED' => 3, 'DECLINED' => 4]);
define("DZF_STATUS_TITLE", [ 'Pending', 'New', 'Delivered', 'Accepted', 'Declined' ]);

class DropZone {

	private $table;
	private $table_fr;

	private static $instance = null;
	private $enabled = false;

	public static function instance(){
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct()
	{
		global $wpdb;
		$this->table = "{$wpdb->prefix}ahm_dropzone";
		$this->table_fr = "{$wpdb->prefix}ahm_filerequests";

		add_shortcode('wpdm_dropzone', [$this, 'frontEnd']);
		add_action('wpdm_after_upload_file_frontend', [$this, 'upload']);
		add_action('wp_ajax_wpdmdz_delete_file', [$this, 'deleteFile']);
		add_action('wp_ajax_wpdmdz_file_info', [$this, 'fileInfo']);
		add_action('wp_ajax_wpdmdz_save_file_info', [$this, 'saveFileInfo']);
		add_action('wp_ajax_wpdmdz_add_comment', [$this, 'addComment']);
		add_action('wp_ajax_wpdmdz_file_accept', [$this, 'fileAccept']);
		add_action('wp_ajax_wpdmdz_file_decline', [$this, 'fileDecline']);
		add_action('wp_ajax_wpdmdz_get_file', [$this, 'getFileDetails']);
		add_action('wp_ajax_wpdmdz_file_request', [$this, 'createFileRequest']);
		add_action('wp_ajax_wpdmdz_fc_files', [$this, 'viewFiles']);
		add_action('wp_ajax_wpdmdz_fc_share', [$this, 'shareRequestLink']);
		add_action('wp_ajax_wpdmdz_share_file', [$this, 'shareFile']);
		add_action('wp_ajax_wpdmdz_share_rlink', [$this, 'sendRequestLink']);
		add_action('wp_ajax_wpdmdz_close_request', [$this, 'closeRequest']);
		add_action('wp_ajax_wpdmdz_delete_request', [$this, 'deleteRequest']);
		add_action('init', [$this, 'download']);
		add_action('wp', [$this, 'fileRequest']);

		add_filter('wpdm_dashboard_menu', [$this, 'dashboardMenu']);


		if (is_admin()) {
			add_action('admin_menu', array($this, 'adminMenu'), 10);
		}
	}

	function hasAccess()
	{
		if(!is_user_logged_in()) return false;
		$roles = get_option('__wpdm_dropzone_access');
		$roles = maybe_unserialize($roles);
		if(!is_array($roles)) $roles = [];
		$curoles = wp_get_current_user()->roles;
		if(!is_array($curoles)) $curoles = [];
		$allowed = count(array_intersect($roles, $curoles));
		return (bool) $allowed;
	}

	public function adminMenu()
	{
		add_submenu_page('edit.php?post_type=wpdmpro', __("DropZone", 'download-manager'), __('DropZone', 'download-manager'), 'access_server_browser', 'wpdm-dropzone', array($this, '_dropZone'));
		return true;
	}

	function dashboardMenu($items)
	{
		if(!$this->hasAccess()) return $items;
		$items['user']['items']['wpdm-dropzone'] = ['icon' => 'fa-solid fa-parachute-box', 'name' => __("DropZone", "download-manager"), 'callback' => [$this, 'frontEnd']];
		return $items;
	}

	function _dropZone()
	{
		include Template::locate("dropzone-admin.php", __DIR__.'/views');
	}

	/**
	 * @param $userID
	 *
	 * @return array|object|\stdClass[]|null
	 */
	function getFiles($userID = null)
	{
		if(!$userID)
			$userID = get_current_user_id();

		$userID = (int)$userID;

		global $wpdb;
		$files =  $wpdb->get_results("select * from {$this->table} where owner = '{$userID}' order by date desc");
		$files_indexed = [];
		foreach ($files as $file) {
			$file->comments = json_decode($file->comments);
			$file->icon = FileSystem::fileTypeIcon($file->file);
			$file->name = preg_replace("/[\d]+wpdm_/", "", basename($file->file));
			$files_indexed[$file->ID] = $file;
		}
		return $files_indexed;
	}

	/**
	 * @return array
	 */
	function allFiles($status = null, $page = 1, $items_per_page = 20)
	{
		global $wpdb;
		$page = $page ?: 1;
		$start = ($page - 1) * $items_per_page;
		$where = isset(DZF_STATUS[$status]) ? "where status = '".DZF_STATUS[$status]."'" : '';
		$files =  $wpdb->get_results("select * from {$this->table} {$where} order by date desc limit $start, $items_per_page");
		$files_indexed = [];
		foreach ($files as $file) {
			$file->comments = json_decode($file->comments);
			$file->icon = FileSystem::fileTypeIcon($file->file);
			$file->name = preg_replace("/[\d]+wpdm_/", "", basename($file->file));
			$files_indexed[$file->ID] = $file;
		}
		return $files_indexed;
	}

	/**
	 * @return array
	 */
	function fileRequests($status = null, $page = 1, $items_per_page = 20)
	{
		global $wpdb;
		$page = $page ?: 1;
		$start = ($page - 1) * $items_per_page;
		$where = $status === 'closed' ? "where ( expiry  <= ".time()." and expiry > 0 ) or closed = 1" : "where (expiry  > ".time()." or expiry = 0) and closed = 0";
		$files =  $wpdb->get_results("select * from {$this->table_fr} {$where} order by date desc limit $start, $items_per_page");
		$files_indexed = [];
		foreach ($files as $file) {
			$file->files = json_decode($file->files);
			$file->file_count = is_array($file->files) ? count($file->files) : 0;
			$files_indexed[$file->ID] = $file;
		}
		return $files_indexed;
	}

	function getFile($ID)
	{
		global $wpdb;
		$file = $wpdb->get_row("select * from {$this->table} where ID = '{$ID}'");
		$file->comments = json_decode($file->comments);
		$file->icon = FileSystem::fileTypeIcon($file->file);
		$file->name = preg_replace("/[\d]+wpdm_/", "", basename($file->file));
		return $file;
	}

	function frontEnd()
	{
		if(!is_user_logged_in()) return WPDM()->user->login->form();
		if(!$this->hasAccess()) return Messages::error(__('Sorry! You do not have access to DropZone', WPDM_TEXT_DOMAIN), -1);

		ob_start();
		include wpdm_tpl_path("dropzone.php", __DIR__.'/views');
		return ob_get_clean();
	}

	function upload($file)
	{
		if(wpdm_query_var('dropzone')) {
			global $wpdb;
			if(!$this->hasAccess()) wp_send_json(['success' => false, 'file' => basename($file), 'size' => WPDM()->fileSystem->fileSize($file), 'ID' => $wpdb->insert_id, 'icon' => FileSystem::fileTypeIcon($file)]);
			$wpdb->insert($this->table, ['file' => WPDM()->fileSystem->relPath($file), 'owner' => get_current_user_id(), 'date' => time(), 'status' => DZF_STATUS['NEW']]);
			do_action("wpdm_dropzone_file_uploaded", $wpdb->insert_id, $file);
			wp_send_json(['success' => true, 'file' => basename($file), 'size' => WPDM()->fileSystem->fileSize($file), 'ID' => $wpdb->insert_id, 'icon' => FileSystem::fileTypeIcon($file)]);
		}

		if(wpdm_query_var('filerequest')) {
			global $wpdb;
			if(!$this->hasAccess()) wp_send_json(['success' => false, 'file' => basename($file), 'size' => WPDM()->fileSystem->fileSize($file), 'ID' => $wpdb->insert_id, 'icon' => FileSystem::fileTypeIcon($file)]);
			$code = wpdm_query_var('code', 'txt');
			$request = $wpdb->get_row("select * from {$wpdb->prefix}ahm_filerequests where code = '{$code}'");
			$files = $request->files ? json_decode($request->files) : [];
			$files[] = $file;
			$wpdb->update("{$wpdb->prefix}ahm_filerequests", ['files' => json_encode($files)], ['ID' => $request->ID]);

			do_action("wpdm_requested_file_uploaded", $request->ID, $file);

			wp_send_json(['success' => true, 'file' => basename($file), 'size' => WPDM()->fileSystem->fileSize($file), 'ID' => $wpdb->insert_id, 'icon' => FileSystem::fileTypeIcon($file)]);
		}
	}


	function deleteFile()
	{
		__::isAuthentic('dznonce', WPDM_PUB_NONCE, 'upload_files');

		$ID = wpdm_query_var('file', 'int');
		$file = $this->getFile($ID);
		if((int)$file->owner === get_current_user_id() || current_user_can(WPDM_ADMIN_CAP)) {
			global $wpdb;
			$wpdb->delete( $this->table, [ 'ID' => $ID, 'owner' => get_current_user_id() ] );
			@unlink(WPDM()->fileSystem->locateFile($file->file));
			do_action("wpdm_dropzone_file_deleted", $file);
		}

		wp_send_json(['success' => true]);

	}

	function fileInfo()
	{
		$file = $this->getFile(wpdm_query_var('file', 'int'));
		if(!current_user_can(WPDM_ADMIN_CAP) && (int)$file->owner !== get_current_user_id()) {
			Messages::error('Yor do not have access to this file!', 1);
		}

		include wpdm_tpl_path('dz-file-info.php', __DIR__.'/views');
		die();
	}

	function saveFileInfo()
	{
		__::isAuthentic('dzfinonce', WPDM_PUB_NONCE, 'upload_files');

		$file = $this->getFile(wpdm_query_var('file', 'int'));
		if(!current_user_can(WPDM_ADMIN_CAP) && (int)$file->owner !== get_current_user_id()) {
			Messages::error('Yor do not have access to this file!', 1);
		}

		$status = DZF_STATUS['NEW'];
		if(wpdm_query_var('act', 'txt') === 'notify' && (int)$file->status <= DZF_STATUS['NEW']) {
			$user = wp_get_current_user();
			$message = 'Hi,<br/>You have received a file:<br/>'.__MailUI::panel("Filename", [$file->file]);
			if($file->title !== '')
				$message .= "<br/>".__MailUI::panel("Title", [$file->title]);

			if($file->description !== '')
				$message .= "<br/>".__MailUI::panel("Description", [$file->description]);

			$message .= "<br/>".__MailUI::panel("Sent By", [$user->display_name, $user->user_email]);

			$dropzone_admin = admin_url("edit.php?post_type=wpdmpro&page=wpdm-dropzone&user={$user->ID}&file={$file->ID}");
			$message .= "<div style='text-align:center'><a href='{$dropzone_admin}' style=\"display: block\" class=\"button\">View File</a></div>";


			$params = array('subject' => "You have received a file from {$user->display_name}", 'to_email' => get_option('admin_email'), 'message' => $message );
			Email::send("default", $params);
			$status = DZF_STATUS['DELIVERED'];
		}

		global $wpdb;
		$wpdb->update($this->table, ['title' => wpdm_query_var('file_title', 'txt'), 'description' => wpdm_query_var('file_description', 'kses'), 'status' => $status], ['ID' => $file->ID]);

		wp_send_json(['success' => true]);
	}

	function addComment(){
		__::isAuthentic('dzcnonce', WPDM_PUB_NONCE, 'upload_files');
		$fileID = wpdm_query_var('file', 'int');
		$file = $this->getFile($fileID);
		$comments = $file->comments;
		if(!is_array($comments)) $comments = [];
		$user = wp_get_current_user();
		$avatar = get_avatar($user->ID);
		$comment = array( 'comment' => wpdm_query_var('comment', 'kses'), 'user' => $user->ID, 'username' => $user->user_login, 'name' => $user->display_name, 'email' => $user->user_email, 'avatar' => $avatar, 'time' => time() );
		array_unshift($comments, $comment);
		global $wpdb;
		$wpdb->update($this->table, ['comments' => json_encode($comments)], ['ID' => $fileID]);

		$message = "Hi,<br/>There is a new comment on your file:" . __MailUI::panel("Message", [wpdm_query_var('comment', 'kses')]);
		$params = array('subject' => "You have received a file from {$user->display_name}", 'to_email' => get_option('admin_email'), 'message' => $message );
		Email::send("default", $params);

		wp_send_json($comments);
	}

	function fileAccept(){
		__::isAuthentic('dzfanonce', WPDM_PUB_NONCE, WPDM_ADMIN_CAP);
		$fileID = wpdm_query_var('file', 'int');
		$file = $this->getFile($fileID);
		$comments = json_decode($file->comments);
		if(!is_array($comments)) $comments = [];
		$user = wp_get_current_user();
		$owner = get_user_by('id', $file->owner);
		$avatar = get_avatar($user->ID);
		$comment = array( 'comment' => 'Your file is accepted!', 'user' => $user->ID, 'username' => $user->user_login, 'name' => $user->display_name, 'email' => $user->user_email, 'avatar' => $avatar, 'time' => time() );
		array_unshift($comments, $comment);
		global $wpdb;
		$wpdb->update($this->table, ['comments' => json_encode($comments), 'status' => DZF_STATUS['ACCEPTED']], ['ID' => $fileID]);

		$message = 'Hi,<br/>Your file has been accepted:<br/>'.__MailUI::panel("Filename", [$file->file]);
		if($file->title !== '')
			$message .= "<br/>".__MailUI::panel("Title", [$file->title]);

		if($file->description !== '')
			$message .= "<br/>".__MailUI::panel("Description", [$file->description]);

		$message .= "<br/>".__MailUI::panel("Accepted By", [$user->display_name]);

		$dropzone_url = get_permalink(get_option('__wpdm_dropzone_page'));
		$message .= "<div style='text-align:center'><a href='{$dropzone_url}' style=\"display: block\" class=\"button\">Go to dropzone</a></div>";


		$params = array('subject' => "Your file has been accepted", 'to_email' => $owner->user_email, 'message' => $message );
		Email::send("default", $params);

		wp_send_json(['success' => true, 'comments' => $comments]);
	}

	function fileDecline(){
		__::isAuthentic('dzfdnonce', WPDM_PUB_NONCE, WPDM_ADMIN_CAP);
		$fileID = wpdm_query_var('file', 'int');
		$file = $this->getFile($fileID);
		$comments = json_decode($file->comments);
		if(!is_array($comments)) $comments = [];
		$user = wp_get_current_user();
		$owner = get_user_by('id', $file->owner);
		$avatar = get_avatar($user->ID);
		$comment = array( 'comment' => 'Your file has been declined!', 'user' => $user->ID, 'username' => $user->user_login, 'name' => $user->display_name, 'email' => $user->user_email, 'avatar' => $avatar, 'time' => time() );
		array_unshift($comments, $comment);
		global $wpdb;
		$wpdb->update($this->table, ['comments' => json_encode($comments), 'status' => DZF_STATUS['DECLINED']], ['ID' => $fileID]);

		$message = 'Hi,<br/>Your file has been declined:<br/>'.__MailUI::panel("Filename", [$file->file]);
		if($file->title !== '')
			$message .= "<br/>".__MailUI::panel("Title", [$file->title]);

		if($file->description !== '')
			$message .= "<br/>".__MailUI::panel("Description", [$file->description]);

		$message .= "<br/>".__MailUI::panel("Declined By", [$user->display_name]);
		$message .= '<p>Go to dropzone to review additional comments</p>';
		$dropzone_url = get_permalink(get_option('__wpdm_dropzone_page'));
		$message .= "<div style='text-align:center'><a href='{$dropzone_url}' style=\"display: block\" class=\"button\">Go to dropzone</a></div>";


		$params = array('subject' => "Your file has been declined", 'to_email' => $owner->user_email, 'message' => $message );
		Email::send("default", $params);

		wp_send_json(['success' => true, 'comments' => $comments]);
	}

	/**
	 * Get file information
	 * @return void
	 */
	function getFileDetails()
	{
		if(!is_user_logged_in()) wp_send_json(['success' => false]);
		global $wpdb;
		$file = $this->getFile(wpdm_query_var('file', 'int'));
		if(current_user_can(WPDM_ADMIN_CAP) || (int)$file->owner === get_current_user_id()){
			wp_send_json($file);
		}
		wp_send_json(['success' => false]);
	}


	/**
	 * Download dropzone file
	 * @return void
	 */
	function download()
	{
		if(isset($_REQUEST['wpdmdzdl'])) {
			$file = $this->getFile(wpdm_query_var('wpdmdzdl', 'int'));
			if(current_user_can(WPDM_ADMIN_CAP) || (int)$file->owner === get_current_user_id()) {
				$file_path = WPDM()->fileSystem->locateFile($file->file);
				if($file_path)
					FileSystem::downloadFile( $file_path, $file->name );
				else
					Messages::error('File not found!');
				die();
			}
		}
	}

	/**
	 * Calculate total used space of given/current user's dropzone
	 * @return string
	 *
	 */
	function fileSpace($user = null)
	{
		$files = $this->getFiles($user);
		$space = 0;
		foreach ($files as $file)
		{
			$space += filesize(WPDM()->fileSystem->locateFile($file->file));
		}
		return __::formatBytes($space);
	}

	function createFileRequest()
	{
		__::isAuthentic('nfr_nonce', WPDM_PRI_NONCE, WPDM_ADMIN_CAP);
		$code = md5(uniqid().time());
		global $wpdb;
		$request = [
			'code' => $code,
			'title' => wpdm_query_var('title', 'txt'),
			'description' => wpdm_query_var('description'),
			'date' => time()
		];
		if (wpdm_query_var('dealline', 'int') === 1) {
			$expiry = wpdm_query_var('expiry_date', 'txt');
			if(!$expiry)
				$request['expiry'] = strtotime('+3 days');
			else
				$request['expiry'] = strtotime($expiry);
		}
		$wpdb->insert("{$wpdb->prefix}ahm_filerequests", $request);
		$url = home_url("/wpdm-file-req/{$code}");
		wp_send_json(['success' => true, 'url' => $url]);
	}

	function fileRequestInfo($id_or_code)
	{
		$id = __::sanitize_var($id_or_code, 'int');
		$code = __::sanitize_var($id_or_code, 'txt');
		global $wpdb;
		if($id > 0)
			$request = $wpdb->get_row("select * from {$this->table_fr} where ID = '{$id}'");
		else
			$request = $wpdb->get_row("select * from {$this->table_fr} where code = '{$code}'");
		if(!$request) return false;
		$request->files = json_decode($request->files);
		$request->files = is_array($request->files) ? $request->files : [];
		$request->file_count = count($request->files);
		$request->url = home_url("/wpdm-file-req/{$request->code}");
		return $request;
	}

	function fileRequest()
	{
		if(substr_count($_SERVER['REQUEST_URI'], 'wpdm-file-req')) {
			$parts = explode('/', $_SERVER['REQUEST_URI']);
			$code = end($parts);
			$code = __::sanitize_var($code, 'txt');
			global $wpdb;
			$request = $wpdb->get_row("select * from {$this->table_fr} where code='{$code}'");

			if(!is_user_logged_in()){
				add_filter("the_content", function ($content){
					$note = __('Admin sent you file request. Please log in to upload files', WPDM_TEXT_DOMAIN);
					return WPDM()->user->login->form(['note_before' => $note]);
				});
				$nopost = true;
				include wpdm_tpl_path('clean-template.php', WPDM_SRC_DIR.'__/views');
				die();
			}

			include Template::locate("file-request.php", WPDM_SRC_DIR.'/DropZone/views/');
			die();
		}
	}

	/**
	 * View received files from a file request
	 * @return void
	 */
	function viewFiles()
	{
		$id = __::query_var('id', 'int');
		global $wpdb;
		$request = $this->fileRequestInfo($id);
		$files = $request->files;
		echo "<table class='table table-striped m-0'><tr><td colspan='2'><h3>{$request->title}</h3>{$request->description}</td></tr>";
		if(is_array($files) && count($files) > 0) {
			foreach ( $files as $file ) {
				$filename = basename( $file );
				$root     = AssetManager::root();
				$path     = str_replace( $root, "", $file );
				$download = home_url( "/?wpdmfmdl=" . Crypt::encrypt( $path ) );
				echo "<tr><td>{$filename}</td><td class='text-right'><a href='{$download}' class='btn btn-xs btn-info'>Download</a></td></tr>";
			}
		} else {
			echo "<tr><td colspan='2'><div class='text-warning'>No files found!</div></td></tr>";
		}
		echo "</table>";
		die();

	}

	function shareFile()
	{
		global $wpdb;
		$id = __::query_var('id', 'int');
		$request = $this->fileRequestInfo($id);
		include Template::locate("share-file.php", __DIR__.'/views');
		die();
	}

	function shareRequestLink()
	{
		global $wpdb;
		$id = __::query_var('id', 'int');
		$request = $this->fileRequestInfo($id);
		include Template::locate("share-request-link.php", __DIR__.'/views');
		die();
	}

	function sendRequestLink()
	{
		__::isAuthentic('dzsl_nonce', WPDM_PRI_NONCE, WPDM_ADMIN_CAP);

		$emails = wpdm_query_var('emails');
		$emails = explode(",", $emails);
		foreach ($emails as &$email){
			$email = is_email($email) ? $email : '';
		}
		$emails = array_unique($emails);

		$request = $this->fileRequestInfo(__::query_var('rid', 'int'));

		$button = "<a style='display: block;text-align: center' href='{$request->url}' class='button green'>".__('Send Files', WPDM_TEXT_DOMAIN)."</a>";

		$message = __::query_var('message', 'kses')."<br/>";
		$message .= __MailUI::panel($request->title, [$request->description]);
		$message .= $button;

		$params = [
			'subject' => sprintf(__("[%s] New file request: {$request->title}", WPDM_TEXT_DOMAIN), get_option('blogname')),
			'to_email' => implode(",", $emails),
			'message' => $message
		];
		Email::send('default', $params);
		wp_send_json(['success' => true]);

	}

	function closeRequest()
	{
		__::isAuthentic('dzcr_nonce', WPDM_PRI_NONCE, WPDM_ADMIN_CAP);
		global $wpdb;
		$wpdb->update($this->table_fr, ['closed' => 1], ['ID' => __::query_var('rid', 'int')]);
		wp_send_json(['success' => true]);
	}

	function deleteRequest()
	{
		__::isAuthentic('dzdr_nonce', WPDM_PRI_NONCE, WPDM_ADMIN_CAP);
		global $wpdb;
		$request = $this->fileRequestInfo(__::query_var('rid', 'int'));
		foreach ($request->files  as $file) {
			if(file_exists($file))
				@unlink($file);
		}
		$wpdb->delete($this->table_fr, ['ID' => __::query_var('rid', 'int')]);
		wp_send_json(['success' => true]);
	}

}