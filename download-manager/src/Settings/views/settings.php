<?php
/**
 * Base: LiveForms
 * Developer: shahjada
 * Team: W3 Eden
 * Date: 7/8/20 17:55
 */

use PrivateMessage\Dialogflow\Dialogflow;

if(!defined("ABSPATH")) die();
?>
<div class="wrap w3eden fixed-top with-sidebar">
    <div id="wppmsg-admin-container">
        <nav class="navbar navbar-default navbar-fixed-top-">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <div class="navbar-brand">
                        <div class="d-flex">
                            <div class="logo">
                                <img src="<?= PM_ASSET_URL ?>images/pmsg.svg" style="width: 40px" alt="LF" />
                            </div>
                            <div style="font-size: 12pt;font-weight: 700;letter-spacing: 0.5px">
                                <?= esc_attr__( 'Settings', PM_TEXT_DOMAIN ) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <ul class="nav navbar-nav navbar-right">
                    <li><button type="button" class="btn btn-lg btn-primary" id="wppmsg_save_settings_btn"><i class="fas fa-hdd"></i> <?= esc_attr__( 'Save Settings', PM_TEXT_DOMAIN ) ?></button></li>
                </ul>
        </nav>

        <div id="wppmsg-admin-content" class="with-sidebar">
            <div id="wppmsg-content-sidebar">

                    <?php //echo $this->settings_api->section_tabs(); ?>

                <div id="tabs" class="nav flex-column nav-pills settings-tabs">
                    <?php PrivateMessage()->settings->renderMenu($tab); ?>
                </div>
            </div>
            <div id="wppmsg-content-container">
                <form id="wppmsg-settings-form" method="post">
                <div id="wppmsg-settings-content">
                    <?php
                    PrivateMessage()->settings->renderSettingsTab($tab);
                    ?>

                </div>
                </form>
                <div id="footernotice">

                </div>
            </div>
        </div>

    </div>
</div>

<script type="text/javascript">

    var _notice = {
        show: function (message, success) {
          if(success === true)
              _notice.success(message);
          else
              _notice.error(message);
        },
        error: function (message) {
            jQuery('#footernotice').html(message).removeClass('show error success').addClass('show error');
        },
        success: function (message) {
            jQuery('#footernotice').html(message).removeClass('show error success').addClass('show success');
        },
        hide: function (message) {
            jQuery('#footernotice').html(message).removeClass('show error success');
        }
    }

    function reload_tab(tabid) {
        jQuery('#tabs #' + tabid).trigger('click');
    }

    jQuery(function($){
        $(".chosen").chosen();
        $("ul#tabs li").click(function() {

        });
        $('#footernotice').on('click', function (){
            $(this).removeClass('show error success');
        });
        $('#message').removeClass('hide').hide();
        $("#tabs a.nav-link").click(function() {
            $("#tabs a.nav-link").removeClass("active");
            $(this).addClass('active');
            $('#wdms_loading').addClass('wpdm-spin');
            $('.wppmsg-menu-loading').remove();
            var secid = 'wppmsg-menu-loading-'+this.id;
            $(this).append('<i class="far fa-sun fa-spin float-right" id="'+secid+'" style="line-height: 46px"></i>')
            var section = this.id;
            _notice.hide();
            $.post(ajaxurl,{action:'wppmsg_settings',section:this.id},function(res){
                $('#wppmsg-settings-content').html(res);
                $(".chosen").chosen();
                $('#'+secid).remove();
                window.history.pushState({"html":res,"pageTitle":"response.pageTitle"},"", "admin.php?page=pmsettings&tab="+section);
            });
            return false;
        });

        window.onpopstate = function(e){
            if(e.state){
                jQuery("#fm_settings").html(e.state.html);
                //document.title = e.state.pageTitle;
            }
        };


        $('#wppmsg_save_settings_btn').click(function(){
            $('#wppmsg-admin-content').addClass('blockui');
            var $btn = $(this);
            var btntxt = $btn.html();
            var w = (parseInt($btn.width())+60)+'px';
            $btn.attr('disabled', 'disabled').css('min-width', w).html('<i class="far fa-sun fa-spin"></i> <?= __('Saving...', 'wppmsg'); ?>');
            _notice.hide();
            $('#wppmsg-settings-form').ajaxSubmit({
                url: ajaxurl,
                beforeSubmit: function(formData, jqForm, options){

                },
                success: function(response, statusText, xhr, $form){
                    $('#wppmsg-admin-content').removeClass('blockui');
                    $btn.removeAttr('disabled', 'disabled').html(btntxt);
                    if(response.success === true)
                        _notice.success(response.message);
                    if(response.success === false)
                        _notice.error(response.message);
                },
                error: function (error) {
                    console.log(error.responseText);
                }
            });

            return false;
        });

        $('body').on("click",'.nav-tabs a', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });



    });

</script>
