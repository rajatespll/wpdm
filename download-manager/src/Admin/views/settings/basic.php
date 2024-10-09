<style>

    h4 {
        color: #336699;
        margin-bottom: 0px;
    }

    em {
        color: #888;
    }

    .wp-switch-editor {
        height: 27px !important;
    }
</style>

<?php
if(isset($show_db_update_notice) && $show_db_update_notice) {
    ?>
    <div class="alert alert-success">
        <?= __('WordPress Download Manager Pro database has been updated successfully', WPDM_TEXT_DOMAIN); ?>
    </div>
    <?php
}
?>
<div class="panel panel-default">
    <div class="panel-heading"><?php _e("URL Structure", "download-manager"); ?></div>
    <div class="panel-body">
        <p><em>
                <?php echo __("Caution: Use unique word for each url base. Also, don't create any page or post with same slug you used for WPDM URL Bases below.", "download-manager"); ?>
            </em></p>
        <div class="row">
            <div class="col-md-4">
                <div class="panel panel-default m-0">
                    <div class="panel-heading"><?php echo __("Package URL Base", "download-manager"); ?></div>
                    <div class="panel-body">
                        <input type="text" class="form-control" name="__wpdm_purl_base" value="<?php echo get_option('__wpdm_purl_base', 'download'); ?>"/>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel panel-default m-0">
                    <div class="panel-heading"><?php echo __("Category URL Base", "download-manager"); ?></div>
                    <div class="panel-body">
                <input type="text" class="form-control" name="__wpdm_curl_base"
                       value="<?php echo get_option('__wpdm_curl_base', 'downloads'); ?>"/>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <input type="hidden" name="__wpdm_has_archive" value="0" />
                <div class="panel panel-default m-0">
                    <div class="panel-heading"><label class="m-0"><input class="mr-3" type="checkbox" id="wpdmap" name="__wpdm_has_archive" value="1"  <?php checked(get_option('__wpdm_has_archive'), 1) ?> /> <?php _e("Enable Archive Page", "download-manager"); ?></label></div>
                    <div class="panel-body"><input  <?php disabled((int)get_option('__wpdm_has_archive'), 0) ?> id="aps" placeholder="<?php _e("Archive Page Slug", "download-manager"); ?>" type="text" class="form-control" name="__wpdm_archive_page_slug"
                       value="<?php echo get_option('__wpdm_archive_page_slug', 'all-downloads'); ?>"/>
                    </div>
                </div>
            </div>
        </div>


    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading"><?php _e( "Package Settings" , "download-manager" ); ?></div>
    <div class="panel-body">
        <div class="form-group mb-0">
            <input type="hidden" value="0" name="__wpdm_delete_expired">
            <label class="fw-4"><input onchange="jQuery(this).is(':checked') ? jQuery('#wpdmde').slideDown():jQuery('#wpdmde').slideUp() ;" type="checkbox" name="__wpdm_delete_expired" value="1" <?php checked(1, get_option('__wpdm_delete_expired')); ?> > <?php echo __("Automatically delete expired packages", "download-manager"); ?></label>
        </div>
        <div class="panel panel-default" id="wpdmde" <?= (int)get_option('__wpdm_delete_expired') === 0 ? 'style="display:none"' : '' ?> >
             <div class="panel-heading"><?= __('Cron URL', WPDM_TEXT_DOMAIN) ?></div>
             <div class="panel-body">
                 <div class="input-group">
                     <input type="text" class="form-control" id="cccronurl" readonly="" value="<?= add_query_arg(['cde' => 'wpdmde', 'cronkey' => WPDM_CRON_KEY], home_url('/')) ?>">
                     <div class="input-group-btn">
                         <button class="btn btn-secondary ttip" type="button" title="" onclick="WPDM.copy('cccronurl')" data-original-title="Copy"><i class="fa fa-copy"></i></button>
                     </div>
                 </div>
                 <div class="note">
                     <em><?= _e('Your need to configure a cron job from your hosting control panel using the url above for reliable cron execution.', WPDM_TEXT_DOMAIN) ?></em>
                 </div>
             </div>
        </div>
        <div class="form-group mb-0">
            <input type="hidden" value="0" name="__wpdm_delete_dfiles">
            <label class="fw-4"><input type="checkbox" name="__wpdm_delete_dfiles" value="1" <?php checked(1, get_option('__wpdm_delete_dfiles')); ?> > <?php echo __("Delete a file when it is detached from a package (Only administrator can use this option)", "download-manager"); ?></label>
        </div>
        <div class="form-group mb-0">
            <input type="hidden" value="0" name="__wpdm_delete_afiles">
            <label class="fw-4"><input type="checkbox" name="__wpdm_delete_afiles" value="1" <?php checked(1, get_option('__wpdm_delete_afiles')); ?> > <?php echo __("Delete attached files when delete a package  (Only administrator can use this option)", "download-manager"); ?></label>
        </div>
        <div class="form-group mb-0">
            <input type="hidden" value="0" name="__wpdm_gutenberg_editor">
            <label class="fw-4"><input type="checkbox" name="__wpdm_gutenberg_editor" value="1" <?php checked(1, get_option('__wpdm_gutenberg_editor')); ?> > <?php echo __("Enable Gutenberg editor for WordPress Download Manager", "download-manager"); ?></label>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading"><?php _e("Access Settings", "download-manager"); ?></div>
    <div class="panel-body">

        <div class="form-group">
            <input type="hidden" value="0" name="__wpdm_exclude_from_search">
            <label><input type="checkbox" name="__wpdm_exclude_from_search" value="1" <?php checked(1, get_option('__wpdm_exclude_from_search')); ?> > <?php echo __("Exclude from search", "download-manager"); ?></label>
            <em class="d-block">
                 <?=__('Whether to exclude wpdm packages from front end search results.', WPDM_TEXT_DOMAIN); ?>
            </em>
        </div>

        <div class="form-group">
            <label><?php echo __("When user is not allowed to download:", "download-manager"); ?></label><br/>
            <select name="_wpdm_hide_all">
                <option value="0"><?php echo __("Only Block Download Link", "download-manager"); ?></option>
                <option value="1" <?php echo get_option('_wpdm_hide_all', 0) == 1 ? 'selected=selected' : ''; ?>><?php echo __("Hide Everything", "download-manager"); ?></option>
            </select>
        </div>

        <div class="form-group">
            <label><?php echo __("File Browser Root:", "download-manager"); ?></label>
            <span title="<?php echo __("Root dir for server file browser", "download-manager"); ?>" class="ttip"><i class="fa fa-info-circle"></i></span>
            <div class="input-group">
                <input type="text" class="form-control"
                       value="<?php echo get_option('_wpdm_file_browser_root', str_replace("\\", "/", dirname(UPLOAD_DIR))); ?>"
                       name="_wpdm_file_browser_root" id="_wpdm_file_browser_root"/>
                <span class="input-group-btn">
                                    <button class="btn btn-secondary ttip" title="<?php _e('Reset Base Dir'); ?>"
                                            type="button"
                                            onclick="jQuery('#_wpdm_file_browser_root').val('<?php echo rtrim(str_replace("\\", "/", dirname(UPLOAD_DIR)), '/'); ?>');"><i
                                                class="fas fa-redo"></i></button>
                                </span>
            </div>
        </div>

        <div class="form-group">
            <label><?php echo __("File Browser Access:", "download-manager"); ?></label><br/>
            <input type="hidden" name="_wpdm_file_browser_access[]" value="[NONE]"/>
            <select style="width: 100%" name="_wpdm_file_browser_access[]" multiple="multiple"
                    data-placeholder="<?php _e("Who will have access to server file browser", "download-manager"); ?>">
                <?php

                $currentAccess = maybe_unserialize(get_option('_wpdm_file_browser_access', array('administrator')));
                $selz = '';

                ?>

                <?php
                global $wp_roles;
                $roles = array_reverse($wp_roles->role_names);
                foreach ($roles as $role => $name) {


                    if ($currentAccess) $sel = (in_array($role, $currentAccess)) ? 'selected=selected' : '';
                    else $sel = '';


                    ?>
                    <option value="<?php echo $role; ?>" <?php echo $sel ?>> <?php echo $name; ?></option>
                <?php } ?>
            </select>
        </div>

        <br/>
        <div class="panel panel-default">
            <div class="panel-heading">
                <?php echo __("reCAPTCHA Settings", "download-manager"); ?>
                <a class="btn btn-xs pull-right btn-info" target="_blank" href="https://www.google.com/recaptcha/admin#list"><?= __('Register Site', WPDM_TEXT_DOMAIN) ?></a>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label><a name="liappid"></a><?php echo __("reCAPTCHA Site Key", "download-manager"); ?></label>
                        <input type="text" class="form-control" name="_wpdm_recaptcha_site_key"
                               value="<?php echo get_option('_wpdm_recaptcha_site_key'); ?>">

                    </div>
                    <div class="col-md-6 form-group">
                        <label><a name="liappid"></a><?php echo __("reCAPTCHA Secret Key", "download-manager"); ?></label>
                        <input type="text" class="form-control" name="_wpdm_recaptcha_secret_key"
                               value="<?php echo get_option('_wpdm_recaptcha_secret_key'); ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group mb-0">
                        <input type="hidden" value="0" name="__wpdm_recaptcha_regform">
                        <label><input type="checkbox" name="__wpdm_recaptcha_regform"
                                      value="1" <?php checked(1, get_option('__wpdm_recaptcha_regform')); ?> > <?php echo __("Enable sign up form CAPTCHA validation", "download-manager"); ?>
                        </label>
                    </div>
                    <div class="col-md-6 form-group mb-0">
                        <input type="hidden" value="0" name="__wpdm_recaptcha_loginform">
                        <label><input type="checkbox" name="__wpdm_recaptcha_loginform"
                                      value="1" <?php checked(1, get_option('__wpdm_recaptcha_loginform')); ?>> <?php echo __("Enable sign in form CAPTCHA validation", "download-manager"); ?>
                        </label>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading"><?php _e("Verification Settings", "download-manager"); ?></div>
    <div class="panel-body">

        <div class="panel panel-default">
            <div class="panel-heading"><?php echo __("Blocked IPs", "download-manager"); ?></div>
            <div class="panel-body">
                <div class="form-group">
                <textarea placeholder="<?php _e("One IP per line", "download-manager"); ?>" rows="5"
                          class="form-control"
                          name="__wpdm_blocked_ips"><?php echo esc_attr(get_option('__wpdm_blocked_ips')); ?></textarea>
                    <em><?php _e("List IP Addresses to blacklist. One IP per line ( Ex: IPv4 - 192.168.23.12 or 192.168.23.1/24 or 192.168.23.* , IPv6 - 2a01:8760:2:3001::1 or 2620:112:3000::/44 )", "download-manager"); ?></em>
                </div>
                <div class="form-group">
                <textarea placeholder="<?php _e("Message to show when an IP is blocked", "download-manager"); ?>"
                          class="form-control"
                          name="__wpdm_blocked_ips_msg"><?php echo get_option('__wpdm_blocked_ips_msg'); ?></textarea>
                    <em><?php _e("Message to show when an IP is blocked", "download-manager"); ?></em>
                </div>
            </div>
        </div>
        <div class="panel panel-default mb-0">
            <div class="panel-heading"><?php echo __('Block Emails', 'download-manager'); ?></div>
            <div class="panel-body">
                <div class="form-group">
                    <label><?php echo __("Blocked Domains:", "download-manager"); ?></label><br/>
                    <textarea name="__wpdm_blocked_domains"
                              class="input form-control"><?php echo get_option('__wpdm_blocked_domains', ''); ?></textarea>
                    <em>One domain per line</em>
                </div>

                <div class="form-group">
                    <label><?php echo __("Blocked Emails:", "download-manager"); ?></label><br/>
                    <textarea name="__wpdm_blocked_emails"
                              class="input form-control"><?php echo get_option('__wpdm_blocked_emails', ''); ?></textarea>
                    <em>One email per line</em>
                </div>
                <div class="form-group">
                <textarea
                        placeholder="<?php _e("Message to show when a email or domain is blocked", "download-manager"); ?>"
                        class="form-control"
                        name="__wpdm_blocked_domain_msg"><?php echo get_option('__wpdm_blocked_domain_msg'); ?></textarea>
                    <em><?php _e("Message to show when a email or domain is blocked", "download-manager"); ?></em>
                </div>
            </div>
        </div>


    </div>
</div>


<div class="panel panel-default">
    <div class="panel-heading"><?php _e("Upload Settings", "download-manager"); ?></div>
    <div class="panel-body">
        <div class="form-group">
            <label><?php _e('Allowed file types to upload', 'download-manager'); ?></label><br/>
            <?php
            $allowed_file_types = get_option("__wpdm_allowed_file_types", '');
            ?>
            <input type="text" class="form-control" value="<?= $allowed_file_types; ?>"
                   placeholder="<?= esc_attr__('Keep empty to use wordpress defaults', 'download-manager'); ?>"
                   name="__wpdm_allowed_file_types"/>
            <em><?php _e('Enter the file extensions you want to allow to upload through WPDM ( ex: png,jpg,pdf )', 'download-manager'); ?></em>
            <br/>

        </div>
        <hr/>

        <div class="form-group">
            <label><?php echo __("When File Already Exists:", "download-manager"); ?></label><br/>
            <select name="__wpdm_overwrrite_file">
                <option value="0"><?php echo __('Rename New File'); ?></option>
                <option value="1" <?php echo get_option('__wpdm_overwrrite_file', 0) == 1 ? 'selected=selected' : ''; ?>><?php echo __("Overwrite", "download-manager"); ?></option>
            </select>
        </div>

        <hr/>
        <div class="form-group">
            <input type="hidden" value="0" name="__wpdm_sanitize_filename"/>
            <label><input <?php checked(1, get_option('__wpdm_sanitize_filename', 0)); ?>
                          type="checkbox" value="1"
                          name="__wpdm_sanitize_filename"> <?php _e("Sanitize Filename", "download-manager"); ?>
            </label><br/>
            <em><?php _e("Check the option to sanitize uploaded file names to remove illegal chars", "download-manager"); ?></em>
            <br/>

        </div>

        <hr/>
        <div class="row">
            <div class="col-md-6">
                <input type="hidden" value="0" name="__wpdm_chunk_upload"/>
                <label><input <?php checked(1, get_option('__wpdm_chunk_upload', 0)); ?>
                            type="checkbox" value="1"
                            name="__wpdm_chunk_upload" class="mr-2"> <?php _e('Chunk Upload', 'download-manager'); ?></label><br/>
                <em><?php _e('Check the option to enable chunk upload to override http upload limits', 'download-manager'); ?></em>
                <br/>

            </div>
            <div class="col-md-6">
                <label><?php _e('Chunk Size', 'download-manager'); ?></label><br/>
                <div class="input-group">
                    <input class="form-control" value="<?php echo get_option('__wpdm_chunk_size', 1024); ?>" type="number"
                           name="__wpdm_chunk_size">
                    <div class="input-group-addon">KB</div>
                </div>
                <br/>

            </div>
        </div>

    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading"><?php _e("DropZone Settings", "download-manager"); ?></div>
    <div class="panel-body">

        <div class="form-group">
            <label for="__wpdm_author_profile"><?php echo __( "DropZone Page" , "download-manager" ); ?></label><br/>
		    <?php wp_dropdown_pages(array('name' => '__wpdm_dropzone_page', 'id' => '__wpdm_dropzone_page', 'show_option_none' => __( "None Selected" , "download-manager" ), 'option_none_value' => '' , 'selected' => get_option('__wpdm_dropzone_page'))) ?><br/>
            <em class="note"><?php printf(__( "The page where you used the short-code %s" , "download-manager" ),'<input style="width: 155px;" readonly="readonly" type="text" value="[wpdm_dropzone]" class="txtsc">'); ?></em>
        </div>

        <div class="form-group">
            <label><?php _e("Enable DropZone for", "download-manager"); ?></label><br/>
            <input type="hidden" name="__wpdm_dropzone_access[]" value="[NONE]" <?php echo $sel ?> />
	        <?php

	        $currentAccess = maybe_unserialize(get_option('__wpdm_dropzone_access'));
	        if(!is_array($currentAccess)) $currentAccess = [];
	        $selz = '';
	        ?>
            <select style="width: 100%" name="__wpdm_dropzone_access[]" multiple="multiple" data-placeholder="<?php _e("Who will have access to DropZone", "download-manager"); ?>">
		        <?php
		        global $wp_roles;
		        $roles = array_reverse($wp_roles->role_names);
		        foreach ($roles as $role => $name) {


			        if ($currentAccess) $sel = (in_array($role, $currentAccess)) ? 'selected=selected' : '';
			        else $sel = '';


			        ?>
                    <option value="<?php echo $role; ?>" <?php echo $sel ?>> <?php echo $name; ?></option>
		        <?php } ?>
            </select>
            <em><?php _e("Select user roles too allow access to DropZone", "download-manager"); ?></em>
            <br/>

        </div>
        <div class="form-group">
            <input type="hidden" value="0" name="__wpdm_dz_p2p_sharing"/>
            <label><input <?php checked(1, get_option('__wpdm_dz_p2p_sharing', 0)); ?>
                        type="checkbox" value="1"
                        name="__wpdm_dz_p2p_sharing" class="mr-2"> <?php _e('Enable user-2-user file sharing', 'download-manager'); ?></label><br/>
            <em><?php _e('Activating this option will enable front-end dropzone users to share file with publicly sharable link', 'download-manager'); ?></em>
        </div>


    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading"><?php _e("Media Files Access Control", "download-manager"); ?></div>
    <div class="panel-body">


        <div class="form-group">
            <label><?php _e("Enable Media File Access Control For", "download-manager"); ?></label><br/>
            <input type="hidden" name="__wpdm_dropzone_access[]" value="[NONE]" <?php echo $sel ?> />
			<?php

			$currentAccess = maybe_unserialize(get_option('__wpdm_dropzone_access'));
			if(!is_array($currentAccess)) $currentAccess = [];
			$selz = '';
			?>
            <select id="__wpdm_mac_access" style="width: 100%" name="__wpdm_mac_access[]" multiple="multiple" data-placeholder="<?php _e("Select roles", "download-manager"); ?>">
				<?php
				global $wp_roles;
				$roles = array_reverse($wp_roles->role_names);
				foreach ($roles as $role => $name) {


					if ($currentAccess) $sel = (in_array($role, $currentAccess)) ? 'selected=selected' : '';
					else $sel = '';


					?>
                    <option value="<?php echo $role; ?>" <?php echo $sel ?>> <?php echo $name; ?></option>
				<?php } ?>
            </select>
            <em><?php _e("Select user roles too allow access to Media File Access Control option", "download-manager"); ?></em>
            <br/>

        </div>


    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading"><?php echo __("Messages", "download-manager"); ?></div>
    <div class="panel-body">

        <div class="form-group">
            <label><?php echo __("Plugin Update Notice:", "download-manager"); ?></label><br>
            <select name="wpdm_update_notice">
                <option value="0"><?php echo __("Enabled", "download-manager"); ?></option>
                <option value="disabled" <?php selected(get_option('wpdm_update_notice'), 'disabled'); ?>><?php echo __("Disabled", "download-manager"); ?></option>
            </select>
        </div>

        <div class="form-group">
            <label for="__wpdm_permission_denied_msg"><?php echo __("Permission Denied Message for Packages:", "download-manager"); ?></label>
            <textarea id="__wpdm_permission_denied_msg" name="__wpdm_permission_denied_msg"
                      class="form-control"><?php echo stripslashes(get_option('__wpdm_permission_denied_msg')); ?></textarea>
        </div>

        <div class="form-group">
            <label><?php echo __("Download Limit Message:", "download-manager"); ?></label>
            <textarea class="form-control" cols="70" rows="6"
                      name="__wpdm_download_limit_exceeded"><?php echo stripslashes(get_option('__wpdm_download_limit_exceeded', __("Download Limit Exceeded!", "download-manager"))); ?></textarea>

        </div>

        <div class="form-group">
            <label><?php echo __("Login Required Message:", "download-manager"); ?></label>
            <textarea class="form-control" cols="70" rows="6"
                      name="wpdm_login_msg"><?php echo get_option('wpdm_login_msg', false) ? stripslashes(get_option('wpdm_login_msg')) : ('<div class="w3eden"><div class="panel panel-default card"><div class="panel-body card-body"><span class="text-danger">Login is required to access this page</span></div><div class="panel-footer card-footer text-right"><a href="' . wp_login_url() . '?redirect_to=[this_url]" class="btn btn-danger wpdmloginmodal-trigger btn-sm"><i class="fa fa-lock"></i> Login</a></div></div></div>'); ?></textarea>
            <em class="note"><?php echo sprintf(__("If you want to show login form instead of message user short-code [wpdm_login_form]. To show login form in a modal popup, please follow %s the doc here %s", "download-manager"), "<a target='_blank' href='https://www.wpdownloadmanager.com/how-to-add-modal-popup-login-form-in-your-wordpress-site/'>", "</a>"); ?></em>

        </div>

    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading"><?php echo __("File Download", "download-manager"); ?></div>
    <div class="panel-body">

        <div class="form-group">
            <label><input onclick="jQuery('#pdm').slideUp();"
                          type="radio" <?php checked(get_option('__wpdm_parallel_download', 1), 1); ?>
                          name="__wpdm_parallel_download"
                          value="1"> <?php _e("Enable Parallel Download", "download-manager"); ?> &nbsp; </label>
            <label><input onclick="jQuery('#pdm').slideDown();"
                          type="radio" <?php checked(get_option('__wpdm_parallel_download', 1), 0); ?>
                          name="__wpdm_parallel_download"
                          value="0"> <?php _e("Disable Parallel Download", "download-manager"); ?></label><br/>
            <em><?php _e("Enable/Disable multiple simultaneous downloads from same IP address", "download-manager"); ?></em>
        </div>
        <hr/>

        <div id="pdm"
             class="form-group" <?php if ((int)get_option('__wpdm_parallel_download', 1) === 1) echo 'style="display:none;"'; ?>>
            <label><?php echo __("Message to show when a download is blocked", "download-manager") ?></label>
            <textarea class="form-control"
                      name="__wpdm_parallel_download_msg"><?php echo get_option('__wpdm_parallel_download_msg', "Another download is in progress from your IP, please wait until finished."); ?></textarea>
            <hr/>
        </div>

        <div class="form-group">
            <label><input type="radio" <?php checked(get_option('__wpdm_auto_download', 1), 1); ?> name="__wpdm_auto_download" value="1"> <?php _e("Enable Auto Download", "download-manager"); ?> &nbsp; </label>
            <label><input type="radio" <?php checked(get_option('__wpdm_auto_download', 1), 0); ?> name="__wpdm_auto_download" value="0"> <?php _e("Disable Auto Download", "download-manager"); ?></label><br/>
            <em><?php _e("Enable or disable auto-download once the lock option validation has been completed", "download-manager"); ?></em>
        </div>
        <hr/>

        <div class="row">
            <div class="col-md-5">
                <label><?php _e("Private Download Link Usage Limit", "download-manager"); ?>:</label><br/>
                <input class="form-control" type="number" name="__wpdm_private_link_usage_limit"
                       value="<?php echo intval(get_option('__wpdm_private_link_usage_limit', 3)); ?>" min="1" step="1">
                <em><?php _e('Private download links ( package with any lock option active ) will expire after it exceeds the limit', "download-manager"); ?></em>
            </div>

            <div class="col-md-7">
                <label><?php _e("Private Download Link Expiration Period", "download-manager"); ?>:</label><br/>
                <div class="row">
                    <div class="col-md-6" style="padding-right: 0">
                        <input min="1" step="1" class="form-control" name="__wpdm_private_link_expiration_period"
                               type="number"
                               value="<?php echo intval(get_option('__wpdm_private_link_expiration_period', 3)); ?>">
                    </div>
                    <div class="col-md-6">
                        <select name="__wpdm_private_link_expiration_period_unit" class="form-control wpdm-custom-select"
                                style="min-width: 100%;max-width: 100%">
                            <option value="60">Mins</option>
                            <option value="3600" <?php selected(intval(get_option('__wpdm_private_link_expiration_period_unit', 0)), 3600); ?>>
                                Hours
                            </option>
                            <option value="86400" <?php selected(intval(get_option('__wpdm_private_link_expiration_period_unit', 0)), 86400); ?>>
                                Days
                            </option>
                        </select>
                    </div>
                </div>
                <em><?php _e("Private download links ( package with any lock option active ) will expire after the period starting from it's generation time", "download-manager"); ?></em>
            </div>
        </div>

        <hr/>

        <div class="form-group"><input type="hidden" name="__wpdm_flat_download_url" value="0">
            <label><input id="fdurl" type="checkbox" <?php checked(get_option('__wpdm_flat_download_url'), 1); ?> name="__wpdm_flat_download_url" value="1"> <?php _e("Enable Flat Download Link", "download-manager"); ?>
            </label><br/>
            <em><?php _e("Easy to remember download link", "download-manager"); ?> ** This feature still on test</em>
        </div>
        <div class="panel panel-default m-0" id="dlub" <?php echo !(int)get_option('__wpdm_flat_download_url') ? 'style="display:none;"' : ''; ?>>
            <div class="panel-heading"><?php echo __("Download URL Base", "download-manager"); ?></div>
            <div class="panel-body">
                <p><?php echo __('Keep base url unique from download base url and package base url', 'download-manager') ?></p>
                <input type="text" class="form-control" onkeyup="jQuery('#fdubs').html(this.value);" name="__wpdm_fdurl_base" value="<?php echo get_option('__wpdm_fdurl_base', 'dl'); ?>"/>
                <em><?= home_url("/") ?><strong style="color: var(--color-primary)" id="fdubs"><?php echo get_option('__wpdm_fdurl_base', 'dl'); ?></strong>/123/file-name.ext</em>
            </div>
        </div>
        <hr/>
        <div class="form-group"><input type="hidden" name="__wpdm_allow_index" value="0">
            <label><input id="__wpdm_allow_index" type="checkbox" <?php checked(get_option('__wpdm_allow_index'), 1); ?> name="__wpdm_allow_index" value="1"> <?php _e("Allow search engine to index attached files", "download-manager"); ?>
            </label><br/>
            <em><?php _e("Remove no-index from download header", "download-manager"); ?></em>
        </div>

        <hr/>

        <div class="form-group"><input type="hidden" name="__wpdm_mask_link" value="0">
            <label><input type="radio" <?php checked(get_option('__wpdm_mask_dlink', 1), 1); ?> name="__wpdm_mask_dlink"
                          value="1"> <?php _e("Mask Download Link", "download-manager"); ?> &nbsp; </label>
            <label><input type="radio" <?php checked(get_option('__wpdm_mask_dlink', 1), 0); ?> name="__wpdm_mask_dlink"
                          value="0"> <?php _e("Unmask Download Link", "download-manager"); ?></label><br/>
            <em><?php _e("Check this option if you want to mask/unmask file download link. If you unmask download link, bots will be able the find any public download link easily.", "download-manager"); ?></em>
        </div>
        <hr/>
        <div class="form-group"><input type="hidden" name="__wpdm_individual_file_download" value="0">
            <label><input type="radio" <?php checked(get_option('__wpdm_individual_file_download', 1), 1); ?>
                          name="__wpdm_individual_file_download"
                          value="1"> <?php _e("Enable Single File Download", "download-manager"); ?> &nbsp; </label>
            <label><input type="radio" <?php checked(get_option('__wpdm_individual_file_download', 1), 0); ?>
                          name="__wpdm_individual_file_download"
                          value="0"> <?php _e("Disable Single File Download", "download-manager"); ?></label><br/>
            <em><?php _e("Check this option if you want to enable/disable single file download from multi-file package", "download-manager"); ?></em>
        </div>
        <hr/>
        <div class="form-group"><input type="hidden" name="__wpdm_cache_zip" value="0">
            <label><input type="checkbox" <?php checked(get_option('__wpdm_cache_zip'), 1); ?> name="__wpdm_cache_zip"
                          value="1"> <?php _e("Cache created zip file from multi-file package", "download-manager"); ?>
            </label><br/>
            <em><?php _e("Check this option if you want to cache the zip file created from multi-file package when someone tries to download", "download-manager"); ?></em>
        </div>
        <hr/>

        <div class="form-group">
            <label><?php echo __("Download Speed:", "download-manager"); ?></label>
            <div class="input-group">
                <input type=text class="form-control" name="__wpdm_download_speed"
                       value="<?php echo intval(get_option('__wpdm_download_speed', 4096)); ?>"/>
                <span class="input-group-addon">KB/s</span>
            </div>
        </div>
        <hr/>
        <em class="note"><?php _e("If you get broken download, then try enabling/disabling following options, as sometimes server may not support output buffering or partial downloads", "download-manager"); ?>
            :</em>
        <hr/>

        <div class="row">
            <div class="col-md-6 form-group">
                <label><?php _e("Resumable Downloads", "download-manager"); ?></label><br/>
                <select name="__wpdm_download_resume" style="width: 100%;min-width: 100%">
                    <option value="1"><?php _e("Enabled", "download-manager"); ?></option>
                    <option value="2" <?php selected(get_option('__wpdm_download_resume'), 2); ?>><?php _e("Disabled", "download-manager"); ?></option>
                </select>
            </div>
            <div class="col-md-6 form-group">
                <label><?php _e("Output Buffering", "download-manager"); ?></label><br/>
                <select name="__wpdm_support_output_buffer" style="width: 100%;min-width: 100%">
                    <option value="1"><?php _e("Enabled", "download-manager"); ?></option>
                    <option value="0" <?php selected(get_option('__wpdm_support_output_buffer'), 0); ?>><?php _e("Disabled", "download-manager"); ?></option>
                </select>
            </div>
        </div>
        <hr/>
        <div class="row">
            <div class="col-md-6">
                <input type="hidden" value="0" name="__wpdm_open_in_browser"/>
                <label><input
                            type="checkbox" <?php checked(get_option('__wpdm_open_in_browser'), 1); ?> value="1"
                            name="__wpdm_open_in_browser"> <?php _e("Open in browser", "download-manager"); ?></label><br/>
                <em><?php _e("Try to open in browser instead of download when someone clicks on download link", "download-manager"); ?></em>
            </div>
            <div class="col-md-6">
                <input type="hidden" value="0" name="__wpdm_open_in_new_window"/>
                <label><input
                            type="checkbox" <?php checked(get_option('__wpdm_open_in_new_window'), 1); ?> value="1"
                            name="__wpdm_open_in_new_window"> <?php _e("Open in new window", "download-manager"); ?></label><br/>
                <em><?php _e("Try to open in new window/tab instead when someone clicks on a download link", "download-manager"); ?></em>
            </div>
        </div>
        <hr/>
        <div class="form-group">
            <hr/>
            <input type="hidden" value="0" name="__wpdm_mdl_off"/>
            <label><input
                        type="checkbox" <?php checked(get_option('__wpdm_mdl_off'), 1); ?> value="1"
                        name="__wpdm_mdl_off"><?php _e("Disable Master Download URL", "download-manager"); ?></label><br/>
            <em><?php _e("Enabling this option will disable any old and new master download link", "download-manager"); ?></em>
            <br/>

        </div>
        <hr/>
        <div class="form-group">
            <label><?php _e("Skip Lock for Loggedin User:", "download-manager"); ?></label><br/>
            <select style="width: 100%" name="__wpdm_skip_locks[]" multiple="multiple"
                    data-placeholder="<?php _e("Select...", "download-manager"); ?>">
                <option value="password" <?php if (in_array('password', maybe_unserialize(get_option('__wpdm_skip_locks', array())))) echo 'selected=selected'; ?>>
                    Password
                </option>
                <option value="email" <?php if (in_array('email', maybe_unserialize(get_option('__wpdm_skip_locks', array())))) echo 'selected=selected'; ?>>
                    Email
                </option>
                <option value="facebooklike" <?php if (in_array('facebooklike', maybe_unserialize(get_option('__wpdm_skip_locks', array())))) echo 'selected=selected'; ?>>
                    Facebook Like
                </option>
                <option value="linkedin" <?php if (in_array('linkedin', maybe_unserialize(get_option('__wpdm_skip_locks', array())))) echo 'selected=selected'; ?>>
                    Linkedin Share
                </option>
                <option value="gplusone" <?php if (in_array('gplusone', maybe_unserialize(get_option('__wpdm_skip_locks', array())))) echo 'selected=selected'; ?>>
                    Google Connect
                </option>
                <option value="tweet" <?php if (in_array('tweet', maybe_unserialize(get_option('__wpdm_skip_locks', array())))) echo 'selected=selected'; ?>>
                    Tweet
                </option>
                <option value="follow" <?php if (in_array('follow', maybe_unserialize(get_option('__wpdm_skip_locks', array())))) echo 'selected=selected'; ?>>
                    Twitter Follow
                </option>
            </select>

        </div>
        <div class="form-group">
            <hr/>
            <input type="hidden" value="0" name="__wpdm_email_lock_session"/>
            <label><input
                        type="checkbox" <?php checked(get_option('__wpdm_email_lock_session'), 1); ?> value="1"
                        name="__wpdm_email_lock_session"> <?php _e("Ask for email only once in a session for email locked downloads", "download-manager"); ?></label>
        </div>
    </div>
</div>


<div class="panel panel-default">
    <div class="panel-heading"><?php _e("Misc Settings", "download-manager"); ?></div>
    <div class="panel-body">


        <table cellpadding="5" cellspacing="0" class="frm" width="100%">

            <?php do_action('basic_settings'); ?>

        </table>

    </div>


</div>

<?php do_action('basic_settings_section');  ?>
<?php do_action('general_settings_section'); ?>

<script>
    jQuery(function ($) {
        $('body').on('click', '#wpdmap', function () {
            if ($(this).is(':checked'))
                $('#aps').removeAttr('disabled');
            else
                $('#aps').attr('disabled', 'disabled');
        });
        $('body').on('click', '#fdurl', function () {
            if ($(this).is(':checked'))
                $('#dlub').slideDown();
            else
                $('#dlub').slideUp();
        });

    });
</script>
<style>
    .w3eden textarea.form-control {
        min-width: 100%;
        max-width: 100%;
        width: 100%;
        height: 70px;
    }
</style>
