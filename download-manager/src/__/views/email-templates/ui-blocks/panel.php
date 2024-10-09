<?php
/**
 * User: shahnuralam
 * Date: 17/11/18
 * Time: 1:09 AM
 */
if (!defined('ABSPATH')) die();

?>
<div class="uicard" style="border:1px solid #c9d1e4;border-radius: 4px;overflow: hidden;">
    <table style="border: 0;width: 100%;padding: 0;margin: 0" cellpadding="0" cellspacing="0">
    <?php if(isset($heading) && $heading != ''){ ?>
    <tr><th style="text-align: left;border-bottom: 1px solid #c9d1e4;background: #ffffff;padding: 10px 15px;font-size: 14pt;font-weight: bold;letter-spacing: 0.5px;line-height: 1.5"><?php echo $heading; ?></th></tr>
    <?php } ?>
    <?php if(isset($content) && is_array($content) && count($content) > 0){
        foreach ($content as $html) {
            if(trim($html) !== '') {
        ?>
        <tr><td style="<?php if(isset($footer) && $footer !== ''){ ?>border-bottom: 1px solid #c9d1e4;<?php } ?>background: #ffffff;padding: 10px 15px;letter-spacing: 0.5px;line-height: 1.5"><?php echo wpautop($html); ?></td></tr>
    <?php }
        }
    } ?>
    <?php if(isset($footer) && !empty(trim($footer))){ ?>
        <tr><td style="background: #f7f9fd;padding: 10px 15px;"><?php echo $footer; ?></td></tr>
    <?php } ?>
    </table>
</div>
