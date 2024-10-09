<?php

namespace WPDM\Settings;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

use PrivateMessage\__\__;


class SettingsController {
	private static $instance;

	/**
	 * @var Section[]
	 */
	public $settings_sections;
	public $settings_fields;
	/**
	 * @var Email
	 */
	public $email;
	/**
	 * @var General
	 */
	public $general;

	/**
	 * @var ChatAutomation
	 */
	public $chatautomation;

    /**
     * @var License
     */
    public $license;

	public static function getInstance() {
		if ( self::$instance === null ) {
			self::$instance = new self;
			self::$instance->actions();
		}

		return self::$instance;
	}

	function __construct() {
		$this->initiateSettings();
	}

	private function actions() {

		add_action( 'wp_ajax_wppmsg_settings', array( $this, 'loadSettingsPage' ) );
		add_action( 'wp_ajax_wppmsg_save_settings', array( $this, 'saveSettings' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 99999 );

	}

	/**
	 * @usage Initiate Settings Tabs
	 */
	function initiateSettings() {

		$tabs                 = [];
		$this->general        = $tabs['general'] = new General();
		$this->email          = $tabs['email'] = new Email();
		$this->chatautomation = $tabs['chatautomation'] = new ChatAutomation();
		$this->license        = $tabs['license'] = new License();

		$this->settings_sections = apply_filters( "add_wppmsg_settings_tab", $tabs );

	}


	static function settingsPageUrl( $id = null ) {
		$params = [ 'page' => 'pmsettings' ];
		if ( $id ) {
			$params['tab'] = $id;
		}

		return add_query_arg( $params );
	}

	/**
	 * @usage  Admin Settings Tab Helper
	 *
	 * @param string $sel
	 */
	public function renderMenu( $sel = '' ) {

		foreach ( $this->settings_sections as $id => $tab ) {
			if ( $sel === $id ) {
				echo "<a class='nav-link active' id='{$id}' href='" . self::settingsPageUrl( $id ) . "'><i class='{$tab->icon} mr-2'></i>{$tab->title}</a>";
			} else {
				echo "<a class='nav-link' id='{$id}' href='" . self::settingsPageUrl( $id ) . "'><i class='{$tab->icon} mr-2'></i>{$tab->title}</a>";
			}

		}
	}

	public function renderSettingsTab( $tab_id ) {
		call_user_func( [ $this->settings_sections[ $tab_id ], 'render' ] );

	}

	function loadSettingsPage() {
		global $stabs;
		if ( current_user_can( PM_ADMIN_CAP ) ) {
			$this->renderSettingsTab( __::query_var( 'section' ) );
		}
		die();
	}

	function saveSettings() {
		$setting_fields = $this->settings_sections[ __::query_var( 'section' ) ]->settings_fields;

		foreach ( $setting_fields as $name => $field ) {
			$value          = __::query_var( $name, [ 'validate' => __::valueof( $field, 'validate' ) ] );
			$value_original = $value;
			if ( __::valueof( $field, 'save_callback' ) !== '' ) {
				call_user_func( __::valueof( $field, 'save_callback' ), $value, $field );
			} else {
				if ( __::valueof( $field, 'validate_callback' ) !== '' ) {
					$value = call_user_func( __::valueof( $field, 'validate_callback' ), $value );
				}
				if ( ! is_wp_error( $value ) ) {
					update_option( $name, $value, false );
				} else {
					wp_send_json( [ 'success' => false, 'message' => $value->get_error_message() ] );
				}
			}
		}
		wp_send_json( [ 'success' => true, 'message' => 'Settings saved successfully!' ] );
	}


	function admin_menu() {
		add_submenu_page( "wpdmpm", __( 'Settings', PM_TEXT_DOMAIN ), __( 'Settings', PM_TEXT_DOMAIN ), PM_ADMIN_CAP, 'pmsettings', array(
			$this,
			'settings'
		), 999 );
	}

	function settings() {
		$tab = __::query_var( 'tab', [ 'default' => 'general' ] );
		include __DIR__ . '/views/settings.php';
	}


}


