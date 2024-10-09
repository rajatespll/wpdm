<?php

if(!defined('ABSPATH')) die();
$logo = wpdm_valueof($params, 'logo');
if(!$logo) $logo = get_site_icon_url();
?>
<div class="w3eden">
    <div id="wpdmlogin" <?php if(wpdm_query_var('action') == 'lostpassword') echo 'class="lostpass"'; ?>>
        <?php if($logo && !is_user_logged_in()){ ?>
            <div class="text-center wpdmlogin-logo">
                <a href="<?php echo home_url('/'); ?>"><img alt="Logo" src="<?php echo esc_attr($logo);?>" /></a>
            </div>
        <?php } ?>






        <?php do_action("wpdm_before_login_form"); ?>


        <form name="loginform" id="loginform" action="" method="post" class="login-form" >

            <input type="hidden" name="permalink" value="<?php the_permalink(); ?>" />

            <div id="__signin_msg"><?php
                $wpdm_signup_success = \WPDM\__\Session::get('__wpdm_signup_success');
                if(isset($_GET['signedup'])){
                    if($wpdm_signup_success == '') $wpdm_signup_success = apply_filters("wpdm_signup_success", __("Your account has been created successfully.", "download-manager"));
                    ?>
                    <div class="alert alert-success dismis-on-click">
                        <?= $wpdm_signup_success; ?>
                    </div>
                    <?php
                }
                ?></div>


            <?php
            if(isset($params['note_before']) && $params['note_before'] !== '') {  ?>
                <div class="alert alert-info alert-note-before mb-3" >
                    <?= esc_attr($params['note_before']); ?>
                </div>
            <?php } ?>

            <?= $this->formFields($params); ?>


            <?php  if(isset($params['note_after']) && $params['note_before'] !== '') {  ?>
                <div class="alert alert-info alter-note-after mb-3" >
                    <?= esc_attr($params['note_after']); ?>
                </div>
            <?php } ?>

            <?php do_action("wpdm_login_form"); ?>
            <?php do_action("login_form"); ?>

            <div class="row login-form-meta-text text-muted mb-3" style="font-size: 10px">
                <div class="col-md-5"><label><input class="wpdm-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /><?php _e( "Remember Me" , WPDM_TEXT_DOMAIN ); ?></label></div>
                <div class="col-md-7 text-right"><a class="color-blue" href="<?php echo wpdm_lostpassword_url(); ?>"><?php _e( "Forgot Password?" , WPDM_TEXT_DOMAIN ); ?></a></div>
            </div>

            <div class="row">
                <div class="col-md-12"><button type="submit" name="wp-submit" id="loginform-submit" class="btn btn-block btn-primary btn-lg"><i class="fas fa-user-shield"></i> &nbsp;<?php _e( "Login" , WPDM_TEXT_DOMAIN ); ?></button></div>
                <?php if(isset($regurl) && $regurl != ''){ ?>
                    <div class="col-md-12"><br/><a href="<?php echo esc_attr($regurl); ?>" class="btn btn-block btn-link btn-xs wpdm-reg-link  color-primary"><?php _e( "Don't have an account yet?" , WPDM_TEXT_DOMAIN ); ?> <i class="fas fa-user-plus"></i> <?php _e( "Register Now" , WPDM_TEXT_DOMAIN ); ?></a></div>
                <?php } ?>
            </div>

            <div class="row">
                <?php
                if(count($__wpdm_social_login) > 1) { ?>
                    <div class="col-sm-12">
                        <div class="text-center card card-default" style="margin: 20px 0 0 0">
                            <?php if(!$_social_only){ ?>
                                <div class="card-header c-pointer" data-toggle="collapse" href="#socialllogin" role="button" aria-expanded="false" aria-controls="socialllogin"><?php echo isset($params['social_title'])?$params['social_title']:__("Or login using your social account", "download-manager"); ?></div>
                            <?php } else { ?>
                                <div class="card-header c-pointer"><?php echo isset($params['social_title'])?$params['social_title']:__("Login using your social account", "download-manager"); ?></div>
                            <?php } ?>
                            <?php if(!$_social_only){ ?><div class="collapse" id="socialllogin"><?php } ?>
                                <div class="card-body">
                                    <?php if(isset($__wpdm_social_login['facebook'])){ ?><button type="button" onclick="return _PopupCenter('<?php echo home_url('/?sociallogin=facebook'); ?>', 'Facebook', 400,400);" class="btn btn-social wpdm-facebook wpdm-facebook-connect"><i class="fab fa-facebook-f"></i></button><?php } ?>
                                    <?php if(isset($__wpdm_social_login['twitter'])){ ?><button type="button" onclick="return _PopupCenter('<?php echo home_url('/?sociallogin=twitter'); ?>', 'Twitter', 400,400);" class="btn btn-social wpdm-twitter wpdm-linkedin-connect"><i class="fab fa-twitter"></i></button><?php } ?>
                                    <?php if(isset($__wpdm_social_login['linkedin'])){ ?><button type="button" onclick="return _PopupCenter('<?php echo home_url('/?sociallogin=linkedin'); ?>', 'LinkedIn', 400,400);" class="btn btn-social wpdm-linkedin wpdm-twitter-connect"><i class="fab fa-linkedin-in"></i></button><?php } ?>
                                    <?php if(isset($__wpdm_social_login['google'])){ ?><button type="button" onclick="return _PopupCenter('<?php echo home_url('/?sociallogin=google'); ?>', 'Google', 400,400);" class="btn btn-social wpdm-google-plus wpdm-google-connect"><i class="fab fa-google"></i></button><?php } ?>
                                </div>
                                <?php if(!$_social_only){ ?></div><?php } ?>
                        </div>
                    </div>

                <?php } ?>
            </div>


            <input type="hidden" name="redirect_to" value="<?= htmlspecialchars_decode($log_redirect); ?>" />



        </form>



        <?php do_action("wpdm_after_login_form"); ?>

    </div>


</div>
<script>
    jQuery(function ($) {
        <?php if(!isset($params['form_submit_handler']) || $params['form_submit_handler'] !== false){ ?>
        var llbl = $('#loginform-submit').html();
        $('#loginform').submit(function () {
            $('#loginform-submit').html(WPDM.html("i", "", "fa fa-spin fa-sync")+" <?php _e( "Logging In..." , WPDM_TEXT_DOMAIN ); ?>").attr('disabled', 'disabled');
            WPDM.blockUI('#loginform');
            $(this).ajaxSubmit({
                error: async function(error) {
                    WPDM.unblockUI('#loginform');
                    console.log(error);
                    if(typeof error.responseJSON !== 'undefined') {
                    $('#loginform').prepend(WPDM.html("div", error.responseJSON.messages, "alert alert-danger"));
                    $('#loginform-submit').html(llbl).removeAttr('disabled');
                    <?php if((int)get_option('__wpdm_recaptcha_loginform', 0) === 1 && get_option('_wpdm_recaptcha_site_key') != ''){ ?>
                    try {
                        grecaptcha.reset();
                    } catch (e) {

                    }
                    <?php } ?>
                    } else {
                        setTimeout(function () {
                            location.href = "<?= wp_sanitize_redirect(htmlspecialchars_decode($log_redirect)); ?>";
                        }, 1000);
                    }
                },
                success: async function (res) {
                    WPDM.unblockUI('#loginform');
                    if (!res.success) {
                        $('form .alert-danger').hide();
                        $('#loginform').prepend(WPDM.html("div", res.message, "alert alert-danger"));
                        $('#loginform-submit').html(llbl).removeAttr('disabled');
                        <?php if((int)get_option('__wpdm_recaptcha_loginform', 0) === 1 && get_option('_wpdm_recaptcha_site_key') != ''){ ?>
                        try {
                            grecaptcha.reset();
                        } catch (e) {

                        }
                        <?php } ?>
                    } else {
                        let proceed = await WPDM.doAction("wpdm_user_login", res);
                        $('#loginform-submit').html(WPDM.html("i", "", "fa fa-sun fa-spider") + " " + res.message);
                        setTimeout(function () {
                            location.href = "<?= wp_sanitize_redirect(htmlspecialchars_decode($log_redirect)); ?>";
                        }, 1000);
                    }
                }
            });
            return false;
        });
        <?php } ?>
        $('body').on('click', 'form .alert-danger', function(){
            $(this).slideUp();
        });

    });
</script>
