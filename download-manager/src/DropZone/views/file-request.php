<?php
if (!defined('ABSPATH')) die();
/**
 * User: shahnuralam
 * Date: 1/26/18
 * Time: 12:33 AM
 * Updated: 2020-06-19
 */


?><!DOCTYPE html>
<html style="background: transparent">
<head>
    <title><?php _e('File Request', WPDM_TEXT_DOMAIN); ?>:  <?php echo $request->title; ?></title>
    <script>
        var wpdm_url = <?= json_encode(WPDM()->wpdm_urls); ?>;
    </script>
    <link rel="stylesheet" href="<?php echo WPDM_BASE_URL; ?>assets/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="<?php echo WPDM_BASE_URL; ?>assets/css/front.css" />
    <script src="<?php echo includes_url(); ?>/js/jquery/jquery.js"></script>
    <script src="<?php echo WPDM_BASE_URL; ?>assets/bootstrap/js/bootstrap.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Sen:400,700" rel="stylesheet">
    <script src="<?php echo WPDM_BASE_URL ?>assets/js/simple-scrollbar.js"></script>

    <?php
    WPDM()->apply::uiColors();

    ?>
    <style>
        body{
            font-family: "Sen", sans-serif;
            font-weight: 400;
            font-size: 10px;
            color: #425676;
            background: #233459;
        }
        .w3eden #wpdm-download h1{
            font-size: 11pt;
            font-weight: 800;
            line-height: 1.5;
        }
        h1,h2,h3{
            font-weight: 800;
        }
        .w3eden #wpdm-download h3{
            font-size: 16pt;
            font-weight: 700 !important;
        }
        .w3eden p{
            font-size: 11pt;
            font-weight: 400;
            margin: 0;
        }
        #wpdm-download .modal-dialog{
            width: 360px;
            max-width: 96%;
        }
        #wpdm-download .modal-content{
            border-radius: 4px;
            border: 0;
            box-shadow: 0 0 15px rgba(0,0,0,0.12);
        }
        #wpdm-download .modal-footer{
            border-top: 1px solid #eeeeee;
            background: #fafafa;
            padding: 15px;
        }
        .w3eden #wpdm-download .btn{
            padding: 12px;
            font-weight: 600 !important;
            font-size: 9pt;
            letter-spacing: 1.5px;
        }
        .modal-backdrop{
            background: rgba(70, 99, 156, 0.87);
        }
        .modal-backdrop.show{
            opacity: 1;
        }
        p svg{
            width: 12px;
            display: inline-block;
            margin-right: 3px;
            margin-top: -3px;
        }
        .w3eden .list-group {
            border-color: rgba(67, 93, 148, 0.1) !important;
            max-height: 120px;
            overflow: auto;
            border-radius: 0 !important;
        }
        .w3eden .list-group div.file-item{
            padding: 10px;
            color: var(--color-muted);
            font-size: 10px;
            border-color: rgba(67, 93, 148, 0.1) !important;
            line-height: 1.5;
            border-radius: 0 !important;
        }
        .w3eden .list-group div.file-item h3{
            font-size:10pt;
            margin: 0;
            font-weight: 600;
            color: #4b6286;
        }
        .w3eden .list-group div.file-item svg{
            width: 18px;
            margin-top: 5px;
        }

        .ss-wrapper {
            overflow : hidden;
            height   : 100%;
            position : relative;
            z-index  : 1;
            float: left;
            width: 100%;
        }

        .ss-content {
            height          : 100%;
            width           : 100%;
            padding         : 0 32px 0 0;
            position        : relative;
            right           : -18px;
            overflow        : auto;
            -moz-box-sizing : border-box;
            box-sizing      : border-box;
        }

        .ss-scroll {
            position            : relative;
            background          : rgba(0, 0, 0, .1);
            width               : 9px;
            border-radius       : 4px;
            top                 : 0;
            z-index             : 2;
            cursor              : pointer;
            opacity: 0;
            transition: opacity 0.25s linear;
        }

        .ss-container:hover .ss-scroll {
            opacity: 1;
        }

        .ss-grabbed {
            user-select: none;
            -o-user-select: none;
            -moz-user-select: none;
            -khtml-user-select: none;
            -webkit-user-select: none;
        }


        #__myfiles{
            font-size: 14px;
        }
        #__myfiles .card{
            transition: 300ms all ease-in-out;
            cursor: pointer;
        }
        #__myfiles .card:focus,
        #__myfiles .card:hover{
            border-color: #36b7ff;
            transition: 300ms all ease-in-out;
            box-shadow: 0 0 4px rgba(54, 183, 255, 0.54);
        }
        #__myfiles span._fsettings{
            width: 120px;
            text-align: right;
            height: 40px;
            display: table-cell;
            vertical-align: middle;
        }
        #__myfiles span._file {
            display: inline-block;
            width: calc(100% - 122px);
            white-space: break-spaces;
        }
        .modal-dialog .btn{
            padding: 6px 20px !important;
            font-size: 13px !important;
        }
        #__comments .media-body{
            font-size: 13px;
        }
        #__comments .avatar{
            width: 48px;
            min-width: 48px;
            height: auto;
        }
        .text-small{
            font-size: 11px;
        }

        .filet{
            text-overflow: ellipsis;
            overflow: hidden;
            max-width: 400px;
            white-space: nowrap;
            display: block;
            font-weight: bold;
        }

        .progress-bar {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -ms-flex-direction: column;
            flex-direction: column;
            -webkit-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;
            color: #fff;
            text-align: center;
            background-color: #007bff;
            transition: width .6s ease;
            height: 16px;
        }

        .progress-bar-animated {
            -webkit-animation: progress-bar-stripes 1s linear infinite;
            animation: progress-bar-stripes 1s linear infinite;
        }
        .progress-bar-striped {
            background-image: linear-gradient(45deg,rgba(255,255,255,.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.15) 50%,rgba(255,255,255,.15) 75%,transparent 75%,transparent);
            background-size: 1rem 1rem;
        }
        .w3eden .drag-drop #drag-drop-area {
            border: 2px dashed rgba(1, 160, 255, 0.25);
            background: #44bbff0d;
            border-radius: 4px;
        }
        .w3eden .drag-drop.drag-over #drag-drop-area {
            border: 2px dashed rgba(1, 160, 255, 0.66);
            background: rgba(68, 187, 255, 0.13);
            border-radius: 4px;
        }
        .w3eden .drag-drop.drag-over #drag-drop-area .fa-solid{
            color: rgba(1, 160, 255, 1);;
        }
        #plupload-browse-button{
            border-radius: 500px;
            font-size: 13px;
            padding: 8px 30px;
            color: #ffffff;
        }
        .feature-card h3{
            margin: 0;font-size: 18px;font-weight: 800;
            color: #5146a8;
        }
        .feature-card .icn{
            font-size: 20px;
            padding: 13px;
            /*background: rgba(50, 0, 255, 0.1);*/
            background: linear-gradient(60deg, #9f8fff, #715dd9) !important;
            /*color: rgb(109, 78, 234);*/
            color: #ffffff;
            border-radius: 2px;
        }
        .feature-card .nt{
            font-size: 14px;
            color: rgba(76, 56, 205, 0.58);
        }
        .feature-card{
            border: 1px solid rgb(109, 78, 234) !important;
            background: linear-gradient(60deg, #ffffff, #faf9ff) !important;
            box-shadow: 0 0 5px rgba(109, 78, 234, 0.3);
        }
        #filelist:empty{
            display: none;
        }
        .w3eden .drag-drop-inside{
            padding: 60px !important;
        }
    </style>
    <script src='<?= includes_url("js/plupload/moxie.min.js") ?>' id='moxiejs-js'></script>
    <script src='<?= includes_url("js/plupload/plupload.min.js") ?>' id='plupload-js'></script>
</head>

<body class="w3eden">
<div id="wpdm-download" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="wpdm-download-title" aria-hidden="true" style="overflow: auto">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div id="finished" style="display: none;text-align: center;position: absolute;background: #fff;z-index: 9999;top: 0;left: 0;right: 0;bottom: 0;vertical-align: middle;border-radius: 4px">
                <div style="position: absolute;top: 50%;transform: translate(0, -50%);width: 100%;text-align: center;padding: 40px">
                    <img style="width: 200px;height: auto" src="<?= WPDM_ASSET_URL ?>images/finished.png" alt="Finished!" />
                    <h3 class="mt-2"><?php _e('Finished uploading', WPDM_TEXT_DOMAIN); ?></h3>
                    <p><?php _e('We shall let Admin know<br/>you uploaded files.', WPDM_TEXT_DOMAIN); ?></p>
                    <button class="btn btn-sm btn-secondary mt-3" type="button" onclick="jQuery('#finished').fadeOut();"><?php _e('Upload more...', WPDM_TEXT_DOMAIN); ?></button>
                </div>
            </div>
            <div class="modal-body">
                <div class="mb-1 text-muted"><small>Admin sent you this request</small></div>
                <h3><?php echo $request->title; ?></h3>
	            <?php echo wpautop(esc_html($request->description)); ?>
                <br/>
                <div id="plupload-upload-ui" class="hide-if-no-js">
                    <div id="drag-drop-area" style="height: auto">
                        <div class="drag-drop-inside">
                            <p class="drag-drop-info mb-0">
                                <svg style="width: 48px;height: 48px;fill: #1fadff57;" version="1.1" viewBox="0 0 50 50" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="Layer_1"><path d="M33.414,1H3v42h23.214c1.824,3.556,5.521,6,9.786,6c6.065,0,11-4.935,11-11c0-4.264-2.444-7.961-6-9.786V8.586L33.414,1z    M34,4.414L37.586,8H34V4.414z M25.418,41H5V3h27v7h7v17.417c-0.063-0.018-0.129-0.024-0.192-0.041   c-0.351-0.093-0.708-0.168-1.071-0.226c-0.123-0.02-0.246-0.041-0.37-0.057C36.919,27.038,36.464,27,36,27c-6.065,0-11,4.935-11,11   c0,0.464,0.038,0.918,0.094,1.366c0.016,0.125,0.037,0.248,0.057,0.371c0.058,0.366,0.134,0.726,0.228,1.079   C25.395,40.877,25.401,40.94,25.418,41z M45,38c0,4.962-4.037,9-9,9c-3.692,0-6.868-2.236-8.255-5.424   c-0.112-0.259-0.21-0.521-0.297-0.787c-0.007-0.023-0.017-0.044-0.024-0.067c-0.087-0.271-0.157-0.545-0.217-0.822   c-0.005-0.023-0.012-0.044-0.017-0.067c-0.056-0.267-0.095-0.537-0.126-0.808c-0.004-0.034-0.012-0.067-0.016-0.101   C27.016,38.618,27,38.31,27,38c0-4.962,4.037-9,9-9c0.31,0,0.619,0.016,0.924,0.047c0.027,0.003,0.053,0.009,0.081,0.012   c0.279,0.031,0.555,0.072,0.829,0.129c0.017,0.004,0.033,0.009,0.05,0.013c0.282,0.061,0.562,0.133,0.839,0.222   c0.022,0.007,0.043,0.017,0.065,0.024c0.266,0.087,0.528,0.185,0.787,0.297C42.763,31.131,45,34.308,45,38z"/><polygon points="37,33 35,33 35,37 31,37 31,39 35,39 35,43 37,43 37,39 41,39 41,37 37,37  "/></g><g/></svg>
                                <br/><br/>
					            <?php _e('Drop files here', WPDM_TEXT_DOMAIN); ?></p>
                            <p class="mb-2"><?php _ex('or', 'Uploader: Drop files here - or - Select Files', WPDM_TEXT_DOMAIN); ?></p>
                            <p class="drag-drop-buttons"><button id="plupload-browse-button" type="button" class="btn wpdm-vimeo"><?php esc_attr_e('Select Files', WPDM_TEXT_DOMAIN); ?></button></p>
                        </div>
                    </div>
                </div>
                <div id="filelist" class="mt-3"></div>
	            <?php
	            $slimit = get_option('__wpdm_max_upload_size',0);
	            if($slimit>0)
		            $slimit = wp_convert_hr_to_bytes($slimit.'M');
	            else
		            $slimit = wp_max_upload_size();

	            $plupload_init = array(
		            'runtimes'            => 'html5,silverlight,flash,html4',
		            'browse_button'       => 'plupload-browse-button',
		            'container'           => 'plupload-upload-ui',
		            'drop_element'        => 'drag-drop-area',
		            'file_data_name'      => 'attach_file', // (current_user_can(WPDM_ADMIN_CAP)?'package_file':'attach_file'),
		            'multiple_queues'     => true,
		            'max_file_size'       => $slimit.'b',
		            'url'                 => admin_url('admin-ajax.php'),
		            'flash_swf_url'       => includes_url('js/plupload/plupload.flash.swf'),
		            'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
		            'filters'             => array(array('title' => __('Allowed Files', WPDM_TEXT_DOMAIN), 'extensions' =>  implode(",", WPDM()->fileSystem->getAllowedFileTypes()))),
		            'multipart'           => true,
		            'urlstream_upload'    => true,

		            // additional post data to send to our ajax hook
		            'multipart_params'    => array(
			            '_ajax_nonce' => wp_create_nonce(NONCE_KEY),
			            'filerequest' => 1,
			            'code' => $request->code,
			            '__noconflict' => 1,
			            '__wpdmfm_upload' => wp_create_nonce(NONCE_KEY),
			            'action'      =>  'wpdm_frontend_file_upload', //(current_user_can(WPDM_ADMIN_CAP)?'wpdm_admin_upload_file':'wpdm_frontend_file_upload'),            // the ajax action name
		            ),
	            );

	            if(get_option('__wpdm_chunk_upload',0) == 1){
		            $plupload_init['chunk_size'] = get_option('__wpdm_chunk_size', 1024).'kb';
		            $plupload_init['max_retries'] = 3;
	            }

	            // we should probably not apply this filter, plugins may expect wp's media uploader...
	            $plupload_init = apply_filters('plupload_init', $plupload_init); ?>

            </div>
            <div class="modal-footer" id="mftr" style="display: none">
                <button type="button" id="supload" class="btn btn-success btn-sm"><?php _e('Upload', WPDM_TEXT_DOMAIN); ?></button>
            </div>
        </div>
    </div>
    <div class="text-center" style="width: 100%;z-index: 999999;position: absolute;bottom: 20px;color: rgba(255,255,255, 0.4);font-size: 10px">
        &mdash; &nbsp;<a href="<?php echo home_url('/'); ?>" style="color: rgba(255,255,255, 0.4);">Go to Home</a> &mdash;
    </div>
</div>
<script src='<?= includes_url("js/plupload/wp-plupload.min.js") ?>' id='wp-plupload-js'></script>
<script>
    jQuery(function ($) {
        $('#wpdm-download').modal({backdrop: 'static'});
        // create the uploader and pass the config from above
        var uploader = new plupload.Uploader(<?php echo json_encode($plupload_init); ?>);

        // checks if browser supports drag and drop upload, makes some css adjustments if necessary
        uploader.bind('Init', function(up){
            var uploaddiv = $('#plupload-upload-ui');

            if(up.features.dragdrop){
                uploaddiv.addClass('drag-drop');
                $('#drag-drop-area')
                    .bind('dragover.wp-uploader', function(){ uploaddiv.addClass('drag-over'); })
                    .bind('dragleave.wp-uploader, drop.wp-uploader', function(){ uploaddiv.removeClass('drag-over'); });

            }else{
                uploaddiv.removeClass('drag-drop');
                $('#drag-drop-area').unbind('.wp-uploader');
            }
        });

        uploader.init();

        // a file was added in the queue
        uploader.bind('FilesAdded', function(up, files){
            //var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);

            //uploader.settings.multipart_params.current_path = '__dropzone__';

            plupload.each(files, function(file){
                $('#filelist').prepend(
                    '<div class="card file mb-2" id="' + file.id + '"><div class="card-header txtellipsis px-3 py-2"><b>' +

                    file.name + '</b></div><div class="card-body px-3 py-2">' +
                    '<div class="progress" style="margin: 0;background: #eeeeee"><div style="width: 0" class="progress-bar progress-bar-info progress-bar-striped fileprogress progress-bar-animated" role="progressbar"></div><span class="sr-only" style="position: absolute;bottom: 10px;left: 43%;">(<span>' + plupload.formatSize(0) + '</span>/' + plupload.formatSize(file.size) + ')</span></div></div></div>');
            });

            up.refresh();
            //up.start();

            $('#mftr').slideDown();
        });

        uploader.bind('UploadProgress', function(up, file) {
            console.log(file.percent);
            $('#' + file.id + " .fileprogress").width(file.percent + "%");
            $('#' + file.id + " span").html(plupload.formatSize(parseInt(file.size * file.percent / 100)));
        });

        uploader.bind('UploadComplete', function(up, files) {
            $('#finished').fadeIn();
            $('#mftr').slideUp();
        });


        // a file was uploaded
        uploader.bind('FileUploaded', function(up, file, response) {
            var d = new Date();
            var ID = d.getTime();
            if(response.status == 200) {
                response = JSON.parse(response.response);
                if (response.success) {
                    $('#__nofiles').hide();
                    $('#' + file.id).remove();
                    $('#__myfiles').prepend(`<div class="card p-3 mb-2" id="filerow_${response.ID}">
                                       <div class="_file">
                                           <div class="media">
                                               <div class="mr-3 _comments_file" data-file="${response.ID}">
                                                   <img style="width: 40px;min-width: 40px" src="${response.icon}" alt="Icon" />
                                               </div>
                                               <div class="media-body _comments_file" style="max-width: calc(100% - 190px) !important;" data-file="${response.ID}">
                                                   <div class="filet">${response.file}</div>
                                                   <div class="text-muted text-small"><i class="fa fa-hdd"></i> ${response.size}</div>
                                               </div>
                                               <div class="ml-3">
                                                   <span class="_fsettings">
                                                        <a href="#" class="btn btn-sm btn-info _edit_file" data-file="${response.ID}"><i class="fa fa-pen-alt"></i></a>
                                                        <a href="#" class="btn btn-sm btn-danger _delete_file" data-file="${response.ID}"><i class="fas fa-trash"></i></a>
                                                    </span>
                                               </div>
                                           </div>
                                       </div>
                                    </div>`);
                } else {
                    $('#' + file.id).addClass('upfailed');
                    $('#' + file.id + ".upfailed").on('click', function () {
                        $(this).slideUp();
                    });
                }
            } else {
                $('#' + file.id).addClass('upfailed');
                $('#' + file.id + ".upfailed").on('click', function () {
                    $(this).slideUp();
                });
            }
        });

        $('#supload').on('click', function (e) {
            e.preventDefault();
            uploader.start();
        });

    });
</script>
</body>
</html>
