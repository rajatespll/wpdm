<?php

namespace WPDM\Settings;

use WPDM\Form\Form;

if(!defined("ABSPATH")) die("Shit happens!");


class General extends Section
{

    public $pm_to_admin;
    public $pm_to_user;
    public $fe_delete_message;
    public $fe_allow_file;
    public $pm_admin_email;
    public $pm_check_interval;
    public $pm_page_id;
    public $pm_reg_page_id;
    public $pm_login_page_id;
    public $chat_page_id;
    public $pm_limit;
    public $chat_active;
    public $chat_allowed_roles = ['guest'];
    public $chat_active_fb;
    public $multiline_msg;
    public $greeting_visitor;
    public $greeting_member;
    public $anonymous_chat;
    public $admin_avatar = 'https://cdn4.iconfinder.com/data/icons/famous-character-vol-2-flat/48/Avatar_Famous_Characters-08-512.png';
    public $visitor_avatar = 'https://cdn0.iconfinder.com/data/icons/user-pictures/100/unknown_1-2-512.png';
    public $chatbox_size = [ 'width' => 400, 'height' => 600 ];
    public $chatbox_pos = [ 'h' => 20, 'v' => 20, 'c' => 'right' ];
    public $chat_icon = PM_ASSET_URL.'images/messenger.svg';
    public $pm_wpdm_dashboard = false;
    public $pm_file_types = 'png,pdf,jpg,jpeg,doc,ppt,docx,txt';
	public $pm_file_size = 4;
    function __construct()
    {
        $this->section = 'general';
        $this->title = __('General', PM_TEXT_DOMAIN);
        $this->icon = 'fa fa-cog';
        $this->settings_groups = [
            'pm_send_settings'      => __('Basic Settings', PM_TEXT_DOMAIN),
            'pm_permission_settings'=> __('Permissions', PM_TEXT_DOMAIN),
            'pm_limit'              => __('Message Limits', PM_TEXT_DOMAIN),
            'chat'                  => __('Live Chat Settings', PM_TEXT_DOMAIN),
            'pm_ui_settings'        => __('UI Settings', PM_TEXT_DOMAIN),
        ];

	    $this->settings_fields['__pmadminemail'] = ['group' => 'pm_send_settings', 'type' => 'email', 'label' => __('Admin email to receive private message', PM_TEXT_DOMAIN), 'attrs' => [ 'name' => '__pmadminemail', 'id' => '__pmadminemail', 'class' => 'form-control', 'value' => ($this->pm_admin_email = get_option('__pmadminemail', '')), 'placeholder' => __("Enter a valid email address", PM_TEXT_DOMAIN)]];
        $this->settings_fields['__pmcheckinterval'] = ['group' => 'pm_send_settings', 'type' => 'number', 'label' => __('New message check interval', PM_TEXT_DOMAIN), 'attrs' => [ 'name' => '__pmcheckinterval', 'id' => '__pmcheckinterval', 'class' => 'form-control', 'value' => ($this->pm_check_interval = get_option('__pmcheckinterval', '')), 'placeholder' => __("Seconds", PM_TEXT_DOMAIN)]];
        $this->settings_fields['__pmpage'] = ['group' => 'pm_send_settings', 'type' => 'custom', 'custom_control' => 'wp_dropdown_pages', 'label' => __('Private message page', PM_TEXT_DOMAIN), 'attrs' => [ 'name' => '__pmpage', 'id' => '__pmpage', 'class' => 'form-control', 'selected' => ($this->pm_page_id = get_option('__pmpage', '')), 'echo' => 0]];
        $this->settings_fields['__pmregpage'] = ['group' => 'pm_send_settings', 'type' => 'custom', 'custom_control' => 'wp_dropdown_pages', 'label' => __('Signup page', PM_TEXT_DOMAIN), 'attrs' => [ 'name' => '__pmregpage', 'id' => '__pmregpage', 'class' => 'form-control', 'selected' => ($this->pm_reg_page_id = get_option('__pmregpage', '')), 'echo' => 0]];
        $this->settings_fields['__pmloginpage'] = ['group' => 'pm_send_settings', 'type' => 'custom', 'custom_control' => 'wp_dropdown_pages', 'label' => __('Signin page', PM_TEXT_DOMAIN), 'attrs' => [ 'name' => '__pmloginpage', 'id' => '__pmloginpage', 'class' => 'form-control', 'selected' => ($this->pm_login_page_id = get_option('__pmloginpage', '')), 'echo' => 0]];

	    $this->settings_fields['__pmtoadmin'] = ['group' => 'pm_permission_settings', 'type' => 'checkbox', 'attrs' => [ 'name' => '__pmtoadmin', 'id' => '__pmtoadmin', 'title' => __('Allow members to send message to site admin', PM_TEXT_DOMAIN), 'class' => 'wppmsg-custom-check', 'value' => 1, 'checked' => ($this->pm_to_admin = (int)get_option('__pmtoadmin', 0))]];
	    $this->settings_fields['__pmtouser'] = ['group' => 'pm_permission_settings', 'type' => 'checkbox', 'attrs' => [ 'name' => '__pmtouser', 'id' => '__pmtouser', 'title' => __('Allow members to send message to other site members', PM_TEXT_DOMAIN), 'class' => 'wppmsg-custom-check', 'value' => 1, 'checked' => ($this->pm_to_user = (int)get_option('__pmtouser', 0))]];
	    $this->settings_fields['__fedeletemessage'] = ['group' => 'pm_permission_settings', 'type' => 'checkbox', 'attrs' => [ 'name' => '__fedeletemessage', 'id' => '__fedeletemessage', 'title' => __('Allow members to delete thread', PM_TEXT_DOMAIN), 'class' => 'wppmsg-custom-check', 'value' => 1, 'checked' => ($this->fe_delete_message = (int)get_option('__fedeletemessage', 0))]];
	    $this->settings_fields['__feallowfile'] = ['group' => 'pm_permission_settings', 'type' => 'checkbox', 'attrs' => [ 'name' => '__feallowfile', 'id' => '__feallowfile', 'title' => __('Allow members to attach files', PM_TEXT_DOMAIN), 'class' => 'wppmsg-custom-check', 'value' => 1, 'checked' => ($this->fe_allow_file = (int)get_option('__feallowfile', 0))]];
	    $this->settings_fields['__pmfiletypes'] = ['group' => 'pm_permission_settings', 'type' => 'text', 'label' => __('Allowed file types for attachment', PM_TEXT_DOMAIN), 'attrs' => [ 'name' => '__pmfiletypes', 'id' => '__pmfiletypes', 'class' => 'form-control', 'value' => $this->pm_file_types = get_option('__pmfiletypes', 'png,pdf,jpg,jpeg,doc,ppt,docx,txt'), 'placeholder' => esc_attr__( 'Allowed file type ( ex: pdf,png,jpg )', PM_TEXT_DOMAIN )]];
	    $this->settings_fields['__pmmaxfilesize'] = ['group' => 'pm_permission_settings', 'type' => 'number', 'label' => __('Maximum file size in MegaBytes(MB)', PM_TEXT_DOMAIN), 'attrs' => [ 'name' => '__pmmaxfilesize', 'id' => '__pmmaxfilesize', 'class' => 'form-control', 'value' => $this->pm_file_size = get_option('__pmmaxfilesize', '4'), ]];


	    //WPDM user dashboard menu
        if(function_exists('WPDM')) {
            $this->settings_groups['pm_wpdm_settings'] = __('Download Manager Settings', PM_TEXT_DOMAIN);
            $this->settings_fields['__pmwpdmdashboard'] = ['group' => 'pm_wpdm_settings', 'type' => 'checkbox', 'attrs' => [ 'name' => '__pmwpdmdashboard', 'id' => '__pmwpdmdashboard', 'title' => __('Show private message option with user dashboard', PM_TEXT_DOMAIN), 'class' => 'wppmsg-custom-check', 'value' => 1, 'checked' => ($this->pm_wpdm_dashboard = (int)get_option('__pmwpdmdashboard', 0))]];
        }

        //Message limit for user roles
        $this->settings_fields['__pmlimit'] = ['group' => 'pm_limit', 'type' => 'custom', 'custom_control' => [ new CustomControls(), 'messageLimits'], 'attrs' => [ 'name' => '__pmlimit', 'id' => '__pmlimit', 'class' => 'form-control', 'value' => ($this->pm_limit = get_option('__pmlimit', '')), 'placeholder' => __("Seconds", PM_TEXT_DOMAIN)]];

        $button_text = get_option( "wppm_fbcpageID" ) === ""  ?__( "Setup Facebook Integration" ):__( "Edit Facebook Integration" );

        //Chat settings
        $this->settings_fields['__chatactive'] = ['group' => 'chat', 'note' => __('Shows chat bubble on bottom right corner of your site', PM_TEXT_DOMAIN), 'type' => 'checkbox', 'attrs' => [ 'name' => '__chatactive', 'id' => '__chatactive', 'title' => __('Enable live chat box', PM_TEXT_DOMAIN), 'class' => 'wppmsg-custom-check', 'value' => 1, 'checked' => ($this->chat_active = get_option('__chatactive', ''))]];
        $this->settings_fields['__chatactivefb'] = ['group' => 'chat', 'note' => __('<button onclick="wppm_fbc_setupCustomerChat()" class="btn btn-primary wppm-fbc_setupButton" style="font-size: 11px;box-shadow: none" type="button">'. $button_text.'</button>', PM_TEXT_DOMAIN), 'type' => 'checkbox', 'attrs' => [ 'name' => '__chatactivefb', 'id' => '__chatactivefb', 'title' => __('Enable facebook chat', PM_TEXT_DOMAIN), 'class' => 'wppmsg-custom-check', 'value' => 1, 'checked' => ($this->chat_active_fb = get_option('__chatactivefb', ''))]];
        $this->settings_fields['__multiline_msg'] = ['group' => 'chat', 'note' => __('Press enter to sent message, Press Shift + Enter for new line', PM_TEXT_DOMAIN), 'type' => 'checkbox', 'attrs' => [ 'name' => '__multiline_msg', 'id' => '__multiline_msg', 'title' => __('Enable multi-line message', PM_TEXT_DOMAIN), 'class' => 'wppmsg-custom-check', 'value' => 1, 'checked' => ($this->multiline_msg = get_option('__multiline_msg', 0))]];
        $this->settings_fields['__allowed_roles'] = ['group' => 'chat', 'note' => __('Who should have access to live chat', PM_TEXT_DOMAIN), 'type' => 'custom', 'custom_control' => [new CustomControls(), 'roleSelector'], 'attrs' => [ 'name' => '__allowed_roles', 'id' => '__allowed_roles', 'title' => __('Allowed roles/users', PM_TEXT_DOMAIN), 'selected' => ($this->chat_allowed_roles = maybe_unserialize(get_option('__allowed_roles', ['guest'])))]];
        $this->settings_fields['__anonymous_chat'] = ['group' => 'chat', 'note' => __('Visitor does not need to give their email', PM_TEXT_DOMAIN), 'type' => 'checkbox', 'attrs' => [ 'name' => '__anonymous_chat', 'id' => '__anonymous_chat', 'title' => __('Allow visitors to chat anonymously', PM_TEXT_DOMAIN), 'class' => 'wppmsg-custom-check', 'value' => 1, 'checked' => ($this->anonymous_chat = get_option('__anonymous_chat', 0))]];
        $this->settings_fields['__chatboxstyle'] = ['group' => 'chat', 'type' => 'custom', 'custom_control' => [ new CustomControls(), 'chatTemplates'], 'label' => __('Chatbox Style', PM_TEXT_DOMAIN), 'attrs' => [ 'name' => '__chatboxstyle', 'id' => '__chatboxstyle', 'class' => 'form-control', 'selected' => get_option('__chatboxstyle', '')]];
        $this->settings_fields['__chatpage'] = ['group' => 'chat', 'type' => 'custom', 'custom_control' => 'wp_dropdown_pages', 'label' => __('Chat Page', PM_TEXT_DOMAIN), 'note' => __('The page where you have used the shortcode <code>[live_chat]</code>, if you are not using the shortcode, do not select any page', PM_TEXT_DOMAIN), 'attrs' => [ 'name' => '__chatpage', 'id' => '__chatpage', 'class' => 'form-control', 'selected' => ($this->chat_page_id = get_option('__chatpage', '')), 'echo' => 0, 'show_option_none' => 'Not using chat shortcode']];
        $this->settings_fields['__greeting_visitor'] = ['group' => 'chat', 'type' => 'text', 'label' => __('Greeting message for visitors', PM_TEXT_DOMAIN), 'attrs' => [ 'name' => '__greeting_visitor', 'id' => '__greeting_visitor', 'class' => 'form-control', 'value' => ($this->greeting_visitor = get_option('__greeting_visitor', 'Please give me your email address in case we disconnect in the middle somehow')), 'placeholder' => __("Greeting message for visitors", PM_TEXT_DOMAIN)]];
        $this->settings_fields['__greeting_member'] = ['group' => 'chat', 'type' => 'text', 'label' => __('Greeting message for members', PM_TEXT_DOMAIN), 'note' => __('User <code>{name}</code> to mention members name', PM_TEXT_DOMAIN), 'attrs' => [ 'name' => '__greeting_member', 'id' => '__greeting_member', 'class' => 'form-control', 'value' => ($this->greeting_member = get_option('__greeting_member', 'Hello {name}, welcome back. How may I help you today?')), 'placeholder' => __("Greeting message for members", PM_TEXT_DOMAIN)]];

        $this->settings_fields['__chat_icon'] = ['group' => 'chat', 'type' => 'meidapicker', 'label' => __('Chat Icon', PM_TEXT_DOMAIN), 'attrs' => [ 'name' => '__chat_icon', 'id' => '__chat_icon',  'class' => 'form-control', 'value' => ($this->chat_icon = get_option('__chat_icon', PM_ASSET_URL.'images/messenger.svg'))]];
        $this->chat_icon = $this->chat_icon ?: PM_ASSET_URL.'images/messenger.svg';

        $this->settings_fields['__admin_avatar'] = ['group' => 'chat', 'type' => 'meidapicker', 'label' => __('Default Avatar For Admin', PM_TEXT_DOMAIN), 'attrs' => [ 'name' => '__admin_avatar', 'id' => '__admin_avatar',  'class' => 'form-control', 'value' => ($this->admin_avatar = get_option('__admin_avatar', 'https://cdn4.iconfinder.com/data/icons/famous-character-vol-2-flat/48/Avatar_Famous_Characters-08-512.png'))]];
        $this->settings_fields['__visitor_avatar'] = ['group' => 'chat', 'type' => 'meidapicker', 'label' => __('Default Avatar For Visitor', PM_TEXT_DOMAIN), 'attrs' => [ 'name' => '__visitor_avatar', 'id' => '__visitor_avatar',  'class' => 'form-control', 'value' => ($this->visitor_avatar = get_option('__visitor_avatar', 'https://cdn0.iconfinder.com/data/icons/user-pictures/100/unknown_1-2-512.png'))]];
        $this->settings_fields['__chatbox_size'] = ['group' => 'chat', 'type' => 'custom', 'custom_control' => [ new CustomControls(), 'dimension'], 'label' => __('Chat box with and height', PM_TEXT_DOMAIN), 'attrs' => [ 'name' => '__chatbox_size', 'id' => '__chatbox_size',  'class' => 'form-control', 'value' => ($this->chatbox_size = get_option('__chatbox_size', ['width' => 450, 'height' => 600]))]];
        $this->settings_fields['__chatbox_pos'] = ['group' => 'chat', 'type' => 'custom', 'custom_control' => [ new CustomControls(), 'position'], 'label' => __('Chat box position', PM_TEXT_DOMAIN), 'attrs' => [ 'name' => '__chatbox_pos', 'id' => '__chatbox_pos',  'class' => 'form-control', 'value' => ($this->chatbox_pos = get_option('__chatbox_pos', ['h' => 20, 'v' => 20, 'c' => 'right']))]];



        $this->settings_fields['__pmjsfix'] = ['group' => 'pm_ui_settings', 'type' => 'checkbox', 'attrs' => [ 'name' => '__pmjsfix', 'id' => '__pmjsfix', 'title' => __('Disable bootstrap.js from frontend', PM_TEXT_DOMAIN), 'class' => 'wppmsg-custom-check', 'value' => 1, 'checked' => get_option('__pmjsfix', '')]];
        $this->settings_fields['__pmcssfix'] = ['group' => 'pm_ui_settings', 'type' => 'checkbox', 'attrs' => [ 'name' => '__pmcssfix', 'id' => '__pmcssfix', 'title' => __('Disable bootstrap.css from frontend', PM_TEXT_DOMAIN), 'class' => 'wppmsg-custom-check', 'value' => 1, 'checked' => get_option('__pmcssfix', '')]];
        $this->settings_fields['__pmfafix'] = ['group' => 'pm_ui_settings', 'type' => 'checkbox', 'attrs' => [ 'name' => '__pmfafix', 'id' => '__pmfafix', 'title' => __('Disable FontAwesome from frontend', PM_TEXT_DOMAIN), 'class' => 'wppmsg-custom-check', 'value' => 1, 'checked' => get_option('__pmfafix', '')]];


    }
}
