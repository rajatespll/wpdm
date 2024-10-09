<?php
/**
 * Base: wpdmpro
 * Developer: shahjada
 * Team: W3 Eden
 * Date: 2019-08-30 12:11
 */

if(!defined("ABSPATH")) die();
global $flatList;
$flatList = [];
function __print_items($items, $level = 0){
    global $flatList;
    foreach ($items as $index => $node) {
        $flatList[$index] = $node;
        if($node['type'] === 'dir') {
            $file = esc_attr(basename($node['path']));
            echo "<li class='tree-item level-{$level}' data-index='{$index}'>{$file}";
            if (count($node['items']) > 0) {
                echo "<ul>";
                __print_items($node['items'], $level+1);
                echo "</ul>";
            }
            echo "</li>";
        }
    }
}

?>
<script id='wpdm-frontjs-js-extra'>
    var wpdm_url = {"home":"<?= home_url('/') ?>","site":"<?= home_url('/') ?>","ajax":"<?= admin_url('/admin-ajax.php') ?>"};
    var wpdm_js = {"spinner":"<i class=\"fas fa-sun fa-spin\"><\/i>","client_id":"<?= \WPDM\__\Session::$deviceID ?>"};
</script>
<script src="<?= WPDM_ASSET_URL ?>js/front.js" ></script>
<script src="<?= WPDM_ASSET_URL ?>bootstrap/js/bootstrap.min.js" ></script>
<link rel="stylesheet" href="<?= WPDM_ASSET_URL ?>fontawesome/css/all.min.css">
<link rel="stylesheet" href="<?= WPDM_ASSET_URL ?>bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= WPDM_ASSET_URL ?>css/front.css">
<style>
    #asset-viewer{
        white-space: normal;
    }
    #dirview,
    #card,
    #explorer{
        min-width: 100%;
        min-height: 100px;
    }

    .container{
        display: flex;
        height: 100%;
    }

</style>

<div  class="w3eden">
    <div class="container mt-4">
        <?php echo do_shortcode("[wpdm_dir_view dir='{$this->path}']"); ?>
    </div>
</div>
