<?php


namespace WPDM\Settings;


use WPDM\__\__;
use WPDM\Form\Field;

class CustomControls extends __
{
    function messageLimits($attrs)
    {
        ob_start();
        include __DIR__.'/views/message-limits.php';
        return ob_get_clean();

    }

    function dimension($attrs)
    {
        $html = "<div class='row'><div class='col-md-6'><div class='input-group'>";
        $html .= Field::number(['name' => $attrs['name']."[width]", 'id' => $attrs['id']."_width", 'value' => $attrs['value']['width'], 'placeholder' => __('Width', PM_TEXT_DOMAIN)]);
        $html .= "<div class='input-group-append'><div class='input-group-text'>px</div></div></div></div><div class='col-md-6'><div class='input-group'>";
        $html .= Field::number(['name' => $attrs['name']."[height]", 'id' => $attrs['id']."_height", 'value' => $attrs['value']['height'], 'placeholder' => __('Height', PM_TEXT_DOMAIN)]);
        $html .= "<div class='input-group-append'><div class='input-group-text'>px</div></div></div></div></div>";

        return $html;
    }

    function position($attrs)
    {
        $html = "<div class='row'><div class='col-md-4'><div class='input-group'>";
        $html .= Field::number(['name' => $attrs['name']."[h]", 'id' => $attrs['id']."_h", 'value' => self::valueof($attrs, 'value/h'), 'placeholder' => __('Horizontal Space', PM_TEXT_DOMAIN)]);
        $html .= "<div class='input-group-append'><div class='input-group-text'>px</div></div></div></div><div class='col-md-4'><div class='input-group'>";
        $html .= Field::number(['name' => $attrs['name']."[v]", 'id' => $attrs['id']."_v", 'value' => self::valueof($attrs, 'value/v'), 'placeholder' => __('Vertical Space', PM_TEXT_DOMAIN)]);
        $html .= "<div class='input-group-append'><div class='input-group-text'>px</div></div></div></div><div class='col-md-4'><div class='input-group'>";
        $html .= Field::select(['name' => $attrs['name']."[c]", 'id' => $attrs['id']."_c", 'options' => ['left' => esc_attr__( 'Left', PM_TEXT_DOMAIN ), 'right' => esc_attr__( 'Right', PM_TEXT_DOMAIN )]], self::valueof($attrs, 'value/c'));
        $html .= "<div class='input-group-append'><div class='input-group-text'>Side</div></div></div></div></div>";

        return $html;
    }

    function chatTemplates($attrs)
    {

        $chatbox_template_dirs = array(
            PM_BASE_DIR.'/src/Chat/views/chatbox',
            get_template_directory().'/private-message/chatbox',
            get_stylesheet_directory().'/private-message/chatbox',

        );
        $chatbox_styles = array();
        $__templates = array();
        foreach ($chatbox_template_dirs as $chatbox_template_dir){
            if(file_exists($chatbox_template_dir)) {
                $_templates = scandir($chatbox_template_dir);
                foreach ($_templates as $template) {
                    if (strstr($template, '.php')) {
                        $__templates[$template] = $chatbox_template_dir . '/' . $template;
                    }
                }
            }
        }

        foreach ($__templates as $file => $path){
            $content = file_get_contents($path);
            preg_match_all("/\#\#\#([^\#]+)\#\#\#/", $content, $matched);
            if(isset($matched[1], $matched[1][0])) {
                $name = trim($matched[1][0]);
                $chatbox_styles[$file] = $name;
            }
        }

        $chatbox_styles = apply_filters("wpdmpm_chatbox_styles", $chatbox_styles);
        ob_start();
        ?>
        <select name="<?= __::valueof($attrs, 'name'); ?>" class="form-control">
            <?php foreach ($chatbox_styles as $file => $name){ ?>
                <option value="<?php echo $file; ?>" <?php selected($file, __::valueof($attrs, 'selected')) ?>><?php echo $name; ?></option>
            <?php } ?>
        </select>
        <?php
        return ob_get_clean();
    }

    function emailTemplates($attrs)
    {

        $email_template_dirs = array(
            PM_BASE_DIR.'src/__/views/email-templates',
            get_template_directory().'/private-message/email-templates',
            get_stylesheet_directory().'/private-message/email-templates',

        );
        $email_tempalte = [];
        $__templates = array();
        foreach ($email_template_dirs as $email_template_dir){
            if(file_exists($email_template_dir)) {
                $_templates = scandir($email_template_dir);
                foreach ($_templates as $template) {
                    if (strstr($template, '.html')) {
                        $__templates[$template] = $email_template_dir . '/' . $template;
                    }
                }
            }
        }

        foreach ($__templates as $file => $path){
            $content = file_get_contents($path);
            preg_match_all("/<!--([\s]*)TemplateName:([\s]*)([^-->]+)-->/", $content, $matched);
            if(isset($matched[1], $matched[3][0])) {
                $name = trim($matched[3][0]);
                $email_tempalte[$file] = $name;
            }
        }

        $chatbox_styles = apply_filters("pm_email_template", $email_tempalte);
        ob_start();
        ?>
        <select name="<?= __::valueof($attrs, 'name'); ?>" class="form-control">
            <?php foreach ($chatbox_styles as $file => $name){ ?>
                <option value="<?php echo $file; ?>" <?php selected($file, __::valueof($attrs, 'selected')) ?>><?php echo $name; ?></option>
            <?php } ?>
        </select>
        <?php
        return ob_get_clean();
    }

    function keyFileProcessor($attrs)
    {
        ob_start();
        if(__::valueof($attrs, 'value')) {
            $filedata = json_decode(get_option('__cakeyfile'), true);
            echo "<div class='card' style='margin-bottom: 15px;margin-top: 0'><div class='card-body p-0'><table class='table table-striped'><thead style='background: #f8f8f8'><tr><th>Key</th><th>Value</th></tr></thead>";
            foreach ($filedata as $key => $value) {
                if($key !== 'private_key')
                    echo "<tr><td>{$key}</td><td>{$value}</td></tr>";
            }
            echo "</table></div></div>";

        }
        ?>
        <div class="card" style="margin-bottom: 15px">
            <div class="card-body">
                <a style="padding: 5px 20px;font-size: 13px" target="_blank" class="btn btn-info" href="https://console.cloud.google.com/apis/api/dialogflow.googleapis.com/overview"><?php _e('Enable Dialogflow API', PM_TEXT_DOMAIN); ?></a>
                <a style="padding: 5px 20px;font-size: 13px" target="_blank" class="btn btn-primary" href="https://console.cloud.google.com/home/dashboard"><?php _e('Generate Key File', PM_TEXT_DOMAIN); ?></a>
            </div>
        </div>
         <textarea placeholder="<?php echo $attrs['placeholder']; ?>" name="<?php echo $attrs['name']; ?>" id="<?php echo $attrs['id']; ?>" class="form-control"></textarea>
        <?php
        return ob_get_clean();
    }

    function saveKeyData($value, $field)
    {
        if($_REQUEST[__::valueof($field, 'attrs/name')] !== '' && is_object(json_decode(stripslashes($_REQUEST[__::valueof($field, 'attrs/name')]))))
            update_option(__::valueof($field, 'attrs/name'), stripslashes($_REQUEST[__::valueof($field, 'attrs/name')]));
    }

    function roleSelector($attrs)
    {
        global $wp_roles;
        $roles = $wp_roles->get_names();
        ob_start();
        ?>
        <label class="d-block"><?= self::valueof($attrs, 'title'); ?></label>
        <select name="<?= $attrs['name']; ?>[]" class="chosen" style="width: 100%" multiple="multiple">
        <option <?php selected(true, in_array('guest', self::valueof($attrs, 'selected'))) ?> value="guest">Everyone</option>
        <?php
        foreach($roles as $id => $name){ ?>
            <option value="<?php echo $id; ?>" <?php selected(in_array($id, self::valueof($attrs, 'selected')), true) ?>><?php echo $name; ?></option>
        <?php }
        echo "</select>";
        return ob_get_clean();
    }

}
