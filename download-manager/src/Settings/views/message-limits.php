<?php
if(!defined("ABSPATH")) die("Shit happens!");

global $wp_roles;
$roles = $wp_roles->get_names();
?>
<table class="table table-striped">
    <thead>
    <tr>
        <th><?php _e( "User Role", PM_TEXT_DOMAIN ) ?></th>
        <th style="width: 100px"><?php _e( "Limit", PM_TEXT_DOMAIN ) ?></th>
    </tr>
    </thead>
    <?php foreach($roles as $id=>$name){ ?>
        <tr><td><?php echo $name; ?></td><td><input type="number" class="form-control input-sm" value="<?php echo \PrivateMessage\__\__::valueof($attrs, 'value/'.$id); ?>" name="<?= $attrs['name']; ?>[<?php echo $id; ?>]" /></td></tr>
    <?php } ?>
</table>
<style>
    td{ vertical-align: middle !important; }
    #row_pm_limit .card-body {
        padding: 0 !important;
    }
    #row_pm_limit .form-control{
        padding: 0 10px;
        line-height: 32px;
        height: 32px;
        font-size: 10pt;
        font-weight: 600;
    }
</style>
