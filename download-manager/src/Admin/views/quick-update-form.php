<?php
if(!defined('ABSPATH')) die('Dream more!');
?>
<style>
    #all-package-settings .select2{
        width: 100% !important;
    }
</style>
<div class="w3eden" id="all-package-settings">
<div class="panel panel-default">
    <div class="panel-body">
        <h3><?= $post->post_title ?></h3>
    </div>
</div>
<form method="post" id="wpdm-quick-update-form">
    <?php wp_nonce_field(WPDM_PRI_NONCE, '__wpdmqun') ?>
    <input type="hidden" name="action" value="wpdm_quick_update">
    <input type="hidden" name="pid" value="<?= $post->ID ?>">
		<div id="package-settings">
			<table cellpadding="5" id="file_settings_table" cellspacing="0" width="100%" class="table table-bordered table-hover">
				<tr id="access_row">
					<td valign="top"><?php echo __( "Allow Access:" , "download-manager" ); ?></td>
					<td>
						<?php
						global $wp_roles;
						$roles = array_reverse($wp_roles->role_names);
						$currentAccess = get_post_meta($post->ID, '__wpdm_access', true);
						$currentAccess = maybe_unserialize($currentAccess);
						?>
						<select name="file[access][]"  data-placeholder="<?php _e( "Who should be able to download?" , "download-manager" ); ?>"  class="chzn-select role" multiple="multiple" id="access" style="max-width: 100%;min-width: 100%;width: 100%">
							<?php
							$selz = '';
							if(  is_array($currentAccess) ) $selz = (in_array('guest',$currentAccess))?'selected=selected':'';
							if(!isset($_GET['post']) && !$currentAccess) $selz = 'selected=selected';
							?>

							<option value="guest" <?php echo $selz  ?>><?php echo __( "All Visitors" , "download-manager" ); ?></option>
							<?php
							foreach( $roles as $role => $name ) {



								if(  is_array($currentAccess) ) $sel = (in_array($role,$currentAccess))?'selected=selected':'';
								else $sel = '';



								?>
								<option value="<?php echo $role; ?>" <?php echo $sel  ?>> <?php echo $name; ?></option>
							<?php } ?>
						</select>
					</td></tr>



				<tr id="template_row">
					<td><?php echo __( "Link Template:" , "download-manager" ); ?></td>
					<td><?php

						echo WPDM()->packageTemplate->dropdown(array('type'=>'link','name' => 'file[template]', 'id'=>'lnk_tpl', 'selected' => get_post_meta($post->ID,'__wpdm_template',true)), true);

						?>

					</td>
				</tr>


				<tr id="page_template_row">
					<td><?php echo __( "Page Template:" , "download-manager" ); ?></td>
					<td><?php
						echo WPDM()->packageTemplate->dropdown(array('type'=>'page','name' => 'file[page_template]', 'id'=>'pge_tpl', 'selected' => get_post_meta($post->ID,'__wpdm_page_template',true)), true);
						?>

					</td>
				</tr>
				<?php if(isset($_GET['post'])&&$_GET['post']!=''){ ?>
					<tr>
						<td><?php echo __( "Master Key" , "download-manager" ); ?></td>
						<td><input class="form-control" style="font-family: monospace;letter-spacing: 1px" type="text" readonly="readonly" value="<?php echo get_post_meta($post->ID, '__wpdm_masterkey', true); ?>"> <label class="fw-4"><input type="checkbox" value="1" name="reset_key" /> <?php echo __( "Regenerate Master Key for Download" , "download-manager" ); ?></label> <i class="info fa-solid fa-circle-info" data-placement="top" title="<?php echo __( "This key can be used for direct download" , "download-manager" ); ?>"></i></td>
					</tr>
				<?php } ?>
				<?php do_action("wpdm_package_settings_tr", $post->ID); ?>
			</table>
			<div class="clear"></div>
		</div>
    <button class="btn btn-primary"><?= __('Save Changes', WPDM_TEXT_DOMAIN) ?></button>
</form>


</div>








<!-- all js ------>

<script type="text/javascript">

    jQuery(function($) {

        $("#all-package-settings select").select2({no_results_text: "", width: "50%", minimumResultsForSearch: 6});

        $('#wpdm-quick-update-form').on('submit', function (e) {
            e.preventDefault();
            WPDM.blockUI('#all-package-settings');
            $(this).ajaxSubmit({
                url: ajaxurl,
                success: function (res) {
                    WPDM.unblockUI('#all-package-settings');
                }

            })
        });

    });



</script>



