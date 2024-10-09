<?php
if (!defined('ABSPATH')) die();
/**
 * User: shahnuralam
 * Date: 1/16/18
 * Time: 12:33 AM
 */
error_reporting(0);
//global $post;
$ID = wpdm_query_var('__wpdmlo');
//$post = get_post(wpdm_query_var('__wpdmlo'));
//setup_postdata($post);
//$pack = new \WPDM\Package();
//$pack->Prepare(get_the_ID());
$form_lock = (int)get_post_meta($ID, '__wpdm_form_lock', true);
$terms_lock = (int)get_post_meta($ID, '__wpdm_terms_lock', true);
$base_price = (double)get_post_meta($ID, '__wpdm_base_price', true);
?><!DOCTYPE html>
<html style="background: transparent">
<head>
    <title>Download <?php get_the_title($ID); ?></title>
    <?php if($form_lock === 1  || $base_price > 0) wp_head(); else { ?>
        <link rel="stylesheet" href="<?php echo WPDM_ASSET_URL; ?>bootstrap/css/bootstrap.min.css" />
        <link rel="stylesheet" href="<?php echo WPDM_ASSET_URL; ?>css/front.css" />
        <link rel="stylesheet" href="<?= WPDM_FONTAWESOME_URL ?>" />
        <script src="<?php echo includes_url(); ?>/js/jquery/jquery.js"></script>
        <script src="<?php echo includes_url(); ?>/js/jquery/jquery.form.min.js"></script>
        <script src="<?php echo WPDM_ASSET_URL; ?>bootstrap/js/bootstrap.min.js"></script>
        <script src="<?php echo WPDM_ASSET_URL; ?>js/front.js"></script>
        <?php
        $_font = get_option('__wpdm_google_font', 'Sen');
        $font = explode(":", $_font);
        $font = $font[0];
        $font = $font ? $font . ',' : '';
        if($_font) {
        ?>
        <link href="https://fonts.googleapis.com/css2?family=<?php echo str_replace("regular", 400, $_font); ?>" rel="stylesheet">
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
            .w3eden .btn
            {
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
    <?php
    }
    $wpdmss = maybe_unserialize(get_option('__wpdm_disable_scripts', array()));
    $uicolors = maybe_unserialize(get_option('__wpdm_ui_colors', array()));
    $primary = isset($uicolors['primary']) ? $uicolors['primary'] : '#4a8eff';
    $secondary = isset($uicolors['secondary']) ? $uicolors['secondary'] : '#4a8eff';
    $success = isset($uicolors['success']) ? $uicolors['success'] : '#18ce0f';
    $info = isset($uicolors['info']) ? $uicolors['info'] : '#2CA8FF';
    $warning = isset($uicolors['warning']) ? $uicolors['warning'] : '#f29e0f';
    $danger = isset($uicolors['danger']) ? $uicolors['danger'] : '#ff5062';
    $font = get_option('__wpdm_google_font', 'Sen');
    $font = explode(":", $font);
    $font = $font[0];
    $font = $font ? "\"{$font}\"," : '';
    if (is_singular('wpdmpro'))
	    $ui_button = get_option('__wpdm_ui_download_button');
    else
	    $ui_button = get_option('__wpdm_ui_download_button_sc');
    $class = ".btn." . (isset($ui_button['color']) ? $ui_button['color'] : 'btn-primary') . (isset($ui_button['size']) && $ui_button['size'] != '' ? "." . $ui_button['size'] : '');

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
    ?>
    <style>
        html, body{
            overflow: visible;
            height: 100%;
            width: 100%;
            padding: 0;
            margin: 0;
            font-weight: 300;
            font-size: 10pt;
            font-family: var(--wpdm-font);
        }
        h4.modal-title{
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #555555;
            font-size: 11pt;
            display: inline-block;
            font-family: var(--wpdm-font);
        }

        .w3eden label{
            font-weight: 400;
        }
        img{
            max-width: 100%;
        }
        .modal-backdrop{
            background: rgba(0,0,0,0.5);
        }

        .wpdm-social-lock.btn {
            display: block;
            width: 100%;
        }

        @-moz-keyframes spin {
            from { -moz-transform: rotate(0deg); }
            to { -moz-transform: rotate(360deg); }
        }
        @-webkit-keyframes spin {
            from { -webkit-transform: rotate(0deg); }
            to { -webkit-transform: rotate(360deg); }
        }
        @keyframes spin {
            from {transform:rotate(0deg);}
            to {transform:rotate(360deg);}
        }
        .spin{
            -webkit-animation-name: spin;
            -webkit-animation-duration: 2000ms;
            -webkit-animation-iteration-count: infinite;
            -webkit-animation-timing-function: linear;
            -moz-animation-name: spin;
            -moz-animation-duration: 2000ms;
            -moz-animation-iteration-count: infinite;
            -moz-animation-timing-function: linear;
            -ms-animation-name: spin;
            -ms-animation-duration: 2000ms;
            -ms-animation-iteration-count: infinite;
            -ms-animation-timing-function: linear;

            animation-name: spin;
            animation-duration: 2000ms;
            animation-iteration-count: infinite;
            animation-timing-function: linear;
            display: inline-block;
        }


        .w3eden .card-default {
            margin-top: 10px !important;
        }
        .btn{
            outline: none !important;
        }
        .w3eden .card{
            margin-bottom: 0;
        }
        .w3eden .card:last-child{
            margin-bottom: 10px !important;
        }
        .w3eden .modal-header{
            border: 0;
        }
        .w3eden .modal-content{
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.2);
            border: 0;
            border-radius: 6px;
            background: rgb(255,255,255);
            max-width: 100%;
        }
        .w3eden .modal-body{
            max-height:  calc(100vh - 210px);
            overflow-y: auto;
            padding: 0 10px !important;
        }


        .w3eden .input-group-lg .input-group-btn .btn{
            border-top-right-radius: 4px !important;
            border-bottom-right-radius: 4px !important;
        }
        .w3eden .wpforms-field-medium{
            max-width: 100% !important;
            width: 100% !important;
        }

        .w3eden .input-group.input-group-lg .input-group-btn .btn {
            font-size: 11pt !important;
        }

        .modal-icon{
            padding: 4px;
            display: inline-block;
            width: 72px;
            height: 72px;
            top: 0;
            border-radius: 500px;
            margin-top: -36px;
            left: calc(50% - 36px);
            box-shadow: 0 0 3px rgba(0,0,0,0.3);
            position: absolute;
            z-index: 999999;
            background: rgb(254,254,254);
            background: -moz-linear-gradient(45deg,  rgba(254,254,254,1) 19%, rgba(226,226,226,1) 100%);
            background: -webkit-linear-gradient(45deg,  rgba(254,254,254,1) 19%,rgba(226,226,226,1) 100%);
            background: linear-gradient(45deg,  rgba(254,254,254,1) 19%,rgba(226,226,226,1) 100%);
            filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#fefefe', endColorstr='#e2e2e2',GradientType=1 );
        }

        .modal-content{
            padding-top: 36px !important;
        }
        .close{
            position: absolute;
            z-index: 999999;
            top: 5px;
            right: 5px;
            opacity: 0 !important;
        }
        .modal-content h4{
            margin: 0 0 10px;
            font-size: 11pt;
        }
        .modal-content:hover .close{
            opacity: 0.8 !important;
        }
        .close:hover .fa-times-circle{
            color: #ff3c54 !important;
        }
        .close .fa-times-circle,
        .close:hover .fa-times-circle,
        .modal-content:hover .close,
        .close{
            -webkit-transition: ease-in-out 400ms;
            -moz-transition: ease-in-out 400ms;
            -ms-transition: ease-in-out 400ms;
            -o-transition: ease-in-out 400ms;
            transition: ease-in-out 400ms;
        }
        .wp-post-image{
            width: 100%;
            height: auto;
            border-radius: 500px;
        }

        .btn-viewcart,
        #cart_submit{
            line-height: 30px !important;
            width: 100%;
        }
        .w3eden h3.wpdmpp-product-price{
            text-align: center;
            margin-bottom: 30px !important;
        }
        .modal-icon img{
            border-radius: 500px;
            width: 100% !important;
            height: auto !important;
        }
        form *{
            max-width: 100% !important;
        }
        .card-body {
            line-height: 1.5;
            letter-spacing: 0.5px;
            font-size: 11pt;
        }
    </style>




    <?php do_action("wpdm_modal_iframe_head"); ?>
</head>
<body class="w3eden" style="background: transparent">

<div class="modal fade" id="wpdm-locks" tabindex="-1" role="dialog" aria-labelledby="wpdm-optinmagicLabel">
    <div class="modal-dialog modal-dialog-centered" role="document" style="width: <?php echo $terms_lock === 1?395:365; ?>px;max-width: calc(100% - 20px);">
        <div class="modal-content">
            <div class="modal-icon">
                <?php if(has_post_thumbnail($ID)) echo get_the_post_thumbnail($ID, 'thumbnail'); else echo WPDM()->package::icon($ID, true, 'p-2'); ?>
            </div>
            <div class="text-center mt-3 mb-3">
                <button type="button" class="close btn btn-link p-0" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">
                        <svg style="width: 24px" id="Outlined" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><title/><g id="Fill"><path d="M16,2A14,14,0,1,0,30,16,14,14,0,0,0,16,2Zm0,26A12,12,0,1,1,28,16,12,12,0,0,1,16,28Z"/><polygon points="19.54 11.05 16 14.59 12.46 11.05 11.05 12.46 14.59 16 11.05 19.54 12.46 20.95 16 17.41 19.54 20.95 20.95 19.54 17.41 16 20.95 12.46 19.54 11.05"/></g></svg>
                    </span></button>
                <h4 class="d-block"><?php echo ($base_price > 0)? __('Buy','download-manager'): __('Download','download-manager'); ?></h4>
                <div style="letter-spacing: 1px;font-weight: 400;margin-top: 5px" class="color-purple d-block"><?php echo get_the_title($ID); ?></div>
            </div>
            <div class="modal-body" id="wpdm-lock-options">
                <?php
                echo WPDM()->package->downloadLink(wpdm_query_var('__wpdmlo', 'int'), 1);
                ?>
            </div>

        </div>

    </div>
    <?php

    ?>
</div>

<script>

    jQuery(function ($) {

        $('a').each(function () {
            if($(this).attr('href') !== '#')
                $(this).attr('target', '_blank');
        });

        /*$('body').on('click','a', function () {
            if($(this).attr('href') !== '#')
                $(this).attr('target', '_parent');
        });*/

        /*$('body').on('click','a[data-downloadurl]', function () {
            window.parent.location.href = $(this).data('downloadurl');
        });*/

        $('#wpdm-locks').on('hidden.bs.modal', function (e) {
            var parentWindow = document.createElement("a");
            parentWindow.href = document.referrer.toString();
            if(parentWindow.hostname === window.location.hostname)
                window.parent.hideLockFrame();
            else
                window.parent.postMessage({'task': 'hideiframe'}, "*");
        });

        showModal();
    });

    function showModal() {
        jQuery('#wpdm-locks').modal('show');
    }

</script>
<div style="display: none">
    <?php  if($form_lock === 1 || $base_price > 0) wp_footer(); ?>
    <?php do_action("wpdm_modal_iframe_footer"); ?>
</div>
</body>
</html>
