<?php
/**
 * Author: shahnuralam
 * Date: 2018-12-28
 * Updated: 2023-12-01
 * Version: 6.4.3
 */
if (!defined('ABSPATH')) die();
$site_key = get_option('_wpdm_recaptcha_site_key');
if($site_key) {
    $PID = (int)$package['ID'];

    $newtab = (int)get_option('__wpdm_open_in_new_window');
?>
<div class='card'><div class='card-header'>
        <span id="capc_label_<?php echo $PID; ?>"><?=esc_attr__("Verify CAPTCHA to Download", "download-manager"); ?></span>
        <span id="capcv_label_<?php echo $PID; ?>" style="display: none"><?php _e( "Your Download Link is Ready" , "download-manager" ); ?></span>
    </div>
    <div class='panel-body card-body wpdm-social-locks text-center'>
<script src='https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit'></script>
<div  id="reCaptchaLock_<?php echo $PID; ?>"></div>
<div id="msg_<?php echo $PID; ?>"></div>
<script type="text/javascript">
    var ctz = new Date().getMilliseconds();
    var siteurl = "<?php echo home_url('/?__wpdmnocache='); ?>"+ctz,force="<?php echo esc_attr($force); ?>";
    var verifyCallback_<?php echo $PID; ?> = function(response) {
        jQuery.post("<?php echo wpdm_rest_url('validate-captcha'); ?>",{__wpdm_ID:<?php echo $PID;?>, __wpdmfl: '<?php echo wpdm_query_var('__wpdmfl') ?>',dataType:'json',execute:'wpdm_getlink',force:force,social:'c',reCaptchaVerify:response,action:'wpdm_ajax_call'},function(res){
            if(res !== undefined && res.downloadurl !== undefined && res.downloadurl !== '') {

                <?php if($newtab) { ?>
                if(res.autostart === true) {
                    window.open(res.downloadurl, '_blank');
                }
                <?php } else { ?>
                if(res.autostart === true) {
                    if (window.parent === undefined)
                        location.href = res.downloadurl;
                    else
                        window.parent.location.href = res.downloadurl;
                }
                <?php } ?>
                jQuery('#capc_label_<?php echo $PID; ?>').hide();
                jQuery('#capcv_label_<?php echo $PID; ?>').show();
                jQuery('#reCaptchaLock_<?php echo $PID; ?>').html('<a onclick="window.open(\''+ res.downloadurl +'\', \'<?= $newtab ? '_blank' : '' ?>\')" href="#" class="wpdm-download-button btn btn-success btn-block btn-lg"><?php _e( "Download" , "download-manager" ); ?></a>');
            } else {
                jQuery('#msg_<?php echo $PID; ?>').html(''+res.error);
            }
        });
    };
    var widgetId2;
    var onloadCallback = function() {
        grecaptcha.render('reCaptchaLock_<?php echo $PID; ?>', {
            'sitekey' : '<?php echo $site_key; ?>',
            'callback' : verifyCallback_<?php echo $PID; ?>,
            'theme' : 'light'
        });
    };
</script>
    </div></div>
<?php }
