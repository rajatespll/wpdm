<?php

namespace WPDM\Package;

global $gp1c, $tbc;


use WPDM\__\__;
use WPDM\__\__MailUI;
use WPDM\__\Crypt;
use WPDM\__\Email;
use WPDM\__\Parser;
use WPDM\__\Session;
use WPDM\__\Template;
use WPDM\__\UI;
use WPDM\SocialConnect\SocialConnect;

class PackageLocks
{

    public function __construct(){

    }


    public static function linkedInShare($package)
    {

        return "<button class='wpdm-social-lock btn wpdm-linkedin' data-url='".SocialConnect::LinkedinAuthUrl($package['ID'])."'><i class='fab fa-linkedin-in'></i> ".__( "Share", "download-manager" )."</button>";


    }

    public static function twitterFollow($package){

        return "<button class='wpdm-social-lock btn wpdm-twitter' data-url='".SocialConnect::TwitterAuthUrl($package['ID'], 'follow')."'><i class='fab fa-twitter'></i> Follow</button>";

    }

    public static function askPassword($package){
        ob_start();
        $unqid = uniqid();
        $field_id = $unqid.'_'.$package['ID'];
        include Template::locate("lock-options/password-lock.php", __DIR__.'/views');
        $data = ob_get_clean();
        return $data;
    }

    public static  function askEmail($package)
    {

        $data = '<div class="alert alert-danger">'.esc_attr__( 'Email Lock Is Not Enabled for This Download!', 'download-manager' ).'</div>';
        if (isset($package['email_lock']) && $package['email_lock'] == '1') {

            $lock = 'locked';
            $unqid = uniqid();
            $package['email_lock_title'] = !isset($package['email_lock_title']) || $package['email_lock_title'] === '' ? get_post_meta($package['ID'], '__wpdm_email_lock_title', true) : $package['email_lock_title'];
            $section_title = $package['email_lock_title'] != ''? $package['email_lock_title']:__( "Subscribe To Download" , "download-manager" );
            $button_label = isset($package['button_label']) ? $package['button_label'] : __( "Download" , "download-manager" );
            $form_button_label = __( "Submit" , "download-manager" );
            $form_button_label = apply_filters("wpdm_email_lock_form_button_label", $form_button_label, $package);
            $intro = isset($package['email_intro']) ? "<p>" . $package['email_intro'] . "</p>" : '';
            $field_id = $unqid.'_'.$package['ID'];

            ob_start();
            include Template::locate("lock-options/email-lock-form.php", __DIR__.'/views');
            $data = ob_get_clean();

        }
        return apply_filters("wpdm_email_lock_html", $data);
    }

    public static function tweet($package){
        return "<button class='wpdm-social-lock btn wpdm-twitter' data-url='".SocialConnect::TwitterAuthUrl($package['ID'])."'><i class='fab fa-twitter'></i> Tweet</button>";
    }

    public static function facebookLike($package, $buttononly = false)
    {

        return "<button class='wpdm-social-lock btn wpdm-facebook' data-url='".SocialConnect::FacebookLikeUrl($package['ID'])."'><i class='fab fa-facebook-f'></i> ".__( "Like", "download-manager" )."</button>";

    }

    function likedOnFacebook()
    {
        $limit = get_option('__wpdm_private_link_usage_limit', 3);
        $xpire_period = ((int)get_option('__wpdm_private_link_expiration_period', 3)) * ((int)get_option('__wpdm_private_link_expiration_period_unit', 60));
        $xpire_period = $xpire_period > 0 ? $xpire_period : 3600;
        $download_url = WPDM()->package->expirableDownloadLink(wpdm_query_var('__wpdm_ID', 'int'), $limit, $xpire_period);
        $data['downloadurl'] = $download_url;
		$data['autostart'] = (bool) ((int)get_option( '__wpdm_auto_download' ));
        wp_send_json($data);
        die();
    }

    public static function reCaptchaLock($package, $buttononly = false){
        ob_start();
        $force = str_replace("=", "", base64_encode("unlocked|" . date("Ymdh")));
        include Template::locate("lock-options/recaptcha-lock.php", __DIR__.'/views');
        return ob_get_clean();
    }

    function validateCaptcha()
    {
        $limit = get_option('__wpdm_private_link_usage_limit', 3);
        $xpire_period = ((int)get_option('__wpdm_private_link_expiration_period', 3)) * ((int)get_option('__wpdm_private_link_expiration_period_unit', 60));
        $xpire_period = $xpire_period > 0 ? $xpire_period : 3600;
        $ret = wpdm_remote_post('https://www.google.com/recaptcha/api/siteverify', array('secret' => get_option('_wpdm_recaptcha_secret_key'), 'response' => $_POST['reCaptchaVerify'], 'remoteip' => $_SERVER['REMOTE_ADDR']));
        $ret = json_decode($ret);
        if ($ret->success == 1) {
            $download_url = WPDM()->package->expirableDownloadLink(wpdm_query_var('__wpdm_ID', 'int'), $limit, $xpire_period);
            $data['downloadurl'] = $download_url;
	        $data['autostart'] = (bool) ((int)get_option( '__wpdm_auto_download' ));
        } else {
            $data['error'] = __("Captcha Verification Failed!", "wpmdpro");
        }

        wp_send_json($data);
        die();
    }

    function validatePassword()
    {
        $password = isset($_REQUEST['password']) ? sanitize_text_field($_REQUEST['password']) : null;
        $packageID = wpdm_query_var('__wpdm_ID', ['validate' => 'int']);
		$file = wpdm_query_var('__wpdmfl', 'txt');

	    if(!$password || !$packageID) wp_send_json(['message' => __("Invalid request!", "download-manager"), 'success' => false]);

        $passwords = WPDM()->package->isPasswordProtected($packageID);
        $passwordUsage = maybe_unserialize(get_post_meta($packageID, '__wpdm_password_usage', true));
        $passwordUsageLimit = (int)get_post_meta($packageID, '__wpdm_password_usage_limit', true);

        $limit = get_option('__wpdm_private_link_usage_limit', 3);
        $expirePeriod = ((int)get_option('__wpdm_private_link_expiration_period', 60)) * ((int)get_option('__wpdm_private_link_expiration_period_unit', 60));
        $expirePeriod = $expirePeriod > 0 ? $expirePeriod : 3600;

        $data = [ 'success' => false ];
        $error = false;
        //Check if the given password is matched
        if ($passwords && $password != $passwords && substr_count($passwords, "[$password]") < 1) {
            $data['message'] = __("Wrong Password!", "download-manager") . " &nbsp; <span><i class='fas fa-redo'></i> " . __("Try Again", "download-manager") . " </span>";
			$error = true;
        }

        //Check if given password is empty
        if ($passwords && $password == '') {
            $data['message'] = __("Wrong Password!", "download-manager") . " &nbsp; <span class='color-blue'><i class='fas fa-redo'></i> " . __("Try Again", "download-manager") . " </span>";
            $file = array();
            $error = true;
        }

        $passwordUsed = wpdm_valueof($passwordUsage, $password, ['validate' => 'int']);
        if( $passwordUsageLimit > 0  && $passwordUsed >= $passwordUsageLimit)
            $data['message'] = __("Password usages limit exceeded", "download-manager");
        else if(!$error){
            if(!is_array($passwordUsage)) $passwordUsage = [];
            Session::set("pass_verified_" . $packageID, 1);
            $passwordUsage[$password] = wpdm_valueof($passwordUsage, $password, ['validate' => 'int']) + 1;
            update_post_meta($packageID, '__wpdm_password_usage', $passwordUsage);
			$download_url = WPDM()->package->expirableDownloadLink($packageID, $limit, $expirePeriod);
			$download_url = $file ? add_query_arg(['ind' => $file], $download_url) : $download_url;
            $data = ['success' => true, 'downloadurl' => $download_url];
	        $data['autostart'] = (bool) ((int)get_option( '__wpdm_auto_download' ));
        }
        wp_send_json($data);
    }

    /**
     * @usage Validate individual file password
     */
    function checkFilePassword()
    {
        $limit = get_option('__wpdm_private_link_usage_limit', 3);
        $packageID = wpdm_query_var('wpdmfileid', 'int');
		$fileID = wpdm_query_var('wpdmfile', 'txt');
	    $password = wpdm_query_var('filepass', 'html');
	    if(!$password || !$packageID) wp_send_json(['msg' => __("Invalid request!", "download-manager"), 'success' => false]);
	    $passwords = WPDM()->package->getPasswords($packageID, $fileID);
        $_passwordUsage = maybe_unserialize(get_post_meta($packageID, '__wpdm_password_usage', true));
        $passwordUsage = __::valueof($_passwordUsage, $password, ['default' => 0, 'validate' => 'int']);

        if ($password == $passwords || substr_count($passwords, "[{$password}]")) {
            $passwordUsageLimit = get_post_meta($packageID, '__wpdm_password_usage_limit', true);
            if ($passwordUsage >= $passwordUsageLimit && $passwordUsageLimit > 0) {
                $data = ['success' => false, 'msg' => __("Password usages limit exceeded", "download-manager")];
                wp_send_json($data);
            } else {
                if (!is_array($_passwordUsage)) $_passwordUsage = [];
                $_passwordUsage[$password] = $passwordUsage + 1;
                update_post_meta($packageID, '__wpdm_password_usage', $_passwordUsage);
            }

            wp_send_json(['success' => true, 'downloadurl' => WPDM()->package->expirableDownloadLink($packageID, $limit)."&ind={$fileID}", 'autostart' => (bool) ((int)get_option( '__wpdm_auto_download' ))]);

        } else
            wp_send_json(['msg' => __("Invalid password", "download-manager"), 'success' => false]);

    }

    function handleEmailLock()
    {
        global $wpdb;
        $data = [ 'success' => false ];

        $packageID = wpdm_query_var('__wpdm_ID', ['validate' => 'int']);
	    $file = wpdm_query_var('__wpdmfl', 'txt');

        if(!$packageID || get_post_type($packageID) !== 'wpdmpro') {
            $data['message'] = esc_attr__( 'Package not found!', 'download-manager' );
            wp_send_json($data);
        }

        $emailLocked = (int)get_post_meta($packageID, '__wpdm_email_lock', true);
        $limit = get_option('__wpdm_private_link_usage_limit', 3);
        $expirePeriod = ((int)get_option('__wpdm_private_link_expiration_period', 60)) * ((int)get_option('__wpdm_private_link_expiration_period_unit', 60));
        $expirePeriod = $expirePeriod > 0 ? $expirePeriod : 3600;
        $key = uniqid();



        if (!$emailLocked) {
            $data['message'] = esc_attr__( 'Email lock is not enabled for this package', 'download-manager' );
            wp_send_json($data);
        }

        if (wpdm_verify_email(wpdm_query_var('email'))) {

			if((int)get_option('__wpdm_email_lock_session')) {
				Session::set('email_lock', wpdm_query_var('email'));
			}

            $subject = esc_attr__( "Your Download Link", 'download-manager' );
            $site = get_option('blogname');

            $custom_form_data = isset($_POST['custom_form_field']) ? $_POST['custom_form_field'] : array();
            if (isset($_REQUEST['name'])) $custom_form_data = ['name' => $_REQUEST['name']] + $custom_form_data;

	        foreach ($custom_form_data as &$custom_field_value) {
		        if(substr_count($custom_field_value, 'hidden_')) {
			        $custom_field_value = str_replace("hidden_", "", $custom_field_value);
			        $custom_field_value = Crypt::decrypt($custom_field_value);
			        $custom_field_value = Parser::process($custom_field_value);
		        }
	        }

            /**
             * Do something before sending download link
             */
            do_action("wpdm_before_email_download_link", $_POST, ['ID' => $packageID]);

            /**
             *  $emailLockDownloadLink = 0 <-- Email download link now
             *  $emailLockDownloadLink = 1 <-- Show downwload link on screen / Download instantly
             *  $emailLockDownloadLink = 2 <-- Wait for admin approval
             */
            $emailLockDownloadLink = (int)get_post_meta($packageID, '__wpdm_email_lock_idl', true);
            /**
             * Do you also want to email download link when showing download link on screen
             * For condition $emailLockDownloadLink = 1
             */
            $emailDownloadLink = (int)get_post_meta($packageID, '__wpdm_email_lock_idl_email', true);

            $requestStatus = $emailLockDownloadLink === 0 ? 3 : $emailLockDownloadLink;
            $wpdb->insert("{$wpdb->prefix}ahm_emails", array('email' => wpdm_query_var('email'), 'pid' => $packageID, 'date' => time(), 'custom_data' => serialize($custom_form_data), 'request_status' => $requestStatus));
            $subscriberID = $wpdb->insert_id;

			$args = ['subscriber' => Crypt::encrypt($subscriberID)];
			if($file)
				$args['ind'] = $file;

            $downloadURL = add_query_arg($args, WPDM()->package->expirableDownloadLink($packageID, $limit, $expirePeriod));
            $downloadPageURL = add_query_arg($args, WPDM()->package->expirableDownloadPage($packageID, $limit, $expirePeriod));

            if ($emailLockDownloadLink === 0 || ($emailLockDownloadLink == 1 && $emailDownloadLink == 0)) {
                $name = isset($cff['name']) ? $cff['name'] : '';
                $email_params = array('to_email' => $_POST['email'], 'name' => $name, 'download_count' => $limit, 'package_name' => get_the_title($packageID), 'package_url' => get_permalink($packageID), 'download_url' => $downloadURL, 'download_page_url' => $downloadPageURL);
                $email_params = apply_filters("wpdm_email_lock_mail_params", $email_params, ['ID' => $packageID]);
                \WPDM\__\Email::send("email-lock", $email_params);
            }
            $elmsg = sanitize_textarea_field(get_post_meta($packageID, '__wpdm_email_lock_msg', true));
            if ($emailLockDownloadLink === 0) {
                $data['downloadurl'] = "";
                $data['message'] = ($elmsg != '' ? $elmsg : __("Download link sent to your email!", "download-manager"));
                $data['success'] = true;
            } else if ($emailLockDownloadLink === 2) {
                $data['downloadurl'] = "";
                $data['message'] = ($elmsg != '' ? $elmsg : __("Admin will review your request soon!", "download-manager"));
                $data['success'] = true;

	            $message = __MailUI::panel("Your have a new pending download request for:", ["<a href='".get_permalink($packageID)."'>".get_the_title($packageID)."</a>"])
	                       ."<a href='".admin_url('edit.php?post_type=wpdmpro&page=wpdm-subscribers')."' class='button full green'>".__('Review the request', WPDM_TEXT_DOMAIN)."</a>";

	            $email               = new Email();
	            $email->to( get_option( 'admin_email' ) )
	                  ->from( 'no-reply@' . $_SERVER['HTTP_HOST'], get_bloginfo( 'name' ) )
	                  ->subject('Download Request For '.get_the_title($packageID))
	                  ->message( $message )
	                  ->template( 'default' )
	                  ->sendMail();

            } else {
                $data['success'] = true;
                $data['downloadurl'] = $downloadURL;
	            $data['autostart'] = (bool) ((int)get_option( '__wpdm_auto_download' ));
                if ($emailDownloadLink == 0)
                    $data['message'] = ($elmsg != '' ? $elmsg : __("Download link also sent to your email!", "download-manager"));
                else
                    $data['message'] = ($elmsg != '' ? $elmsg : __("Your download link is ready!", "download-manager"));
            }

            $data = apply_filters("wpdm_email_lock_response_data", $data);


            $_pdata = $_POST;
            $_pdata['pid'] = $packageID;
            $_pdata['time'] = time();

            Session::set("__wpdm_email_lock_verified", $_pdata, 604800);

            wp_send_json($data);
            die();
        } else {
            $data['downloadurl'] = "";
            $data['message'] = get_option('__wpdm_blocked_domain_msg');
            if (trim($data['message']) === '') $data['message'] = __("Invalid Email Address!", "download-manager");
            $data['success'] = false;

            wp_send_json($data);
            die();
        }
    }

}
