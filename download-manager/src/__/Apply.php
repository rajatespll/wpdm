<?php

namespace WPDM\__;

use WPDM\AuthorDashboard\AuthorDashboard;


class Apply {

	function __construct() {


		$this->adminActions();
		$this->frontendActions();

	}

	function frontendActions() {

		add_filter( 'wpdm_custom_data', array( $this, 'skipLocks' ), 10, 2 );
		add_action( "wp_ajax_nopriv_showLockOptions", array( $this, 'showLockOptions' ) );
		add_action( "wp_ajax_showLockOptions", array( $this, 'showLockOptions' ) );
		add_action( "wp_ajax_wpdm_addtofav", array( $this, 'addToFav' ) );

		//add_action( "wp_ajax_wpdm_email_lock", [ $this, 'verifyEmailLock' ] );
		//add_action( "wp_ajax_nopriv_wpdm_email_lock", [ $this, 'verifyEmailLock' ] );

		add_action( "wp_ajax_wpdm_generate_password", [ $this, 'generatePassword' ] );
		add_action( "wp_ajax_wpdm-activate-shop", [ $this, 'activatePremiumPackage' ] );
		add_action( "wp_ajax_wpdm-install-addon", [ $this, 'installAddon' ] );

		if ( is_admin() ) {
			return;
		}

		add_action( "init", array( $this, 'triggerDownload' ), 9 );
		add_action( 'init', array( $this, 'addWriteRules' ), 0 );


		add_filter( 'widget_text', 'do_shortcode' );

		add_action( 'query_vars', array( $this, 'dashboardPageVars' ), 1 );
		add_action( 'request', array( $this, 'rssFeed' ) );
		add_filter( 'pre_get_posts', array( $this, 'queryTag' ) );

		add_filter( 'ajax_query_attachments_args', array( $this, 'usersMediaQuery' ) );


		add_action( 'wp_head', array( $this, 'addGenerator' ), 9999 );
		add_filter( 'post_comments_feed_link', array( $this, 'removeCommentFeed' ) );

		add_filter( 'the_excerpt_embed', array( $this, 'oEmbed' ) );

		add_filter( 'get_avatar_data', array( $this, 'shopLogo' ), 10, 3 );

		add_action( 'wp_head', array( $this, 'wpHead' ), 999999 );

		add_filter( 'template_include', array( $this, 'cleanTemplate' ), 99 );

	}

	function adminActions() {
		if ( ! is_admin() ) {
			return;
		}
		add_action( 'after_switch_theme', array( $this, 'flashRules' ) );
		add_action( 'save_post', array( $this, 'dashboardPages' ) );
		add_action( 'wp_ajax_clear_cache', array( $this, 'clearCache' ) );
		add_action( 'wp_ajax_clear_stats', array( $this, 'clearStats' ) );
		add_action( 'wp_ajax_generate_tdl', array( $this, 'generateTDL' ) );
		add_action( 'admin_head', array( $this, 'uiColors' ) );

	}

	function cleanTemplate( $template ) {
		global $post;
		if ( ! $post || ! is_singular( 'page' ) ) {
			return $template;
		}

		if ( $post->ID == get_option( '__wpdm_login_url', 0 ) && ! is_user_logged_in() && (int) get_option( '__wpdm_clean_login', 0 ) === 1 ) {
			return wpdm_tpl_path( 'clean-template.php', __DIR__ . '/views' );
		}

		if ( $post->ID == get_option( '__wpdm_user_dashboard', 0 ) && ! is_user_logged_in() && (int) get_option( '__wpdm_clean_login', 0 ) === 1 ) {
			return wpdm_tpl_path( 'clean-template.php', __DIR__ . '/views' );
		}

		if ( $post->ID == get_option( '__wpdm_author_dashboard', 0 ) && ! is_user_logged_in() && (int) get_option( '__wpdm_clean_login', 0 ) === 1 ) {
			return wpdm_tpl_path( 'clean-template.php', __DIR__ . '/views' );
		}

		if ( $post->ID == get_option( '__wpdm_author_dashboard', 0 ) && is_user_logged_in() && (int) get_option( '__wpdm_clean_login', 0 ) === 1 ) {
			//return wpdm_tpl_path('author-dashboard-blank.php');
		}

		if ( $post->ID == get_option( '__wpdm_register_url', 0 ) && (int) get_option( '__wpdm_clean_signup', 0 ) === 1 ) {
			return wpdm_tpl_path( 'clean-template.php', __DIR__ . '/views' );
		}

		return $template;
	}

	function authorPage( $wp_query ) {
		if ( (int) $wp_query->is_author === 1 && ( $ppid = WPDM()->setting->author_profile( 'int' ) ) > 0 && $ppid === $wp_query->query_vars['page_id'] ) {
			wpdmdd( $ppid );
			unset( $wp_query->query['post_type'] );
			$pagename        = get_pagename( $ppid );
			$wp_query->query = array( 'page' => '', 'pagename' => $pagename );
			$wp_query->set( 'author_name', null );
			$wp_query->set( 'pagename', $pagename );
			$wp_query->is_archive           = false;
			$wp_query->is_post_type_archive = false;
			$wp_query->queried_object_id    = $ppid;
			$wp_query->queried_object       = get_post( $ppid );
		}

		//wpdmdd($wp_query);
		return $wp_query;
	}

	function skipLocks( $data, $id ) {
		global $current_user;
		$skiplocks = maybe_unserialize( get_option( '__wpdm_skip_locks', array() ) );
		if ( is_user_logged_in() ) {
			foreach ( $skiplocks as $lock ) {
				unset( $data[ $lock . "_lock" ] ); // = 0;
			}
		}

		return $data;
	}

	function docStream() {
		if ( strstr( $_SERVER['REQUEST_URI'], 'wpdm-doc-preview' ) ) {
			preg_match( "/wpdm\-doc\-preview\/([0-9]+)/", $_SERVER['REQUEST_URI'], $mat );
			$file_id = $mat[1];
			$files   = WPDM()->package->getFiles( $file_id );
			if ( count( $files ) == 0 ) {
				die( 'No file found!' );
			}
			$sfile = '';
			foreach ( $files as $i => $sfile ) {
				$ifile = $sfile;
				$sfile = explode( ".", $sfile );
				$fext  = end( $sfile );
				if ( in_array( end( $sfile ), array( 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx' ) ) ) {
					$sfile = $ifile;
					break;
				}
			}
			if ( $sfile == '' ) {
				die( 'No supported document found!' );
			}
			if ( file_exists( UPLOAD_DIR . $sfile ) ) {
				$sfile = UPLOAD_DIR . $sfile;
			}
			if ( ! file_exists( $sfile ) ) {
				die( 'No supported document found!' );
			}

			if ( strstr( $sfile, '://' ) ) {
				header( "location: {$sfile}" );
			} else {
				FileSystem::downloadFile( $sfile, basename( $sfile ) );
			}
			die();
		}
	}

	function addWriteRules() {
		global $wp_rewrite;
		$udb_page_id = get_option( '__wpdm_user_dashboard', 0 );
		if ( $udb_page_id ) {
			$page_name = get_post_field( "post_name", $udb_page_id );
			add_rewrite_rule( '^' . $page_name . '/(.+)/?', 'index.php?page_id=' . $udb_page_id . '&udb_page=$matches[1]', 'top' );
			//dd($wp_rewrite);
		}
		$adb_page_id = get_option( '__wpdm_author_dashboard', 0 );

		if ( $adb_page_id ) {
			$page_name = get_post_field( "post_name", $adb_page_id );
			add_rewrite_rule( '^' . $page_name . '/(.+)/?', 'index.php?page_id=' . $adb_page_id . '&adb_page=$matches[1]', 'top' );
		}

		$ap_page_id = get_option( '__wpdm_author_profile', 0 );

		if ( $ap_page_id ) {
			$page_name = get_post_field( "post_name", $ap_page_id );
			add_rewrite_rule( '^' . $page_name . '/(.+)/?$', 'index.php?pagename=' . $page_name . '&profile=$matches[1]', 'top' );
		}

		//wpdmdd($wp_rewrite);
		//add_rewrite_rule('^wpdmdl/([0-9]+)/?', 'index.php?wpdmdl=$matches[1]', 'top');
		//add_rewrite_rule('^wpdmdl/([0-9]+)/ind/([^\/]+)/?', 'index.php?wpdmdl=$matches[1]&ind=$matches[2]', 'top');
		//if(is_404()) dd('404');
		//$wp_rewrite->flush_rules();
		//dd($wp_rewrite);
	}

	function flashRules() {
		$this->addWriteRules();
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}

	function wpdmproTemplates( $template ) {
		$_template    = basename( $template );
		$style_global = get_option( '__wpdm_cpage_style', 'basic' );
		$style        = get_term_meta( get_queried_object_id(), '__wpdm_style', true );
		$style        = in_array( $style, [ 'basic', 'ltpl' ] ) ? $style : $style_global;
		if ( $style === 'ltpl' && ( is_tax( 'wpdmcategory' ) || is_post_type_archive( 'wpdmpro' ) ) ) {
			$template = Template::locate( "taxonomy-wpdmcategory.php", WPDM_SRC_DIR . 'Category/views' );
		}

		/*if($_template !== 'single-wpdmpro.php' && is_singular('wpdmpro')){
			$template = Template::locate("single-wpdmpro.php", WPDM_TPL_FALLBACK, WPDM_TPL_FALLBACK);
		}*/

		return $template;
	}

	function dashboardPages( $post_id ) {
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( get_post_type( $post_id ) !== 'page' ) {
			return;
		}
		$page_id = get_option( '__wpdm_user_dashboard', 0 );
		$post    = get_post( $post_id );
		$flush   = 0;

		//If no dashboard page is selected ( $page_id === 0 )
		// And current page is dashboard shorotcode
		if ( (int) $page_id === 0 && has_shortcode( $post->post_content, "wpdm_user_dashboard" ) ) {
			update_option( '__wpdm_user_dashboard', $post_id );
			$flush = 1;
		}

		if ( has_shortcode( $post->post_content, "wpdm_package_form" ) ) {
			update_option( '__wpdm_package_form', $post_id );
		}

		$page_id = get_option( '__wpdm_author_dashboard', 0 );

		if ( (int) $page_id === 0 && has_shortcode( $post->post_content, "wpdm_frontend" ) ) {
			update_option( '__wpdm_author_dashboard', $post_id );
			$flush = 1;
		}

		$page_id = get_option( '__wpdm_author_profile', 0 );

		if ( (int) $page_id === 0 && has_shortcode( $post->post_content, "wpdm_user_profile" ) ) {
			update_option( '__wpdm_author_profile', $post_id );
			$flush = 1;
		}

		if ( $flush == 1 ) {
			$this->addWriteRules();
			global $wp_rewrite;
			$wp_rewrite->flush_rules();
		}

	}

	function dashboardPageVars( $vars ) {
		array_push( $vars, 'udb_page', 'adb_page', 'page_id', 'wpdmdl', 'ind', 'profile', 'wpdm_asset_key' );

		return $vars;
	}

	function playMedia() {

		if ( strstr( "!{$_SERVER['REQUEST_URI']}", "/wpdm-media/" ) ) {
			$media = explode( "wpdm-media/", $_SERVER['REQUEST_URI'] );
			$media = explode( "/", $media[1] );
			list( $ID, $file, $name ) = $media;
			$key = wpdm_query_var( '_wpdmkey' );

			if ( isset( $_SERVER['HTTP_RANGE'] ) ) {
				$partialContent = true;
				preg_match( '/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches );
				$offset = intval( $matches[1] );
				$length = intval( $matches[2] ) - $offset;
			} else {
				$partialContent = false;
			}

			$keyValid = is_wpdmkey_valid( $ID, $key, true );

			if ( ! $partialContent ) {
				if ( $key == '' || ! $keyValid ) {
					die( Messages::permission_denied( $ID ) );
				}
			}
			$files  = WPDM()->package->getFiles( $ID );
			$file   = $files[ $file ];
			$file   = FileSystem::fullPath( $file, $ID );
			$stream = new StreamMedia( $file );
			$stream->start();
			die();
		}
	}


	/**
	 * @usage Process Download Request from lock options
	 */
	function triggerDownload() {

		global $wpdb, $current_user, $wp_query;
		$dlbase         = get_option('__wpdm_fdurl_base', 'wpdmdl');
		if ( preg_match( "/\/{$dlbase}\/([\d]+)\/(.+)\/(.*)/", $_SERVER['REQUEST_URI'], $matched ) ) {
            if((int)get_option('__wpdm_flat_download_url')) {
	            $_REQUEST['wpdmdl']             = $_GET['wpdmdl'] = (int) $matched[1];
	            $_REQUEST['ind']                = $_GET['ind'] = (int) $matched[2];
	            $wp_query->query_vars['wpdmdl'] = (int) $matched[1];
	            $wp_query->query_vars['ind']    = (int) $matched[2];
            }
		}
		else if ( preg_match( "/\/download\/([\d]+)\/(.*)/", $_SERVER['REQUEST_URI'], $matched ) ) {
			if((int)get_option('__wpdm_flat_download_url')) {
				$_REQUEST['wpdmdl']             = $_GET['wpdmdl'] = (int) $matched[1];
				$wp_query->query_vars['wpdmdl'] = (int) $matched[1];
			}
		}

		//Instant download link processing
		if ( isset( $_GET['wpdmidl'] ) ) {
			$file = TempStorage::get( "__wpdm_instant_download_" . wpdm_query_var( 'wpdmidl' ) );
			if ( ! $file ) {
				Messages::error( __( "Download ID not found or expired", "download-manager" ), 1 );
			}
			if ( ! file_exists( $file ) ) {
				Messages::error( __( "The file is already removed from the server!", "download-manager" ), 1 );
			}

			FileSystem::downloadFile( $file, wpdm_basename( $file ) );
			die();
		}

		//Regular download processing
		if ( ! isset( $wp_query->query_vars['wpdmdl'] ) && ! isset( $_GET['wpdmdl'] ) ) {
			return;
		}

		$id = isset( $_GET['wpdmdl'] ) ? (int) $_GET['wpdmdl'] : (int) $wp_query->query_vars['wpdmdl'];
		if ( $id <= 0 ) {
			return;
		}


		//Master key validation
		$masterKey        = wpdm_query_var( 'masterkey' );
		$hasMasterKey     = $masterKey !== '' ? true : false;
		$isMasterKeyValid = WPDM()->package->validateMasterKey( $id, $masterKey );
		$isMaster         = $hasMasterKey && $isMasterKeyValid;

		//Temporary download key validation
		$key = wpdm_query_var( '_wpdmkey' );
		$key = $key == '' && array_key_exists( '_wpdmkey', $wp_query->query_vars ) ? $wp_query->query_vars['_wpdmkey'] : $key;
		$key = preg_replace( "/[^_a-z|A-Z|0-9]/i", "", $key );

		$keyValid = 0;

		if ( $key ) {
			$keyValid = is_wpdmkey_valid( $id, $key, true );

			if ( (int) $keyValid !== 1 ) {
				Messages::error( __( "&mdash; Invalid download link &mdash;", "download-manager" ), 1 );
			}
		}


		if ( WPDM()->package->isLocked( $id ) && ! $keyValid && ! $isMaster ) {
			Messages::error( __( "&mdash; You are not allowed to download &mdash;", "download-manager" ), 1 );
		}

		$package = WPDM()->package->init( $id );
		$package = (array) $package;
		$package['access'] = WPDM()->package->allowedRoles( $id );

		$package = apply_filters("wpdm_before_download", $package);


		if ( $isMaster || $keyValid ) {
			$package['access'] = array( 'guest' );
		}

        //wpdmdd($package);
		$matched = ( is_array( @maybe_unserialize( $package['access'] ) ) && is_user_logged_in() ) ? array_intersect( $current_user->roles, @maybe_unserialize( $package['access'] ) ) : array();


		if ( ( ( $id != '' && is_user_logged_in() && count( $matched ) < 1 && ! @in_array( 'guest', $package['access'] ) ) || ( ! is_user_logged_in() && ! @in_array( 'guest', $package['access'] ) && $id != '' ) ) ) {
			do_action( "wpdm_download_permission_denied", $id );
			wpdm_download_data( "permission-denied.txt", __( "You don't have permission to download this file", "download-manager" ) );
			die();
		} else {
			if ( $package['ID'] > 0 ) {

				if ( (int) $package['quota'] == 0 || $package['quota'] > $package['download_count'] ) {
					$package['force_download'] = wpdm_query_var( '_wpdmkey' );
					include( WPDM_BASE_DIR . "src/wpdm-start-download.php" );
				} else {
					wpdm_download_data( "stock-limit-reached.txt", __( "Stock Limit Reached", "download-manager" ) );
				}

			}

		}
	}


	/**
	 * @usage Add with main RSS feed
	 *
	 * @param $query
	 *
	 * @return mixed
	 */
	function rssFeed( $query ) {
		if ( isset( $query['feed'] ) && ! isset( $query['post_type'] ) && get_option( '__wpdm_rss_feed_main', 0 ) == 1 ) {
			$query['post_type'] = array( 'post', 'wpdmpro' );
		}

		return $query;
	}

	/**
	 * @usage Schedule custom ping
	 *
	 * @param $post_id
	 */
	function customPings( $post_id ) {
		wp_schedule_single_event( time() + 5000, 'do_pings', array( $post_id ) );
	}

	/**
	 * @usage Allow access to server file browser for selected user roles
	 */
	function sfbAccess() {

		global $wp_roles;
		if ( ! is_array( $wp_roles->roles ) ) {
			return;
		}
		$roleids = array_keys( $wp_roles->roles );
		$roles   = get_option( '_wpdm_file_browser_access', array( 'administrator' ) );
		$naroles = array_diff( $roleids, $roles );
		foreach ( $roles as $role ) {
			$role = get_role( $role );
			if ( is_object( $role ) && ! is_wp_error( $role ) ) {
				$role->add_cap( 'access_server_browser' );
			}
		}

		foreach ( $naroles as $role ) {
			$role = get_role( $role );
			if ( is_object( $role ) && ! is_wp_error( $role ) && in_array( 'access_server_browser', $role->capabilities ) ) {
				$role->remove_cap( 'access_server_browser' );
			}
		}

	}

	/**
	 * @usage Allow front-end users to access their own files only
	 *
	 * @param $query_params
	 *
	 * @return string
	 */
	function usersMediaQuery( $query_params ) {
		global $current_user;

		if ( current_user_can( 'edit_posts' ) ) {
			return $query_params;
		}

		if ( is_user_logged_in() ) {
			$query_params['author'] = $current_user->ID;
		}

		return $query_params;
	}

	/**
	 * @usage Add packages wth tag query
	 *
	 * @param $query
	 *
	 * @return mixed
	 */
	function queryTag( $query ) {

		if ( $query->is_tag() && $query->is_main_query() ) {
			$post_type = get_query_var( 'post_type' );
			if ( ! is_array( $post_type ) ) {
				$post_type = array( 'post', 'page', 'wpdmpro', 'nav_menu_item' );
			} else {
				$post_type = array_merge( $post_type, array( 'post', 'wpdmpro', 'nav_menu_item' ) );
			}
			$query->set( 'post_type', $post_type );
		}

		return $query;
	}

	/**
	 * Empty cache dir
	 */
	function clearCache() {
		__::isAuthentic( 'ccnonce', WPDM_PRI_NONCE, 'manage_options' );

		FileSystem::deleteFiles( WPDM_CACHE_DIR, false );
		FileSystem::deleteFiles( WPDM_CACHE_DIR . 'pdfthumbs/', false );

		Session::reset();
		TempStorage::clear();

		die( 'ok' );
	}

	/**
	 * Delete all download hostory
	 */
	function clearStats() {
		__::isAuthentic( 'csnonce', WPDM_PRI_NONCE, 'manage_options' );

		global $wpdb;
		$wpdb->query( 'truncate table ' . $wpdb->prefix . 'ahm_download_stats' );
		$wpdb->query( 'truncate table ' . $wpdb->prefix . 'ahm_user_download_counts' );
		$wpdb->query( "delete from {$wpdb->prefix}postmeta where meta_key='__wpdmx_user_download_count'" );
		die( 'ok' );
	}


	/**
	 * @usage Add generator tag
	 */
	function addGenerator() {
		echo '<meta name="generator" content="WordPress Download Manager ' . WPDM_VERSION . '" />' . "\r\n";
	}

	function oEmbed( $content ) {
		if ( get_post_type( get_the_ID() ) !== 'wpdmpro' ) {
			return $content;
		}
		if ( function_exists( 'wpdmpp_effective_price' ) && wpdmpp_effective_price( get_the_ID() ) > 0 ) {
			$template = '<table class="table table-bordered"><tbody><tr><td colspan="2">[excerpt_200]</td></tr><tr><td>[txt=Price]</td><td>[currency][effective_price]</td></tr><tr><td>[txt=Version]</td><td>[version]</td></tr><tr><td>[txt=Total Files]</td><td>[file_count]</td></tr><tr><td>[txt=File Size]</td><td>[file_size]</td></tr><tr><td>[txt=Create Date]</td><td>[create_date]</td></tr><tr><td>[txt=Last Updated]</td><td>[update_date]</td><tr><td colspan="2" style="text-align: right;border-bottom: 0"><a class="wpdmdlbtn" href="[page_url]" target="_parent">[txt=Buy Now]</a></td></tr></tbody></table><br/><style> .wpdmdlbtn {-moz-box-shadow:inset 0px 1px 0px 0px #9acc85;-webkit-box-shadow:inset 0px 1px 0px 0px #9acc85;box-shadow:inset 0px 1px 0px 0px #9acc85;background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #74ad5a), color-stop(1, #68a54b));background:-moz-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:-webkit-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:-o-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:-ms-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:linear-gradient(to bottom, #74ad5a 5%, #68a54b 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#74ad5a\', endColorstr=\'#68a54b\',GradientType=0);background-color:#74ad5a;-moz-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;border:1px solid #3b6e22;display:inline-block;cursor:pointer;color:#ffffff !important; font-size:12px;font-weight:bold;padding:10px 20px;text-transform: uppercase;text-decoration:none !important;}.wpdmdlbtn:hover {background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #68a54b), color-stop(1, #74ad5a));background:-moz-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:-webkit-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:-o-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:-ms-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:linear-gradient(to bottom, #68a54b 5%, #74ad5a 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#68a54b\', endColorstr=\'#74ad5a\',GradientType=0);background-color:#68a54b;}.wpdmdlbtn:active {position:relative;top:1px;} .table{width:100%;border: 1px solid #eeeeee;} .table td{ padding:10px;border-bottom:1px solid #eee;}</style>';
		} else {
			$template = '<table class="table table-bordered"><tbody><tr><td colspan="2">[excerpt_200]</td></tr><tr><td>[txt=Version]</td><td>[version]</td></tr><tr><td>[txt=Total Files]</td><td>[file_count]</td></tr><tr><td>[txt=File Size]</td><td>[file_size]</td></tr><tr><td>[txt=Create Date]</td><td>[create_date]</td></tr><tr><td>[txt=Last Updated]</td><td>[update_date]</td><tr><td colspan="2" style="text-align: right;border-bottom: 0"><a class="wpdmdlbtn" href="[page_url]" target="_parent">[txt=Download]</a></td></tr></tbody></table><br/><style> .wpdmdlbtn {-moz-box-shadow:inset 0px 1px 0px 0px #9acc85;-webkit-box-shadow:inset 0px 1px 0px 0px #9acc85;box-shadow:inset 0px 1px 0px 0px #9acc85;background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #74ad5a), color-stop(1, #68a54b));background:-moz-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:-webkit-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:-o-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:-ms-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:linear-gradient(to bottom, #74ad5a 5%, #68a54b 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#74ad5a\', endColorstr=\'#68a54b\',GradientType=0);background-color:#74ad5a;-moz-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;border:1px solid #3b6e22;display:inline-block;cursor:pointer;color:#ffffff !important; font-size:12px;font-weight:bold;padding:10px 20px;text-transform: uppercase;text-decoration:none !important;}.wpdmdlbtn:hover {background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #68a54b), color-stop(1, #74ad5a));background:-moz-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:-webkit-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:-o-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:-ms-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:linear-gradient(to bottom, #68a54b 5%, #74ad5a 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#68a54b\', endColorstr=\'#74ad5a\',GradientType=0);background-color:#68a54b;}.wpdmdlbtn:active {position:relative;top:1px;} .table{width:100%; border: 1px solid #eeeeee; } .table td{ padding:10px;border-bottom:1px solid #eee;}</style>';
		}

		return WPDM()->package->fetchTemplate( $template, get_the_ID() );
	}

	function showLockOptions() {
		if ( ! isset( $_REQUEST['id'] ) ) {
			die( 'ID Missing!' );
		}
		echo WPDM()->package->downloadLink( (int) $_REQUEST['id'], 1 );
		die();
	}

	function addToFav() {
		if ( ! is_user_logged_in() ) {
			die( 'error' );
		}
		$pid    = (int) $_REQUEST['pid'];
		$ufavs  = maybe_unserialize( get_post_meta( $pid, '__wpdm_favs', true ) );
		$myfavs = maybe_unserialize( get_user_meta( get_current_user_id(), '__wpdm_favs', true ) );
		if ( ! is_array( $myfavs ) ) {
			$myfavs = array();
		}
		if ( ! is_array( $ufavs ) ) {
			$ufavs = array();
		}

		if ( is_array( $myfavs ) && ( $key = array_search( $_REQUEST['pid'], $myfavs ) ) !== false ) {
			unset( $myfavs[ $key ] );
			if ( isset( $ufavs[ get_current_user_id() ] ) ) {
				unset( $ufavs[ get_current_user_id() ] );
			}
		} else {
			$myfavs[]                       = $_REQUEST['pid'];
			$ufavs[ get_current_user_id() ] = time();
		}
		update_user_meta( get_current_user_id(), '__wpdm_favs', $myfavs );
		update_post_meta( $pid, '__wpdm_favs', $ufavs );
		die( 'ok' );
	}


	function verifyEmail( $errors, $sanitized_user_login, $user_email ) {
		if ( ! $errors ) {
			$errors = new \WP_Error();
		}
		if ( ! wpdm_verify_email( $user_email ) ) {
			$emsg = get_option( '__wpdm_blocked_domain_msg' );
			if ( trim( $emsg ) === '' ) {
				$emsg = __( 'Your email address is blocked!', 'download-manager' );
			}
			$errors->add( 'blocked_email', $emsg );
		}

		return $errors;
	}

	function verifyLoginEmail( $user, $user_login, $user_pass ) {

		$user_email = null;
		if ( ! is_email( $user_login ) && ! $user ) {
			$_user = get_user_by( 'user_login', $user_login );
			if ( $_user ) {
				$user_email = $_user->user_email;
			}
		} else if ( is_email( $user_login ) ) {
			$user_email = $user_login;
		} else if ( $user && isset( $user->user_email ) ) {
			$user_email = $user->user_email;
		}

		if ( is_email( $user_email ) && ! wpdm_verify_email( $user_email ) ) {
			$user = new \WP_Error();
			$emsg = get_option( '__wpdm_blocked_domain_msg' );
			if ( trim( $emsg ) === '' ) {
				$emsg = __( 'Your email address is blocked!', 'download-manager' );
			}
			$user->add( 'blocked_email', $emsg );
		}

		return $user;
	}

	function generateTDL() {
		if ( is_user_logged_in() && AuthorDashboard::hasAccess( get_current_user_id() ) && wp_verify_nonce( $_REQUEST['__tdlnonce'], NONCE_KEY ) ) {
			$pid  = (int) $_REQUEST['pid'];
			$pack = get_post( $pid );
			if ( current_user_can( WPDM_ADMIN_CAP ) || $pack->post_author == get_current_user_id() ) {
				$expire        = ( (int) $_REQUEST['exmisd'] * (int) $_REQUEST['expire_multiply'] );
				$download_url  = WPDM()->package->expirableDownloadLink( $pid, (int) $_REQUEST['ulimit'], $expire );
				$download_page = WPDM()->package->expirableDownloadPage( $pid, (int) $_REQUEST['ulimit'], $expire );
				wp_send_json( array( 'download_url' => $download_url, 'download_page' => $download_page ) );
				die();
			}
		}
		wp_send_json( array(
			'download_url'  => 'Error! Unauthorized Access',
			'download_page' => 'Error! Unauthorized Access'
		) );

	}


	function validateLoginPage( $content ) {
		if ( is_singular( 'page' ) ) {
			$id = get_option( '__wpdm_login_url', 0 );
			if ( $id > 0 && $id == get_the_ID() ) {
				if ( ! has_shortcode( $content, 'wpdm_login_form' ) && ! has_shortcode( $content, 'wpdm_user_dashboard' ) && ! has_shortcode( $content, 'wpdm_author_dashboard' ) ) {
					$content = WPDM()->user->login->form();
				}
			}
		}

		return $content;

	}

	function removeCommentFeed( $feed ) {
		if ( get_post_type() == 'wpdmpro' && get_option( '__wpdm_has_archive', false ) == false ) {
			$feed = false;
		}

		return $feed;
	}


	function shopLogo( $args, $id ) {
		if ( is_object( $id ) ) {
			$id = $id->user_id;
		}
		if ( is_email( $id ) ) {
			$user = get_user_by( 'email', $id );
			$id   = $user->ID;
		}

		$store = get_user_meta( $id, '__wpdm_public_profile', true );
		if ( isset( $store['logo'] ) && $store['logo'] != '' ) {
			$args['url'] = $store['logo'];
		}

		return $args;
	}


	function wpHead() {

		self::googleFont();
		?>
        <script>
            function wpdm_rest_url(request) {
                return "<?= wpdm_rest_url( '' ); ?>" + request;
            }
        </script>
		<?php
	}

	static function googleFont() {
		$wpdmss   = maybe_unserialize( get_option( '__wpdm_disable_scripts', array() ) );
		$uicolors = maybe_unserialize( get_option( '__wpdm_ui_colors', array() ) );
		//$ltemplates = maybe_unserialize(get_option("_fm_link_templates", true));
		//$ptemplates = maybe_unserialize(get_option("_fm_page_templates", true));
		$_font = get_option( '__wpdm_google_font', 'Sen' );
		$font  = explode( ":", $_font );
		$font  = $font[0];
		$font  = $font ? $font . ',' : '';

		$css = WPDM()->packageTemplate->getStyles( 'link' );

		?>
		<?php if ( (int)get_option('__wpdm_enable_gf', 0) === 1 && get_option( '__wpdm_google_font' ) !== '' ) { ?>
            <link href="https://fonts.googleapis.com/css2?family=<?php echo str_replace( "regular", 400, $_font ); ?>"
                  rel="stylesheet">
            <style>
                .w3eden .fetfont,
                .w3eden .btn,
                .w3eden .btn.wpdm-front h3.title,
                .w3eden .wpdm-social-lock-box .IN-widget a span:last-child,
                .w3eden .card-header,
                .w3eden .card-footer,
                .w3eden .badge,
                .w3eden .label,
                .w3eden .table,
                .w3eden .card-body,
                .w3eden .wpdm-frontend-tabs a,
                .w3eden .alert:before,
                .w3eden .discount-msg,
                .w3eden .panel.dashboard-panel h3,
                .w3eden #wpdm-dashboard-sidebar .list-group-item,
                .w3eden #package-description .wp-switch-editor,
                .w3eden .w3eden.author-dashbboard .nav.nav-tabs li a,
                .w3eden .wpdm_cart thead th,
                .w3eden #csp .list-group-item,
                .w3eden .modal-title {
                    font-family: <?php echo $font; ?> -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
                }

                .w3eden .btn {
                    font-weight: 800 !important;
                }

                .w3eden .btn {
                    letter-spacing: 1px;
                    text-transform: uppercase;
                }

                .w3eden #csp .list-group-item {
                    text-transform: unset;
                }


            </style>
		<?php } ?>
        <style>
            <?php
			echo '/* WPDM Link Template Styles */';
			echo $css;
			?>
        </style>
		<?php
		self::uiColors();

	}

	static function uiColors( $override_option = false ) {

		$wpdmss = maybe_unserialize( get_option( '__wpdm_disable_scripts', array() ) );
		$wpdm_bscss_pages = get_option('__wpdm_bscss_pages', []);
		if ( is_array( $wpdmss ) && in_array( 'wpdm-front', $wpdmss ) && ! is_admin() && $override_option !== true && !isset($_REQUEST['__wpdmlo']) && !in_array(get_the_ID(), $wpdm_bscss_pages) ) {
			return;
		}

		$uicolors  = maybe_unserialize( get_option( '__wpdm_ui_colors', array() ) );
		$primary   = isset( $uicolors['primary'] ) ? $uicolors['primary'] : '#4a8eff';
		$secondary = isset( $uicolors['secondary'] ) ? $uicolors['secondary'] : '#4a8eff';
		$success   = isset( $uicolors['success'] ) ? $uicolors['success'] : '#18ce0f';
		$info      = isset( $uicolors['info'] ) ? $uicolors['info'] : '#2CA8FF';
		$warning   = isset( $uicolors['warning'] ) ? $uicolors['warning'] : '#f29e0f';
		$danger    = isset( $uicolors['danger'] ) ? $uicolors['danger'] : '#ff5062';
		$font      = get_option( '__wpdm_google_font', 'Sen' );
		$font      = explode( ":", $font );
		$font      = $font[0];
		$font      = $font ? "\"{$font}\"," : '';
		if ( is_singular( 'wpdmpro' ) ) {
			$ui_button = get_option( '__wpdm_ui_download_button' );
		} else {
			$ui_button = get_option( '__wpdm_ui_download_button_sc' );
		}
		$class = ".btn." . ( isset( $ui_button['color'] ) ? $ui_button['color'] : 'btn-primary' ) . ( isset( $ui_button['size'] ) && $ui_button['size'] != '' ? "." . $ui_button['size'] : '' );

		?>
        <style>

            :root {
                --color-primary: <?php echo $primary; ?>;
                --color-primary-rgb: <?php echo wpdm_hex2rgb($primary); ?>;
                --color-primary-hover: <?php echo isset($uicolors['primary'])?$uicolors['primary_hover']:'#4a8eff'; ?>;
                --color-primary-active: <?php echo isset($uicolors['primary'])?$uicolors['primary_active']:'#4a8eff'; ?>;
                --color-secondary: <?php echo $secondary; ?>;
                --color-secondary-rgb: <?php echo wpdm_hex2rgb($secondary); ?>;
                --color-secondary-hover: <?php echo isset($uicolors['secondary'])?$uicolors['secondary_hover']:'#4a8eff'; ?>;
                --color-secondary-active: <?php echo isset($uicolors['secondary'])?$uicolors['secondary_active']:'#4a8eff'; ?>;
                --color-success: <?php echo $success; ?>;
                --color-success-rgb: <?php echo wpdm_hex2rgb($success); ?>;
                --color-success-hover: <?php echo isset($uicolors['success_hover'])?$uicolors['success_hover']:'#4a8eff'; ?>;
                --color-success-active: <?php echo isset($uicolors['success_active'])?$uicolors['success_active']:'#4a8eff'; ?>;
                --color-info: <?php echo $info; ?>;
                --color-info-rgb: <?php echo wpdm_hex2rgb($info); ?>;
                --color-info-hover: <?php echo isset($uicolors['info_hover'])?$uicolors['info_hover']:'#2CA8FF'; ?>;
                --color-info-active: <?php echo isset($uicolors['info_active'])?$uicolors['info_active']:'#2CA8FF'; ?>;
                --color-warning: <?php echo $warning; ?>;
                --color-warning-rgb: <?php echo wpdm_hex2rgb($warning); ?>;
                --color-warning-hover: <?php echo isset($uicolors['warning_hover'])?$uicolors['warning_hover']:'orange'; ?>;
                --color-warning-active: <?php echo isset($uicolors['warning_active'])?$uicolors['warning_active']:'orange'; ?>;
                --color-danger: <?php echo $danger; ?>;
                --color-danger-rgb: <?php echo wpdm_hex2rgb($danger); ?>;
                --color-danger-hover: <?php echo isset($uicolors['danger_hover'])?$uicolors['danger_hover']:'#ff5062'; ?>;
                --color-danger-active: <?php echo isset($uicolors['danger_active'])?$uicolors['danger_active']:'#ff5062'; ?>;
                --color-green: <?php echo isset($uicolors['green'])?$uicolors['green']:'#30b570'; ?>;
                --color-blue: <?php echo isset($uicolors['blue'])?$uicolors['blue']:'#0073ff'; ?>;
                --color-purple: <?php echo isset($uicolors['purple'])?$uicolors['purple']:'#8557D3'; ?>;
                --color-red: <?php echo isset($uicolors['red'])?$uicolors['red']:'#ff5062'; ?>;
                --color-muted: rgba(69, 89, 122, 0.6);
                --wpdm-font: <?php echo $font; ?> -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            }

            .wpdm-download-link<?php echo $class; ?> {
                border-radius: <?php echo (isset($ui_button['borderradius'])?$ui_button['borderradius']:4); ?>px;
            }


        </style>
		<?php

	}


	/**
	 * @usage Password generator
	 */
	function generatePassword() {
		if ( ! current_user_can( WPDM_MENU_ACCESS_CAP ) || ! wpdm_is_ajax() ) {
			die();
		}
		include( Template::locate( 'generate-password.php', __DIR__ . '/views' ) );
		die();

	}

	/**
	 * @usage Active premium package add-on / shopping cart
	 */
	function activatePremiumPackage() {
		if ( current_user_can( WPDM_ADMIN_CAP ) ) {
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			$upgrader     = new \Plugin_Upgrader( new \Plugin_Installer_Skin( compact( 'title', 'url', 'nonce', 'plugin', 'api' ) ) );
			$downloadlink = 'https://www.wpdownloadmanager.com/?wpdmdl=15671';
			ob_start();
			echo "<div id='acto'>";
			if ( file_exists( dirname( dirname( __FILE__ ) ) . '/wpdm-premium-packages/' ) ) {
				$upgrader->upgrade( $downloadlink );
			} else {
				$upgrader->install( $downloadlink );
			}
			echo '</div><style>#acto .wrap { display: none; }</style>';
			$data = ob_get_clean();
			if ( file_exists( dirname( WPDM_BASE_DIR ) . '/wpdm-premium-packages/wpdm-premium-packages.php' ) ) {
				activate_plugin( 'wpdm-premium-packages/wpdm-premium-packages.php' );
				echo "Congratulation! Your Digital Store is Activated. <a href='' class='btn btn-warning'>Refresh The Page!</a>";
			} else {
				echo "Automatic Installation Failed! Please <a href='https://www.wpdownloadmanager.com/?wpdmdl=15671' target='_blank' class='btn btn-warning'>Download</a> and install manually";
			}
			die();
		} else {
			die( "Only site admin is authorized to install add-on" );
		}
	}

	/**
	 * Added from v4.1.1
	 * WPDM add-on installer
	 */
	function installAddon() {
		if ( isset( $_REQUEST['addon'] ) && current_user_can( 'manage_options' ) && wp_verify_nonce( $_REQUEST['__wpdmpinn'], $_REQUEST['addon'] . NONCE_KEY ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			$upgrader   = new \Plugin_Upgrader( new \Plugin_Installer_Skin( compact( 'title', 'url', 'nonce', 'plugin', 'api' ) ) );
			$plugin_dir = wpdm_query_var( 'dirname', 'txt', false );
			if ( $plugin_dir ) {
				$plugin_data = wpdm_plugin_data( $plugin_dir );
				$plugin_file = $plugin_data ? $plugin_data['plugin_index_file'] : false;
				if ( $plugin_file ) {
					if ( is_plugin_active( $plugin_file ) ) {
						deactivate_plugins( $plugin_file );
					}
					delete_plugins( array( $plugin_file ) );
				}
			}
			if ( strpos( wpdm_query_var( 'addon', 'txt' ), '.zip' ) ) {
				$downloadlink = wpdm_query_var( 'addon', 'txt' );
			} else {
				$downloadlink = 'https://www.wpdownloadmanager.com/?wpdmdl=' . wpdm_query_var( 'addon', 'int' );
			}
			$upgrader->install( $downloadlink );
			die();
		} else {
			die( "Only site admin is authorized to install add-on" );
		}
	}


}

/*add_filter("wpcf7_form_response_output", function( $output, $class, $content, $obj, $status ) {
    return "OK";
}, 10, 5);*/
