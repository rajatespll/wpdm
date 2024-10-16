<?php

namespace WPDM\Admin\Menu;

use WPDM\__\__;
use WPDM\__\Installer;
use WPDM\__\Session;

define('WPDMSET_NONCE_KEY', 'xV)Op=Oa<y{Z>~jJ{Y#;(kRz<61x&[Rf$R76?[`6kyGvVa}*/.S#%1{[*>tJw2rp');

class Settings
{

    function __construct()
    {
        //add_action('admin_init', array($this, 'checkSaveSettingsAuth'), 1);
        add_action('admin_init', array($this, 'initiateSettings'));
        add_action('wp_ajax_wpdm_settings', array($this, 'loadSettingsPage'));
        add_action('admin_menu', array($this, 'Menu'), 999999);
    }

    function Menu(){
        add_submenu_page('edit.php?post_type=wpdmpro', __( "Settings &lsaquo; Download Manager" , "download-manager" ), __( "Settings" , "download-manager" ), WPDM_ADMIN_CAP, 'settings', array($this, 'UI'));
    }

    function checkSaveSettingsAuth(){
        if(wpdm_query_var('task') === 'wdm_save_settings') {
            check_ajax_referer(WPDMSET_NONCE_KEY, '__wpdms_nonce');
            if(!wp_verify_nonce($_POST['__wpdms_nonce'], WPDMSET_NONCE_KEY)) die(__('Security token is expired! Refresh the page and try again.', 'download-manager'));
            if(!current_user_can('manage_options')) die(__( "You are not allowed to change settings!", "download-manager" ));
        }
    }

    function loadSettingsPage()
    {
        global $stabs;

        $this->checkSaveSettingsAuth();

        if (current_user_can(WPDM_MENU_ACCESS_CAP)) {
            $section = wpdm_query_var('section');
            if(isset($stabs[$section], $stabs[$section]['callback']))
                call_user_func($stabs[$section]['callback']);
            else "<div class='panel panel-danger'><div class='panel-body color-red'><i class='fa fa-exclamation-triangle'></i> ".__( "Something is wrong!", "download-manager" )."</div></div>";
        }
        die();
    }

    function UI(){
        include wpdm_admin_tpl_path("settings.php");
    }

    /**
     * @param $tabid
     * @param $tabtitle
     * @param $callback
     * @param string $icon
     * @return array
     */
    public static function createMenu($tabid, $tabtitle, $callback, $icon = 'fa fa-cog')
    {
        return array('id' => $tabid, 'icon'=>$icon, 'link' => 'edit.php?post_type=wpdmpro&page=settings&tab=' . $tabid, 'title' => $tabtitle, 'callback' => $callback);
    }


    /**
     * @usage Initiate Settings Tabs
     */
    function initiateSettings()
    {
        global $stabs;
        $tabs = array();
        $tabs['basic'] = array('id' => 'basic','icon'=>'fa-solid fa-sliders', 'link' => 'edit.php?post_type=wpdmpro&page=settings', 'title' => __('General', 'download-manager'), 'callback' => array($this, 'basic'));
        $tabs['wpdmui'] = array('id' => 'wpdmui','icon'=>'fas fa-fill-drip', 'link' => 'edit.php?post_type=wpdmpro&page=settings', 'title' => __('User Interface', 'download-manager'), 'callback' => array($this, 'userInterface'));
        $tabs['frontend'] = array('id' => 'frontend','icon'=>'fa fa-desktop', 'link' => 'edit.php?post_type=wpdmpro&page=settings&tab=frontend', 'title' => __('Frontend Access', 'download-manager'), 'callback' => array($this, 'Frontend'));
        $tabs['social-connects'] = array('id' => 'social-connects','icon'=>'fab fa-twitter', 'link' => 'edit.php?post_type=wpdmpro&page=settings&tab=social-connects', 'title' => __('Social Settings', 'download-manager'), 'callback' => array($this, 'socialConnects'));

        // Add buddypress settings menu when buddypress plugin is active
        if (function_exists('bp_is_active')) {
            $tabs['buddypress'] = array('id' => 'buddypress','icon'=>'fa fa-users', 'link' => 'edit.php?post_type=wpdmpro&page=settings&tab=buddypress', 'title' => __('BuddyPress', 'download-manager'), 'callback' => array($this, 'buddypress'));
        }

        if(defined('WPDM_CLOUD_STORAGE')){
            $tabs['cloud-storage'] = array('id' => 'cloud-storage','icon'=>'fa-solid fa-cloud-arrow-up',  'link' => 'edit.php?post_type=wpdmpro&page=settings&tab=cloud-storage', 'title' => __('Cloud Storage', 'download-manager'), 'callback' => array($this, 'cloudStorage'));
        }

        if(!$stabs) $stabs = array();


        $stabs = $tabs + $stabs;

        $stabs = apply_filters("add_wpdm_settings_tab", $stabs);

        $stabs['plugin-update'] = array('id' => 'plugin-update','icon'=>'fa fa-sync',  'link' => 'edit.php?post_type=wpdmpro&page=settings&tab=plugin-update', 'title' => __('Updates', 'download-manager'), 'callback' => array($this, 'pluginUpdate'));
        $stabs['license'] = array('id' => 'license','icon'=>'fa fa-key',  'link' => 'edit.php?post_type=wpdmpro&page=settings&tab=license', 'title' => __('License', 'download-manager'), 'callback' => array($this, 'License'));
        $stabs['privacy'] = array('id' => 'privacy','icon'=>'fas fa-user-shield',  'link' => 'edit.php?post_type=wpdmpro&page=settings&tab=privacy', 'title' => __('Privacy', 'download-manager'), 'callback' => array($this, 'privacy'));

    }


    /**
     * @usage  Admin Settings Tab Helper
     * @param string $sel
     */
    public static function renderMenu($sel = '')
    {
        global $stabs;

        foreach ($stabs as $tab) {
            $isactive = ($sel == $tab['id']) ? 'class="active"' : '';
            $icon = isset($tab['icon']) ? "<i class='{$tab['icon']}'></i>" : "";
            $icon = apply_filters("wpdm_settings_menu_item_icon", $icon, $tab);
            echo "<li {$isactive}><a id='{$tab['id']}' data-icon='{$tab['icon']}' href='{$tab['link']}'>{$icon}{$tab['title']}</a></li>";
        }
    }

    function basic(){

        if (isset($_POST['task']) && $_POST['task'] == 'wdm_save_settings') {

            if(!current_user_can('manage_options')) die(__( "You are not allowed to change settings!", "download-manager" ));

            if(!wp_verify_nonce($_POST['__wpdms_nonce'], WPDMSET_NONCE_KEY)) die(__('Security token is expired! Refresh the page and try again.', 'download-manager'));

            if ($_POST['__wpdm_curl_base'] == '') $_POST['__wpdm_curl_base'] = 'wpdm-category';
            if ($_POST['__wpdm_purl_base'] == '') $_POST['__wpdm_purl_base'] = 'wpdm-package';
            if ($_POST['__wpdm_curl_base'] == $_POST['__wpdm_purl_base']) $_POST['__wpdm_curl_base'] .= 's';
            foreach ($_POST as $optn => $optv) {
                if(strpos("__".$optn, '_wpdm_')) {
                    if($optn === '_wpdm_file_browser_root') {
	                    $optv = realpath($optv);
	                    $optv = $optv ? trailingslashit($optv) : get_home_path();
	                    $optv = str_replace("\\", "/", $optv);
                    }
                    $optv = wpdm_sanitize_array($optv);
                    update_option($optn, $optv, false);
                }
            }

            WPDM()->apply->sfbAccess();

            if (!isset($_POST['__wpdm_skip_locks'])) delete_option('__wpdm_skip_locks');
            if (!isset($_POST['__wpdm_login_form'])) delete_option('__wpdm_login_form');
            if (!isset($_POST['__wpdm_cat_desc'])) delete_option('__wpdm_cat_desc');
            if (!isset($_POST['__wpdm_cat_img'])) delete_option('__wpdm_cat_img');
            if (!isset($_POST['__wpdm_cat_tb'])) delete_option('__wpdm_cat_tb');
            flush_rewrite_rules();
            global $wp_rewrite, $WPDM;
            $WPDM->registerPostTypeTaxonomy();
            $wp_rewrite->flush_rules();
            die('Settings Saved Successfully');
        }
        $show_db_update_notice = 0;
        if(Installer::dbUpdateRequired()){
	        $show_db_update_notice = 1;
            Installer::updateDB();
        }

        include wpdm_admin_tpl_path("settings/basic.php");

    }

    function userInterface(){

        if (isset($_POST['task']) && $_POST['task'] == 'wdm_save_settings' && current_user_can(WPDM_ADMIN_CAP)) {

            if(!wp_verify_nonce($_POST['__wpdms_nonce'], WPDMSET_NONCE_KEY)) die(__('Security token is expired! Refresh the page and try again.', 'download-manager'));

            foreach ($_POST as $optn => $optv) {
                if(strpos("__".$optn, '_wpdm_')) {
                    $optv = wpdm_sanitize_array($optv);
                    //echo $optn."=".$optv."<br/>";
                    update_option($optn, $optv, false);
                }
            }

            die(__( "Settings Saved Successfully", "download-manager" ));
        }
        include wpdm_admin_tpl_path("settings/user-interface.php");

    }


    function frontEnd(){
        if(isset($_POST['section']) && $_POST['section']=='frontend' && isset($_POST['task']) && $_POST['task']=='wdm_save_settings' && current_user_can(WPDM_ADMIN_CAP)){
            if(!wp_verify_nonce($_POST['__wpdms_nonce'], WPDMSET_NONCE_KEY)) die(__('Security token is expired! Refresh the page and try again.', 'download-manager'));

            foreach($_POST as $k => $v){
                if(strpos("__".$k, '_wpdm_')){
                    $v = wpdm_sanitize_array($v);
                    update_option($k, $v, false);
                }
            }



            global $wp_roles;

            $roleids = array_keys($wp_roles->roles);
            $adb_roles = maybe_unserialize(get_option('__wpdm_front_end_access',array()));
            $dzn_roles = maybe_unserialize(get_option('__wpdm_dropzone_access',array()));
            $roles = array_unique(array_merge($adb_roles, $dzn_roles));
            $naroles = array_diff($roleids, $roles);

            foreach($roles as $role) {
                $role = get_role($role);
                if(is_object($role))
                    $role->add_cap('upload_files');
            }

            foreach($naroles as $role) {
                $role = get_role($role);
                if(!isset($role->capabilities['edit_posts']) || $role->capabilities['edit_posts']!=1)
                    $role->remove_cap('upload_files');
            }

            $refresh = 0;

            $page_id = $_POST['__wpdm_user_dashboard'];
            if($page_id != '') {
                $page_name = get_post_field("post_name", $page_id);
                add_rewrite_rule('^' . $page_name . '/(.+)/?', 'index.php?page_id=' . $page_id . '&udb_page=$matches[1]', 'top');
                $refresh = 1;
            }

            $page_id = $_POST['__wpdm_author_dashboard'];
            if($page_id != '') {
                $page_name = get_post_field("post_name", $page_id);
                add_rewrite_rule('^' . $page_name . '/(.+)/?', 'index.php?page_id=' . $page_id . '&adb_page=$matches[1]', 'top');
                $refresh = 1;
            }

            $page_id = $_POST['__wpdm_author_profile'];
            if((int)$page_id > 0) {
                $page_name = get_post_field("post_name", $page_id);
                add_rewrite_rule('^' . $page_name . '/(.+)/?$', 'index.php?pagename=' . $page_name . '&profile=$matches[1]', 'top');
                $refresh = 1;
            }

            if($refresh == 1){
                global $wp_rewrite;
                $wp_rewrite->flush_rules(true);
            }

            die('Settings Saved Successfully!');
        }
        include wpdm_admin_tpl_path("settings/frontend.php");
    }

    function socialConnects(){
        if(isset($_POST['section']) && $_POST['section']=='social-connects' && isset($_POST['task']) && $_POST['task']=='wdm_save_settings' && current_user_can(WPDM_ADMIN_CAP)){

            if(!wp_verify_nonce($_POST['__wpdms_nonce'], WPDMSET_NONCE_KEY)) die(__('Security token is expired! Refresh the page and try again.', 'download-manager'));

            foreach($_POST as $k => $v){
                if(strpos("__".$k, '_wpdm_')){
                    update_option($k, wpdm_sanitize_array($v), false);
                }
            }
            die('Settings Saved Successfully!');
        }
        include wpdm_admin_tpl_path("settings/social-connects.php");
    }

    function buddypress(){
        if(isset($_POST['section']) && $_POST['section']=='buddypress' && isset($_POST['task']) && $_POST['task']=='wdm_save_settings' && current_user_can(WPDM_ADMIN_CAP)){

            if(!wp_verify_nonce($_POST['__wpdms_nonce'], WPDMSET_NONCE_KEY)) die(__('Security token is expired! Refresh the page and try again.', 'download-manager'));

            foreach($_POST as $k => $v){
                if(strpos("__".$k, '_wpdm_')){
                    update_option($k, wpdm_sanitize_array($v), false);
                }
            }
            die('Settings Saved Successfully!');
        }
        include wpdm_admin_tpl_path("settings/buddypress.php");
    }

    function cloudStorage(){
        if(isset($_POST['section']) && $_POST['section']=='cloud-storage' && isset($_POST['task']) && $_POST['task']=='wdm_save_settings' && current_user_can(WPDM_ADMIN_CAP)){

            if(!wp_verify_nonce($_POST['__wpdms_nonce'], WPDMSET_NONCE_KEY)) die(__('Security token is expired! Refresh the page and try again.', 'download-manager'));

            foreach($_POST as $k => $v){
                if(strpos("__".$k, '_wpdm_')){
                    update_option($k, wpdm_sanitize_array($v), false);
                }
            }
            do_action("wpdm_save_settings_cloud-storage");
	        wp_send_json(['success' => true, 'msg' => 'Settings saved successfully!']);
        }
        include wpdm_admin_tpl_path("settings/cloud-storage.php");
    }

    function pluginUpdate(){
        if(isset($_REQUEST['__lononce']) && wp_verify_nonce($_REQUEST['__lononce'], WPDMSET_NONCE_KEY)){
            delete_option('__wpdm_suname');
            delete_option('__wpdm_supass');
            delete_option('__wpdm_purchased_items');
            delete_option('__wpdm_freeaddons');
            delete_option('__wpdm_core_update_check');
            delete_option('__wpdm_access_token');
            Session::clear('__wpdm_download_url');
            die('<script>location.href="edit.php?post_type=wpdmpro&page=settings&tab=plugin-update";</script>Refreshing...');
        }

        if(isset($_POST['__wpdm_suname']) && $_POST['__wpdm_suname'] != ''){

            if(!wp_verify_nonce($_POST['__wpdms_nonce'], WPDMSET_NONCE_KEY)) die(__('Security token is expired! Refresh the page and try again.', 'download-manager'));

            update_option('__wpdm_suname',$_POST['__wpdm_suname'], false);
            update_option('__wpdm_supass',$_POST['__wpdm_supass'], false);
            delete_option('__wpdm_purchased_items');
            delete_option('__wpdm_freeaddons');
            delete_option('__wpdm_core_update_check');
            delete_option('__wpdm_access_token');
            Session::clear('__wpdm_download_url');
            $access_token = wpdm_access_token();
            if($access_token != '') {
                $purchased_items = wpdm_remote_get('https://www.wpdownloadmanager.com/?wpdmppaction=getpurchaseditems&wpdm_access_token=' . $access_token);
                $ret = json_decode($purchased_items);
                update_option('__wpdm_purchased_items', $purchased_items, false);
                die('<script>location.href=location.href;</script>Login successful. Refreshing...');
            }  else{
                die('Error: Invalid Login!');
            }

        }
        if(isset($_POST['__wpdm_suname'], $_POST['__wpdm_supass']) && empty($_POST['__wpdm_suname'])){
            die('Error: Must enter a valid username!');
        }

        if(get_option('__wpdm_suname') != '') {
            $purchased_items = get_option('__wpdm_purchased_items', false);
            if(!$purchased_items || wpdm_query_var('newpurchase') != '' ) {
                $purchased_items = wpdm_remote_get('https://www.wpdownloadmanager.com/?wpdmppaction=getpurchaseditems&wpdm_access_token=' . wpdm_access_token());
                update_option('__wpdm_purchased_items', $purchased_items, false);
                delete_option('wpdm_latest');
                delete_option('wpdm_latest_check');
            }
            $purchased_items = json_decode($purchased_items);
            //wpdmdd($purchased_items);
            if (isset($purchased_items->error)){ delete_option('__wpdm_suname');  delete_option('__wpdm_purchased_items'); }
            if (isset($purchased_items->error)) $purchased_items->error = str_replace("[redirect]", admin_url("edit.php?post_type=wpdmpro&page=settings&tab=plugin-update"), $purchased_items->error);
        }
        if(get_option('__wpdm_freeaddons') == '' || wpdm_query_var('newpurchase') != '') {
            $freeaddons = wpdm_remote_get('https://www.wpdownloadmanager.com/?wpdm_api_req=getPackageList&cat_id=1148');
            update_option('__wpdm_freeaddons', $freeaddons, false);
        }
        $freeaddons = json_decode(get_option('__wpdm_freeaddons'));
        include wpdm_admin_tpl_path("settings/addon-update.php");
    }

    function License()
    {
        if (isset($_POST['task']) && $_POST['task'] == 'wdm_save_settings') {

            if (is_valid_license_key()) {
                delete_option('__wpdm_core_update_check');
                Session::clear('__wpdm_download_url');
                update_option('_wpdm_license_key', esc_attr($_POST['_wpdm_license_key']), false);
                wp_send_json(['success' => true, 'msg' => 'Congratulation! Your <b>Download Manager</b> copy registered successfully!', 'reload' => true]);
            } else {
                delete_option('_wpdm_license_key');
	            wp_send_json(['success' => false, 'msg' => 'Invalid License Key!']);
            }
        }
        $license = json_decode(get_option('__wpdm_license_det'));
        ?>
        <div class="panel panel-default">

            <div class="panel-heading"><b>License Key&nbsp;</b></div>
            <div class="panel-body"><input type="text" placeholder="Enter License Key" class="form-control input-lg" value="<?php echo get_option('_wpdm_license_key'); ?>"
                                           name="_wpdm_license_key"/></div>

        </div>
        <?php if($license && $license->order_id) {

            ?>
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading"><?= __('Order ID', WPDM_TEXT_DOMAIN) ?></div>
                    <div class="panel-body">
                        <?= $license->order_id; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading"><?= __::valueof($license, 'auto_renew', [], 'int') ? __('Next Billing Date', WPDM_TEXT_DOMAIN) :  __('Expiration Date', WPDM_TEXT_DOMAIN) ?></div>
                    <div class="panel-body">
                        <?= date(get_option('date_format'), $license->expire); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
        <?php

    }

    function Privacy(){
        if (wpdm_query_var('task') == 'wdm_save_settings' && wpdm_query_var('section') == 'privacy') {
            update_option('__wpdm_noip', wpdm_query_var('__wpdm_noip', 'int', 0));
            update_option('__wpdm_delstats_on_udel', wpdm_query_var('__wpdm_delstats_on_udel', 'int', 0));
            update_option('__wpdm_checkout_privacy', wpdm_query_var('__wpdm_checkout_privacy', 'int', 0));
            update_option('__wpdm_checkout_privacy_label', wpdm_query_var('__wpdm_checkout_privacy_label', 'txt'));
            update_option('__wpdm_tmp_storage', wpdm_query_var('__wpdm_tmp_storage', 'txt', 'db'));
            update_option('__wpdm_auto_clean_cache', wpdm_query_var('__wpdm_auto_clean_cache', 'int', 0));
            _e("Privacy Settings Saved Successfully", "download-manager");
            die();
        }
        include wpdm_admin_tpl_path("settings/privacy.php");
    }


}
