<?php
if(!defined('ABSPATH')) die('Dream more!');
?>
<div class="panel panel-default">
	<div class="panel-heading"><?php echo __( "User Signup Settings" , "download-manager" ); ?></div>
	<div class="panel-body">
		<div class="form-group"><input type="hidden" name="__wpdm_signups_need_approval" value="0">
			<label><input type="checkbox" name="__wpdm_signups_need_approval" value="1" <?php checked(get_option('__wpdm_signups_need_approval', 0), 1); ?> > <?php echo __( "Keep new signups pending for admin review" , "download-manager" ); ?></label>
		</div>
        <div class="form-group">
            <label><?php _e('Message to show when signup request is pending', WPDM_TEXT_DOMAIN); ?>:</label>
            <input type="text" class="form-control" name="__wpdm_pending_approval_msg" placeholder="<?= esc_attr__('Message to show when pending user is trying to login'); ?>" value="<?= esc_attr(get_option('__wpdm_pending_approval_msg')); ?>">
        </div>

		<div class="form-group">
            <label><?php _e('Message to show when signup request is declined', WPDM_TEXT_DOMAIN); ?>:</label>
            <input type="text" class="form-control" name="__wpdm_declined_signup_msg" placeholder="<?= esc_attr__('Message to show when declined user is trying to login'); ?>" value="<?= esc_attr(get_option('__wpdm_declined_signup_msg', 'Your signup request was declined, you are not allowed to login!')); ?>">
        </div>

		<div class="form-group">
            <label><?php _e('Message to show when account is suspended', WPDM_TEXT_DOMAIN); ?>:</label>
            <input type="text" class="form-control" name="__wpdm_declined_signup_msg" placeholder="<?= esc_attr__('Message to show when suspended user is trying to login'); ?>" value="<?= esc_attr(get_option('__wpdm_suspended_acc_msg', 'Your account is suspended, you are not allowed to login!')); ?>">
        </div>

		<label><?php echo __( "Allowed Sign up Roles" , "download-manager" ); ?></label>
		<p class="note"><?php _e( "You can add role id with signup form shortcode as parameter or select role when using WPDM Gutenberg block for signup form and users signed up using that form will be assigned to that selected role. Here you can select the allowed role to use with the signup form", "download-manager" ) ?></p>
		<table class="table table-striped m-0">
			<thead>
			<tr>
				<th style="width: 20px"></th>
				<th>Role Name</th>
				<th align="right">Role ID</th>
			</tr>
			</thead>
			<tbody>
			<?php
			global $wp_roles;
			$roles = array_reverse($wp_roles->role_names);
			$signupRoles = get_option('__wpdm_signup_roles');
			if(!is_array($signupRoles)) $signupRoles = array();
			foreach( $roles as $role => $name ) {
				if($role !== 'administrator') {
					?>

					<tr>
						<td>
							<input id="__<?php echo $role; ?>" <?php checked(in_array($role, $signupRoles), 1) ?>
							       type="checkbox" name="__wpdm_signup_roles[]"
							       value="<?php echo $role; ?>"></td>
						<td width="250px"><label
								for="__<?php echo $role; ?>"><?php echo $name; ?></label></td>
						<td align="right"><input style="font-family: monospace;background: #ffffff"
						                         type="text" class="form-control input-sm"
						                         readonly="readonly" value="<?php echo $role; ?>"></td>
					</tr>
					<?php
				}
			} ?>
			</tbody>
		</table>
	</div>
</div>
