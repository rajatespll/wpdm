<?php
if(!defined('ABSPATH')) die('Dream more!');

$files = WPDM()->dropZone->getFiles();

?>
<style>
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
        width: 150px;
        text-align: right;
        height: 40px;
        display: table-cell;
        vertical-align: middle;
        opacity: 0.5;
        transition: all ease-in-out 300ms;
    }
    #__myfiles .card:hover span._fsettings{
        opacity: 1;
    }
    #__myfiles span._file {
        display: inline-block;
        width: calc(100% - 122px);
        white-space: break-spaces;
    }
    .modal-dialog .btn{
        padding: 6px 20px !important;
        font-size: 11px !important;
    }
    .w3eden .modal-header .close{
        color: var(--color-danger-active);
        opacity: 0.7;
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
        max-width: 300px;
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
        font-size: 14px;
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
</style>
<div class="w3eden">
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <?php if(!is_array($files) || count($files) == 0) { ?>
                <div id="__nofiles" class="card" style="height: 346px !important;text-align: center !important;line-height: 344px">
                    No files here yet!
                </div>
                <ul class="list-group" id="__myfiles">
                </ul>
                <?php } else { ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4 feature-card">
                                <div class="card-body">
                                    <div class="media">
                                        <div class="mr-3"><i class="fa fa-layer-group fa-3x icn"></i></div>
                                        <div class="media-body">
                                            <div class="nt"><?php _e('Total Files', WPDM_TEXT_DOMAIN); ?></div>
                                            <h3><?= count($files); ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-4 feature-card">
                                <div class="card-body">
                                    <div class="media">
                                        <div class="mr-3"><i class="fa fa-hdd fa-3x icn"></i></div>
                                        <div class="media-body">
                                            <div class="nt"><?php _e('Space Used', WPDM_TEXT_DOMAIN); ?></div>
                                            <h3><?= WPDM()->dropZone->fileSpace(); ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <div id="__myfiles">
                    <?php foreach ($files as $file){ ?>
                        <div class="card p-3 mb-2" id="filerow_<?= $file->ID ?>">
                           <div class="_file">
                               <div class="media">
                                   <div class="mr-3 _comments_file" data-file="<?= $file->ID ?>">
                                       <img style="width: 40px;min-width: 40px" src="<?= \WPDM\__\FileSystem::fileTypeIcon($file->file) ?>" alt="<?= basename($file->file); ?> Icon" />
                                   </div>
                                   <div class="media-body _comments_file" style="max-width: calc(100% - 190px) !important;" data-file="<?= $file->ID ?>">
                                       <div class="filet"><?= $file->name; ?></div>
                                       <div class="text-muted text-small"><i class="fa fa-hdd"></i> <?= WPDM()->fileSystem->fileSize($file->file); ?> <i class="fa fa-calendar ml-2"></i> <?= wp_date(get_option('date_format')." ".get_option('time_format'), $file->date) ?> <span class="color-info"><i class="fa fa-star ml-2"></i>  <strong><?= DZF_STATUS_TITLE[$file->status] ?></strong></span></div>
                                   </div>
                                   <div class="ml-3">
                                       <span class="_fsettings">
                                            <a href="#" class="btn btn-sm btn-secondary _edit_file" data-file="<?= $file->ID ?>"><i class="fa fa-pen-alt"></i></a>
                                            <a href="#" class="btn btn-sm btn-info _share_file" data-file="<?= $file->ID ?>"><i class="fa fa-share-alt"></i></a>
                                            <a href="#" class="btn btn-sm btn-primary _comments_file" data-file="<?= $file->ID ?>"><i class="fa fa-comments"></i></a>
                                            <?php do_action("wpdm_dropzone_file_action", $file); ?>
                                            <a href="#" class="btn btn-sm btn-danger _delete_file" data-file="<?= $file->ID ?>"><i class="fas fa-trash"></i></a>
                                        </span>
                                   </div>
                               </div>
                           </div>
                        </div>
                    <?php } ?>
                </div>
                <?php } ?>

            </div>
            <div class="col-md-4">
                <div id="plupload-upload-ui" class="hide-if-no-js">
                    <div id="drag-drop-area" style="height: auto">
                        <div class="drag-drop-inside" style="padding: 100px 0">
                            <p class="drag-drop-info mb-0">
                                <i class="fa-solid fa-arrow-up-from-bracket fa-3x"></i><br/><br/>
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
			            'dropzone' => 1,
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

                <div id="slide-out-panel" class="slide-out-panel">
                    <header>File Details</header>
                    <section class="w3eden">
                        <div id="cmtfrmw">
                            <form method="post" id="cmtfrm">
                                <input type="hidden" name="dzcnonce" value="<?= wp_create_nonce(WPDM_PUB_NONCE) ?>" />
                                <input type="hidden" name="file" value="" id="dzcmf" />
                                <div class="card mb-3">
                                    <textarea placeholder="<?= __('Write Comment...', WPDM_TEXT_DOMAIN); ?>" class="form-control" name="comment" required="required" style="box-shadow: none !important;border: 0 !important;min-height: 80px"></textarea>
                                    <div class="card-footer bg-white text-right">
                                        <button type="submit" class="btn btn-primary btn-sm" style="padding: 0 15px;min-height: 32px;max-height: 32px;line-height: 24px"><?= __('Add Comment', WPDM_TEXT_DOMAIN); ?></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div id="__comments" style="height: calc(100vh - 244px);overflow: auto;">
                            <div class="card mb-3" v-for="(comment, index) in comments" :id="'comment_' + index">
                                <div class="card-body">
                                    <div class="media">
                                        <div class="mr-3"><div v-html="comment.avatar"></div></div>
                                        <div class="media-body">
                                            <strong>{{comment.name}}</strong><br/>
                                            {{comment.comment}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>

                <script src="<?php echo WPDM_BASE_URL ?>assets/js/vue.min.js"></script>
                <script src="<?= WPDM_BASE_URL ?>assets/drawer/js/slide-out-panel.min.js"></script>
                <link rel="stylesheet" href="<?= WPDM_BASE_URL ?>assets/drawer/css/slide-out-panel.min.css" />
                <script type="text/javascript">

                    let file = 0;

                    jQuery(document).ready(function($){

                        const slideOutPanel = $('#slide-out-panel').SlideOutPanel({

                            width: '500px'

                        });


                        let $body = $('body'), ufint = 'save';

                        $body.on('click', '.btn-ufin', function () {
                            ufint = $(this).data('act');
                        });

                        $body.on('submit', '#updatefileinfo', function (e) {
                            e.preventDefault();
                            WPDM.blockUI('#efin');
                            $(this).ajaxSubmit({
                                url: wpdm_url.ajax+'?act='+ufint,
                                success: function (response) {
                                    WPDM.unblockUI('#efin');
                                }
                            });
                        });


                        $body.on('submit', '#sharedropzonefile', function (e) {
                            e.preventDefault();
                            WPDM.blockUI('#efin');
                            $(this).ajaxSubmit({
                                url: wpdm_url.ajax,
                                success: function (response) {
                                    WPDM.unblockUI('#efin');
                                }
                            });
                        });


                        $body.on('click', '._edit_file', function (e) {
                            e.preventDefault();
                            //slideOutPanel.open();
                            bam = WPDM.bootAlert('<?= __('File Details', WPDM_TEXT_DOMAIN); ?>', {url: wpdm_url.ajax + '?action=wpdmdz_file_info&file='+$(this).data('file')}, 405);
                        });

                        $body.on('click', '._share_file', function (e) {
                            e.preventDefault();
                            //slideOutPanel.open();
                            bam = WPDM.bootAlert('<?= __('Share File', WPDM_TEXT_DOMAIN); ?>', {url: wpdm_url.ajax + '?action=wpdmdz_share_file&file='+$(this).data('file')}, 405);
                        });

                        $body.on('click', '._comments_file', function (e) {
                            e.preventDefault();
                            slideOutPanel.open();
                            file = $(this).data('file');
                            $('#dzcmf').val(file);
                            WPDM.blockUI('#__comments');
                            $.get(wpdm_url.ajax, {action: 'wpdmdz_get_file', file: file}, function (file) {
                                __wpdmdz__comments.comments = file.comments;
                                WPDM.unblockUI('#__comments');
                            });
                        });


                        $body.on('click', '._delete_file', function (e) {
                            e.preventDefault();

                            let file = $(this).data('file');

                            WPDM.confirm('<?= __('Deleting a file!', WPDM_TEXT_DOMAIN); ?>', '<?= __('Are you sure? This action can not be reverted!', WPDM_TEXT_DOMAIN); ?>', [
                                {
                                    label: 'Yes, Confirm!',
                                    class: 'btn-danger',
                                    callback: function () {
                                        $(this).find('.modal-body').html('<i class="fa fa-sun fa-spin"></i> <?= __('Deleting', WPDM_TEXT_DOMAIN); ?>...');
                                        var confirm = $(this);
                                        $.post(wpdm_url.ajax, {action: 'wpdmdz_delete_file', file: file, dznonce: '<?= wp_create_nonce(WPDM_PUB_NONCE); ?>'}, function (res) {
                                            $(`#filerow_${file}`).remove();
                                            confirm.modal('hide');
                                        });
                                    }
                                },
                                {
                                    label: 'No, Later',
                                    class: 'btn-info',
                                    callback: function () {
                                        $(this).modal('hide');
                                    }
                                }
                            ]);
                        });


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
                                    '<div class="progress" style="margin: 0;"><div class="progress-bar progress-bar-info progress-bar-striped fileprogress progress-bar-animated" role="progressbar"><span class="sr-only">(<span>' + plupload.formatSize(0) + '</span>/' + plupload.formatSize(file.size) + ')</span></div></div></div></div>');
                            });

                            up.refresh();
                            up.start();
                        });

                        uploader.bind('UploadProgress', function(up, file) {
                            console.log(file.percent);
                            $('#' + file.id + " .fileprogress").width(file.percent + "%");
                            $('#' + file.id + " span").html(plupload.formatSize(parseInt(file.size * file.percent / 100)));
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

                    });

                </script>

                <script>
                    var __wpdmdz__comments = new Vue({
                        el: '#__comments',
                        data: {
                            comments: []
                        }
                    });
                    jQuery(function ($) {
                        let $body = $('body');
                        $body.on('submit', '#cmtfrm', function (e) {
                            e.preventDefault();
                            WPDM.blockUI('#cmtfrmw');
                            $(this).ajaxSubmit({
                                url: wpdm_url.ajax+'?action=wpdmdz_add_comment',
                                success: function (response) {
                                    WPDM.unblockUI('#cmtfrmw');
                                    __wpdmdz__comments.comments = response;
                                }
                            });
                        });
                        $body.on('click', '.btn-accept', function (e) {
                            let $btn = $(this);
                            $.post(wpdm_url.ajax, {action: 'wpdmdz_file_accept', dzfanonce: '<?= wp_create_nonce(WPDM_PUB_NONCE); ?>', file: $btn.data('file')}, function (res) {
                                $('.btn-accept, .btn-decline').remove();
                                __wpdmdz__comments.comments = res.comments;
                            });
                        });

                        $body.on('click', '.btn-decline', function (e) {
                            let $btn = $(this);
                            $.post(wpdm_url.ajax, {action: 'wpdmdz_file_decline', dzfdnonce: '<?= wp_create_nonce(WPDM_PUB_NONCE); ?>', file: $btn.data('file')}, function (res) {
                                $('.btn-accept, .btn-decline').remove();
                                __wpdmdz__comments.comments = res.comments;
                            });
                        });
                    });
                </script>

                <div class="clear"></div>
            </div>
        </div>
    </div>
</div>
